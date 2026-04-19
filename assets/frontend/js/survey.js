/**
 * Survey Sphere Frontend JavaScript
 * survey-sphere\assets\frontend\js\survey.js
 */
(function () {
    'use strict';

    console.log('[SurveySphere] Script loaded');

    document.addEventListener('DOMContentLoaded', function () {
        console.log('[SurveySphere] DOM ready');
        var wrappers = document.querySelectorAll('.survey-sphere-wrapper');
        console.log('[SurveySphere] Found wrappers:', wrappers.length);
        wrappers.forEach(function (wrapper, i) {
            console.log('[SurveySphere] Init wrapper', i, wrapper.id);
            initSurvey(wrapper);
        });
    });

    function initSurvey(wrapper) {
        console.log('[SurveySphere] initSurvey start');

        var currentIndex = 0;
        var slides = wrapper.querySelectorAll('.question-slide');
        var prevBtn = wrapper.querySelector('.prev-btn');
        var nextBtn = wrapper.querySelector('.next-btn');
        var submitBtn = wrapper.querySelector('.submit-btn');
        var progressFill = wrapper.querySelector('.progress-fill');
        var currentSpan = wrapper.querySelector('.current-question');
        var totalSpan = wrapper.querySelector('.total-questions');
        var form = wrapper.querySelector('.survey-form');
        var resultsDiv = wrapper.querySelector('.survey-results');
        var restartBtn = wrapper.querySelector('.restart-btn');
        var saveResultBtn = wrapper.querySelector('.save-result-btn');
        var saveEmailForm = wrapper.querySelector('.save-email-form');
        var saveEmailInput = wrapper.querySelector('.save-email-input');
        var confirmSaveBtn = wrapper.querySelector('.confirm-save-btn');
        var cancelSaveBtn = wrapper.querySelector('.cancel-save-btn');
        var saveMessage = wrapper.querySelector('.save-message');

        var surveyId = wrapper.dataset.surveyId;
        var collectedAnswers = {};

        console.log('[SurveySphere] Survey ID:', surveyId);
        console.log('[SurveySphere] Slides:', slides.length);
        console.log('[SurveySphere] Elements:', {
            form: !!form,
            resultsDiv: !!resultsDiv,
            saveResultBtn: !!saveResultBtn,
            saveEmailForm: !!saveEmailForm
        });

        if (!slides.length) {
            console.warn('[SurveySphere] No slides found');
            return;
        }

        totalSpan.textContent = slides.length;

        function showSlide(index) {
            slides.forEach(function (slide, i) {
                slide.style.display = i === index ? 'block' : 'none';
            });
            var progress = ((index + 1) / slides.length) * 100;
            progressFill.style.width = progress + '%';
            currentSpan.textContent = index + 1;
            prevBtn.style.display = index === 0 ? 'none' : 'inline-block';
            nextBtn.style.display = index === slides.length - 1 ? 'none' : 'inline-block';
            submitBtn.style.display = index === slides.length - 1 ? 'inline-block' : 'none';
            currentIndex = index;
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                if (currentIndex > 0) showSlide(currentIndex - 1);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                if (!slides[currentIndex].querySelector('input[type="radio"]:checked')) {
                    alert(wrapper.dataset.pleaseSelect || 'Please select an answer');
                    return;
                }
                if (currentIndex < slides.length - 1) showSlide(currentIndex + 1);
            });
        }

        function getOverallLevel(score) {
            if (score <= 25) {
                return {
                    name: 'Критический',
                    color: '#dc3545',
                    description: 'ИТ-инфраструктура требует немедленного вмешательства.'
                };
            }
            if (score <= 50) {
                return {
                    name: 'Низкий',
                    color: '#fd7e14',
                    description: 'Присутствуют системные проблемы. Необходим аудит.'
                };
            }
            if (score <= 75) {
                return {
                    name: 'Средний',
                    color: '#ffc107',
                    description: 'Базовые процессы настроены, но есть зоны для улучшения.'
                };
            }
            return {
                name: 'Высокий',
                color: '#28a745',
                description: 'IT-функция стабильно работает.'
            };
        }

        function showResults() {
            console.log('[SurveySphere] showResults called');

            var segments = window.surveySphereData?.segments || [];

            if (segments.length === 0) {
                resultsDiv.querySelector('.results-summary').innerHTML = '<h3>Нет сегментов для отображения</h3>';
                form.style.display = 'none';
                resultsDiv.style.display = 'block';
                return;
            }

            var segmentScores = {};
            var segmentCounts = {};

            segments.forEach(function (seg) {
                segmentScores[seg.id] = 0;
                segmentCounts[seg.id] = 0;
            });
            segmentScores['uncategorized'] = 0;
            segmentCounts['uncategorized'] = 0;

            for (var qId in collectedAnswers) {
                var option = wrapper.querySelector('input[value="' + collectedAnswers[qId] + '"]');
                if (option) {
                    var questionSlide = option.closest('.question-slide');
                    var segmentId = questionSlide?.dataset.segmentId || 'uncategorized';
                    var score = parseFloat(option.dataset.score) || 0;

                    segmentScores[segmentId] = (segmentScores[segmentId] || 0) + score;
                    segmentCounts[segmentId] = (segmentCounts[segmentId] || 0) + 1;
                }
            }

            var maxPossibleScore = 10;
            var segmentPercentages = {};
            var totalPercentage = 0;
            var segmentCount = 0;

            segments.forEach(function (seg) {
                var avg = segmentCounts[seg.id] > 0
                    ? segmentScores[seg.id] / segmentCounts[seg.id]
                    : 0;
                var percentage = Math.round((avg / maxPossibleScore) * 100);
                segmentPercentages[seg.id] = percentage;
                totalPercentage += percentage;
                segmentCount++;
            });

            var overallScore = segmentCount > 0 ? Math.round(totalPercentage / segmentCount) : 0;
            var level = getOverallLevel(overallScore) || { name: 'Н/Д', color: '#999', description: '' };

            form.style.display = 'none';
            resultsDiv.style.display = 'block';

            var summary = resultsDiv.querySelector('.results-summary');
            if (summary) {
                summary.innerHTML = `
                    <div class="overall-score">
                        <div class="score-circle" style="border-color: ${level.color}">
                            <span class="score-value">${overallScore}%</span>
                        </div>
                        <div class="score-level" style="color: ${level.color}">${level.name}</div>
                        <p class="score-description">${level.description}</p>
                    </div>
                `;
            }

            var canvas = resultsDiv.querySelector('canvas');
            if (canvas && typeof Chart !== 'undefined') {
                Chart.register(
                    Chart.ArcElement, Chart.RadialLinearScale, Chart.CategoryScale,
                    Chart.LinearScale, Chart.BarElement, Chart.LineElement, Chart.PointElement,
                    Chart.Title, Chart.Tooltip, Chart.Legend, Chart.Filler,
                    Chart.PolarAreaController, Chart.RadarController,
                    Chart.DoughnutController, Chart.BarController
                );

                var chartType = canvas.dataset.chartType || 'polarArea';
                var labels = segments.map(function (s) { return s.name; });
                var data = segments.map(function (s) { return segmentPercentages[s.id] || 0; });
                var colors = segments.map(function (s) { return s.color + 'CC'; });
                var borderColors = segments.map(function (s) { return s.color; });

                var existing = Chart.getChart(canvas);
                if (existing) existing.destroy();

                if (chartType === 'polarArea') {
                    var backgroundDatasets = [
                        { data: segments.map(function () { return 25; }), backgroundColor: 'rgba(220, 53, 69, 0.15)', borderWidth: 0 },
                        { data: segments.map(function () { return 50; }), backgroundColor: 'rgba(253, 126, 20, 0.12)', borderWidth: 0 },
                        { data: segments.map(function () { return 75; }), backgroundColor: 'rgba(255, 193, 7, 0.1)', borderWidth: 0 },
                        { data: segments.map(function () { return 100; }), backgroundColor: 'rgba(40, 167, 69, 0.05)', borderWidth: 0 }
                    ];

                    new Chart(canvas, {
                        type: 'polarArea',
                        data: { labels: labels, datasets: backgroundDatasets },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: { legend: { display: false }, tooltip: { enabled: false } },
                            scales: { r: { max: 100, ticks: { display: false }, grid: { display: false } } }
                        }
                    });

                    new Chart(canvas, {
                        type: 'polarArea',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: data,
                                backgroundColor: colors,
                                borderColor: borderColors,
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: { position: 'bottom', labels: { font: { size: 12 } } },
                                title: { display: true, text: 'Результаты по сегментам', font: { size: 16 } }
                            },
                            scales: { r: { max: 100, ticks: { stepSize: 25, callback: function (v) { return v + '%'; } } } }
                        }
                    });
                } else {
                    new Chart(canvas, {
                        type: chartType,
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Score (%)',
                                data: data,
                                backgroundColor: colors,
                                borderColor: borderColors,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: { legend: { display: chartType !== 'bar' } },
                            scales: chartType === 'bar' || chartType === 'radar' ? { y: { max: 100 } } : {}
                        }
                    });
                }
                console.log('[SurveySphere] Chart drawn');
            }
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                console.log('[SurveySphere] Form submitted');

                var allChecked = true;
                slides.forEach(function (slide) {
                    if (!slide.querySelector('input[type="radio"]:checked')) allChecked = false;
                });

                if (!allChecked) {
                    alert(wrapper.dataset.answerAll || 'Please answer all questions');
                    return;
                }

                collectedAnswers = {};
                slides.forEach(function (slide) {
                    var checked = slide.querySelector('input[type="radio"]:checked');
                    if (checked) {
                        var name = checked.getAttribute('name');
                        var match = name.match(/answers\[(.*?)\]/);
                        if (match) {
                            collectedAnswers[match[1]] = checked.value;
                        }
                    }
                });

                console.log('[SurveySphere] Collected answers:', collectedAnswers);

                try {
                    localStorage.setItem('survey_' + surveyId, JSON.stringify({
                        answers: collectedAnswers,
                        timestamp: Date.now()
                    }));
                    console.log('[SurveySphere] Saved to localStorage');
                } catch (e) {
                    console.error('[SurveySphere] localStorage error:', e);
                }

                showResults();
            });
        }

        if (restartBtn) {
            restartBtn.addEventListener('click', function () {
                console.log('[SurveySphere] Restart clicked');
                form.reset();
                form.style.display = 'block';
                resultsDiv.style.display = 'none';
                if (saveEmailForm) saveEmailForm.style.display = 'none';
                showSlide(0);
            });
        }

        if (saveResultBtn) {
            saveResultBtn.addEventListener('click', function () {
                console.log('[SurveySphere] Save button clicked');
                if (saveEmailForm) {
                    saveEmailForm.style.display = 'block';
                    if (saveMessage) saveMessage.textContent = '';
                }
            });
        }

        if (cancelSaveBtn) {
            cancelSaveBtn.addEventListener('click', function () {
                if (saveEmailForm) saveEmailForm.style.display = 'none';
                if (saveEmailInput) saveEmailInput.value = '';
            });
        }

        if (confirmSaveBtn) {
            confirmSaveBtn.addEventListener('click', function () {
                var email = saveEmailInput ? saveEmailInput.value.trim() : '';
                console.log('[SurveySphere] Confirm save, email:', email);

                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    if (saveMessage) {
                        saveMessage.textContent = 'Please enter a valid email';
                        saveMessage.style.color = 'red';
                    }
                    return;
                }

                confirmSaveBtn.disabled = true;
                if (saveMessage) {
                    saveMessage.textContent = 'Saving...';
                    saveMessage.style.color = 'blue';
                }

                var formData = new FormData();
                formData.append('action', 'survey_sphere_submit_attempt');
                formData.append('_wpnonce', surveySphereData.nonce);
                formData.append('email', email);
                formData.append('survey_id', surveyId);
                for (var qId in collectedAnswers) {
                    if (collectedAnswers.hasOwnProperty(qId)) {
                        formData.append('answers[' + qId + ']', collectedAnswers[qId]);
                    }
                }

                fetch(surveySphereData.ajaxUrl, { method: 'POST', body: formData })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        console.log('[SurveySphere] AJAX response:', data);
                        if (data.success) {
                            if (saveMessage) {
                                saveMessage.textContent = 'Result saved!';
                                saveMessage.style.color = 'green';
                            }
                            if (saveEmailInput) saveEmailInput.value = '';
                            setTimeout(function () {
                                if (saveEmailForm) saveEmailForm.style.display = 'none';
                            }, 1500);
                        } else {
                            if (saveMessage) {
                                saveMessage.textContent = data.data.message || 'Error';
                                saveMessage.style.color = 'red';
                            }
                        }
                    })
                    .catch(function (err) {
                        console.error('[SurveySphere] AJAX error:', err);
                        if (saveMessage) {
                            saveMessage.textContent = 'Network error';
                            saveMessage.style.color = 'red';
                        }
                    })
                    .finally(function () {
                        confirmSaveBtn.disabled = false;
                    });
            });
        }

        try {
            var saved = localStorage.getItem('survey_' + surveyId);
            if (saved) {
                var data = JSON.parse(saved);
                if (data.answers) {
                    collectedAnswers = data.answers;
                    console.log('[SurveySphere] Loaded answers from localStorage, showing results');
                    setTimeout(function () {
                        showResults();
                    }, 100);
                    return;
                }
            }
        } catch (e) {
            console.error('[SurveySphere] Failed to load from localStorage:', e);
        }

        showSlide(0);
    }
})();