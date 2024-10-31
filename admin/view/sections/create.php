<?php

defined( 'ABSPATH' ) or exit;

?>
<div id="rchp-section-create" class="rchp-section">
    <form id="rchp-create-affiliate-form">
        <p class="form-field">
            <label for="rchp-create-name"><?php _e( 'Affiliate name', 'rch-woocommerce-affiliates' ); ?></label>
            <input type="text" name="name" id="rchp-create-name" required>
        </p>
        <p class="form-field">
            <input type="checkbox" name="create-code-automatically" id="rchp-create-code-automatically" checked="checked">
            <label for="rchp-create-code-automatically"><?php _e( 'Automatically generate an affiliate code', 'rch-woocommerce-affiliates' ); ?></label>
        </p>
        <p id="rchp-create-manual-code-container" class="form-field rchp-hidden">
            <label for="rchp-create-code"><?php _e( 'Affiliate code', 'rch-woocommerce-affiliates' ); ?></label>
            <input type="text" name="code" id="rchp-create-code" class="rchp-alphanumeric">
        </p>
        <p class="form-field">
            <label for="rchp-create-user_id"><?php _e( 'User who can see the reports for this Affiliate when logged in.', 'rch-woocommerce-affiliates' ); ?></label>
            <?php
                wp_dropdown_users( array(
                    'show_option_none'  => 'None',
                    'name'              => 'user_id',
                    'id'                => 'rchp-create-user_id',
                    'show'              => 'user_login',
                ) );
            ?>
        </p>
        <p class="form-field">
            <label for="rchp-create-commission"><?php _e( 'Commission rate', 'rch-woocommerce-affiliates' ); ?> (%)</label>
            <input type="text" name="commission" id="rchp-create-commission" value="<?php echo number_format( $rch_affiliates->default_commission, 4 ); ?>" required="">
        </p>
        <div class="rchp-input-field-container" style="margin-top: 12px;">
            <input type="submit" id="rchp-create-affiliate-save-button" class="button button-primary" value="<?php _e( 'Create affiliate', 'rch-woocommerce-affiliates' ); ?>">
        </div>
        <div class="rchp-admin-message"></div>
    </form>
    <p>
        <strong><?php _e( 'Enrollments', 'rch-woocommerce-affiliates' ); ?></strong><br>
        
        <?php
            printf( __( 'You can allow users to request enrollment in your affiliate program. Just create a page with the short code %s', 'rch-woocommerce-affiliates' ), '<strong><span class="rchp-shortcode">[' . RCHP_ENROLL_SHORTCODE . ']</span></strong>' );
        ?>
    </p>
</div>
