<?php
/**
 * Edit survey view
 * @var Survey $survey
 * src/Admin/Views/surveys/edit.php
 */

 $questionRepo = new \SurveySphere\Database\Repositories\QuestionRepository();
 $optionRepo = new \SurveySphere\Database\Repositories\OptionRepository();
 $questions = $questionRepo->findBySurveyId($survey->id);
 ?>
 <div class="wrap">
     <h1>
         <?php echo esc_html($survey->name); ?>
         <a href="<?php echo admin_url('admin.php?page=survey-sphere'); ?>" class="page-title-action">
             <?php esc_html_e('Back to Surveys', 'survey-sphere'); ?>
         </a>
     </h1>
     
     <div id="survey-sphere-editor" data-survey-id="<?php echo esc_attr($survey->publicId); ?>">
         <div class="survey-settings" style="margin-bottom: 20px; padding: 15px; background: #fff; border: 1px solid #ccd0d4;">
             <label for="chart-type-select">
                 <strong><?php esc_html_e('Chart Type:', 'survey-sphere'); ?></strong>
             </label>
             <select id="chart-type-select" data-survey-id="<?php echo esc_attr($survey->publicId); ?>">
                 <option value="polarArea" <?php selected($survey->chartType, 'polarArea'); ?>>
                     <?php esc_html_e('Polar Area Chart', 'survey-sphere'); ?>
                 </option>
                 <option value="radar" <?php selected($survey->chartType, 'radar'); ?>>
                     <?php esc_html_e('Radar Chart', 'survey-sphere'); ?>
                 </option>
                 <option value="doughnut" <?php selected($survey->chartType, 'doughnut'); ?>>
                     <?php esc_html_e('Doughnut Chart', 'survey-sphere'); ?>
                 </option>
                 <option value="bar" <?php selected($survey->chartType, 'bar'); ?>>
                     <?php esc_html_e('Bar Chart', 'survey-sphere'); ?>
                 </option>
             </select>
         </div>
         
         <div class="survey-sphere-editor">
             <div class="editor-header">
                 <h2><?php esc_html_e('Questions', 'survey-sphere'); ?></h2>
                 <div>
                     <button type="button" class="button button-primary" id="add-question-btn">
                         <?php esc_html_e('Add Question', 'survey-sphere'); ?>
                     </button>
                     <button type="button" class="button" id="add-existing-question-btn">
                         <?php esc_html_e('Add Existing Question', 'survey-sphere'); ?>
                     </button>
                 </div>
             </div>
             
             <!-- Модальное окно выбора вопроса -->
             <div id="existing-questions-modal" style="display: none;">
                 <div class="modal-overlay"></div>
                 <div class="modal-content">
                     <div class="modal-header">
                         <h3><?php esc_html_e('Select Question from Library', 'survey-sphere'); ?></h3>
                         <button type="button" class="modal-close">&times;</button>
                     </div>
                     <div class="modal-body">
                         <input type="text" id="question-search" placeholder="<?php esc_attr_e('Search questions...', 'survey-sphere'); ?>">
                         <div id="existing-questions-list"></div>
                     </div>
                     <div class="modal-footer">
                         <button type="button" class="button modal-cancel"><?php esc_html_e('Cancel', 'survey-sphere'); ?></button>
                     </div>
                 </div>
             </div>
             
             <div class="questions-container" id="questions-container">
                 <?php if (empty($questions)): ?>
                     <div class="no-questions">
                         <p><?php esc_html_e('No questions yet. Click "Add Question" to create your first question.', 'survey-sphere'); ?></p>
                     </div>
                 <?php else: ?>
                     <?php foreach ($questions as $index => $question): ?>
                         <?php $options = $optionRepo->findByQuestionId($question->id); ?>
                         <div class="question-item" data-question-id="<?php echo esc_attr($question->publicId); ?>">
                             <div class="question-header">
                                 <span class="question-number"><?php echo esc_html($index + 1); ?>.</span>
                                 <input type="text" 
                                        class="question-text" 
                                        value="<?php echo esc_attr($question->text); ?>" 
                                        placeholder="<?php esc_attr_e('Enter your question', 'survey-sphere'); ?>">
                                 <button type="button" class="button button-small remove-question">
                                     <?php esc_html_e('Remove', 'survey-sphere'); ?>
                                 </button>
                             </div>
                             
                             <div class="options-container" data-question-id="<?php echo esc_attr($question->publicId); ?>">
                                 <?php if (empty($options)): ?>
                                     <p class="options-placeholder"><?php esc_html_e('No options yet.', 'survey-sphere'); ?></p>
                                 <?php else: ?>
                                     <?php foreach ($options as $optIndex => $option): ?>
                                         <div class="option-item" data-option-id="<?php echo esc_attr($option->publicId); ?>">
                                             <span class="option-letter"><?php echo chr(65 + $optIndex); ?>.</span>
                                             <input type="text" 
                                                    class="option-text" 
                                                    value="<?php echo esc_attr($option->text); ?>" 
                                                    placeholder="<?php esc_attr_e('Option text', 'survey-sphere'); ?>">
                                             <input type="number" 
                                                    class="option-score" 
                                                    value="<?php echo esc_attr($option->score); ?>" 
                                                    placeholder="<?php esc_attr_e('Score', 'survey-sphere'); ?>"
                                                    step="0.1"
                                                    style="width: 80px;">
                                             <button type="button" class="button button-small remove-option">
                                                 ✕
                                             </button>
                                         </div>
                                     <?php endforeach; ?>
                                 <?php endif; ?>
                             </div>
                             
                             <div class="add-option-wrapper">
                                 <input type="text" 
                                        class="new-option-text" 
                                        placeholder="<?php esc_attr_e('New option text', 'survey-sphere'); ?>">
                                 <input type="number" 
                                        class="new-option-score" 
                                        placeholder="<?php esc_attr_e('Score', 'survey-sphere'); ?>"
                                        step="0.1"
                                        value="0"
                                        style="width: 80px;">
                                 <button type="button" class="button add-option-btn">
                                     <?php esc_html_e('Add Option', 'survey-sphere'); ?>
                                 </button>
                             </div>
                         </div>
                     <?php endforeach; ?>
                 <?php endif; ?>
             </div>
             
             <div class="editor-footer">
                 <button type="button" class="button button-primary" id="save-survey-btn">
                     <?php esc_html_e('Save All Changes', 'survey-sphere'); ?>
                 </button>
                 <span class="spinner" style="float: none; margin: 0 10px;"></span>
                 <span class="save-status"></span>
             </div>
         </div>
     </div>
 </div>