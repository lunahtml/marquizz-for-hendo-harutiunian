<?php
/**
 * Survey wrapper template
 */
?>
<div class="survey-sphere-wrapper" 
     id="survey-sphere-<?php echo esc_attr($survey->publicId); ?>" 
     data-survey-id="<?php echo esc_attr($survey->publicId); ?>"
     data-please-select="<?php esc_attr_e('Please select an answer', 'survey-sphere'); ?>"
     data-answer-all="<?php esc_attr_e('Please answer all questions', 'survey-sphere'); ?>">
    
    <div class="survey-header">
        <h2><?php echo esc_html($survey->name); ?></h2>
        <?php if ($survey->description): ?>
            <p class="survey-description"><?php echo esc_html($survey->description); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="survey-progress">
        <div class="progress-bar">
            <div class="progress-fill" style="width: 0%"></div>
        </div>
        <div class="progress-text">
            <span class="current-question">1</span> / <span class="total-questions"><?php echo count($questions); ?></span>
        </div>
    </div>
    
    <form class="survey-form" method="post">
        <?php wp_nonce_field('survey_sphere_submit', 'survey_nonce'); ?>
        <input type="hidden" name="survey_id" value="<?php echo esc_attr($survey->publicId); ?>">
        
        <div class="questions-container">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-slide" data-question-index="<?php echo $index; ?>" style="<?php echo $index > 0 ? 'display: none;' : ''; ?>">
                    <div class="question-header">
                        <h3>
                            <span class="question-number"><?php echo $index + 1; ?>.</span>
                            <?php echo esc_html($question->text); ?>
                        </h3>
                    </div>
                    
                    <div class="options-list">
                        <?php 
                        $options = $optionsByQuestion[$question->id] ?? [];
                        foreach ($options as $option): 
                        ?>
                            <label class="option-item">
                                <input type="radio" 
                                       name="answers[<?php echo esc_attr($question->publicId); ?>]" 
                                       value="<?php echo esc_attr($option->publicId); ?>"
                                       data-score="<?php echo esc_attr($option->score); ?>"
                                       required>
                                <span class="option-text"><?php echo esc_html($option->text); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="survey-navigation">
            <button type="button" class="button prev-btn" style="display: none;"><?php esc_html_e('Previous', 'survey-sphere'); ?></button>
            <button type="button" class="button next-btn"><?php esc_html_e('Next', 'survey-sphere'); ?></button>
            <button type="submit" class="button button-primary submit-btn" style="display: none;"><?php esc_html_e('Show Results', 'survey-sphere'); ?></button>
        </div>
    </form>
    
    <div class="survey-results" style="display: none;">
        <div class="results-chart">
            <canvas id="survey-chart-<?php echo esc_attr($survey->publicId); ?>" 
                    data-chart-type="<?php echo esc_attr($survey->chartType); ?>"></canvas>
        </div>
        <div class="results-summary"></div>
        
        <div class="results-actions">
            <button type="button" class="button restart-btn"><?php esc_html_e('Restart Survey', 'survey-sphere'); ?></button>
            <button type="button" class="button button-primary save-result-btn"><?php esc_html_e('Save My Result', 'survey-sphere'); ?></button>
        </div>
        
        <div class="save-email-form" style="display: none; margin-top: 20px;">
            <input type="email" class="save-email-input" placeholder="<?php esc_attr_e('Your email', 'survey-sphere'); ?>">
            <button type="button" class="button button-primary confirm-save-btn"><?php esc_html_e('Save', 'survey-sphere'); ?></button>
            <button type="button" class="button cancel-save-btn"><?php esc_html_e('Cancel', 'survey-sphere'); ?></button>
            <p class="save-message" style="margin-top: 10px;"></p>
        </div>
    </div>
</div>