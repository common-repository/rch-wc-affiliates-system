<?php

defined( 'ABSPATH' ) or exit;

?>
<style>
    .rchp-form-field-label {
        font-weight: 600;
        display: block;
    }

    .rchp-form-field {
        margin-bottom: 1.0em;
        width: 100%;
    }
</style>
<form id="rchp-enroll-form">
    <label for="rchp-enroll-name" class="rchp-form-field-label"><?php _e( 'Organization name', 'rch-woocommerce-affiliates' ); ?></label>
    <input type="text" id="rchp-enroll-name" class="rchp-form-field" placeholder="<?php _e( 'Required', 'rch-woocommerce-affiliates' ); ?>" required>

    <label for="rchp-enroll-email" class="rchp-form-field-label"><?php _e( 'Email address', 'rch-woocommerce-affiliates' ); ?></label>
    <input type="text" id="rchp-enroll-email" class="rchp-form-field" placeholder="<?php _e( 'Required', 'rch-woocommerce-affiliates' ); ?>" required>

    <label for="rchp-enroll-comments" class="rchp-form-field-label"><?php _e( 'Comments', 'rch-woocommerce-affiliates' ); ?></label>
    <textarea id="rchp-enroll-comments" class="rchp-form-field" placeholder="<?php _e( 'Optional', 'rch-woocommerce-affiliates' ); ?>"></textarea>

    <input type="submit" id="rchp-enroll-submit" value="<?php _e( 'Submit', 'rch-woocommerce-affiliates' ); ?>">
    <div id="rchp-enroll-result"></div>
</form>
<script>
    jQuery(function() {
        jQuery('#rchp-enroll-form').on('submit', function(e) {
            var form = jQuery('#rchp-enroll-form');
            var name = jQuery('#rchp-enroll-name').val();
            var email = jQuery('#rchp-enroll-email').val();
            var comments = jQuery('#rchp-enroll-comments').val();
            var button = jQuery('#rchp-enroll-submit');
            var result = jQuery('#rchp-enroll-result');

            button.prop('disabled', true).val('<?php _e( 'Please wait...', 'rch-woocommerce-affiliates' ); ?>')
            result.text('').css('color', 'initial');

            jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {'action': 'rch-affiliates-enroll', 'name': name, 'email': email, 'comments': comments, 'security': '<?php echo wp_create_nonce( 'rch-affiliates-enroll' ); ?>'}, function( r ) {
                button.prop('disabled', false).val('<?php _e( 'Submit', 'rch-woocommerce-affiliates' ); ?>');
                if (r.success) {
                    result.text('<?php _e( 'Request sent.', 'rch-woocommerce-affiliates' ); ?>').css('color', 'blue');
                    form.trigger('reset');
                } else {
                    result.text(r.data.message).css('color', 'red');
                }
            }).fail(function(xhr, textStatus, errorThrown) {
                button.prop('disabled', false).val('<?php _e( 'Submit', 'rch-woocommerce-affiliates' ); ?>');
                if (errorThrown) {
                    result.text(errorThrown).css('color', 'red');
                } else {
                    result.text('<?php _e( 'Unknown error', 'rch-woocommerce-affiliates' ); ?>').css('color', 'red');
                }
            });

            e.preventDefault();
            return false;
        });
    });
</script>
<?php
