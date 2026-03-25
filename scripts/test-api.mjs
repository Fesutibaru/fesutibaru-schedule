#!/usr/bin/env node
/**
 * Fesutibaru Public API — diagnostic script.
 *
 * Tests the API endpoint directly (bypassing WordPress) and can trigger
 * cache revalidation to verify the full pipeline is working.
 *
 * Usage:
 *   node scripts/test-api.mjs                          # fetch events
 *   node scripts/test-api.mjs --endpoint venues        # fetch venues
 *   node scripts/test-api.mjs --endpoint people        # fetch people
 *   node scripts/test-api.mjs --revalidate             # trigger revalidation then fetch
 *   node scripts/test-api.mjs --full                   # revalidate, wait, fetch, compare
 *
 * Environment (set in scripts/.env or pass inline):
 *   API_BASE_URL          — e.g. https://demo.fesutibaru.com
 *   API_KEY               — Bearer token for the Public API
 *   REVALIDATION_SECRET   — (optional) secret for the revalidation webhook
 */

import { readFileSync } from 'fs'
import { resolve, dirname } from 'path'
import { fileURLToPath } from 'url'

const __dirname = dirname(fileURLToPath(import.meta.url))

// ---------------------------------------------------------------------------
// Load .env from scripts/.env (simple key=value, no dependencies needed)
// ---------------------------------------------------------------------------
function loadEnv() {
  try {
    const envPath = resolve(__dirname, '.env')
    const lines = readFileSync(envPath, 'utf-8').split('\n')
    for (const line of lines) {
      const trimmed = line.trim()
      if (!trimmed || trimmed.startsWith('#')) continue
      const eq = trimmed.indexOf('=')
      if (eq === -1) continue
      const key = trimmed.slice(0, eq).trim()
      const val = trimmed.slice(eq + 1).trim().replace(/^["']|["']$/g, '')
      if (!process.env[key]) process.env[key] = val
    }
  } catch {
    // No .env file — that's fine, use process env
  }
}

loadEnv()

// ---------------------------------------------------------------------------
// Config
// ---------------------------------------------------------------------------
const API_BASE_URL = process.env.API_BASE_URL?.replace(/\/$/, '')
const API_KEY = process.env.API_KEY
const REVALIDATION_SECRET = process.env.REVALIDATION_SECRET

if (!API_BASE_URL || !API_KEY) {
  console.error('Missing API_BASE_URL or API_KEY.')
  console.error('Set them in scripts/.env or as environment variables.')
  process.exit(1)
}

// ---------------------------------------------------------------------------
// Args
// ---------------------------------------------------------------------------
const args = process.argv.slice(2)
const flags = new Set(args.filter(a => a.startsWith('--')).map(a => a.replace('--', '')))
const endpointArg = args[args.indexOf('--endpoint') + 1] || 'events'
const endpoint = ['events', 'venues', 'people'].includes(endpointArg) ? endpointArg : 'events'

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------
async function fetchAPI(path, label) {
  const url = `${API_BASE_URL}/api/v1/${path}`
  console.log(`\n→ GET ${url}`)
  const start = Date.now()

  const res = await fetch(url, {
    headers: {
      'Authorization': `Bearer ${API_KEY}`,
      'Accept': 'application/json',
    },
  })

  const elapsed = Date.now() - start
  const body = await res.text()

  console.log(`  Status: ${res.status} (${elapsed}ms)`)

  // Show cache-related headers
  const cacheHeaders = ['cache-control', 'x-vercel-cache', 'age', 'x-nextjs-cache']
  for (const h of cacheHeaders) {
    const val = res.headers.get(h)
    if (val) console.log(`  ${h}: ${val}`)
  }

  if (!res.ok) {
    console.error(`  Error: ${body.slice(0, 200)}`)
    return null
  }

  let data
  try {
    data = JSON.parse(body)
  } catch {
    console.error('  Failed to parse JSON')
    return null
  }

  const items = data.data ?? data
  if (Array.isArray(items)) {
    console.log(`  Items returned: ${items.length}`)
    if (label) console.log(`  [${label}]`)
    return items
  }

  console.log(`  Response keys: ${Object.keys(data).join(', ')}`)
  return data
}

function summariseEvents(events) {
  if (!Array.isArray(events) || events.length === 0) return

  console.log('\n  Sample events:')
  const sample = events.slice(0, 5)
  for (const e of sample) {
    const time = e.startTime || e.start_time || '??:??'
    const title = e.title || '(no title)'
    const venue = e.venue?.name || ''
    console.log(`    ${time}  ${title}${venue ? '  @ ' + venue : ''}`)
  }
  if (events.length > 5) console.log(`    ... and ${events.length - 5} more`)

  // Show most recent updated_at if available
  const timestamps = events
    .map(e => e.updatedAt || e.updated_at)
    .filter(Boolean)
    .sort()
    .reverse()

  if (timestamps.length > 0) {
    console.log(`\n  Most recently updated event: ${timestamps[0]}`)
  }
}

async function triggerRevalidation(tags) {
  if (!REVALIDATION_SECRET) {
    console.error('\n✗ Cannot revalidate — REVALIDATION_SECRET not set.')
    return false
  }

  const url = `${API_BASE_URL}/api/webhooks/revalidate-public`
  console.log(`\n→ POST ${url}`)

  const body = tags ? { tags } : {}

  const res = await fetch(url, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${REVALIDATION_SECRET}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(body),
  })

  const data = await res.json().catch(() => null)
  console.log(`  Status: ${res.status}`)
  if (data) console.log(`  Response: ${JSON.stringify(data)}`)

  return res.ok
}

function compareResults(before, after) {
  if (!before || !after) {
    console.log('\n⚠ Cannot compare — missing data from one of the fetches.')
    return
  }

  console.log('\n━━━ Comparison ━━━')
  console.log(`  Before: ${before.length} items`)
  console.log(`  After:  ${after.length} items`)

  if (JSON.stringify(before) === JSON.stringify(after)) {
    console.log('  Result: ⚠ Data is IDENTICAL — cache may not have been invalidated')
  } else {
    console.log('  Result: ✓ Data CHANGED after revalidation')

    // Show what changed
    const beforeIds = new Set(before.map(e => e.id))
    const afterIds = new Set(after.map(e => e.id))
    const added = [...afterIds].filter(id => !beforeIds.has(id))
    const removed = [...beforeIds].filter(id => !afterIds.has(id))
    if (added.length) console.log(`  Added:   ${added.length} item(s)`)
    if (removed.length) console.log(`  Removed: ${removed.length} item(s)`)

    // Check for updated fields on matching items
    let fieldChanges = 0
    for (const a of after) {
      const b = before.find(e => e.id === a.id)
      if (b && JSON.stringify(a) !== JSON.stringify(b)) fieldChanges++
    }
    if (fieldChanges) console.log(`  Modified: ${fieldChanges} item(s) have different field values`)
  }
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------
async function main() {
  console.log('Fesutibaru API Diagnostic')
  console.log(`Base URL: ${API_BASE_URL}`)
  console.log(`Endpoint: ${endpoint}`)

  if (flags.has('full')) {
    // Full pipeline test: fetch → revalidate → wait → fetch → compare
    console.log('\n━━━ Full revalidation test ━━━')

    const before = await fetchAPI(endpoint, 'BEFORE revalidation')
    if (endpoint === 'events') summariseEvents(before)

    const ok = await triggerRevalidation()
    if (!ok) return

    console.log('\n  Waiting 3s for cache to propagate...')
    await new Promise(r => setTimeout(r, 3000))

    const after = await fetchAPI(endpoint, 'AFTER revalidation')
    if (endpoint === 'events') summariseEvents(after)

    compareResults(before, after)

  } else if (flags.has('revalidate')) {
    await triggerRevalidation()

  } else {
    // Simple fetch
    const data = await fetchAPI(endpoint)
    if (endpoint === 'events') summariseEvents(data)
  }
}

main().catch(err => {
  console.error('\nFatal error:', err.message)
  process.exit(1)
})
