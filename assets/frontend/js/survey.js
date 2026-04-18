/**
 * Survey Sphere Frontend JavaScript
 * survey-sphere\assets\frontend\js\survey.js
 */


/**
 * Survey Sphere Frontend JavaScript
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
        var currentPercentage = 0;

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

                // Сохраняем в localStorage
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

        function showResults() {
            console.log('[SurveySphere] showResults called');

            var totalScore = 0, count = 0;
            for (var qId in collectedAnswers) {
                var option = wrapper.querySelector('input[value="' + collectedAnswers[qId] + '"]');
                if (option) {
                    totalScore += parseFloat(option.dataset.score) || 0;
                    count++;
                }
            }
            var averageScore = count > 0 ? totalScore / count : 0;
            currentPercentage = Math.round((averageScore / 10) * 100);

            console.log('[SurveySphere] Score:', currentPercentage + '%');

            form.style.display = 'none';
            resultsDiv.style.display = 'block';

            var summary = resultsDiv.querySelector('.results-summary');
            if (summary) {
                summary.innerHTML = '<h3>Your Score: ' + currentPercentage + '%</h3>';
            }

            var canvas = resultsDiv.querySelector('canvas');
            if (canvas && typeof Chart !== 'undefined') {
                console.log('[SurveySphere] Drawing chart, type:', canvas.dataset.chartType);

                Chart.register(
                    Chart.ArcElement, Chart.RadialLinearScale, Chart.CategoryScale,
                    Chart.LinearScale, Chart.BarElement, Chart.LineElement, Chart.PointElement,
                    Chart.Title, Chart.Tooltip, Chart.Legend, Chart.Filler,
                    Chart.PolarAreaController, Chart.RadarController,
                    Chart.DoughnutController, Chart.BarController
                );

                var chartType = canvas.dataset.chartType || 'polarArea';
                if (chartType === 'polar') chartType = 'polarArea';

                var existing = Chart.getChart(canvas);
                if (existing) existing.destroy();

                new Chart(canvas, {
                    type: chartType,
                    data: {
                        labels: ['Score'],
                        datasets: [{
                            data: [currentPercentage, 100 - currentPercentage],
                            backgroundColor: ['#4CAF50', '#e0e0e0'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: { legend: { display: false } }
                    }
                });
                console.log('[SurveySphere] Chart drawn');
            } else {
                console.warn('[SurveySphere] Canvas or Chart not available');
            }
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

                console.log('[SurveySphere] Sending AJAX to:', surveySphereData.ajaxUrl);

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

        // Загружаем сохранённые ответы из localStorage
        try {
            var saved = localStorage.getItem('survey_' + surveyId);
            if (saved) {
                var data = JSON.parse(saved);
                if (data.answers) {
                    collectedAnswers = data.answers;
                    console.log('[SurveySphere] Loaded answers from localStorage, showing results');

                    // Сразу показываем результаты
                    setTimeout(function () {
                        showResults();
                    }, 100);

                    return; // Выходим, не показываем вопросы
                }
            }
        } catch (e) {
            console.error('[SurveySphere] Failed to load from localStorage:', e);
        }

        showSlide(0);
    }
})();