/**
 * Survey Sphere Editor JavaScript
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
        var surveyId = $('#survey-sphere-editor').data('survey-id');

        function updateQuestionNumbers() {
            $('.question-item').each(function (index) {
                $(this).find('.question-number').text((index + 1) + '.');
            });
        }

        function updateOptionLetters($container) {
            $container.find('.option-item').each(function (index) {
                $(this).find('.option-letter').text(String.fromCharCode(65 + index) + '.');
            });
        }

        function escapeHtml(text) {
            return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function saveQuestion($question, callback) {
            var questionText = $question.find('.question-text').val();
            var orderIndex = $question.index();

            if (!questionText) {
                alert('Please enter a question');
                callback(false);
                return;
            }

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'survey_sphere_save_question',
                    _wpnonce: surveySphereAdmin.nonce,
                    survey_id: surveyId,
                    question_text: questionText,
                    order_index: orderIndex
                },
                success: function (response) {
                    if (response.success) {
                        callback(true, response.data.question.id);
                    } else {
                        alert('Error: ' + response.data.message);
                        callback(false);
                    }
                },
                error: function () {
                    alert('Server error occurred');
                    callback(false);
                }
            });
        }

        function addOption(questionId, optionText, optionScore, $wrapper, $btn, $question) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'survey_sphere_save_option',
                    _wpnonce: surveySphereAdmin.nonce,
                    question_id: questionId,
                    option_text: optionText,
                    score: optionScore
                },
                success: function (response) {
                    if (response.success) {
                        var $container = $question.find('.options-container');
                        $container.find('.options-placeholder').remove();

                        var optionCount = $container.find('.option-item').length;
                        var letter = String.fromCharCode(65 + optionCount);

                        var optionHtml = `
                            <div class="option-item" data-option-id="${response.data.option.id}">
                                <span class="option-letter">${letter}.</span>
                                <input type="text" class="option-text" value="${escapeHtml(optionText)}" placeholder="Option text">
                                <input type="number" class="option-score" value="${optionScore}" placeholder="Score" step="0.1" style="width: 80px;">
                                <button type="button" class="button button-small remove-option">✕</button>
                            </div>
                        `;

                        $container.append(optionHtml);
                        $wrapper.find('.new-option-text').val('');
                        $wrapper.find('.new-option-score').val('0');
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function () {
                    alert('Server error occurred');
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Add Option');
                }
            });
        }

        // Загрузить список вопросов
        // Загрузить список вопросов
        function loadExistingQuestions(search = '') {
            $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: {
                    action: 'survey_sphere_get_questions',
                    _wpnonce: surveySphereAdmin.nonce,
                    exclude_survey_id: surveyId,
                    search: search
                },
                success: function (response) {
                    if (response.success) {
                        var html = '';
                        if (response.data.questions && response.data.questions.length > 0) {
                            response.data.questions.forEach(function (q) {
                                html += `
                            <div class="existing-question-item" data-question-id="${q.id}">
                                <strong>${escapeHtml(q.text)}</strong>
                                <p>Options: ${q.options_count || 0}</p>
                                <button type="button" class="button button-small attach-question-btn">
                                    Add to Survey
                                </button>
                            </div>
                        `;
                            });
                        }
                        $('#existing-questions-list').html(html || '<p>No available questions found</p>');
                    } else {
                        $('#existing-questions-list').html('<p>Error loading questions</p>');
                    }
                },
                error: function () {
                    $('#existing-questions-list').html('<p>Server error</p>');
                }
            });
        }
        // Add new question
        $('#add-question-btn').on('click', function () {
            var questionHtml = `
                <div class="question-item" data-question-id="new">
                    <div class="question-header">
                        <span class="question-number">New.</span>
                        <input type="text" class="question-text" placeholder="Enter your question">
                        <button type="button" class="button button-small remove-question">Remove</button>
                    </div>
                    <div class="options-container">
                        <p class="options-placeholder">No options yet.</p>
                    </div>
                    <div class="add-option-wrapper">
                        <input type="text" class="new-option-text" placeholder="New option text">
                        <input type="number" class="new-option-score" placeholder="Score" step="0.1" value="0" style="width: 80px;">
                        <button type="button" class="button add-option-btn">Add Option</button>
                    </div>
                </div>
            `;

            $('.no-questions').remove();
            $('#questions-container').append(questionHtml);
            updateQuestionNumbers();
        });

        // Открыть модальное окно
        $('#add-existing-question-btn').on('click', function () {
            loadExistingQuestions();
            $('#existing-questions-modal').fadeIn();
        });

        // Закрыть модальное окно
        $('.modal-close, .modal-cancel').on('click', function () {
            $('#existing-questions-modal').fadeOut();
        });

        // Remove question with AJAX
        $(document).on('click', '.remove-question', function () {
            if (!confirm('Delete this question and all its options?')) return;

            var $question = $(this).closest('.question-item');
            var questionId = $question.data('question-id');
            var $btn = $(this);

            if (questionId === 'new') {
                $question.remove();
                updateQuestionNumbers();
                if ($('.question-item').length === 0) {
                    $('#questions-container').html('<div class="no-questions"><p>No questions yet. Click "Add Question" to create your first question.</p></div>');
                }
                return;
            }

            $btn.prop('disabled', true).text('Deleting...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'survey_sphere_delete_question',
                    _wpnonce: surveySphereAdmin.nonce,
                    question_id: questionId
                },
                success: function (response) {
                    if (response.success) {
                        $question.remove();
                        updateQuestionNumbers();
                        if ($('.question-item').length === 0) {
                            $('#questions-container').html('<div class="no-questions"><p>No questions yet. Click "Add Question" to create your first question.</p></div>');
                        }
                    } else {
                        alert('Error: ' + response.data.message);
                        $btn.prop('disabled', false).text('Remove');
                    }
                },
                error: function () {
                    alert('Server error occurred');
                    $btn.prop('disabled', false).text('Remove');
                }
            });
        });

        // Add option
        $(document).on('click', '.add-option-btn', function () {
            var $wrapper = $(this).closest('.add-option-wrapper');
            var $question = $(this).closest('.question-item');
            var questionId = $question.data('question-id');
            var optionText = $wrapper.find('.new-option-text').val();
            var optionScore = $wrapper.find('.new-option-score').val();

            if (!optionText) {
                alert('Please enter option text');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).text('Adding...');

            if (questionId === 'new') {
                saveQuestion($question, function (success, newQuestionId) {
                    if (success) {
                        questionId = newQuestionId;
                        $question.data('question-id', questionId);
                        addOption(questionId, optionText, optionScore, $wrapper, $btn, $question);
                    } else {
                        $btn.prop('disabled', false).text('Add Option');
                    }
                });
            } else {
                addOption(questionId, optionText, optionScore, $wrapper, $btn, $question);
            }
        });

        // Remove option with AJAX
        $(document).on('click', '.remove-option', function () {
            if (!confirm('Delete this option?')) return;

            var $option = $(this).closest('.option-item');
            var optionId = $option.data('option-id');
            var $btn = $(this);

            if (!optionId || optionId === 'new') {
                $option.remove();
                updateOptionLetters($option.closest('.options-container'));
                return;
            }

            $btn.prop('disabled', true).text('...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'survey_sphere_delete_option',
                    _wpnonce: surveySphereAdmin.nonce,
                    option_id: optionId
                },
                success: function (response) {
                    if (response.success) {
                        $option.remove();
                        updateOptionLetters($option.closest('.options-container'));
                    } else {
                        alert('Error: ' + response.data.message);
                        $btn.prop('disabled', false).text('✕');
                    }
                },
                error: function () {
                    alert('Server error occurred');
                    $btn.prop('disabled', false).text('✕');
                }
            });
        });

        // Прикрепить существующий вопрос
        $(document).on('click', '.attach-question-btn', function () {
            var $item = $(this).closest('.existing-question-item');
            var questionId = $item.data('question-id');
            var orderIndex = $('.question-item').length;

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'survey_sphere_attach_question',
                    _wpnonce: surveySphereAdmin.nonce,
                    survey_id: surveyId,
                    question_id: questionId,
                    order_index: orderIndex
                },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                }
            });
        });

        // Update chart type
        $('#chart-type-select').on('change', function () {
            var chartType = $(this).val();
            var surveyIdFromSelect = $(this).data('survey-id');
            var $select = $(this);

            $select.prop('disabled', true);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'survey_sphere_update_chart_type',
                    _wpnonce: surveySphereAdmin.nonce,
                    survey_id: surveyIdFromSelect,
                    chart_type: chartType
                },
                success: function (response) {
                    if (response.success) {
                        var $status = $('.save-status');
                        $status.text('Chart type updated!').fadeIn();
                        setTimeout(function () {
                            $status.fadeOut();
                        }, 2000);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function () {
                    alert('Server error occurred');
                },
                complete: function () {
                    $select.prop('disabled', false);
                }
            });
        });

        $('#save-survey-btn').on('click', function () {
            alert('Survey saved successfully!');
        });
    });

})(jQuery);