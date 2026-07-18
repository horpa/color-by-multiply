(function () {
    'use strict';

    const dataElement = document.getElementById('practice-data');
    const grid = document.getElementById('practice-grid');
    const completeBanner = document.getElementById('practice-complete');

    if (!dataElement || !grid) {
        return;
    }

    let payload;

    try {
        payload = JSON.parse(dataElement.textContent || '');
    } catch (error) {
        return;
    }

    const exercises = payload.exercises || [];
    const messages = payload.messages || {};
    const solved = new Set();

    function findCell(row, column) {
        return grid.querySelector('.practice-grid-cell[data-row="' + row + '"][data-col="' + column + '"]');
    }

    function findExerciseItem(index) {
        return document.querySelector('.practice-exercise-item[data-exercise-index="' + index + '"]');
    }

    function showFeedback(index, text, type) {
        const item = findExerciseItem(index);

        if (!item) {
            return;
        }

        const feedback = item.querySelector('.practice-feedback');

        if (feedback) {
            feedback.textContent = text;
            feedback.className = 'practice-feedback practice-feedback--' + type;
        }
    }

    function markCorrect(exercise, input) {
        const cell = findCell(exercise.row, exercise.column);

        if (cell) {
            cell.style.backgroundColor = exercise.color;
            cell.classList.add('practice-grid-cell--filled');
        }

        input.classList.remove('practice-answer--wrong');
        input.classList.add('practice-answer--correct');
        input.disabled = true;
        solved.add(exercise.index);
        showFeedback(exercise.index, messages.correct || 'Correct!', 'correct');

        if (solved.size === exercises.length && completeBanner) {
            completeBanner.hidden = false;
        }
    }

    function markWrong(index, input) {
        input.classList.add('practice-answer--wrong');
        showFeedback(index, messages.wrong || 'Try again', 'wrong');
    }

    function validateInput(input) {
        const index = parseInt(input.dataset.exerciseIndex, 10);
        const exercise = exercises.find(function (item) {
            return item.index === index;
        });

        if (!exercise || input.disabled || solved.has(index)) {
            return;
        }

        const rawValue = input.value.trim();

        if (rawValue === '') {
            input.classList.remove('practice-answer--wrong');
            showFeedback(index, '', '');

            return;
        }

        const value = parseInt(rawValue, 10);

        if (value === exercise.answer) {
            markCorrect(exercise, input);
        } else {
            markWrong(index, input);
        }
    }

    document.querySelectorAll('.practice-answer').forEach(function (input) {
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                validateInput(input);
            }
        });

        input.addEventListener('blur', function () {
            validateInput(input);
        });

        input.addEventListener('input', function () {
            if (!input.disabled) {
                input.classList.remove('practice-answer--wrong');
                showFeedback(parseInt(input.dataset.exerciseIndex, 10), '', '');
            }
        });
    });
})();
