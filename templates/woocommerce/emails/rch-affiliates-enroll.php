<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<div>
    <h1><?php _e( 'Affiliate enrollment request', 'rch-woocommerce-affiliates' ); ?></h1>
    <p>
        <?php _e( 'Please contact them via email.', 'rch-woocommerce-affiliates' ); ?>
    </p>
    <p>
        <strong><?php _e( 'Organization name', 'rch-woocommerce-affiliates' ); ?></strong><br>
        <?php echo $item_data->name; ?>
    </p>
    <p>
        <strong><?php _e( 'Email address', 'rch-woocommerce-affiliates' ); ?></strong><br>
        <a href="mailto: <?php echo $item_data->email; ?>"><?php echo $item_data->email; ?></a>
    </p>
    <p>
        <strong><?php _e( 'Comments', 'rch-woocommerce-affiliates' ); ?></strong><br>
        <?php echo $item_data->comments; ?>
    </p>
</div>

<?php do_action( 'woocommerce_email_footer' ); ?>
