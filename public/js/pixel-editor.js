(function () {
    'use strict';

    const form = document.getElementById('pixel-editor-form');
    const grid = document.getElementById('pixel-grid');

    if (!form || !grid) {
        return;
    }

    const paletteRow = form.querySelector('.palette-row');
    const colorInputsContainer = form.querySelector('.palette-color-inputs');
    const addColorButton = form.querySelector('.palette-add-button');
    const eraserButton = form.querySelector('.eraser-button');
    const DEFAULT_NEW_COLORS = ['#e63946', '#2a9d8f', '#457b9d', '#e9c46a', '#f4a261', '#8338ec', '#06d6a0'];

    const firstSwatch = form.querySelector('.palette-swatch');
    let activeIndex = firstSwatch ? parseInt(firstSwatch.dataset.paletteIndex, 10) : 1;
    let isPainting = false;

    function cellFromEvent(event) {
        const target = event.target;

        if (target instanceof Element && target.classList.contains('pixel-cell')) {
            return target;
        }

        return event.target instanceof Element ? event.target.closest('.pixel-cell') : null;
    }

    function hiddenInputForCell(button) {
        const wrap = button.closest('.pixel-cell-wrap');

        return wrap ? wrap.querySelector('input[type="hidden"]') : null;
    }

    function findPaletteInput(index) {
        const item = form.querySelector('.palette-item[data-palette-index="' + index + '"]');

        return item ? item.querySelector('input[type="hidden"][data-palette-index]') : null;
    }

    function getPaletteCount() {
        return form.querySelectorAll('.palette-item').length;
    }

    function getMaxColors() {
        if (!paletteRow) {
            return 7;
        }

        return parseInt(paletteRow.dataset.maxColors, 10) || 7;
    }

    function updateAddButtonState() {
        if (!addColorButton) {
            return;
        }

        const atMax = getPaletteCount() >= getMaxColors();
        addColorButton.disabled = atMax;
        addColorButton.setAttribute('aria-disabled', atMax ? 'true' : 'false');
    }

    function setActiveTool(index) {
        activeIndex = index;

        form.querySelectorAll('.palette-swatch').forEach(function (swatch) {
            const isActive = parseInt(swatch.dataset.paletteIndex, 10) === index;
            swatch.classList.toggle('active', isActive);
            swatch.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        if (eraserButton) {
            const isEraser = index === 0;
            eraserButton.classList.toggle('active', isEraser);
            eraserButton.setAttribute('aria-pressed', isEraser ? 'true' : 'false');
        }
    }

    function openColorPicker(index) {
        const colorInput = form.querySelector('.palette-color-input[data-palette-index="' + index + '"]');

        if (!colorInput) {
            return;
        }

        try {
            if (typeof colorInput.showPicker === 'function') {
                colorInput.showPicker();
                return;
            }
        } catch (error) {
            // Fall back to programmatic click below.
        }

        colorInput.click();
    }

    function getColorForIndex(index) {
        if (index === 0) {
            return '#ffffff';
        }

        const input = findPaletteInput(index);

        return input ? input.value : '#000000';
    }

    function paintCell(button) {
        const hiddenInput = hiddenInputForCell(button);

        if (!hiddenInput) {
            return;
        }

        hiddenInput.value = String(activeIndex);
        button.style.backgroundColor = getColorForIndex(activeIndex);
    }

    function handlePaletteColorChange(colorInput) {
        const index = parseInt(colorInput.dataset.paletteIndex, 10);
        const newColor = colorInput.value;
        const hiddenPaletteInput = findPaletteInput(index);
        const swatch = form.querySelector('.palette-swatch[data-palette-index="' + index + '"]');

        if (hiddenPaletteInput) {
            hiddenPaletteInput.value = newColor;
        }

        if (swatch) {
            swatch.style.backgroundColor = newColor;
        }

        setActiveTool(index);

        grid.querySelectorAll('.pixel-cell').forEach(function (button) {
            const hiddenInput = hiddenInputForCell(button);

            if (hiddenInput && parseInt(hiddenInput.value, 10) === index) {
                button.style.backgroundColor = newColor;
            }
        });
    }

    function addPaletteColor() {
        if (!paletteRow || !colorInputsContainer || !addColorButton) {
            return;
        }

        const count = getPaletteCount();

        if (count >= getMaxColors()) {
            updateAddButtonState();
            return;
        }

        const newIndex = count + 1;
        const defaultColor = DEFAULT_NEW_COLORS[(newIndex - 1) % DEFAULT_NEW_COLORS.length];
        const colorLabel = addColorButton.dataset.colorLabel || 'Color';
        const changeLabel = addColorButton.dataset.changeLabel || 'Change';

        const item = document.createElement('div');
        item.className = 'palette-item';
        item.dataset.paletteIndex = String(newIndex);

        const swatchWrap = document.createElement('div');
        swatchWrap.className = 'palette-swatch-wrap';

        const swatch = document.createElement('button');
        swatch.type = 'button';
        swatch.className = 'palette-swatch';
        swatch.dataset.paletteIndex = String(newIndex);
        swatch.style.backgroundColor = defaultColor;
        swatch.title = colorLabel + ' ' + newIndex;
        swatch.setAttribute('aria-label', colorLabel + ' ' + newIndex);
        swatch.setAttribute('aria-pressed', 'false');
        swatchWrap.appendChild(swatch);

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'palette[]';
        hiddenInput.value = defaultColor;
        hiddenInput.dataset.paletteIndex = String(newIndex);

        const changeButton = document.createElement('button');
        changeButton.type = 'button';
        changeButton.className = 'palette-change-button';
        changeButton.dataset.paletteIndex = String(newIndex);
        changeButton.textContent = changeLabel;

        item.appendChild(swatchWrap);
        item.appendChild(hiddenInput);
        item.appendChild(changeButton);
        paletteRow.appendChild(item);

        const colorInput = document.createElement('input');
        colorInput.type = 'color';
        colorInput.className = 'palette-color-input';
        colorInput.dataset.paletteIndex = String(newIndex);
        colorInput.value = defaultColor;
        colorInput.tabIndex = -1;
        colorInputsContainer.appendChild(colorInput);

        setActiveTool(newIndex);
        updateAddButtonState();
        openColorPicker(newIndex);
    }

    if (paletteRow) {
        paletteRow.addEventListener('click', function (event) {
            const swatch = event.target instanceof Element ? event.target.closest('.palette-swatch') : null;

            if (swatch && paletteRow.contains(swatch)) {
                event.preventDefault();
                event.stopPropagation();
                setActiveTool(parseInt(swatch.dataset.paletteIndex, 10));
                return;
            }

            const changeButton = event.target instanceof Element ? event.target.closest('.palette-change-button') : null;

            if (changeButton && paletteRow.contains(changeButton)) {
                event.stopPropagation();
                openColorPicker(parseInt(changeButton.dataset.paletteIndex, 10));
            }
        });
    }

    if (addColorButton) {
        addColorButton.addEventListener('click', function () {
            addPaletteColor();
        });
    }

    if (eraserButton) {
        eraserButton.addEventListener('click', function () {
            setActiveTool(0);
        });
    }

    if (colorInputsContainer) {
        colorInputsContainer.addEventListener('input', function (event) {
            const colorInput = event.target instanceof Element ? event.target.closest('.palette-color-input') : null;

            if (colorInput) {
                handlePaletteColorChange(colorInput);
            }
        });
    }

    grid.addEventListener('click', function (event) {
        const cell = cellFromEvent(event);

        if (!cell) {
            return;
        }

        event.preventDefault();
        paintCell(cell);
    });

    grid.addEventListener('mousedown', function (event) {
        if (event.button !== 0) {
            return;
        }

        const cell = cellFromEvent(event);

        if (!cell) {
            return;
        }

        event.preventDefault();
        isPainting = true;
        paintCell(cell);
    });

    grid.addEventListener('mouseover', function (event) {
        if (!isPainting) {
            return;
        }

        const cell = cellFromEvent(event);

        if (cell) {
            paintCell(cell);
        }
    });

    grid.addEventListener('touchstart', function (event) {
        const cell = cellFromEvent(event);

        if (!cell) {
            return;
        }

        event.preventDefault();
        isPainting = true;
        paintCell(cell);
    }, { passive: false });

    grid.addEventListener('touchmove', function (event) {
        if (!isPainting) {
            return;
        }

        event.preventDefault();
        const touch = event.touches[0];
        const target = document.elementFromPoint(touch.clientX, touch.clientY);

        if (target instanceof Element && target.classList.contains('pixel-cell')) {
            paintCell(target);
        }
    }, { passive: false });

    document.addEventListener('mouseup', function () {
        isPainting = false;
    });

    document.addEventListener('touchend', function () {
        isPainting = false;
    });

    setActiveTool(activeIndex);
    updateAddButtonState();
})();
