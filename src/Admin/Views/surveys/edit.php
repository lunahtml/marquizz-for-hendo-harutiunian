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
     
     <div id="survey-sphere-root" data-survey-id="<?php echo esc_attr($survey->publicId); ?>">
    <div class="loading">Loading React editor...</div>
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