<?php

defined( 'ABSPATH' ) or exit;

if ( is_array( $products ) ) {
    ?>
    <div class="rchp-admin-report-table-container">
        <button id="rchp-products-report-export-button" class="button rchp-export-button"><i class="fas fa-file-export"></i> <?php _e( 'Export', 'rch-woocommerce-affiliates' ); ?></button>
        <table id="rchp-products-table" class="rchp-admin-table">
            <thead>
                <tr>
                    <?php
                        $table_columns = rchp_products_report_columns();
                        require( 'table-header.php' );
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    if ( count( $products ) > 0 ) {
                        foreach ( $products as $product ) {
                            ?>
                            <tr data-id="<?php echo $product->product_id; ?>">
                                <td>
                                    <?php
                                        if ( !empty( $product->product_id ) ) {
                                            ?>
                                            <a href="<?php echo get_edit_post_link( $product->product_id, 'edit' ); ?>"><?php echo esc_html( $product->product_name ); ?></a>
                                            <?php
                                        } else {
                                            echo esc_html( $product->product_name );
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php echo number_format( $product->quantity ); ?>
                                </td>
                                <td>
                                    <?php echo wc_price( $product->revenue ); ?>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="<?php echo count( $table_columns ); ?>">
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