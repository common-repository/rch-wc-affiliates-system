<?php
defined( 'ABSPATH' ) or exit;
if ( ! class_exists( 'RCH_Affiliates_Admin' ) ) :
final class RCH_Affiliates_Admin {
    public $settings;
    function __construct() {
        global $rch_affiliates;

        $this->settings = array(
            array(
                'title'     => __( 'RCH Affiliates', 'rch-woocommerce-affiliates' ),
                'type'      => 'title',
                'desc'      => '',
                'id'        => 'rch_affiliates_options',
            ),
            array(
                'title'     => __( 'Affiliate Program Name', 'rch-woocommerce-affiliates' ),
                'desc'      => __( 'Shown to affiliates when they log in to check their reports.', 'rch-woocommerce-affiliates' ),
                'id'        => 'rch_affiliates_program_name',
                'default'   => __( 'Affiliate Program', 'rch-woocommerce-affiliates' ),
                'type'      => 'text',
            ),
            array(
                'title'     => __( 'Use Coupon Code', 'rch-woocommerce-affiliates' ),
                'desc'      => __( 'When enabled, the Coupon Code field can be used to associate an order with an affiliate. Coupon must exist in WooCommerce although can be for 0. The affiliate link will still work as well.', 'rch-woocommerce-affiliates' ),
                'id'        => 'rch_affiliates_coupon_code_linking',
                'default'   => 'yes',
                'type'      => 'checkbox',
            ),
            array(
                'title'     => __( 'Pre-Tax Commission', 'rch-woocommerce-affiliates' ),
                'desc'      => __( 'Should the commission be based on the pre-tax product price?', 'rch-woocommerce-affiliates' ),
                'id'        => 'rch_affiliates_commission_before_tax',
                'default'   => 'yes',
                'type'      => 'checkbox',
            ),
            array(
                'title'     => __( 'Affiliate Code URL Field', 'rch-woocommerce-affiliates' ),
                'desc'      => __( 'For example, ' . apply_filters( 'rch_affiliates_shop_page_url', get_permalink( wc_get_page_id( 'shop' ) ) ) . '?affiliate=123 for the value "affiliate". Multiple options can be separated by a comma.', 'rch-woocommerce-affiliates' ),
                'id'        => 'rchp_url_fields',
                'default'   => 'affiliate',
                'type'      => 'text',
            ),
            array(
                'title'     => __( 'Affiliate Endpoint', 'rch-woocommerce-affiliates' ),
                'desc'      => sprintf( __( 'The URL for the report shown to affiliate users. Default: %s', 'rch-woocommerce-affiliates' ), trailingslashit( get_permalink( get_option('woocommerce_myaccount_page_id') ) ) . 'affiliate-report/' ),
                'id'        => 'rchp_affiliate_endpoint',
                'default'   => 'affiliate-report',
                'type'      => 'text',
            ),
            array(
                'title'     => __( 'Cookie Lifetime', 'rch-woocommerce-affiliates' ),
                'desc'      => sprintf( __( 'The number of days the Affiliate tracking cookie is stored for visitors. Default: %s', 'rch-woocommerce-affiliates' ), '30' ),
                'id'        => 'rch_affiliates_cookie_days',
                'default'   => '30',
                'type'      => 'number',
            ),
            array(
                'title'     => __( 'Show Code In Cart', 'rch-woocommerce-affiliates' ),
                'desc'      => __( 'Check this box to show the Affiliate Code on the Cart page. If no code is applied, nothing will be shown. Default: unchecked.', 'rch-woocommerce-affiliates' ),
                'id'        => 'rch_affiliates_show_code_in_cart',
                'default'   => 'no',
                'type'      => 'checkbox',
            ),
            array(
                'title'     => __( 'Show Code In Checkout', 'rch-woocommerce-affiliates' ),
                'desc'      => __( 'Check this box to show the Affiliate Code on the Checkout page. If no code is applied, nothing will be shown. Default: unchecked.', 'rch-woocommerce-affiliates' ),
                'id'        => 'rch_affiliates_show_code_in_checkout',
                'default'   => 'no',
                'type'      => 'checkbox',
            ),
            array(
                'type'      => 'sectionend',
                'id'        => 'rch_affiliates_options',
            ),
        );

        // Show an alert on the backend if we don't have the minimum required version.
        if ( !$rch_affiliates->wc_min_version( RCHP_WC_VERSION_MINIMUM ) ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_version_error' ) );
            return;
        }

        add_action( 'admin_menu', array( $this, 'admin_menu' ), 11, 1 );
        add_filter( 'custom_menu_order', array( $this, 'custom_menu_order' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'restrict_manage_posts', array( $this, 'filter_orders_by_affiliate'), 20 );
        add_filter( 'request', array( $this, 'filter_orders_by_affiliate_query' ) );
        add_action( 'admin_init', array( $this, 'send_exported_file' ) );
        add_action( 'product_cat_add_form_fields', array( $this, 'edit_category_fields' ) );
        add_action( 'product_cat_edit_form_fields', array( $this, 'edit_category_fields' ) );
        add_action( 'created_term', array( $this, 'save_category_fields' ), 10, 3 );
        add_action( 'edit_term', array( $this, 'save_category_fields' ), 10, 3 );
        add_filter( 'manage_edit-product_cat_columns', array( $this, 'product_cat_columns' ) );
        add_filter( 'manage_product_cat_custom_column', array( $this, 'product_cat_column' ), 10, 3 );
        add_filter( 'pwbe_product_columns', array( $this, 'pwbe_product_columns' ) );
        add_action( 'woocommerce_product_options_general_product_data', array( $this, 'woocommerce_product_options_general_product_data' ) );
        add_action( 'woocommerce_variation_options_pricing', array( $this, 'woocommerce_variation_options_pricing' ), 10, 3 );
        add_action( 'woocommerce_admin_process_product_object', array( $this, 'woocommerce_admin_process_product_object' ) );
        add_action( 'woocommerce_save_product_variation', array( $this, 'woocommerce_save_product_variation' ), 10, 2 );

        add_action( 'wp_ajax_rchp-affiliates-report', array( $this, 'ajax_affiliates_report' ) );
        add_action( 'wp_ajax_rchp-products-report', array( $this, 'ajax_products_report' ) );
        add_action( 'wp_ajax_rchp-export-report', array( $this, 'ajax_export_report' ) );
        add_action( 'wp_ajax_rchp-create-affiliate', array( $this, 'ajax_create_affiliate' ) );
        add_action( 'wp_ajax_rchp-edit-affiliate', array( $this, 'ajax_edit_affiliate' ) );
        add_action( 'wp_ajax_rchp-delete-affiliate', array( $this, 'ajax_delete_affiliate' ) );
        add_action( 'wp_ajax_rchp-save-commissions', array( $this, 'ajax_save_commissions' ) );
    }

    function woocommerce_version_error() {
        ?>
        <div class="error notice">
            <p><?php printf( __( 'PW WooCommerce Affiliates requires WooCommerce version %s or later.', 'rch-woocommerce-affiliates' ), RCHP_WC_VERSION_MINIMUM ); ?></p>
        </div>
        <?php
    }

    function admin_menu() {
        global $rch_affiliates;

        if ( empty ( $GLOBALS['admin_page_hooks']['rcode'] ) ) {
            add_menu_page(
                __( 'RCH Affiliates', 'rch-woocommerce-affiliates' ),
                __( 'RCH Affiliates', 'rch-woocommerce-affiliates' ),
                RCHP_REQUIRES_PRIVILEGE,
                'rcode',
                array( $this, 'index' ),
                'dashicons-buddicons-tracking'
            );

            add_submenu_page(
                'rcode',
                __( 'RCH Affiliates', 'rch-woocommerce-affiliates' ),
                __( 'RCH Affiliates', 'rch-woocommerce-affiliates' ),
                RCHP_REQUIRES_PRIVILEGE,
                'rcode',
                array( $this, 'index' )
            );

            remove_submenu_page( 'rcode', 'rcode' );
        }

        add_submenu_page(
            'rcode',
            __( 'RCH Affiliates', 'rch-woocommerce-affiliates' ),
            __( 'RCH Affiliates', 'rch-woocommerce-affiliates' ),
            RCHP_REQUIRES_PRIVILEGE,
            'rch-affiliates',
            array( $this, 'index' )
        );

        remove_submenu_page( 'rcode', 'rcode-plugins' );
    }

    function custom_menu_order( $menu_order ) {
        global $submenu;

        if ( isset( $submenu['rcode'] ) ) {
            usort( $submenu['rcode'], array( $this, 'sort_menu_by_title' ) );
        }

        return $menu_order;
    }

    function sort_menu_by_title( $a, $b ) {
        if ( $a[2] == 'rcode-plugins' ) {
            return 1;
        } else if ( $b[2] == 'rcode-plugins' ) {
            return -1;
        } else {
            return strnatcasecmp( $a[0], $b[0] );
        }
    }


    function index() {
        require( 'view/index.php' );
    }

    function admin_enqueue_scripts( $hook ) {
        global $wp_scripts;
        global $rch_affiliates;

        wp_register_style( 'rch-affiliates-icon', $rch_affiliates->relative_url( '/admin/assets/css/icon-style.css' ), array( 'admin-menu' ), RCHP_VERSION );
        wp_enqueue_style( 'rch-affiliates-icon' );

        if ( !empty( $hook ) && substr( $hook, -strlen( 'rch-affiliates' ) ) === 'rch-affiliates' ) {
            wp_register_style( 'rch-affiliates-admin', $rch_affiliates->relative_url( '/admin/assets/css/rch-affiliates-admin.css' ), array(), RCHP_VERSION );
            wp_enqueue_style( 'rch-affiliates-admin' );

            wp_enqueue_script( 'rch-affiliates-admin', $rch_affiliates->relative_url( '/admin/assets/js/rch-affiliates-admin.js' ), array( 'jquery' ), RCHP_VERSION );
            wp_localize_script( 'rch-affiliates-admin', 'rchp', array(
                'ordersUrl' => admin_url( 'edit.php?post_type=shop_order' ),
                'exportUrl' => admin_url( 'admin.php?page=rch-affiliates' ),
                'i18n' => array(
                    'loading' => __( 'Loading...', 'rch-woocommerce-affiliates' ),
                    'activating' => __( 'Activating...', 'rch-woocommerce-affiliates' ),
                    'saving' => __( 'Saving...', 'rch-woocommerce-affiliates' ),
                    'exporting' => __( 'Exporting...', 'rch-woocommerce-affiliates' ),
                    'linkCopied' => __( 'Link copied to clipboard', 'rch-woocommerce-affiliates' ),
                    'confirmDelete' => __( 'Are you sure you want to delete this affiliate?', 'rch-woocommerce-affiliates' ),
                ),
                'nonces' => array(
                    'affiliatesReport' => wp_create_nonce( 'rch-affiliates-affiliates-report' ),
                    'productsReport' => wp_create_nonce( 'rch-affiliates-products-report' ),
                    'exportReport' => wp_create_nonce( 'rch-affiliates-export-report' ),
                    'createAffiliate' => wp_create_nonce( 'rch-affiliates-create-affiliate' ),
                    'editAffiliate' => wp_create_nonce( 'rch-affiliates-edit-affiliate' ),
                    'deleteAffiliate' => wp_create_nonce( 'rch-affiliates-delete-affiliate' ),
                    'saveCommissions' => wp_create_nonce( 'rch-affiliates-save-commissions' ),
                    'saveSettings' => wp_create_nonce( 'rch-affiliates-save-settings' ),
                )
            ) );

            wp_register_script( 'fontawesome', $rch_affiliates->relative_url( '/admin/assets/js/fontawesome.min.js' ), array(), RCHP_FONT_AWESOME_VERSION );
            wp_enqueue_script( 'fontawesome' );

            wp_register_script( 'fontawesome-solid', $rch_affiliates->relative_url( '/admin/assets/js/fontawesome-solid.min.js' ), array( 'fontawesome' ), RCHP_FONT_AWESOME_VERSION );
            wp_enqueue_script( 'fontawesome-solid' );

            wp_register_style( 'jquery-ui-style', $rch_affiliates->relative_url( '/assets/css/jquery-ui-style.min.css', __FILE__ ), array(), RCHP_VERSION );
            wp_enqueue_style( 'jquery-ui-style' );

            wp_enqueue_script( 'jquery-ui-datepicker' );
        }
    }

    function filter_orders_by_affiliate() {
        global $typenow;

        if ( 'shop_order' === $typenow ) {

            $affiliates = rchp_affiliates_list();
            ?>
            <select name="rch_affiliate" id="dropdown_shop_order_rch_affiliate_code">
                <option value="">
                    <?php esc_html_e( 'All Affiliates', 'rch-woocommerce-affiliates' ); ?>
                </option>

                <?php
                    foreach ( $affiliates as $affiliate ) {
                        $code = $affiliate->code;
                        ?>
                        <option value="<?php echo esc_attr( $code ); ?>" <?php echo esc_attr( isset( $_GET['rch_affiliate'] ) ? selected( $code, $_GET['rch_affiliate'], false ) : '' ); ?>>
                            <?php echo esc_html( sprintf( '%s (%s)', $affiliate->name, $code ) ); ?>
                        </option>
                        <?php
                    }
                ?>
            </select>
            <?php
        }
    }

    function filter_orders_by_affiliate_query( $vars ) {
        global $typenow;

        if ( 'shop_order' === $typenow && isset( $_GET['rch_affiliate'] ) && !empty( $_GET['rch_affiliate'] ) ) {
            $vars['meta_key']   = '_rch_affiliate_code';
            $vars['meta_value'] = wc_clean( $_GET['rch_affiliate'] );
        }
        return $vars;
    }

    function ajax_affiliates_report() {

        check_ajax_referer( 'rch-affiliates-affiliates-report', 'security' );

        ob_start();
        $affiliates = $this->affiliates_report();
        require( 'view/affiliates-table.php' );
        $html = ob_get_clean();

        wp_send_json_success( array( 'html' => $html ) );
    }

    function ajax_products_report() {

        check_ajax_referer( 'rch-affiliates-products-report', 'security' );

        ob_start();
        $products = $this->products_report();
        require( 'view/products-table.php' );
        $html = ob_get_clean();

        wp_send_json_success( array( 'html' => $html ) );
    }

    function affiliates_report() {
        global $rchp_sort;
        global $rchp_sort_order;

        $active = true;
        $limit = 1000;

        $form = array();
        parse_str( $_REQUEST['form'], $form );

        $affiliate_id = absint( $form['affiliate_id'] );
        $begin_date = wc_clean( $form['begin_date'] );
        $end_date = wc_clean( $form['end_date'] );

        $rchp_sort = 'revenue';
        if ( !empty( $form['sort'] ) ) {
            $rchp_sort = wc_clean( $form['sort'] );
        }

        $rchp_sort_order = 'desc';
        if ( !empty( $form['sort_order'] ) ) {
            $rchp_sort_order = wc_clean( $form['sort_order'] );
        }

        $affiliates = rchp_affiliates_report( $affiliate_id, $begin_date, $end_date, $rchp_sort, $rchp_sort_order, $active, $limit );

        return apply_filters( 'rchp_affiliates_report', $affiliates );
    }

    function products_report() {
        global $rchp_sort;
        global $rchp_sort_order;

        $active = true;
        $limit = 1000;

        $form = array();
        parse_str( $_REQUEST['form'], $form );

        $affiliate_id = absint( $form['affiliate_id'] );
        $begin_date = wc_clean( $form['begin_date'] );
        $end_date = wc_clean( $form['end_date'] );

        $rchp_sort = 'revenue';
        if ( !empty( $form['sort'] ) ) {
            $rchp_sort = wc_clean( $form['sort'] );
        }

        $rchp_sort_order = 'desc';
        if ( !empty( $form['sort_order'] ) ) {
            $rchp_sort_order = wc_clean( $form['sort_order'] );
        }

        $products = rchp_products_report( $affiliate_id, $begin_date, $end_date, $rchp_sort, $rchp_sort_order, $active, $limit );

        return apply_filters( 'rchp_products_report', $products );
    }

    function ajax_export_report() {

        check_ajax_referer( 'rch-affiliates-export-report', 'security' );

        $form = array();
        parse_str( $_REQUEST['form'], $form );

        $output_filename = wp_tempnam();
        $csv_file = fopen( $output_filename, 'w' );

        if ( $form['report_type'] == 'products' ) {
            $report_type = 'products';
            $data = $this->products_report();
            $columns = rchp_products_report_columns();
        } else {
            $report_type = 'affiliates';
            $data = $this->affiliates_report();
            $columns = rchp_affiliates_report_columns();
        }

        // Output the header row.
        if ( !empty( $columns ) ) {
            fputcsv( $csv_file, wp_list_pluck( $columns, 'label' ) );
        }

        foreach ( $data as &$row ) {
            foreach( $row as $key => &$value ) {
                if ( isset( $columns[ $key ] ) ) {
                    $value = trim( preg_replace( '/\s+/', ' ', $value ) );
                } else {
                    unset( $row->$key );
                }
            }

            fputcsv( $csv_file, (array) $row );
        }

        fclose( $csv_file );

        wp_send_json_success(
            array(
                'report_type' => $report_type,
                'output_filename' => $output_filename
            )
        );
    }

    function send_exported_file() {
        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'rchp_export' && isset( $_REQUEST['report_type'] ) && isset( $_REQUEST['filename'] ) ) {
            if ( !current_user_can( RCHP_REQUIRES_PRIVILEGE ) ) { wp_die( 'Unauthorized.' ); }

            $filename = wc_clean( $_REQUEST['filename'] );
            $download_filename = ucfirst( wc_clean( $_REQUEST['report_type'] ) ) . '.csv';

            $extension = pathinfo( $filename, PATHINFO_EXTENSION );
            if ( strtolower( $extension ) != 'tmp' ) {
                wp_die( 'Invalid filename.' );
            }

            header( 'Content-Type: application/octet-stream' );
            header( 'Content-Disposition: attachment; filename="' . $download_filename . '"' );
            header( 'Content-Description: File Transfer' );
            header( 'Expires: 0' );
            header( 'Cache-Control: must-revalidate' );
            header( 'Pragma: public' );
            header( 'Content-Length: ' . filesize( $filename ) );
            readfile( $filename );
            unlink( $filename );
            exit;
        }
    }

    function edit_category_fields( $term ) {
        global $rch_affiliates;

        $commission = '';
        if ( is_object( $term ) ) {
            $commission = get_woocommerce_term_meta( $term->term_id, 'rch_affiliates_commission', true );
        }

        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label><?php _e( 'Commission', 'rch-woocommerce-affiliates' ); ?></label></th>
            <td>
                <input name="rch_affiliates_commission" id="rch-affiliates-commission" type="text" value="<?php echo $commission; ?>" placeholder="<?php echo number_format( $rch_affiliates->default_commission, 4 ); ?>%" size="8">
                <p class="description"><?php _e( 'If no value is specified, the default commission rate will be used.', 'rch-woocommerce-affiliates' ); ?></p>
            </td>
        </tr>
        <?php
    }

    function save_category_fields( $term_id, $tt_id = '', $taxonomy = '' ) {
        global $rch_affiliates;

        if ( isset( $_POST['rch_affiliates_commission'] ) && 'product_cat' === $taxonomy ) {
            $commission = preg_replace( "/[^0-9.]/", "", $_POST['rch_affiliates_commission'] );
            if ( is_numeric( $commission ) ) {
                update_woocommerce_term_meta( $term_id, 'rch_affiliates_commission', $commission );
            } else {
                delete_woocommerce_term_meta( $term_id, 'rch_affiliates_commission' );
            }
        }
    }

    function product_cat_columns( $columns ) {
        $new_columns = array();

        $new_columns['rch_affiliate_commission'] = __( 'Commission', 'woocommerce' );

        $columns           = array_merge( $columns, $new_columns );
        $columns['handle'] = '';

        return $columns;
    }

    function pwbe_product_columns( $columns ) {
        $insert_after = __( 'Sale end date', 'woocommerce' );

        $new_column = array( array(
            'name' => __( 'Commission (%)', 'rch-woocommerce-affiliates' ),
            'type' => 'number',
            'table' => 'meta',
            'field' => '_rch_affiliate_commission',
            'visibility' => 'both',
            'readonly' => false,
            'sortable' => 'true'
        ) );

        $index = array_search( $insert_after, array_column( $columns, 'name' ) );
        if ( empty( $index ) ) {
            $index = array_search( __( 'Regular price', 'woocommerce' ), array_column( $columns, 'name' ) );
            if ( empty( $index ) ) {
                $index = count( $columns );
            }
        }
        array_splice( $columns, $index + 1, 0, $new_column );

        return $columns;
    }

    function woocommerce_product_options_general_product_data() {
        global $product_object;

        woocommerce_wp_text_input(
            array(
                'id'          => '_rch_affiliate_commission',
                'name'        => 'rch_affiliate_commission',
                'value'       => $product_object->get_meta( '_rch_affiliate_commission' ),
                'label'       => __( 'Commission (%)', 'rch-woocommerce-affiliates' ),
                'placeholder' => number_format( rchp_get_product_commission( $product_object, '', true ), 4 ) . '%',
                'desc_tip'    => 'true',
                'description' => __( 'Set the commission for this product.', 'rch-woocommerce-affiliates' ),
            )
        );
    }

    function woocommerce_variation_options_pricing( $loop, $variation_data, $variation ) {
        $product = wc_get_product( $variation );

        woocommerce_wp_text_input(
            array(
                'id'            => "variable_rch_affiliate_commission_{$loop}",
                'name'          => "variable_rch_affiliate_commission[{$loop}]",
                'value'         => $product->get_meta( '_rch_affiliate_commission' ),
                'label'         => __( 'Commission (%)', 'rch-woocommerce-affiliates' ),
                'placeholder'   => number_format( rchp_get_product_commission( $product, '', true ), 4 ) . '%',
                'desc_tip'    => 'true',
                'description' => __( 'Set the commission for this variation.', 'rch-woocommerce-affiliates' ),
            )
        );
    }

    function woocommerce_admin_process_product_object( $product ) {
        if ( isset( $_POST['rch_affiliate_commission'] ) ) {
            $product->update_meta_data( '_rch_affiliate_commission', sanitize_text_field( $_POST['rch_affiliate_commission'] ) );
        }
    }

    function woocommerce_save_product_variation( $variation_id, $loop ) {
        if ( isset( $_POST['variable_rch_affiliate_commission'] ) && isset( $_POST['variable_rch_affiliate_commission'][ $loop ] ) ) {
            $variation = new WC_Product_Variation( $variation_id );
            $variation->update_meta_data( '_rch_affiliate_commission', sanitize_text_field( $_POST['variable_rch_affiliate_commission'][ $loop ] ) );
            $variation->save();
        }
    }

    function product_cat_column( $columns, $column, $id ) {
        global $rch_affiliates;

        if ( 'rch_affiliate_commission' === $column ) {
            $commission = get_woocommerce_term_meta( $id, 'rch_affiliates_commission', true );
            if ( empty( $commission ) ) {
                $columns .= sprintf( __( 'Default (%s)', 'rch-woocommerce-affiliates' ), number_format( $rch_affiliates->default_commission, 4 ) . '%' );
            } else {
                $columns .= '<b>' . number_format( $commission, 4 ) . '%</b>';
            }
        }
        if ( 'handle' === $column ) {
            $columns .= '<input type="hidden" name="term_id" value="' . esc_attr( $id ) . '" />';
        }
        return $columns;
    }

    function ajax_create_affiliate() {

        check_ajax_referer( 'rch-affiliates-create-affiliate', 'security' );

        $form = array();
        parse_str( $_REQUEST['form'], $form );

        $code = wc_clean( $form['code'] );
        $name = wc_clean( $form['name'] );
        $user_id = intval( $form['user_id'] );
        $commission = wc_clean( $form['commission'] );

        $name = wc_clean( $name );
        if ( empty( $name ) ) {
            $result = __( 'Name cannot be empty.', 'rch-woocommerce-affiliates' );
        } else {
            if ( !empty( $code ) ) {
                $result = rchp_add_affiliate( $code, $name, $user_id, $commission );
            } else {
                $result = rchp_create_affiliate( $name, $user_id, $commission );
            }
        }

        if ( is_a( $result, 'RCH_Affiliate' ) ) {
            wp_send_json_success( array( 'message' => sprintf( __( 'Added new affiliate: %s', 'rch-woocommerce-affiliates' ), $name ) ) );
        } else {
            wp_send_json_error( array( 'message' => $result ) );
        }
    }

    function ajax_edit_affiliate() {

        check_ajax_referer( 'rch-affiliates-edit-affiliate', 'security' );

        $form = array();
        parse_str( $_REQUEST['form'], $form );

        $affiliate_id = absint( $form['affiliate_id'] );
        $code = wc_clean( $form['code'] );
        $name = wc_clean( $form['name'] );
        $user_id = intval( $form['user_id'] );
        $commission = wc_clean( $form['commission'] );

        $name = wc_clean( $name );
        if ( empty( $name ) ) {
            $result = __( 'Name cannot be empty.', 'rch-woocommerce-affiliates' );

        } else if ( empty( $code ) ) {
            $result = __( 'Code cannot be empty.', 'rch-woocommerce-affiliates' );

        } else if ( empty( $affiliate_id ) ) {
            $result = __( 'Affiliate ID cannot be empty.', 'rch-woocommerce-affiliates' );

        } else {
            $result = rchp_edit_affiliate( $affiliate_id, $code, $name, $user_id, $commission );
        }

        if ( is_a( $result, 'RCH_Affiliate' ) ) {
            wp_send_json_success( array( 'message' => sprintf( __( 'Saved affiliate: %s', 'rch-woocommerce-affiliates' ), $name ) ) );
        } else {
            wp_send_json_error( array( 'message' => $result ) );
        }
    }

    function ajax_delete_affiliate() {

        check_ajax_referer( 'rch-affiliates-delete-affiliate', 'security' );

        $affiliate_id = absint( $_POST['affiliate_id'] );

        $affiliate = rchp_get_affiliate( $affiliate_id );

        if ( $affiliate !== false ) {
            $affiliate->delete();
            wp_send_json_success();
        } else {
            wp_send_json_error( array( 'message' => __( 'Could not locate affiliate by ID', 'rch-woocommerce-affiliates' ) . ' ' . $affiliate_id ) );
        }
    }

    function ajax_save_commissions() {
        global $rch_affiliates;

        check_ajax_referer( 'rch-affiliates-save-commissions', 'security' );

        $form = array();
        parse_str( $_REQUEST['form'], $form );

        $default_commission = preg_replace( "/[^0-9.]/", "", $form['default_commission'] );
        if ( !is_numeric( $default_commission ) ) {
            $default_commission = '0';
        }

        update_option( 'rch_affiliates_default_commission', $default_commission );

        wp_send_json_success( array( 'message' => __( 'Saved', 'rch-woocommerce-affiliates' ) ) );
    }

    function ajax_save_settings() {
        global $rch_affiliates;

        check_ajax_referer( 'rch-affiliates-save-settings', 'security' );

        $form = array();
        parse_str( $_REQUEST['form'], $form );

        WC_Admin_Settings::save_fields( $this->settings, $form );

        $html = '<span style="color: blue;">' . __( 'Settings saved.', 'rch-woocommerce-affiliates' ) . '</span>';

        wp_send_json_success( array( 'html' => $html ) );
    }

}

global $rch_affiliates_admin;
$rch_affiliates_admin = new RCH_Affiliates_Admin();

endif;