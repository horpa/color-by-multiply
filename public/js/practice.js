(function () {
    'use strict';

    const dataElement = document.getElementById('practice-data');
    const grid = document.getElementById('practice-grid');
    const completeBanner = document.getElementById('practice-complete');
    const progressCount = document.getElementById('practice-progress-count');
    const progressFill = document.getElementById('practice-progress-fill');
    const progressRoot = document.getElementById('practice-progress');

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
    let fireworksLaunched = false;
    let activeItem = null;

    function launchFireworks() {
        if (fireworksLaunched || exercises.length === 0) {
            return;
        }

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        fireworksLaunched = true;

        const canvas = document.createElement('canvas');
        canvas.className = 'practice-fireworks';
        canvas.setAttribute('aria-hidden', 'true');
        document.body.appendChild(canvas);

        const ctx = canvas.getContext('2d');

        if (!ctx) {
            canvas.remove();

            return;
        }

        let width = 0;
        let height = 0;
        const particles = [];
        const colors = ['#ff4d6d', '#ffd166', '#06d6a0', '#118ab2', '#8338ec', '#ff006e', '#fb5607', '#8a139c'];
        let burstsRemaining = 7;
        let burstTimer = 0;
        let idleFrames = 0;
        let animationId = 0;

        function resize() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        }

        function createBurst(x, y) {
            const count = 36 + Math.floor(Math.random() * 24);

            for (let i = 0; i < count; i++) {
                const angle = (Math.PI * 2 * i) / count + (Math.random() - 0.5) * 0.35;
                const speed = 2.5 + Math.random() * 4.5;

                particles.push({
                    x: x,
                    y: y,
                    vx: Math.cos(angle) * speed,
                    vy: Math.sin(angle) * speed,
                    life: 1,
                    decay: 0.01 + Math.random() * 0.014,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    size: 2 + Math.random() * 2.5
                });
            }
        }

        function cleanup() {
            cancelAnimationFrame(animationId);
            window.removeEventListener('resize', resize);
            canvas.remove();
        }

        function tick() {
            ctx.clearRect(0, 0, width, height);
            burstTimer++;

            if (burstTimer > 22 && burstsRemaining > 0) {
                createBurst(
                    width * (0.15 + Math.random() * 0.7),
                    height * (0.15 + Math.random() * 0.45)
                );
                burstsRemaining--;
                burstTimer = 0;
            }

            for (let i = particles.length - 1; i >= 0; i--) {
                const particle = particles[i];

                particle.x += particle.vx;
                particle.y += particle.vy;
                particle.vy += 0.06;
                particle.vx *= 0.985;
                particle.life -= particle.decay;

                if (particle.life <= 0) {
                    particles.splice(i, 1);
                    continue;
                }

                ctx.globalAlpha = particle.life;
                ctx.fillStyle = particle.color;
                ctx.beginPath();
                ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
                ctx.fill();
            }

            ctx.globalAlpha = 1;

            if (particles.length === 0 && burstsRemaining === 0) {
                idleFrames++;

                if (idleFrames > 45) {
                    cleanup();

                    return;
                }
            } else {
                idleFrames = 0;
            }

            animationId = requestAnimationFrame(tick);
        }

        resize();
        createBurst(width * 0.5, height * 0.3);
        burstsRemaining--;
        window.addEventListener('resize', resize);
        animationId = requestAnimationFrame(tick);
    }

    function updateProgress() {
        const total = exercises.length;
        const done = solved.size;

        if (progressCount) {
            progressCount.textContent = done + ' / ' + total;
        }

        if (progressFill) {
            const percent = total === 0 ? 0 : Math.round((done / total) * 100);
            progressFill.style.width = percent + '%';
        }

        if (progressRoot) {
            progressRoot.setAttribute('aria-valuenow', String(done));
            progressRoot.setAttribute('aria-valuemax', String(total));
        }
    }

    function celebrateCompletion() {
        if (completeBanner) {
            completeBanner.hidden = false;
            completeBanner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        launchFireworks();
    }

    function findCell(row, column) {
        return grid.querySelector('.practice-grid-cell[data-row="' + row + '"][data-col="' + column + '"]');
    }

    function findExerciseItem(index) {
        return document.querySelector('.practice-exercise-item[data-exercise-index="' + index + '"]');
    }

    function clearActiveHighlight() {
        if (activeItem) {
            activeItem.classList.remove('practice-exercise-item--active');
            activeItem = null;
        }
    }

    function highlightExercise(index) {
        const item = findExerciseItem(index);

        clearActiveHighlight();

        if (!item || solved.has(index)) {
            return;
        }

        item.classList.add('practice-exercise-item--active');
        activeItem = item;
    }

    function showFeedback(index, text, type) {
        const item = findExerciseItem(index);

        if (!item) {
            return;
        }

        const feedback = item.querySelector('.practice-feedback');

        if (feedback) {
            feedback.textContent = text;
            feedback.className = 'practice-feedback' + (type ? ' practice-feedback--' + type : '');
        }
    }

    function focusNextUnsolved(afterIndex) {
        const inputs = Array.prototype.slice.call(document.querySelectorAll('.practice-answer:not(:disabled)'));

        if (inputs.length === 0) {
            return;
        }

        let next = inputs.find(function (input) {
            return parseInt(input.dataset.exerciseIndex, 10) > afterIndex;
        });

        if (!next) {
            next = inputs[0];
        }

        next.focus({ preventScroll: false });
        next.select();
    }

    function markCorrect(exercise, input) {
        const cell = findCell(exercise.row, exercise.column);
        const item = findExerciseItem(exercise.index);

        if (cell) {
            cell.style.backgroundColor = exercise.color;
            cell.classList.add('practice-grid-cell--filled');
        }

        if (item) {
            item.classList.add('practice-exercise-item--solved');
            item.classList.remove('practice-exercise-item--active');
        }

        input.classList.remove('practice-answer--wrong');
        input.classList.add('practice-answer--correct');
        input.disabled = true;
        solved.add(exercise.index);
        showFeedback(exercise.index, messages.correct || 'Correct!', 'correct');
        updateProgress();

        if (solved.size === exercises.length) {
            clearActiveHighlight();
            celebrateCompletion();
        } else {
            window.setTimeout(function () {
                focusNextUnsolved(exercise.index);
            }, 120);
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

        input.addEventListener('focus', function () {
            highlightExercise(parseInt(input.dataset.exerciseIndex, 10));
        });

        input.addEventListener('input', function () {
            if (!input.disabled) {
                input.classList.remove('practice-answer--wrong');
                showFeedback(parseInt(input.dataset.exerciseIndex, 10), '', '');
            }
        });
    });

    updateProgress();
})();
