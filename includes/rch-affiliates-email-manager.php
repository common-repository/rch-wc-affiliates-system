<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'RCH_Affiliates_Email_Manager' ) ) :

final class RCH_Affiliates_Email_Manager {

    function __construct() {
        add_action( 'rch_affiliates_send_enrollment_email', array( $this, 'trigger_email_action' ), 10, 3 );
        add_filter( 'woocommerce_email_classes', array( $this, 'woocommerce_email_classes' ) );
        add_action( 'woocommerce_email_actions', array( $this, 'woocommerce_email_actions' ) );
    }

    function woocommerce_email_classes( $emails ) {
        if ( ! isset( $emails[ 'WC_Email_RCH_Affiliates_Enroll' ] ) ) {
            $emails[ 'WC_Email_RCH_Affiliates_Enroll' ] = include_once( 'emails/class-wc-email-rch-affiliates-enroll.php' );
        }

        return $emails;
    }

    function trigger_email_action( $name, $email, $comments ) {
        WC_Emails::instance();
        do_action( 'rch_affiliates_pending_email_notification', $name, $email, $comments );
    }

    function woocommerce_email_actions( $email_actions ) {
        $email_actions[] = 'rch_affiliates_pending_email';
        $email_actions[] = 'rch_affiliates_recipient_email';

        return $email_actions;
    }
}

new RCH_Affiliates_Email_Manager();

endif;
