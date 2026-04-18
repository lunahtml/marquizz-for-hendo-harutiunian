/**
 * Survey Sphere Admin JavaScript
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
        // Copy shortcode functionality
        $('.copy-shortcode').on('click', function () {
            var shortcode = $(this).data('shortcode');

            // Create temporary input
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(shortcode).select();

            try {
                document.execCommand('copy');
                alert('Shortcode copied to clipboard!');
            } catch (err) {
                alert('Failed to copy shortcode');
            }

            $temp.remove();
        });

        // Delete confirmation
        $('.delete-survey').on('click', function (e) {
            if (!confirm('Are you sure you want to delete this survey?')) {
                e.preventDefault();
            }
        });
    });

})(jQuery);