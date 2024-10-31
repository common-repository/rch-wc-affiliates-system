<?php
/**
 * Plugin Name: RCH WC Affiliates System
 * Description: The affiliates system allows you to run a successful affiliate for Marketing Program and WooCommerce Store.
 * Version: 1.0.0
 * Author: shopifytowp107
 * WC requires at least: 3.2.0
 * WC tested up to: 4.1
*/
define( 'RCHP_VERSION', '1.0.0' );

defined( 'ABSPATH' ) or exit;

if ( !function_exists( 'rcode_define' ) ) :
function rcode_define( $constant_name, $default_value ) {
    defined( $constant_name ) or define( $constant_name, $default_value );
}
endif;

rcode_define( 'RCHP_REQUIRES_PRIVILEGE', 'manage_woocommerce' );
rcode_define( 'RCHP_WC_VERSION_MINIMUM', '3.2.0' );
rcode_define( 'RCHP_PLUGIN_FILE', __FILE__ );
rcode_define( 'RCHP_PLUGIN_ROOT', plugin_dir_path( RCHP_PLUGIN_FILE ) );
rcode_define( 'RCHP_LICENSE_OPTION_NAME', 'rch_affiliates_license' );
rcode_define( 'RCHP_FONT_AWESOME_VERSION', '5.1.1' );
rcode_define( 'RCHP_RANDOM_CODE_LENGTH', '4' );
rcode_define( 'RCHP_RANDOM_CODE_CHARSET', 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789' );
rcode_define( 'RCHP_SESSION_KEY', 'rch-affiliates-code' );
rcode_define( 'RCHP_ENROLL_SHORTCODE', 'rch_affiliates_enroll' );

if ( ! class_exists( 'RCH_Affiliates' ) ) :

final class RCH_Affiliates {

    public $program_name;
    public $default_commission;
    public $url_fields;
    public $affiliate_endpoint;

    function __construct() {
        global $wpdb;

        $wpdb->rcode_affiliate = $wpdb->prefix . 'rcode_affiliate';

        $this->program_name = get_option( 'rch_affiliates_program_name', __( 'Affiliate Program', 'rch-woocommerce-affiliates' ) );
        if ( empty( $this->program_name ) ) { $this->program_name = __( 'Affiliate Program', 'rch-woocommerce-affiliates' ); }

        $this->default_commission = get_option( 'rch_affiliates_default_commission', '0' );
        if ( empty( $this->default_commission ) ) { $this->default_commission = '0'; }

        $this->url_fields = get_option( 'rchp_url_fields', 'affiliate' );
        if ( empty( $this->url_fields ) ) { $this->url_fields = 'affiliate'; }

        $this->affiliate_endpoint = get_option( 'rchp_affiliate_endpoint', 'affiliate-report' );
        if ( empty( $this->affiliate_endpoint ) ) { $this->affiliate_endpoint = 'affiliate-report'; }

        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
        add_action( 'woocommerce_init', array( $this, 'woocommerce_init' ) );
        add_action( 'init', array( $this, 'add_endpoints' ) );

        require_once( 'includes/class-rch-affiliate.php' );
        require_once( 'includes/rchp-functions.php' );
    }

    function plugins_loaded() {
        foreach ( array_map( 'trim', explode( ',', $this->url_fields ) ) as $url_field ) {
            if ( isset( $_GET[ $url_field ] ) ) {
                $code = sanitize_text_field( $_GET[ $url_field ] );
                if ( $this->add_affiliate_code_to_session( $code ) ) {
                    break;
                }
            }
        }
    }

    function woocommerce_init() {
        if ( is_admin() ) {
            require_once( 'admin/admin.php' );
        } else {
            add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
            add_action( 'woocommerce_applied_coupon', array( $this, 'woocommerce_applied_coupon' ) );
            add_filter( 'woocommerce_account_menu_items', array( $this, 'woocommerce_account_menu_items' ) );
            add_action( 'woocommerce_account_' . $this->affiliate_endpoint . '_endpoint', array( $this, 'affiliate_report' ) );
            add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( $this, 'woocommerce_order_data_store_cpt_get_orders_query' ), 10, 2 );
            add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'woocommerce_checkout_update_order_meta' ), 10, 2 );
            add_action( 'woocommerce_cart_totals_before_order_total', array( $this, 'woocommerce_cart_totals_before_order_total' ) );
            add_action( 'woocommerce_review_order_before_order_total', array( $this, 'woocommerce_review_order_before_order_total' ) );
        }

        add_filter( 'woocommerce_attribute_label', array( $this, 'woocommerce_attribute_label' ), 10, 3 );
        require_once( 'includes/rch-affiliates-email-manager.php' );
        require_once( 'includes/rch-affiliates-shortcodes.php' );
    }

    function add_endpoints() {
        add_rewrite_endpoint( $this->affiliate_endpoint, EP_ROOT | EP_PAGES );
    }

    public static function flush_rewrite_rules() {
        global $rch_affiliates;

        $rch_affiliates->add_endpoints();
        flush_rewrite_rules();
    }

    function wc_min_version( $version ) {
        return version_compare( WC()->version, $version, ">=" );
    }

    function relative_url( $url ) {
        return plugins_url( $url, RCHP_PLUGIN_FILE );
    }

    function insert_after( $items, $new_items, $after ) {
        // Search for the item position and +1 since is after the selected item key.
        $position = array_search( $after, array_keys( $items ) ) + 1;

        // Insert the new item.
        $array = array_slice( $items, 0, $position, true );
        $array += $new_items;
        $array += array_slice( $items, $position, count( $items ) - $position, true );

        return $array;
    }


    function wp_enqueue_scripts() {
        global $wp;

        if ( ! empty( $wp->query_vars ) && isset( $wp->query_vars['pagename'] ) && isset( $wp->query_vars[ $this->affiliate_endpoint ] ) ) {
            $myaccount_page = get_post( wc_get_page_id( 'myaccount' ) );
            if ( $wp->query_vars['pagename'] === $myaccount_page->post_name ) {
                wp_enqueue_script( 'rch-affiliates', $this->relative_url( '/assets/js/rch-affiliates.js' ), array( 'jquery' ), RCHP_VERSION );
                wp_localize_script( 'rch-affiliates', 'rchp', array(
                    'i18n' => array(
                        'loading' => __( 'Loading...', 'rch-woocommerce-affiliates' ),
                        'linkCopied' => __( 'Link copied to clipboard', 'rch-woocommerce-affiliates' ),
                    )
                ) );

                if ( boolval( get_option( 'rch_affiliates_use_builtin_jquery_styles', '1' ) ) ) {
                    wp_register_style( 'jquery-ui-style', $this->relative_url( '/assets/css/jquery-ui-style.min.css', __FILE__ ), array(), RCHP_VERSION );
                    wp_enqueue_style( 'jquery-ui-style' );
                }

                wp_enqueue_script( 'jquery-ui-datepicker' );
            }
        }
    }

    function woocommerce_applied_coupon( $coupon_code ) {
        if ( 'yes' == get_option( 'rch_affiliates_coupon_code_linking', 'yes' ) ) {
            $affiliate = rchp_get_active_affiliate( $coupon_code );
            if ( $affiliate ) {
                $this->add_affiliate_code_to_session( $coupon_code );
            }
        }
    }

    function woocommerce_account_menu_items( $items ) {
        if ( rchp_current_user_affiliate_code() !== false ) {
            $new_menu = array();
            $new_menu[ $this->affiliate_endpoint ] = $this->program_name;
            $items = $this->insert_after( $items, $new_menu, 'dashboard' );
        }
        return $items;
    }

    function affiliate_report() {
        $user = wp_get_current_user();
        wc_get_template( 'rch-affiliates-report.php', '', '', RCHP_PLUGIN_ROOT . 'templates/woocommerce/' );
    }

    function woocommerce_order_data_store_cpt_get_orders_query( $query, $query_vars ) {
        if ( ! empty( $query_vars['_rch_affiliate_code'] ) ) {
            $query['meta_query'][] = array(
                'key' => '_rch_affiliate_code',
                'value' => esc_attr( $query_vars['_rch_affiliate_code'] ),
            );
        }
        return $query;
    }

    function add_affiliate_code_to_session( $code ) {
        $affiliate = rchp_get_active_affiliate( $code );
        if ( $affiliate ) {
            $expires = absint( get_option( 'rch_affiliates_cookie_days', '30' ) ) * 60 * 60 * 24;
            setcookie( RCHP_SESSION_KEY, $affiliate->get_code(), time() + $expires, '/' );
            $_COOKIE[ RCHP_SESSION_KEY ] = $affiliate->get_code();
            return true;
        }
        return false;
    }

    function get_affiliate_code_from_session() {
        if ( isset( $_COOKIE[ RCHP_SESSION_KEY ] ) ) {
            return $_COOKIE[ RCHP_SESSION_KEY ];
        } else {
            return null;
        }
    }

    function woocommerce_checkout_update_order_meta( $order_id, $data ) {
        $code = $this->get_affiliate_code_from_session();
        if ( empty( $code ) ) {
            return;
        }

        $affiliate = rchp_get_active_affiliate( $code );
        if ( $affiliate ) {
            $order = new WC_Order( $order_id );

            $pre_tax = get_option( 'rch_affiliates_commission_before_tax', 'yes' );
            $total_commission = 0;

            foreach ( $order->get_items( 'line_item' ) as $line_item ) {
                $product = $line_item->get_product();
                if ( !empty( $product ) ) {
                    $rate = rchp_get_product_commission( $product, $affiliate );
                    if ( $rate > 0 ) {
                        $line_item_total = $line_item->get_total();
                        if ( 'no' === $pre_tax ) {
                            $line_item_total += $line_item->get_total_tax();
                        }

                        $line_item_commission = round( ( $line_item_total * ( $rate / 100 ) ), 4 );
                        $total_commission += $line_item_commission;

                        $line_item->update_meta_data( '_rch_affiliate_commission', $line_item_commission );
                        $line_item->save();
                    }
                }
            }

            $commission = round( $total_commission, wc_get_price_decimals() );

            $order->update_meta_data( '_rch_affiliate_code', $affiliate->get_code() );
            $order->update_meta_data( '_rch_affiliate_commission', $commission );
            $order->add_order_note( sprintf( __( 'Affiliate %s. Commission %s', 'rch-woocommerce-affiliates' ), $affiliate->get_code(), $commission ) );

            $order->save();
        }
    }

    function woocommerce_cart_totals_before_order_total() {
        if ( 'yes' === get_option( 'rch_affiliates_show_code_in_cart', 'no' ) ) {
            global $rch_affiliate_code;
            $rch_affiliate_code = $this->get_affiliate_code_from_session();

            wc_get_template( 'cart/rch-affiliates-cart-totals-before-order-total.php', '', '', RCHP_PLUGIN_ROOT . 'templates/woocommerce/' );
        }
    }

    function woocommerce_review_order_before_order_total() {
        if ( 'yes' === get_option( 'rch_affiliates_show_code_in_checkout', 'no' ) ) {
            global $rch_affiliate_code;
            $rch_affiliate_code = $this->get_affiliate_code_from_session();

            wc_get_template( 'checkout/rch-affiliates-review-order-before-order-total.php', '', '', RCHP_PLUGIN_ROOT . 'templates/woocommerce/' );
        }
    }

    function woocommerce_attribute_label( $label, $name, $product ) {
        switch ( $label ) {
            case '_rch_affiliate_code':
                return __( 'Affiliate code', 'rch-woocommerce-affiliates' );
            break;

            case '_rch_affiliate_commission':
                return __( 'Affiliate commission', 'rch-woocommerce-affiliates' );
            break;

            default:
                return $label;
        }
    }
}

global $rch_affiliates;
$rch_affiliates = new RCH_Affiliates();

register_activation_hook( __FILE__, array( 'RCH_Affiliates', 'flush_rewrite_rules' ) );
register_deactivation_hook( __FILE__, array( 'RCH_Affiliates', 'flush_rewrite_rules' ) );

endif;
