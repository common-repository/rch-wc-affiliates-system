<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $rch_affiliate_code;

if ( !empty( $rch_affiliate_code ) ) {
	?>
    <tr class="rch-affiliate-code-checkout">
        <th><?php _e( 'Affiliate Code', 'rch-woocommerce-affiliates' ); ?></th>
        <td data-title="<?php esc_attr_e( 'Affiliate Code', 'rch-woocommerce-affiliates' ); ?>"><?php echo esc_html( $rch_affiliate_code ); ?></td>
    </tr>
	<?php
}
