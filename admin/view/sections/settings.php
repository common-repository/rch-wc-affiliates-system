<?php

defined( 'ABSPATH' ) or exit;

global $rch_affiliates_admin;

?>
<div id="rchp-section-settings" class="rchp-section">
    <form id="rchp-save-settings-form" method="post">
        <?php
            $settings = $rch_affiliates_admin->settings;
            $settings[0]['title'] = '';

            WC_Admin_Settings::output_fields( $settings );
        ?>
        <p><input type="submit" id="rchp-save-settings-button" class="button button-primary" value="<?php _e( 'Save settings', 'rch-woocommerce-affiliates' ); ?>"></p>
        <div id="rchp-save-settings-message"></div>
    </form>
</div>
