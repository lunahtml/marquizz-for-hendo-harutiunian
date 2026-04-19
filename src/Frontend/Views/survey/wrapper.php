<?php
/**
 * Survey wrapper template
 */
?>
<div id="survey-sphere-root" 
     data-survey-id="<?php echo esc_attr($survey->publicId); ?>"
     data-survey="<?php echo esc_attr(json_encode($surveyData)); ?>">
</div>