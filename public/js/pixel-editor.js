(function () {
    'use strict';

    const form = document.getElementById('pixel-editor-form');
    const grid = document.getElementById('pixel-grid');

    if (!form || !grid) {
        return;
    }

    const swatches = form.querySelectorAll('.palette-swatch');
    const eraserButton = form.querySelector('.eraser-button');
    let activeIndex = swatches.length > 0 ? parseInt(swatches[0].dataset.paletteIndex, 10) : 1;
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

    function setActiveTool(index) {
        activeIndex = index;

        swatches.forEach(function (swatch) {
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

    swatches.forEach(function (swatch) {
        swatch.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            setActiveTool(parseInt(swatch.dataset.paletteIndex, 10));
        });
    });

    form.querySelectorAll('.palette-change-button').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.stopPropagation();
            openColorPicker(parseInt(button.dataset.paletteIndex, 10));
        });
    });

    if (eraserButton) {
        eraserButton.addEventListener('click', function () {
            setActiveTool(0);
        });
    }

    form.querySelectorAll('.palette-color-input').forEach(function (colorInput) {
        colorInput.addEventListener('input', function () {
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
        });
    });

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
})();
