<?php

defined( 'ABSPATH' ) or exit;

$begin_date = isset( $_REQUEST['begin_date'] ) ? addslashes( date( 'Y-m-d 00:00', strtotime( $_REQUEST['begin_date'] ) ) ) : date( 'Y-m-01 00:00:00' );
$end_date = isset( $_REQUEST['end_date'] ) ? addslashes( date( 'Y-m-d 00:00', strtotime( $_REQUEST['end_date'] ) ) ) : date( 'Y-m-01 00:00', strtotime( '+1 month' ) );

?>
<div id="rchp-section-products-report" class="rchp-section">
    <form id="rchp-products-report-form">
        <input type="hidden" name="report_type" value="products">
        <input type="hidden" name="sort" value="">
        <input type="hidden" name="sort_order" value="">

        <table>
            <tr>
                <td style="text-align: right;"><?php _e( 'Affiliate', 'rch-woocommerce-affiliates' ); ?></td>
                <td>
                    <select name="affiliate_id">
                        <option value="0">
                            <?php esc_html_e( 'All Affiliates', 'rch-woocommerce-affiliates' ); ?>
                        </option>

                        <?php
                            foreach ( $affiliates_list as $affiliate ) {
                                ?>
                                <option value="<?php echo esc_attr( $affiliate->rcode_affiliate_id ); ?>">
                                    <?php echo esc_html( sprintf( '%s (%s)', $affiliate->name, $affiliate->code ) ); ?>
                                </option>
                                <?php
                            }
                        ?>
                    </select>
                </td>
    
                <td style="text-align: right;"><?php _e( 'Order Dates', 'rch-woocommerce-affiliates' ); ?></td>
                <td>
                    <input type="text" name="begin_date" class="rchp-date" value="<?php echo date( 'Y-m-d', strtotime( $begin_date ) ); ?>" autocomplete="off" required>
                    <input type="text" name="end_date" class="rchp-date" value="<?php echo date( 'Y-m-d', strtotime( $end_date ) ); ?>" autocomplete="off" required>
                </td>
                <td>&nbsp;</td>
                <td>
                    <input type="submit" class="button button-primary" value="<?php _e( 'Apply filters', 'rch-woocommerce-affiliates' ); ?>">
                </td>
            </tr>
        </table>
    </form>
    <br /> <br />
    <div id="rchp-products-report-results" class="rchp-section-reports-results"></div>
</div>
<?php
