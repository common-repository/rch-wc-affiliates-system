<?php

defined( 'ABSPATH' ) or exit;

$begin_date = isset( $_REQUEST['begin_date'] ) ? addslashes( date( 'Y-m-d 00:00', strtotime( $_REQUEST['begin_date'] ) ) ) : date( 'Y-m-01 00:00:00' );
$end_date = isset( $_REQUEST['end_date'] ) ? addslashes( date( 'Y-m-d 00:00', strtotime( $_REQUEST['end_date'] ) ) ) : date( 'Y-m-01 00:00', strtotime( '+1 month' ) );

?>
<div id="rchp-section-affiliates-report" class="rchp-section" style="display: block;">
    <form id="rchp-affiliates-report-form">
        <input type="hidden" name="report_type" value="affiliates">
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
    <div id="rchp-affiliates-report-results" class="rchp-section-reports-results"></div>
    <div id="rchp-edit-affiliate-container">
        <a href="#" class="button rchp-edit-affiliate-cancel rchp-edit-affiliate-close-button"><i class="fas fa-times"></i></a>
        <form id="rchp-edit-affiliate-form">
            <input type="hidden" id="rchp-edit-affiliate-id" name="affiliate_id" value="">
            <p class="form-field">
                <label for="rchp-edit-name"><?php _e( 'Affiliate name', 'rch-woocommerce-affiliates' ); ?></label>
                <input type="text" name="name" id="rchp-edit-name" required>
            </p>
            <p class="form-field">
                <label for="rchp-edit-user_id"><?php _e( 'User', 'rch-woocommerce-affiliates' ); ?></label>
                <?php
                    wp_dropdown_users( array(
                        'show_option_none'  => 'None',
                        'name'              => 'user_id',
                        'id'                => 'rchp-edit-user_id',
                        'show'              => 'user_login',
                    ) );
                ?>
            </p>
            <p class="form-field">
                <label for="rchp-edit-commission"><?php _e( 'Commission rate', 'rch-woocommerce-affiliates' ); ?> (%)</label>
                <input type="text" name="commission" id="rchp-edit-commission" value="<?php echo number_format( $rch_affiliates->default_commission, 4 ); ?>" required="">
            </p>
            <p class="form-field">
                <label for="rchp-edit-code"><?php _e( 'Affiliate code', 'rch-woocommerce-affiliates' ); ?></label>
                <input type="text" name="code" id="rchp-edit-code" class="rchp-alphanumeric" required>
            </p>
            <div class="rchp-input-field-container">
                <input type="submit" id="rchp-edit-affiliate-save-button" class="button button-primary" value="<?php _e( 'Save', 'rch-woocommerce-affiliates' ); ?>">
                <a href="#" class="rchp-edit-affiliate-cancel"><?php _e( 'Cancel', 'rch-woocommerce-affiliates' ); ?></a>
                <a href="#" class="rchp-edit-affiliate-delete"><?php _e( 'Delete Affiliate', 'rch-woocommerce-affiliates' ); ?></a>
            </div>
            <div class="rchp-admin-message"></div>
        </form>
    </div>
</div>
<?php
