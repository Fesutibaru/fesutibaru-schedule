/**
 * Fesutibaru Schedule — optional front-end enhancements.
 *
 * - Collapsible day sections (click date heading to toggle)
 * - First day expanded by default, rest collapsed
 * - Toggle state persisted in localStorage
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'fesutibaru_schedule_collapsed';

    function getSavedState() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
        } catch (e) {
            return {};
        }
    }

    function saveState(state) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
        } catch (e) {
            // localStorage unavailable — silently ignore
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var days = document.querySelectorAll('.fesutibaru-schedule__day');
        if (!days.length) {
            return;
        }

        var saved = getSavedState();

        days.forEach(function (day, index) {
            var date = day.getAttribute('data-date');

            if (saved.hasOwnProperty(date)) {
                // Restore saved state
                if (saved[date]) {
                    day.classList.add('is-collapsed');
                }
            } else {
                // No saved state — expand first day, collapse the rest
                if (index > 0) {
                    day.classList.add('is-collapsed');
                }
            }

            var heading = day.querySelector('.fesutibaru-schedule__date');
            if (heading) {
                heading.addEventListener('click', function () {
                    day.classList.toggle('is-collapsed');

                    // Persist toggle state
                    var state = getSavedState();
                    state[date] = day.classList.contains('is-collapsed');
                    saveState(state);
                });
            }
        });
    });
})();
