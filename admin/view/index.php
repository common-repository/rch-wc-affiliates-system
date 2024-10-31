<?php

defined( 'ABSPATH' ) or exit;

global $rch_affiliates;
global $wpdb;

$affiliates_list = rchp_affiliates_list();

$rch_affiliate_buttons['affiliates-report'] = array( 'title' => __( 'Affiliates report', 'rch-woocommerce-affiliates' ));
$rch_affiliate_buttons['products-report']   = array( 'title' => __( 'Products report', 'rch-woocommerce-affiliates' ));
$rch_affiliate_buttons['create']            = array( 'title' => __( 'Create an Affiliate', 'rch-woocommerce-affiliates' ) );
$rch_affiliate_buttons['commissions']       = array( 'title' => __( 'Commissions', 'rch-woocommerce-affiliates' ));
$rch_affiliate_buttons['settings']          = array( 'title' => __( 'Settings', 'rch-woocommerce-affiliates' ));
?>
<div class="rchp-main-content">
    <div class="rchp-section-container">
        <div class="rchp-sections">
            <?php
                $selected_class = 'rchp-section-item-selected';
                foreach ( $rch_affiliate_buttons as $name => $section ) {
                    ?>
                    <div class="rchp-section-item <?php echo $selected_class; $selected_class = ''; ?>" data-section="<?php echo esc_attr( $name ); ?>">
                        <div class="rchp-reports-item-title"><?php echo esc_html( $section['title'] ); ?></div>
                    </div>
                    <?php
                }
            ?>
        </div>
    </div>
    <?php
        foreach ( $rch_affiliate_buttons as $name => $section ) {
            require( 'sections/' . $name . '.php' );
        }
    ?>
</div>
