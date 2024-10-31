<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'RCH_Affiliate' ) ) :

class RCH_Affiliate {

    /*
     *
     * Properties
     *
     */
    public function get_id() { return $this->rcode_affiliate_id; }
    private $rcode_affiliate_id;

    public function get_code() { return $this->code; }
    private $code;

    public function get_name() { return $this->name; }
    private $name;

    public function get_user_id() { return $this->user_id; }
    private $user_id;

    public function get_commission() { return $this->commission; }
    private $commission;

    public function get_active() { return $this->active; }
    private $active;

    public function get_create_date() { return $this->create_date; }
    private $create_date;

    public function get_error_message() { return $this->error_message; }
    private $error_message;

    function __construct( $code ) {
        global $wpdb;

        $code = sanitize_text_field( $code );
        if ( !empty( $code ) ) {

            $result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->rcode_affiliate}` WHERE `code` = %s", $code ) );
            if ( $result !== null ) {
                $this->rcode_affiliate_id     = $result->rcode_affiliate_id;
                $this->code                     = $result->code;
                $this->name                     = $result->name;
                $this->user_id                  = $result->user_id;
                $this->commission               = $result->commission;
                $this->active                   = boolval( $result->active );
                $this->create_date              = $result->create_date;
            } else {
                $this->error_message = __( 'Affiliate does not exist.', 'rch-woocommerce-affiliates' );
            }
        } else {
            $this->error_message = __( 'Enter a code.', 'rch-woocommerce-affiliates' );
        }
    }



    /*
     *
     * Public methods.
     *
     */
    public function get_url() {
        return rchp_affiliate_url( $this->code );
    }

    public function get_orders( $begin_date, $end_date ) {
        $range = $begin_date . '...' . $end_date;

        if ( !isset( $this->orders[ $range ] ) ) {
            $orders = wc_get_orders( array(
                '_rch_affiliate_code' => $this->get_code(),
                'date_created' => $range,
                'status' => 'completed',
            ) );

            $this->orders[ $range ] = apply_filters( 'rchp_affiliate_orders', $orders );
        }

        return $this->orders[ $range ];
    }
    private $orders = array();

    public function get_total_commission( $begin_date, $end_date ) {
        $range = $begin_date . '...' . $end_date;

        if ( !isset( $this->total_commission[ $range ] ) ) {
            $total_commission = 0;
            $orders = $this->get_orders( $begin_date, $end_date );

            foreach ( $orders as $order ) {
                foreach ( $order->get_items( 'line_item' ) as $line_item ) {
                    $total_commission += $line_item->get_meta( '_rch_affiliate_commission' );
                }
            }

            $this->total_commission[ $range ] = apply_filters( 'rchp_affiliate_orders', $total_commission );
        }

        return $this->total_commission[ $range ];
    }
    private $total_commission = array();

    public function delete() {
        global $wpdb;

        $wpdb->delete( $wpdb->rcode_affiliate, array( 'rcode_affiliate_id' => $this->get_id() ), array( '%d' ) );
    }



    /*
     *
     * Static Methods
     *
     */

    public static function plugin_activate() {
        global $wpdb;

        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        $wpdb->query( "
            CREATE TABLE IF NOT EXISTS `{$wpdb->rcode_affiliate}` (
                `rcode_affiliate_id` INT NOT NULL AUTO_INCREMENT,
                `code` TEXT NOT NULL,
                `name` TEXT NOT NULL,
                `user_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `commission` DECIMAL( 7, 4 ) NOT NULL DEFAULT 0,
                `active` TINYINT(1) NOT NULL DEFAULT 1,
                `create_user_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `create_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`rcode_affiliate_id`),
                INDEX `ix_rcode_affiliate_id` (`rcode_affiliate_id`),
                UNIQUE `idx_code` ( `code` (128) )
            );
        " );
        if ( $wpdb->last_error != '' ) {
            wp_die( $wpdb->last_error );
        }
    }



    /*
     *
     * Private methods
     *
     */
    private function update_property( $property, $value ) {
        global $wpdb;

        if ( property_exists( $this, $property ) ) {
            if ( $this->{$property} != $value ) {
                $result = $wpdb->update( $wpdb->rcode_affiliate, array ( $property => $value ), array( 'rcode_affiliate_id' => $this->get_id() ) );

                if ( $result !== false ) {
                    $this->{$property} = $value;

                    return true;
                } else {
                    wp_die( $wpdb->last_error );
                }
            }

        } else {
            wp_die( sprintf( __( 'Property %s does not exist on %s', 'rch-woocommerce-affiliates' ), $property, get_class() ) );
        }
    }
}

register_activation_hook( RCHP_PLUGIN_FILE, array( 'RCH_Affiliate', 'plugin_activate' ) );

endif;

?>
