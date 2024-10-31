<?php

defined( 'ABSPATH' ) or exit;

global $rch_affiliates_admin;

?>
<div id="rchp-section-commissions" class="rchp-section">
    <form id="rchp-save-commissions-form" method="post">
        <div class="rchp-section-subtitle">
            <?php _e( 'Default commission', 'rch-woocommerce-affiliates' ); ?>
        </div>
        <div>
            <?php _e( 'Used when creating new Affiliates. Can be from 0 to 100% with up to 4 decimal precision.', 'rch-woocommerce-affiliates' ); ?>
        </div>
        <div>
            <input type="text" class="rchp-input-text-small" name="default_commission" value="<?php echo number_format( get_option( 'rch_affiliates_default_commission', '0' ), 4 ); ?>" autocomplete="off" required>%
        </div>
        <div style="margin-top: 4px;">
            <input type="submit" id="rchp-save-commissions-button" class="button button-primary" value="<?php _e( 'Save', 'rch-woocommerce-affiliates' ); ?>">
            <div id="rchp-save-commissions-message"></div>
        </div>

        <div class="rchp-section-spacer"></div>
        <div class="rchp-section-subtitle">
            <?php _e( 'Commissions by Category', 'rch-woocommerce-affiliates' ); ?>
        </div>
        <div>
            <?php
                _e( 'As a way to encourage affiliates to push certain categories, consider offering a higher commission for that category.', 'rch-woocommerce-affiliates' );
            ?>
            <br>
            <?php
                printf( __( 'To specify a commission by category visit the %s', 'rch-woocommerce-affiliates' ), '<a href="' . admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product' ) . '">' . __( 'WooCommerce Categories setup page.', 'rch-woocommerce-affiliates' ) . '</a>' );
            ?>
        </div>

        <div class="rchp-section-spacer"></div>
        <div class="rchp-section-subtitle">
            <?php _e( 'Commissions by Product', 'rch-woocommerce-affiliates' ); ?>
        </div>
        <div>
            <?php
                _e( 'Individual products can have their own commission rate.', 'rch-woocommerce-affiliates' );
            ?>
            <br>
            <?php
                _e( 'Edit a Product to set the "Commission Rate" field.', 'rch-woocommerce-affiliates' );
            ?>
            <a href="<?php echo admin_url( 'edit.php?post_type=product' ); ?>"><?php _e( 'Go to the WooCommerce Products listing page.', 'rch-woocommerce-affiliates' ); ?></a>
        </div>

        <div class="rchp-section-spacer"></div>
        <div class="rchp-section-subtitle">
            <?php _e( 'Commissions by Affiliate', 'rch-woocommerce-affiliates' ); ?>
        </div>
        <div>
            <?php
                _e( 'Each Affiliate can have their own commission rate. You can specify the commission rate when creating or editing an Affiliate.', 'rch-woocommerce-affiliates' );
            ?>
        </div>
    </form>
</div>
