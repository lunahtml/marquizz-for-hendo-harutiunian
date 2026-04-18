<?php
/**
 * Surveys list view
 * @var Survey[] $surveys
 * src/Admin/Views/surveys/list.php
 */
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Surveys', 'survey-sphere'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=survey-sphere-add'); ?>" class="page-title-action">
        <?php esc_html_e('Add New', 'survey-sphere'); ?>
    </a>
    <hr class="wp-header-end">
    
    <?php if (empty($surveys)): ?>
        <div class="notice notice-info">
            <p><?php esc_html_e('No surveys found. Create your first survey!', 'survey-sphere'); ?></p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Name', 'survey-sphere'); ?></th>
                    <th><?php esc_html_e('Chart Type', 'survey-sphere'); ?></th>
                    <th><?php esc_html_e('Status', 'survey-sphere'); ?></th>
                    <th><?php esc_html_e('Shortcode', 'survey-sphere'); ?></th>
                    <th><?php esc_html_e('Created', 'survey-sphere'); ?></th>
                    <th><?php esc_html_e('Actions', 'survey-sphere'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($surveys as $survey): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($survey->name); ?></strong>
                            <?php if ($survey->description): ?>
                                <br><small><?php echo esc_html($survey->description); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html(ucfirst($survey->chartType)); ?></td>
                        <td>
                            <?php if ($survey->isActive): ?>
                                <span style="color: green;">✓ <?php esc_html_e('Active', 'survey-sphere'); ?></span>
                            <?php else: ?>
                                <span style="color: gray;">✗ <?php esc_html_e('Inactive', 'survey-sphere'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code>[survey_sphere id="<?php echo esc_attr($survey->publicId); ?>"]</code>
                        </td>
                        <td><?php echo esc_html($survey->createdAt); ?></td>
                        <td>
                        <a href="<?php echo admin_url('admin.php?page=survey-sphere-edit&id=' . $survey->publicId); ?>" 
                            class="button button-small">
                                <?php esc_html_e('Edit', 'survey-sphere'); ?>
                        </a>
                            <button class="button button-small copy-shortcode" 
                                    data-shortcode='[survey_sphere id="<?php echo esc_attr($survey->publicId); ?>"]'>
                                <?php esc_html_e('Copy', 'survey-sphere'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    $('.copy-shortcode').on('click', function() {
        var shortcode = $(this).data('shortcode');
        navigator.clipboard.writeText(shortcode).then(function() {
            alert('Shortcode copied!');
        });
    });
});
</script>