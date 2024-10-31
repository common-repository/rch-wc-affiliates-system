<?php

defined( 'ABSPATH' ) or exit;

if ( is_array( $affiliates ) ) {

    ?>
    <div class="rchp-admin-report-table-container">
        <button id="rchp-affiliates-report-export-button" class="button rchp-export-button"><i class="fas fa-file-export"></i> <?php _e( 'Export', 'rch-woocommerce-affiliates' ); ?></button>

        <table id="rchp-affiliates-table" class="rchp-admin-table">
            <thead>
                <tr>
                    <?php
                        $table_columns = rchp_affiliates_report_columns();
                        require( 'table-header.php' );
                    ?>
                    <th>
                        &nbsp;
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if ( count( $affiliates ) > 0 ) {
                        foreach ( $affiliates as $affiliate ) {
                            ?>
                            <tr data-id="<?php echo $affiliate->rcode_affiliate_id; ?>"
                                data-code="<?php echo esc_html( $affiliate->code ); ?>"
                                data-name="<?php echo esc_html( $affiliate->name ); ?>"
                                data-user_id="<?php echo esc_html( $affiliate->user_id ); ?>"
                                data-commission="<?php echo $affiliate->commission; ?>"
                            >
                                <td>
                                    <?php echo esc_html( $affiliate->name ); ?>
                                </td>
                                <td>
                                    <?php echo esc_html( $affiliate->user ); ?>
                                </td>
                                <td>
                                    <?php echo esc_html( $affiliate->code ); ?>
                                </td>
                                <td>
                                    <a href="<?php echo $affiliate->url; ?>" class="rchp-copy-url" title="<?php _e( 'Copy URL to Clipboard', 'rch-woocommerce-affiliates' ); ?>"><?php echo $affiliate->url; ?></a>
                                    <i class="fas fa-copy fa-fw" style="visibility: hidden;"></i>
                                </td>
                                <td>
                                    <?php echo $affiliate->commission; ?> %
                                </td>
                                <td>
                                    <?php echo number_format( $affiliate->order_count ); ?>
                                </td>
                                <td>
                                    <?php echo wc_price( $affiliate->revenue ); ?>
                                </td>
                                <td>
                                    <?php echo wc_price( $affiliate->total_commission ); ?>
                                </td>
                                <td>
                                    <a href="#" class="button rchp-view-orders" title="<?php _e( 'View Orders', 'rch-woocommerce-affiliates' ); ?>"><i class="fas fa-external-link-alt"></i> <?php _e( 'View Orders', 'rch-woocommerce-affiliates' ); ?></a>
                                    <a href="#" class="button rchp-edit-affiliate" title="<?php _e( 'Edit Affiliate', 'rch-woocommerce-affiliates' ); ?>"><i class="fas fa-edit"></i> <?php _e( 'Edit Affiliate', 'rch-woocommerce-affiliates' ); ?></a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="<?php echo count( $table_columns ) + 1; ?>">
                                <?php _e( 'No results', 'rch-woocommerce-affiliates' ); ?>
                            </td>
                        </tr>
                        <?php
                    }
                ?>
            </tbody>
        </table>
    </div>
    <?php
} else {
    ?>
    <div class="rchp-admin-message rchp-admin-error"><?php echo esc_html( $affiliates ); ?></div>
    <?php
}