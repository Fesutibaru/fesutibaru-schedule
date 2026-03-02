/**
 * Fesutibaru Schedule — optional front-end enhancements.
 *
 * - Collapsible day sections (click date heading to toggle)
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        // Collapsible day headings
        var headings = document.querySelectorAll('.fesutibaru-schedule__date');

        headings.forEach(function (heading) {
            heading.addEventListener('click', function () {
                var day = heading.closest('.fesutibaru-schedule__day');
                if (day) {
                    day.classList.toggle('is-collapsed');
                }
            });
        });
    });
})();
