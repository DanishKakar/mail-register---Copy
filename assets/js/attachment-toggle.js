// Toggle any "attachment paper count" field when its linked checkbox
// (data-count-toggle="targetId") is checked/unchecked.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-count-toggle]').forEach(function (checkbox) {
        var target = document.getElementById(checkbox.getAttribute('data-count-toggle'));
        if (!target) return;

        function sync() {
            target.classList.toggle('hidden-field', !checkbox.checked);
            if (!checkbox.checked) {
                var input = target.querySelector('input');
                if (input) input.value = '';
            }
        }

        checkbox.addEventListener('change', sync);
        sync();
    });
});