(function () {
    const form = document.getElementById('blank-canvas-form');
    if (!form) {
        return;
    }

    const presetInput = document.getElementById('palette-preset-input');
    const summaryName = form.querySelector('[data-palette-summary-name]');
    const summaryStrip = form.querySelector('[data-palette-summary-strip]');
    const options = form.querySelectorAll('.palette-option');

    function updateSummary(option) {
        if (!option || !presetInput || !summaryName || !summaryStrip) {
            return;
        }

        presetInput.value = option.dataset.presetId || '';
        summaryName.textContent = option.dataset.presetLabel || '';

        summaryStrip.innerHTML = '';
        option.querySelectorAll('.palette-strip__swatch').forEach(function (swatch) {
            const clone = document.createElement('span');
            clone.className = 'palette-strip__swatch';
            clone.style.backgroundColor = swatch.dataset.color || swatch.style.backgroundColor;
            summaryStrip.appendChild(clone);
        });
    }

    function selectOption(option) {
        options.forEach(function (item) {
            const selected = item === option;
            item.classList.toggle('palette-option--selected', selected);
            item.setAttribute('aria-selected', selected ? 'true' : 'false');
        });

        updateSummary(option);
    }

    options.forEach(function (option) {
        option.addEventListener('click', function () {
            selectOption(option);
        });
    });

    const initiallySelected = form.querySelector('.palette-option--selected');
    if (initiallySelected) {
        updateSummary(initiallySelected);
    }
})();
