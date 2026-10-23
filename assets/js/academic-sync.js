'use strict';

/**
 * Enforces bidirectional synchronization between academic institute and degree program dropdowns.
 * Restricts program options to the selected institute, and auto-selects the institute when a program is chosen.
 */
function setupInstituteCourseSync(deptSelectId, courseSelectId) {
    const deptSelect = document.getElementById(deptSelectId);
    const courseSelect = document.getElementById(courseSelectId);
    if (!deptSelect || !courseSelect) return;

    function syncOptions() {
        const selected = deptSelect.value.trim();
        const groups = courseSelect.querySelectorAll('optgroup');

        groups.forEach(group => {
            const groupLabel = (group.label || '').trim();
            const isMatch = !selected || selected === 'Other Institute / Outsider' || groupLabel === selected;
            group.disabled = !isMatch;
            group.hidden = !isMatch;
            group.querySelectorAll('option').forEach(opt => {
                opt.disabled = !isMatch;
                opt.hidden = !isMatch;
            });
        });

        const activeOption = courseSelect.selectedOptions[0];
        if (activeOption && (activeOption.disabled || (activeOption.parentElement?.tagName === 'OPTGROUP' && activeOption.parentElement.disabled))) {
            courseSelect.value = '';
        }
    }

    deptSelect.addEventListener('change', syncOptions);

    courseSelect.addEventListener('change', function () {
        const parentGroup = this.selectedOptions[0]?.parentElement;
        if (parentGroup?.tagName === 'OPTGROUP' && parentGroup.label && deptSelect.value.trim() !== parentGroup.label.trim()) {
            deptSelect.value = parentGroup.label.trim();
            deptSelect.dispatchEvent(new Event('change'));
        }
    });

    syncOptions();
}

document.addEventListener('DOMContentLoaded', () => {
    setupInstituteCourseSync('department-select', 'course-select');
    setupInstituteCourseSync('req-dept', 'req-course');
});
