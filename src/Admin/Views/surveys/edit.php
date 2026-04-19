<div id="survey-sphere-editor" data-survey-id="<?php echo esc_attr($survey->publicId); ?>">
    <div class="loading">Loading React editor...</div>

</div>

<div class="editor-footer">
    <button type="button" class="button button-primary" id="save-survey-btn">
        <?php esc_html_e('Save All Changes', 'survey-sphere'); ?>
    </button>
    <span class="spinner" style="float: none; margin: 0 10px;"></span>
    <span class="save-status"></span>
</div>