<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'WC_Email_RCH_Affiliates_Enroll' ) ) :

class WC_Email_RCH_Affiliates_Enroll extends WC_Email {

    function __construct() {

        $this->id = 'rch_affiliates_enroll';
        $this->customer_email = false;
        $this->title = __( 'New Affiliate Enrollment Request', 'rch-woocommerce-affiliates' );
        $this->description = __( 'This email is sent from the Affiliate Enrollment form.', 'rch-woocommerce-affiliates' );

        $this->default_subject = __( 'New Affiliate Enrollment Request', 'rch-woocommerce-affiliates' );
        $this->default_heading = __( 'There is a new request for the Affiliate program', 'rch-woocommerce-affiliates' );

        $this->template_html = 'emails/rch-affiliates-enroll.php';
        $this->template_plain = 'emails/plain/rch-affiliates-enroll.php';

        add_action( 'rch_affiliates_pending_email_notification', array( $this, 'queue_notification' ), 10, 3 );
        add_action( 'rch_affiliates_recipient_email_notification', array( $this, 'trigger' ), 10, 3 );

        parent::__construct();

        $this->enabled = 'yes';

        $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );

        $this->template_base = RCHP_PLUGIN_ROOT . 'templates/woocommerce/';
    }

    public function queue_notification( $name, $email, $comments ) {
        $this->trigger( $name, $email, $comments );
    }

    function trigger( $name, $email, $comments ) {
        if ( ! $this->get_recipient() ) {
            return;
        }

        $this->object = $this->create_object( $name, $email, $comments );

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), array() );
    }

    public static function create_object( $name, $email, $comments ) {
        $item_data = new stdClass();

        $item_data->name = $name;
        $item_data->email = $email;
        $item_data->comments = $comments;

        return $item_data;
    }

    function get_content_html() {
        ob_start();

        wc_get_template(
            $this->template_html,
            array(
                'email' => $this,
                'item_data' => $this->object,
                'email_heading' => $this->get_heading()
            ),
            '',
            $this->template_base
        );

        return ob_get_clean();
    }

    function get_content_plain() {
        ob_start();

        wc_get_template(
            $this->template_plain,
            array(
                'email' => $this,
                'item_data' => $this->object,
                'email_heading' => $this->get_heading()
            ),
            '',
            $this->template_base
        );

        return ob_get_clean();
    }

    // form fields that are displayed in WooCommerce->Settings->Emails
    function init_form_fields() {
        $this->form_fields = array(
            'recipient' => array(
                'title'         => __( 'Recipient', 'rch-woocommerce-affiliates' ),
                'type'          => 'text',
                'description'   => __( 'Enter the email address (or addresses, comma separated) that should be notificationed when there is a new Affiliate enrollment request.', 'rch-woocommerce-affiliates' ),
                'default'       => get_option( 'admin_email' )
            ),
            'subject' => array(
                'title'         => __( 'Subject', 'rch-woocommerce-affiliates' ),
                'type'          => 'text',
                'description'   => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'rch-woocommerce-affiliates' ), $this->subject ),
                'placeholder'   => '',
                'default'       => $this->default_subject
            ),
            'heading' => array(
                'title'         => __( 'Email Heading', 'rch-woocommerce-affiliates' ),
                'type'          => 'text',
                'description'   => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'rch-woocommerce-affiliates' ), $this->heading ),
                'placeholder'   => '',
                'default'       => $this->default_heading
            ),
            'email_type' => array(
                'title'         => __( 'Email type', 'rch-woocommerce-affiliates' ),
                'type'          => 'select',
                'description'   => __( 'Choose which format of email to send.', 'rch-woocommerce-affiliates' ),
                'default'       => 'html',
                'class'         => 'email_type',
                'options'       => array(
                    'plain'         => __( 'Plain text', 'rch-woocommerce-affiliates' ),
                    'html'          => __( 'HTML', 'rch-woocommerce-affiliates' ),
                    'multipart'     => __( 'Multipart', 'rch-woocommerce-affiliates' ),
                )
            )
        );
    }
}

endif;

return new WC_Email_RCH_Affiliates_Enroll();
