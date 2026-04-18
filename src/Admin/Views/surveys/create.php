<?php
/**
 * Create survey view
 * @var string|null $error
 */
?>
<div class="wrap">
    <h1><?php esc_html_e('Create New Survey', 'survey-sphere'); ?></h1>
    
    <?php if (isset($error)): ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($error); ?></p>
        </div>
    <?php endif; ?>
    
    <form method="post" action="">
        <?php wp_nonce_field('survey_sphere_create_survey'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="name"><?php esc_html_e('Survey Name', 'survey-sphere'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           class="regular-text" 
                           required 
                           placeholder="<?php esc_attr_e('e.g., IT Diagnostics', 'survey-sphere'); ?>">
                    <p class="description">
                        <?php esc_html_e('Give your survey a descriptive name', 'survey-sphere'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="description"><?php esc_html_e('Description', 'survey-sphere'); ?></label>
                </th>
                <td>
                    <textarea id="description" 
                              name="description" 
                              class="large-text" 
                              rows="3"
                              placeholder="<?php esc_attr_e('Optional description...', 'survey-sphere'); ?>"></textarea>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="chart_type"><?php esc_html_e('Chart Type', 'survey-sphere'); ?></label>
                </th>
                <td>
                    <select id="chart_type" name="chart_type">
                        <option value="polar"><?php esc_html_e('Polar Area Chart', 'survey-sphere'); ?></option>
                        <option value="radar"><?php esc_html_e('Radar Chart', 'survey-sphere'); ?></option>
                        <option value="bar"><?php esc_html_e('Bar Chart', 'survey-sphere'); ?></option>
                        <option value="doughnut"><?php esc_html_e('Doughnut Chart', 'survey-sphere'); ?></option>
                    </select>
                    <p class="description">
                        <?php esc_html_e('Choose how to visualize survey results', 'survey-sphere'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" class="button button-primary">
                <?php esc_html_e('Create Survey', 'survey-sphere'); ?>
            </button>
            <a href="<?php echo admin_url('admin.php?page=survey-sphere'); ?>" class="button">
                <?php esc_html_e('Cancel', 'survey-sphere'); ?>
            </a>
        </p>
    </form>
</div>