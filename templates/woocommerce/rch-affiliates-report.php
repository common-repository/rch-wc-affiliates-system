<?php

defined( 'ABSPATH' ) or exit;

$affiliate_code = rchp_current_user_affiliate_code();
if ( $affiliate_code === false ) {
    echo __( 'This account is not linked to an Affiliate account.', 'rch-woocommerce-affiliates' );
    return;
}

global $rch_affiliates;

$begin_date = isset( $_REQUEST['begin_date'] ) ? addslashes( date( 'Y-m-d 00:00', strtotime( $_REQUEST['begin_date'] ) ) ) : date( 'Y-m-01 00:00:00' );
$end_date = isset( $_REQUEST['end_date'] ) ? addslashes( date( 'Y-m-d 00:00', strtotime( $_REQUEST['end_date'] ) ) ) : date( 'Y-m-01 00:00', strtotime( '+1 month' ) );

$affiliate = new RCH_Affiliate( $affiliate_code );

?>
<style>
    .rchp-title {
        font-weight: 600;
        font-size: 150%;
    }

    .rchp-form {
        margin-top: 2.0em;
        margin-bottom: 1.0em;
    }

    .rchp-stats {
        display: flex;
    }

    .rchp-section {
        margin-right: 32px;
    }
</style>

<div class="rchp-title"><?php _e( 'Affiliate URL', 'rch-woocommerce-affiliates' ); ?></div>
<a href="<?php echo $affiliate->get_url(); ?>" class="rchp-copy-url" title="<?php _e( 'Copy URL to Clipboard', 'rch-woocommerce-affiliates' ); ?>"><?php echo $affiliate->get_url(); ?></a>

<?php
    if ( !empty( $affiliate->get_commission() ) ) {
        ?>
        <form class="rchp-form" method="GET">
            <div><?php _e( 'Order Dates', 'rch-woocommerce-affiliates' ); ?></div>
            <input type="text" name="begin_date" class="rchp-date" value="<?php echo date( 'Y-m-d', strtotime( $begin_date ) ); ?>" autocomplete="off" required>
            <input type="text" name="end_date" class="rchp-date" value="<?php echo date( 'Y-m-d', strtotime( $end_date ) ); ?>" autocomplete="off" required>
            <input type="submit" class="button button-primary" value="<?php _e( 'Filter', 'rch-woocommerce-affiliates' ); ?>">
        </form>
        <div class="rchp-stats">
            <div class="rchp-section">
                <div class="rchp-title"><?php _e( 'Commission rate', 'rch-woocommerce-affiliates' ); ?></div>
                <div>
                    <?php echo $affiliate->get_commission(); ?> %
                </div>
            </div>
            <div class="rchp-section">
                <div class="rchp-title"><?php _e( 'Orders', 'rch-woocommerce-affiliates' ); ?></div>
                <div>
                    <?php echo count( $affiliate->get_orders( $begin_date, $end_date ) ); ?>
                </div>
            </div>
            <div class="rchp-section">
                <div class="rchp-title"><?php _e( 'Commission', 'rch-woocommerce-affiliates' ); ?></div>
                <div>
                    <?php echo wc_price( $affiliate->get_total_commission( $begin_date, $end_date ) ); ?>
                </div>
            </div>
        </div>
    <?php
}
