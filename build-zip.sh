#!/usr/bin/env bash
#
# Build an installable WordPress plugin zip.
#
# Usage: ./build-zip.sh
# Output: fesutibaru-schedule-{version}.zip
#

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

# Extract version from the main plugin file
VERSION=$(grep -m1 "Version:" fesutibaru-schedule.php | sed 's/.*Version:[[:space:]]*//' | tr -d '[:space:]')

if [ -z "$VERSION" ]; then
    echo "Error: Could not extract version from fesutibaru-schedule.php"
    exit 1
fi

ZIP_NAME="fesutibaru-schedule-${VERSION}.zip"
PLUGIN_DIR="fesutibaru-schedule"
BUILD_DIR="/tmp/fesutibaru-build"

echo "Building ${ZIP_NAME}..."

# Clean up any previous build
rm -rf "${BUILD_DIR}" "${ZIP_NAME}"

# Create the plugin directory inside a build folder
mkdir -p "${BUILD_DIR}/${PLUGIN_DIR}"

# Copy plugin files preserving directory structure
cp fesutibaru-schedule.php "${BUILD_DIR}/${PLUGIN_DIR}/"
cp uninstall.php "${BUILD_DIR}/${PLUGIN_DIR}/"
cp readme.txt "${BUILD_DIR}/${PLUGIN_DIR}/"
cp -r includes "${BUILD_DIR}/${PLUGIN_DIR}/"
cp -r assets "${BUILD_DIR}/${PLUGIN_DIR}/"
cp -r templates "${BUILD_DIR}/${PLUGIN_DIR}/"

# Create zip
cd "${BUILD_DIR}"
zip -r "${SCRIPT_DIR}/${ZIP_NAME}" "${PLUGIN_DIR}" -x "*.DS_Store"
cd "${SCRIPT_DIR}"

# Clean up
rm -rf "${BUILD_DIR}"

echo "Done: ${ZIP_NAME} ($(du -h "${ZIP_NAME}" | cut -f1))"
