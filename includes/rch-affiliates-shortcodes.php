<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'RCH_Affiliates_Shortcodes' ) ) :

final class RCH_Affiliates_Shortcodes {

    function __construct() {
        add_shortcode( RCHP_ENROLL_SHORTCODE, array( $this, 'enroll_shortcode' ) );

        add_action( 'wp_ajax_nopriv_rch-affiliates-enroll', array( $this, 'ajax_enroll' ) );
        add_action( 'wp_ajax_rch-affiliates-enroll', array( $this, 'ajax_enroll' ) );
    }

    function enroll_shortcode( $shortcode_attributes, $shortcode_content = '' ) {
        return $this->load_shortcode_template( 'rch-affiliates-enroll.php', $shortcode_attributes, $shortcode_content );
    }

    function ajax_enroll( ) {
        check_ajax_referer( 'rch-affiliates-enroll', 'security' );

        $name = wc_clean( $_POST['name'] );
        $email = wc_clean( $_POST['email'] );
        $comments = wc_clean( $_POST['comments'] );

        do_action( 'rch_affiliates_send_enrollment_email', $name, $email, $comments );

        wp_send_json_success();
    }

    function load_shortcode_template( $template_file, $shortcode_attributes, $shortcode_content = '' ) {
        ob_start();

        global $rchp_shortcode_attributes;
        global $rchp_shortcode_content;

        $rchp_shortcode_attributes = $shortcode_attributes;
        $rchp_shortcode_content = $shortcode_content;

        wc_get_template( "$template_file", '', '', RCHP_PLUGIN_ROOT . 'templates/woocommerce/' );

        return ob_get_clean();
    }
}

global $rch_affiliates_shortcodes;
$rch_affiliates_shortcodes = new RCH_Affiliates_Shortcodes();

endif;
