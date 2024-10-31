<?php

defined( 'ABSPATH' ) or exit;

if ( ! function_exists( 'rchp_current_user_affiliate_code' ) ) {
    function rchp_current_user_affiliate_code() {
        global $wpdb;

        $user = wp_get_current_user();

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT
                a.code
            FROM
                {$wpdb->rcode_affiliate} AS a
            WHERE
                a.user_id = %d
                AND a.active = 1
        ", $user->ID ) );

        if ( is_array( $results ) && count( $results ) > 0 ) {
            return $results[0]->code;
        } else {
            return false;
        }
    }
}

if ( ! function_exists( 'rchp_affiliate_url' ) ) {
    function rchp_affiliate_url( $code = '' ) {
        global $rch_affiliates;

        $shop_page_url = apply_filters( 'rch_affiliates_shop_page_url', get_permalink( wc_get_page_id( 'shop' ) ) );
        $url_fields = array_map( 'trim', explode( ',', $rch_affiliates->url_fields ) );
        $url_prefix = add_query_arg( $url_fields[0], '', $shop_page_url );

        if ( !empty( $code ) ) {
            return $url_prefix . '=' . $code;
        } else {
            return $url_prefix;
        }
    }
}

if ( ! function_exists( 'rchp_affiliates_report_columns' ) ) {
    function rchp_affiliates_report_columns() {
        $columns = array(
            'name'              => array( 'label' => __( 'Affiliate name', 'rch-woocommerce-affiliates' ), 'default_sort' => 'asc' ),
            'user'              => array( 'label' => __( 'User', 'rch-woocommerce-affiliates' ), 'default_sort' => 'asc' ),
            'code'              => array( 'label' => __( 'Affiliate code', 'rch-woocommerce-affiliates' ), 'default_sort' => 'asc' ),
            'url'               => array( 'label' => __( 'URL', 'rch-woocommerce-affiliates' ), 'default_sort' => 'asc' ),
            'commission'        => array( 'label' => __( 'Commission rate', 'rch-woocommerce-affiliates' ), 'default_sort' => 'desc' ),
            'order_count'       => array( 'label' => __( 'Order count', 'rch-woocommerce-affiliates' ), 'default_sort' => 'desc' ),
            'revenue'           => array( 'label' => __( 'Revenue', 'rch-woocommerce-affiliates' ), 'default_sort' => 'desc' ),
            'total_commission'  => array( 'label' => __( 'Commission', 'rch-woocommerce-affiliates' ), 'default_sort' => 'desc' ),
        );

        return apply_filters( 'rchp_affiliates_report_columns', $columns );
    }
}

if ( ! function_exists( 'rchp_affiliates_report' ) ) {
    function rchp_affiliates_report( $affiliate_id, $begin_date, $end_date, $sort, $sort_order, $active, $limit ) {
        global $wpdb;
        global $rch_affiliates;

        $order_by = '';
        if ( !empty( $sort ) ) {
            switch ( $sort ) {
                case 'name':
                    $order_by = ' ORDER BY a.name';
                break;

                case 'user':
                    $order_by = ' ORDER BY user';
                break;

                case 'code':
                    $order_by = ' ORDER BY a.code';
                break;

                case 'url':
                    $order_by = ' ORDER BY a.code';
                break;

                case 'commission':
                    $order_by = ' ORDER BY a.commission';
                break;

                case 'order_count':
                    $order_by = ' ORDER BY order_count';
                break;

                case 'revenue':
                    $order_by = ' ORDER BY revenue';
                break;

                case 'total_commission':
                    $order_by = ' ORDER BY total_commission';
                break;
            }

            if ( !empty( $order_by ) && $sort_order == 'desc' ) {
                $order_by .= ' DESC';
            }

            if ( !empty( $order_by ) ) {
                $order_by .= ', a.name';
            }
        }

        $url_prefix = rchp_affiliate_url();

        $total_select = "COALESCE(SUM(oim_line_total.meta_value), 0)";
        $join_tax = '';
        $pre_tax = get_option( 'rch_affiliates_commission_before_tax', 'yes' );
        if ( 'no' == $pre_tax ) {
            $total_select = "COALESCE(SUM(oim_line_total.meta_value + oim_line_tax.meta_value), 0)";
            $join_tax = "LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS oim_line_tax ON (oim_line_tax.order_item_id = oi.order_item_id AND oim_line_tax.meta_key = '_line_tax')";
        }

        //
        // NOTE: If you add/change columns here, also update rchp_affiliates_report_columns() above.
        //
        $query = $wpdb->prepare( "
            SELECT
                a.rcode_affiliate_id,
                a.name,
                a.user_id,
                CONCAT(u.display_name, ' (', u.user_login, ')') AS user,
                a.code,
                CONCAT('$url_prefix=', a.code) AS url,
                a.commission,
                a.active,
                a.create_user_id,
                a.create_date,
                COUNT(DISTINCT o.ID) AS order_count,
                COALESCE(SUM((
                    SELECT
                        $total_select
                    FROM
                        `{$wpdb->prefix}woocommerce_order_items` AS oi
                    LEFT JOIN
                        `{$wpdb->prefix}woocommerce_order_itemmeta` AS oim_line_total ON (oim_line_total.order_item_id = oi.order_item_id AND oim_line_total.meta_key = '_line_total')
                    $join_tax
                    WHERE
                        oi.order_id = o.ID
                        AND oi.order_item_type = 'line_item'
                )), 0) AS revenue,
                COALESCE(SUM(o_commission.meta_value), 0) AS total_commission
            FROM
                `{$wpdb->rcode_affiliate}` AS a
            LEFT JOIN
                `{$wpdb->prefix}users` AS u ON (CONVERT(u.ID USING utf8) = CONVERT(a.user_id USING utf8))
            LEFT JOIN
                `{$wpdb->postmeta}` AS om_code ON (om_code.meta_key = '_rch_affiliate_code' AND CONVERT(om_code.meta_value USING utf8) = CONVERT(a.code USING utf8))
            LEFT JOIN
                `{$wpdb->posts}` AS o ON (o.ID = om_code.post_id AND o.post_status = 'wc-completed' AND o.post_date BETWEEN %s AND %s)
            LEFT JOIN
                `{$wpdb->postmeta}` AS o_commission ON (o_commission.post_id = o.ID AND o_commission.meta_key = '_rch_affiliate_commission')
            WHERE
                (%d = 0 or a.rcode_affiliate_id = %d)
                AND a.active = %d
            GROUP BY
                a.rcode_affiliate_id,
                a.code,
                a.name,
                a.commission,
                a.active,
                a.create_user_id,
                a.create_date
            $order_by
            LIMIT
                %d
            ",
            $begin_date,
            $end_date,
            $affiliate_id,
            $affiliate_id,
            $active,
            absint( $limit )
        );

        $affiliates = $wpdb->get_results( $query );

        if ( empty( $wpdb->last_error) && null !== $affiliates ) {
            return $affiliates;
        } else {
            return sprintf( __( 'Error while getting Affiliates from the database: %s', 'rch-woocommerce-affiliates' ), $wpdb->last_error );
        }
    }
}

if ( ! function_exists( 'rchp_products_report_columns' ) ) {
    function rchp_products_report_columns() {
        $columns = array(
            'product_name'      => array( 'label' => __( 'Product name', 'rch-woocommerce-affiliates' ), 'default_sort' => 'asc' ),
            'quantity'          => array( 'label' => __( 'Quantity sold', 'rch-woocommerce-affiliates' ), 'default_sort' => 'desc' ),
            'revenue'           => array( 'label' => __( 'Revenue', 'rch-woocommerce-affiliates' ), 'default_sort' => 'desc' ),
        );

        return apply_filters( 'rchp_products_report_columns', $columns );
    }
}

if ( ! function_exists( 'rchp_products_report' ) ) {
    function rchp_products_report( $affiliate_id, $begin_date, $end_date, $sort, $sort_order, $active, $limit ) {
        global $wpdb;

        $order_by = '';
        if ( !empty( $sort ) ) {
            switch ( $sort ) {
                case 'product_name':
                    $order_by = ' ORDER BY product_name';
                break;

                case 'quantity':
                    $order_by = ' ORDER BY quantity';
                break;

                case 'revenue':
                    $order_by = ' ORDER BY revenue';
                break;
            }

            if ( !empty( $order_by ) && $sort_order == 'desc' ) {
                $order_by .= ' DESC';
            }
        }


        $pre_tax = get_option( 'rch_affiliates_commission_before_tax', 'yes' );
        $tax_query = '';
        if ( 'no' == $pre_tax ) {
            $tax_query = " + (SELECT m.meta_value FROM `{$wpdb->prefix}woocommerce_order_itemmeta` AS m WHERE m.order_item_id = order_items.order_item_id AND m.meta_key = '_line_tax' LIMIT 1)";
        }

        //
        // NOTE: If you add/change columns here, also update rchp_products_report_columns() above.
        //
        $query = $wpdb->prepare( "
            SELECT
                product.ID AS product_id,
                order_items.order_item_name AS product_name,
                SUM((SELECT m.meta_value FROM `{$wpdb->prefix}woocommerce_order_itemmeta` AS m WHERE m.order_item_id = order_items.order_item_id AND m.meta_key = '_qty' LIMIT 1)) AS quantity,
                SUM((SELECT m.meta_value FROM `{$wpdb->prefix}woocommerce_order_itemmeta` AS m WHERE m.order_item_id = order_items.order_item_id AND m.meta_key = '_line_total' LIMIT 1) $tax_query) AS revenue
            FROM
                `{$wpdb->postmeta}` AS om_code
            JOIN
                `{$wpdb->posts}` AS o ON (o.ID = om_code.post_id)
            JOIN
                `{$wpdb->rcode_affiliate}` AS a ON (CONVERT(a.code USING utf8) = CONVERT(om_code.meta_value USING utf8))
            JOIN
                `{$wpdb->prefix}woocommerce_order_items` AS order_items ON (order_items.order_id = om_code.post_id AND order_items.order_item_type = 'line_item')
            LEFT JOIN
                `{$wpdb->prefix}posts` AS product ON (product.ID = (SELECT m.meta_value FROM `{$wpdb->prefix}woocommerce_order_itemmeta` AS m WHERE m.order_item_id = order_items.order_item_id AND m.meta_key = '_product_id' LIMIT 1))
            WHERE
                om_code.meta_key = '_rch_affiliate_code'
                AND (%d = 0 OR a.rcode_affiliate_id = %d)
                AND (o.ID IS NULL OR o.post_date BETWEEN %s AND %s)
                AND o.post_status = 'wc-completed'
                AND a.active = %d
            GROUP BY
                product.ID,
                order_items.order_item_name
            $order_by
            LIMIT
                %d
            ",
            $affiliate_id,
            $affiliate_id,
            $begin_date,
            $end_date,
            $active,
            absint( $limit )
        );

        $products = $wpdb->get_results( $query );

        if ( empty( $wpdb->last_error) && null !== $products ) {
            return $products;
        } else {
            return sprintf( __( 'Error while getting Products from the database: %s', 'rch-woocommerce-affiliates' ), $wpdb->last_error );
        }
    }
}

if ( ! function_exists( 'rchp_affiliates_list' ) ) {
    function rchp_affiliates_list( $active = true ) {
        global $wpdb;

        $results = $wpdb->get_results( "
            SELECT
                a.rcode_affiliate_id,
                a.name,
                a.code
            FROM
                `{$wpdb->rcode_affiliate}` AS a
            WHERE
                a.active = true
            ORDER BY
                a.name,
                a.code
        " );

        return $results;
    }
}

if ( ! function_exists( 'rchp_get_affiliate' ) ) {
    function rchp_get_affiliate( $id ) {
        global $wpdb;

        if ( !empty( absint( $id ) ) ) {
            $result = $wpdb->get_row( $wpdb->prepare( "SELECT `code` FROM `{$wpdb->rcode_affiliate}` WHERE rcode_affiliate_id = %d", absint( $id ) ) );
            if ( null !== $result ) {
                return new RCH_Affiliate( $result->code );
            }
        }

        return false;
    }
}

if ( ! function_exists( 'rchp_get_active_affiliate' ) ) {
    function rchp_get_active_affiliate( $code ) {
        $affiliate = new RCH_Affiliate( $code );
        if ( empty( $affiliate->get_error_message() ) && $affiliate->get_active() ) {
            return $affiliate;
        } else {
            return false;
        }
    }
}

if ( ! function_exists( 'rchp_add_affiliate' ) ) {
    function rchp_add_affiliate( $code, $name, $user_id, $commission ) {
        global $wpdb;

        $code = wc_clean( $code );
        $code = preg_replace( '/[^\w]/', '', $code );
        if ( empty( $code ) ) {
            return __( 'Affiliate Code cannot be empty.', 'rch-woocommerce-affiliates' );
        }

        $name = wc_clean( $name );
        if ( empty( $name ) ) {
            return __( 'Name cannot be empty.', 'rch-woocommerce-affiliates' );
        }

        $user_id = intval( $user_id );
        if ( $user_id <= 0 ) {
            $user_id = null;
        }

        $commission = preg_replace( "/[^0-9.]/", "", $commission );
        if ( !is_numeric( $commission ) ) {
            $commission = null;
        }

        $result = $wpdb->insert( $wpdb->rcode_affiliate, array ( 'code' => $code, 'name' => $name, 'user_id' => $user_id, 'commission' => $commission, 'create_user_id' => get_current_user_id() ) );

        if ( $result !== false ) {
            $affiliate = rchp_get_affiliate( $wpdb->insert_id );

            return $affiliate;
        } else {
            return $wpdb->last_error;
        }
    }
}

if ( ! function_exists( 'rchp_create_affiliate' ) ) {
    function rchp_create_affiliate( $name, $user_id, $commission ) {
        // Failsafe. If we haven't generated a code after this many tries, throw an error.
        $attempts = 0;
        $max_attempts = 100;

        // Get a random Code and insert it. If the insertion fails, it is already in use.
        do {
            $attempts++;

            $code = rchp_random_code();
            $affiliate = rchp_add_affiliate( $code, $name, $user_id, $commission );

        } while ( !( $affiliate instanceof RCH_Affiliate ) && $attempts < $max_attempts );

        if ( $affiliate instanceof RCH_Affiliate ) {
            return $affiliate;
        } else {
            return sprintf( __( 'Failed to generate a unique random affiliate code after %d attempts. %s', 'rch-woocommerce-affiliates' ), $attempts, $affiliate );
        }
    }
}

if ( ! function_exists( 'rchp_edit_affiliate' ) ) {
    function rchp_edit_affiliate( $rcode_affiliate_id, $code, $name, $user_id, $commission ) {
        global $wpdb;

        $rcode_affiliate_id = absint( $rcode_affiliate_id );
        if ( empty( $rcode_affiliate_id ) ) {
            return __( 'rcode_affiliate_id cannot be empty.', 'rch-woocommerce-affiliates' );
        }

        $code = wc_clean( $code );
        $code = preg_replace( '/[^\w]/', '', $code );
        if ( empty( $code ) ) {
            return __( 'Affiliate Code cannot be empty.', 'rch-woocommerce-affiliates' );
        }

        $name = wc_clean( $name );
        if ( empty( $name ) ) {
            return __( 'Name cannot be empty.', 'rch-woocommerce-affiliates' );
        }

        $user_id = intval( $user_id );
        if ( $user_id <= 0 ) {
            $user_id = null;
        }

        $commission = preg_replace( "/[^0-9.]/", "", $commission );
        if ( !is_numeric( $commission ) ) {
            $commission = null;
        }

        $affiliate = rchp_get_affiliate( $rcode_affiliate_id );
        $old_code = $affiliate->get_code();

        $result = $wpdb->update( $wpdb->rcode_affiliate, array ( 'code' => $code, 'name' => $name, 'user_id' => $user_id, 'commission' => $commission ), array( 'rcode_affiliate_id' => $rcode_affiliate_id ) );

        if ( $result !== false ) {
            if ( $code != $old_code ) {
                // Move any orders over to the new code.
                $result = $wpdb->update( $wpdb->postmeta, array ( 'meta_value' => $code ), array( 'meta_key' => '_rch_affiliate_code', 'meta_value' => $old_code ) );
            }

            $affiliate = rchp_get_affiliate( $rcode_affiliate_id );

            return $affiliate;
        } else {
            return $wpdb->last_error;
        }
    }
}

if ( ! function_exists( 'rchp_random_code' ) ) {
    function rchp_random_code() {
        $code = '';

        for ( $counter = 0; $counter < RCHP_RANDOM_CODE_LENGTH; $counter++ ) {
            $random = str_shuffle( RCHP_RANDOM_CODE_CHARSET );
            $code .= $random[0];
        }

        return $code;
    }
}

if ( ! function_exists( 'rchp_get_product_commission' ) ) {
    function rchp_get_product_commission( $product, $affiliate = '', $ignore_product_commission = false ) {
        global $rch_affiliates;

        if ( is_numeric( $product ) ) {
            $product = wc_get_product( $product );
        }

        $commissions = array();

        // The overall site default.
        $commissions[] = $rch_affiliates->default_commission;

        // Product (or Variation)
        if ( false === $ignore_product_commission ) {
            $commissions[] = $product->get_meta( '_rch_affiliate_commission' );
        }

        // Parent product (for Variations)
        if ( $product->is_type( 'variation' ) ) {
            $product = wc_get_product( $product->get_parent_id() );
            $commissions[] = $product->get_meta( '_rch_affiliate_commission' );
        }

        // All categories for the product.
        foreach ( $product->get_category_ids( 'edit' ) as $category_id ) {
            $commissions[] = get_woocommerce_term_meta( $category_id, 'rch_affiliates_commission', true );
        }

        // The affiliate.
        if ( !empty( $affiliate ) ) {
            $commissions[] = $affiliate->get_commission();
        }

        arsort( $commissions );

        return array_shift( $commissions );
    }
}