<?php

echo $email_heading . "\n\n";

echo __( 'Please contact them via email.', 'rch-woocommerce-affiliates' ) . "\n";
echo "\n";
echo __( 'Organization name: ', 'rch-woocommerce-affiliates' ) . $item_data->name . "\n";
echo __( 'Email address: ', 'rch-woocommerce-affiliates' ) . $item_data->email . "\n";
echo __( 'Comments: ', 'rch-woocommerce-affiliates' ) . $item_data->comments . "\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
