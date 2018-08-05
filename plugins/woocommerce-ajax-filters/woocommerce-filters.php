<?php
/**
 * Plugin Name: Advanced AJAX Product Filters for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/woocommerce-ajax-filters/
 * Description: Advanced product filtering ability for your WooCommerce shop. Add unlimited filters with one widget.
 * Version: 1.2.6
 * Author: BeRocket
 * Requires at least: 4.0
 * Author URI: http://berocket.com
 * Text Domain: BeRocket_AJAX_domain
 * Domain Path: /languages/
 * WC tested up to: 3.4.3
 */

define( "BeRocket_AJAX_filters_version", '1.2.6' );
define( "BeRocket_AJAX_domain", 'BeRocket_AJAX_domain' );

define( "AAPF_TEMPLATE_PATH", plugin_dir_path( __FILE__ ) . "templates/" );
define( "AAPF_URL", plugin_dir_url( __FILE__ ) );
load_plugin_textdomain('BeRocket_AJAX_domain', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

require_once dirname( __FILE__ ) . '/includes/admin_notices.php';
require_once dirname( __FILE__ ) . '/includes/widget.php';
require_once dirname( __FILE__ ) . '/includes/functions.php';
require_once dirname( __FILE__ ) . '/includes/wizard.php';
require_once dirname( __FILE__ ) . '/wizard/main.php';
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
/**
 * Class BeRocket_AAPF
 */

class BeRocket_AAPF {

    public static $info = array( 
        'id'        => 1,
        'version'   => BeRocket_AJAX_filters_version,
        'plugin'    => '',
        'slug'      => '',
        'key'       => '',
        'name'      => ''
    );

    public static $defaults = array(
        "br_opened_tab"                   => "general",
        "no_products_message"             => "There are no products meeting your criteria",
        "no_products_class"               => "",
        "control_sorting"                 => "0",
        'products_holder_id'              => 'ul.products',
        'woocommerce_result_count_class'  => '.woocommerce-result-count',
        'woocommerce_ordering_class'      => 'form.woocommerce-ordering',
        'woocommerce_pagination_class'    => '.woocommerce-pagination',
        'woocommerce_removes'             => array(
            'result_count'                => '',
            'ordering'                    => '',
            'pagination'                  => '',
        ),
        "seo_friendly_urls"               => "0",
        "filters_turn_off"                => "0",
        "show_all_values"                 => "0",
        "hide_value"                      => array(
            'o'                           => '0',
            'sel'                         => '0',
        ),
        'first_page_jump'                 => '0',
        'use_select2'                     => '0',
        'scroll_shop_top'                 => '0',
        'ajax_request_load'               => '1',
        'use_get_query'                   => '',
        'ajax_request_load_style'         => 'jquery',
        'user_func'                       => array(
            'before_update'               => '',
            'on_update'                   => '',
            'after_update'                => '',
        ),
        'user_custom_css'                 => '',
    );
    public static $values = array(
        'settings_name' => '',
        'option_page'   => 'br-product-filters',
        'premium_slug'  => 'woocommerce-ajax-products-filter',
    );

    function __construct(){
        register_activation_hook(__FILE__, array( __CLASS__, 'br_add_defaults' ) );
        register_uninstall_hook(__FILE__, array( __CLASS__, 'br_delete_plugin_options' ) );
        add_filter( 'BeRocket_updater_add_plugin', array( __CLASS__, 'updater_info' ) );

        if ( ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) && br_get_woocommerce_version() >= 2.1 ) {
            $last_version = get_option('br_filters_version');
            if( $last_version === FALSE ) $last_version = 0;
            if ( version_compare($last_version, BeRocket_AJAX_filters_version, '<') ) {
                self::update_from_older ( $last_version );
            }
            if ( defined('DOING_AJAX') && DOING_AJAX ) {
                add_action( 'setup_theme', array( __CLASS__, 'WPML_fix' ) );
            }
            add_action( 'admin_menu', array( __CLASS__, 'br_add_options_page' ) );
            add_action( 'admin_init', array( __CLASS__, 'register_br_options' ) );
            add_action( 'init', array( __CLASS__, 'init' ) );

            add_shortcode( 'br_filters', array( __CLASS__, 'shortcode' ) );

            if( ! empty($_GET['filters']) and ! defined( 'DOING_AJAX' ) ) {
                add_filter( 'pre_get_posts', array( __CLASS__, 'apply_user_price' ) );
                add_filter( 'pre_get_posts', array( __CLASS__, 'apply_user_filters' ), 99999 );
            }

            if( ! empty($_GET['explode']) && $_GET['explode'] == 'explode' ) {
                add_action( 'woocommerce_before_template_part', array( 'BeRocket_AAPF_Widget', 'pre_get_posts'), 999999 );
                add_action( 'wp_footer', array( 'BeRocket_AAPF_Widget', 'end_clean'), 999999 );
                add_action( 'init', array( 'BeRocket_AAPF_Widget', 'start_clean'), 1 );
            }
            add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
            $plugin_base_slug = plugin_basename( __FILE__ );
            add_filter( 'plugin_action_links_' . $plugin_base_slug, array( __CLASS__, 'plugin_action_links' ) );
            add_filter( 'is_berocket_settings_page', array( __CLASS__, 'is_settings_page' ) );
            add_filter('woocommerce_product_loop_start', array(__CLASS__, 'fix_categories_on_filtering'));
            add_action( 'wp_head', array( __CLASS__, 'br_custom_user_css' ) );
            add_filter( 'is_active_sidebar', array(__CLASS__, 'is_active_sidebar'), 10, 2);
        } else {
			if( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                add_action( 'admin_notices', array( __CLASS__, 'update_woocommerce' ) );
            } else {
                add_action( 'admin_notices', array( __CLASS__, 'no_woocommerce' ) );
            }
        }
        add_filter('berocket_admin_notices_subscribe_plugins', array(__CLASS__, 'admin_notices_subscribe_plugins'));
    }

    public static function updater_info ( $plugins ) {
        self::$info['slug'] = basename( __DIR__ );
        self::$info['plugin'] = plugin_basename( __FILE__ );
        self::$info = apply_filters( 'berocket_aapf_update_info', self::$info );
        $info = get_plugin_data( __FILE__ );
        self::$info['name'] = $info['Name'];
        $plugins[] = self::$info;
        return $plugins;
    }
    public static function is_active_sidebar($is_active_sidebar, $index) {
        if( $is_active_sidebar ) {
            $sidebars_widgets = wp_get_sidebars_widgets();
            $sidebars_widgets = $sidebars_widgets[$index];
            global $wp_registered_widgets;
            $test = $wp_registered_widgets;
            if( is_array($sidebars_widgets) ) {
                $br_options = apply_filters( 'berocket_aapf_listener_br_options', get_option('br_filters_options') );
                foreach($sidebars_widgets as $widgets) {
                    if( strpos($widgets, 'berocket_aapf_widget') === false ) {
                        return $is_active_sidebar;
                    }
                }
                if( ! empty($br_options['filters_turn_off']) || ( ! is_shop() && ! is_product_category() && ! is_product_taxonomy() && ! is_product_tag() ) ) return false;
            }
        }
        return $is_active_sidebar;
    }

    public static function update_from_older( $version ) {
        $option = self::get_aapf_option();
        if ( version_compare($version, '1.2.4', '<') ) {
            $version_index = 1;
        } else {
            $version_index = 2;
        }

        switch ( $version_index ) {
            case 1:
                update_option('berocket_filter_open_wizard_on_settings', true);
                break;
        }

        update_option( 'br_filters_options', $option );
        update_option( 'br_filters_version', BeRocket_AJAX_filters_version );
    }
    public static function fix_categories_on_filtering($content) {
        if( function_exists('wc_set_loop_prop') && ! empty($_GET['filters']) ) {
            wc_set_loop_prop( 'is_filtered', 1 );
        }
        return $content;
    }
    public static function admin_notices_subscribe_plugins($plugins) {
        $plugins[] = self::$info['id'];
        return $plugins;
    }
    public static function is_settings_page($settings_page) {
        if( ! empty($_GET['page']) && $_GET['page'] == self::$values[ 'option_page' ] ) {
            $settings_page = true;
        }
        return $settings_page;
    }
    public static function plugin_action_links($links) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page='.self::$values['option_page'] ) . '" title="' . __( 'View Plugin Settings', 'BeRocket_products_label_domain' ) . '">' . __( 'Settings', 'BeRocket_products_label_domain' ) . '</a>',
		);
		return array_merge( $action_links, $links );
    }
    public static function plugin_row_meta($links, $file) {
        $plugin_base_slug = plugin_basename( __FILE__ );
        if ( $file == $plugin_base_slug ) {
			$row_meta = array(
				'docs'    => '<a href="http://berocket.com/docs/plugin/'.self::$values['premium_slug'].'" title="' . __( 'View Plugin Documentation', 'BeRocket_products_label_domain' ) . '" target="_blank">' . __( 'Docs', 'BeRocket_products_label_domain' ) . '</a>',
				'premium'    => '<a href="http://berocket.com/product/'.self::$values['premium_slug'].'" title="' . __( 'View Premium Version Page', 'BeRocket_products_label_domain' ) . '" target="_blank">' . __( 'Premium Version', 'BeRocket_products_label_domain' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}
		return (array) $links;
    }

    public static function init() {
        //CHOSEN
        wp_register_style( 'select2', plugins_url( 'css/select2.min.css', __FILE__ ) );
        wp_register_script( 'select2', plugins_url( 'js/select2.min.js', __FILE__ ), array( 'jquery' ) );
        
        wp_register_style( 'berocket_aapf_widget-style', plugins_url( 'css/widget.css', __FILE__ ), array(), BeRocket_AJAX_filters_version );
        wp_enqueue_style( 'berocket_aapf_widget-style' );

        /* custom scrollbar */
        wp_enqueue_script( 'berocket_aapf_widget-scroll-script', plugins_url( 'js/scrollbar/Scrollbar.concat.min.js', __FILE__ ), array( 'jquery' ), BeRocket_AJAX_filters_version );
        wp_register_style( 'berocket_aapf_widget-scroll-style', plugins_url( 'css/scrollbar/Scrollbar.min.css', __FILE__ ), array(), BeRocket_AJAX_filters_version );
        wp_enqueue_style( 'berocket_aapf_widget-scroll-style' );
    }

    public static function no_woocommerce() {
        ?>
        <div class="error">
            <p><?php _e( 'Activate WooCommerce plugin before', 'BeRocket_AJAX_domain' ) ?></p>
        </div>
        <?php
    }

    public static function update_woocommerce() {
        ?>
        <div class="error">
            <p><?php _e( 'Update WooCommerce plugin', 'BeRocket_AJAX_domain' ) ?></p>
        </div>
        <?php
    }

    public static function br_add_options_page() {
        add_submenu_page( 'woocommerce', __( 'Product Filters Settings', 'BeRocket_AJAX_domain' ), __( 'Product Filters', 'BeRocket_AJAX_domain' ), 'manage_options', 'br-product-filters', array( __CLASS__, 'br_render_form' ) );
    }

    public static function shortcode( $atts = array() ) {
        $a = shortcode_atts(
            array(
                'widget_type'     => 'filter',
                'attribute'       => '',
                'type'            => 'checkbox',
                'filter_type'     => 'attribute',
                'operator'        => 'OR',
                'title'           => '',
                'product_cat'     => null,
                'cat_propagation' => '',
                'height'          => 'auto',
                'scroll_theme'    => 'dark',
            ), $atts
        );

        if ( isset( $a['product_cat'] ) ) {
            $a['product_cat'] = json_encode( explode( "|", $a['product_cat'] ) );
        }

        if ( ! $a['attribute'] || ! $a['type']  ) return false;

        the_widget( 'BeRocket_AAPF_widget', $a);
    }

    public static function br_render_form() {
        do_action('BeRocket_wizard_javascript');

        $plugin_info = get_plugin_data(__FILE__, false, true);
        $redirect_to_wizard = get_option('berocket_filter_open_wizard_on_settings');
        if( ! empty($redirect_to_wizard) ) {
            delete_option('berocket_filter_open_wizard_on_settings');
            wp_redirect(admin_url( 'admin.php?page=br-aapf-setup' ));
        }
        wp_enqueue_script( 'berocket_aapf_widget-colorpicker', plugins_url( 'js/colpick.js', __FILE__ ), array( 'jquery' ), BeRocket_AJAX_filters_version );
        wp_enqueue_script( 'berocket_aapf_widget-admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), BeRocket_AJAX_filters_version );

        wp_register_style( 'berocket_aapf_widget-colorpicker-style', plugins_url( 'css/colpick.css', __FILE__ ), array(), BeRocket_AJAX_filters_version );
        wp_register_style( 'berocket_aapf_widget-admin-style', plugins_url( 'css/admin.css', __FILE__ ), array(), BeRocket_AJAX_filters_version );
        wp_enqueue_style( 'berocket_aapf_widget-colorpicker-style' );
        wp_enqueue_style( 'berocket_aapf_widget-admin-style' );

        $plugin_info = get_plugin_data(__FILE__, false, true);
        $paid_plugin_info = self::$info;
        include AAPF_TEMPLATE_PATH . "admin-settings.php";
    }

    public static function apply_user_price( $query, $is_shortcode = FALSE ) {
        $option_permalink = get_option( 'berocket_permalink_option' );
        if ( ( ( ! is_admin() && $query->is_main_query() ) || $is_shortcode ) && ( ! empty($_GET['filters']) || $query->get( $option_permalink['variable'], '' ) ) ) {
            br_aapf_args_converter( $query );

            add_filter( 'loop_shop_post_in', array( __CLASS__, 'remove_out_of_stock' ) );
	    if ( ! empty($_POST['price']) ) {
                list( $_GET['min_price'], $_GET['max_price'] ) = $_POST['price'];
                add_filter( 'loop_shop_post_in', array( __CLASS__, 'price_filter' ) );
            }
        }
    }

    public static function apply_user_filters( $query ) {
        if( $query->is_main_query() and
            ( is_shop() or is_product_category() or is_product_taxonomy() or is_product_tag() )
        ) {
            br_aapf_args_converter();
            $args = br_aapf_args_parser();

            if ( ! empty($_POST['price']) ) {
                list( $_GET['min_price'], $_GET['max_price'] ) = $_POST['price'];
                add_filter( 'loop_shop_post_in', array( __CLASS__, 'price_filter' ) );
            }

            if ( ! empty($_POST['limits']) ) {
                add_filter( 'loop_shop_post_in', array( __CLASS__, 'limits_filter' ) );
            }

            $args_fields = array( 'meta_key', 'tax_query', 'fields', 'where', 'join', 'meta_query' );
            foreach ( $args_fields as $args_field ) {
                if ( ! empty($args[$args_field]) ) {
                    $query->set( $args_field, $args[$args_field] );
                }
            }
        }
        return $query;
    }

    public static function remove_out_of_stock( $filtered_posts ) {
        global $wpdb;
  /*      $matched_products_query = $wpdb->get_results( "
            SELECT DISTINCT ID, post_parent, post_type FROM $wpdb->posts
            INNER JOIN $wpdb->postmeta as meta ON ID = meta.post_id
            WHERE post_type IN ( 'product', 'product_variation' ) AND post_status = 'publish'
            AND meta_key = '_stock_status' AND meta_value = 'outofstock'", OBJECT_K );
        $matched_products = array( 0 );

        foreach ( $matched_products_query as $product ) {
            if ( $product->post_type == 'product' )
                $matched_products[] = $product->ID;
            if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) )
                $matched_products[] = $product->post_parent;
        }

        if( is_array($matched_products) ) {
            $matched_products = array_unique( $matched_products );
        } else {
            $matched_products = array( 0 );
        }

        if ( sizeof( $filtered_posts ) == 0) {
            $filtered_posts = $matched_products;
        } else {
            $filtered_posts = array_intersect( $filtered_posts, $matched_products );
        }
*/	
        //Instantiate and lookup LATITUDE, LONGITUDE, and DISTANCE
	//TO-DO: Add code for distance, currently staticly set to 20 miles
	$latitude = "";
	$longitude = "";
	$distance = (!$_GET['radious'])? 10 : $_GET['radious'];
	
	if(count($_COOKIE['address'])==0){
		$latitude = $_GET['_latitude'];
		$longitude = $_GET['_longitude'];
	} elseif(count($_COOKIE['address'])!=0) {
		$latitude = (!$_GET['_latitude'])? $_COOKIE['latitude'] : $_GET['_latitude'];
		$longitude = (!$_GET['_longitude'])? $_COOKIE['longitude'] : $_GET['_longitude'];
	}  	
		
	$a_posts = implode(",", $filtered_posts);
	//WOO-GEO Distance lookup and add to array filter
	$close_by_query = $wpdb->get_results( "SELECT productid as post_id,( 3959 * acos(cos(radians('{$latitude}')) * cos( radians(latitude))* cos( radians( longitude )- radians('{$longitude}'))+ sin(radians('{$latitude}'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location} HAVING distance < '{$distance}' {$a_posts}", OBJECT_K );
	$close_by_products = array( 0 );
		
        foreach ( $close_by_query as $close_by ) {
            $close_by_products[] = $close_by->post_id;
        } 
		
        if( is_array($close_by_products) ) {
            $close_by_products = array_unique( $close_by_products );
        } else {
            $close_by_products = array( 0 );
        }

        if ( sizeof( $filtered_posts ) == 0) {
            $filtered_posts = $close_by_products;
        } else {
            $filtered_posts = array_intersect( $filtered_posts, $close_by_products );
        }

        return (array) $filtered_posts;
    
    }

    public static function remove_hidden( $filtered_posts ) {
        global $wpdb;
        $matched_products_query = $wpdb->get_results( "
            SELECT DISTINCT ID, post_parent, post_type FROM $wpdb->posts
            INNER JOIN $wpdb->postmeta as meta ON ID = meta.post_id
            WHERE post_type IN ( 'product', 'product_variation' ) AND post_status = 'publish'
            AND meta_key = '_visibility' AND meta_value NOT IN ('hidden', 'search')", OBJECT_K );
        $matched_products = array( 0 );

        foreach ( $matched_products_query as $product ) {
            if ( $product->post_type == 'product' )
                $matched_products[] = $product->ID;
            if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) )
                $matched_products[] = $product->post_parent;
        }

        if( is_array($matched_products) ) {
            $matched_products = array_unique( $matched_products );
        } else {
            $matched_products = array( 0 );
        }

        if ( sizeof( $filtered_posts ) == 0) {
            $filtered_posts = $matched_products;
        } else {
            $filtered_posts = array_intersect( $filtered_posts, $matched_products );
        }

        return (array) $filtered_posts;
    }

    public static function limits_filter( $filtered_posts ) {
        global $wpdb;

        if ( ! empty($_POST['limits']) ) {
            $matched_products = false;

            foreach ( $_POST['limits'] as $v ) {
                $matched_products_query = $wpdb->get_results( $wpdb->prepare("
                    SELECT DISTINCT ID, post_parent, post_type FROM $wpdb->posts
                    INNER JOIN $wpdb->term_relationships as tr ON ID = tr.object_id
                    INNER JOIN $wpdb->term_taxonomy as tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                    INNER JOIN $wpdb->terms as t ON t.term_id = tt.term_id
                    WHERE post_type IN ( 'product', 'product_variation' ) AND post_status = 'publish'
                    AND tt.taxonomy = %s AND t.slug BETWEEN %d AND %d
                ", $v[0], $v[1], $v[2] ), OBJECT_K );

                if ( $matched_products_query ) {
                    if ( $matched_products === false ) {
                        $matched_products = array( 0 );
                        foreach ( $matched_products_query as $product ) {
                            if ( $product->post_type == 'product' )
                                $matched_products[] = $product->ID;
                            if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) )
                                $matched_products[] = $product->post_parent;
                        }
                    } else {
                        $new_products = array( 0 );
                        foreach ( $matched_products_query as $product ) {
                            if ( $product->post_type == 'product' && in_array($product->ID, $matched_products))
                                $new_products[] = $product->ID;
                            if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) && in_array($product->post_parent, $matched_products))
                                $new_products[] = $product->post_parent;
                        }
                        $matched_products = $new_products;
                    }
                }
            }

            if ( $matched_products === false ) {
                $matched_products = array( 0 );
            }

            if( is_array($matched_products) ) {
                $matched_products = array_unique( $matched_products );
            } else {
                $matched_products = array( 0 );
            }

            // Filter the id's
            if ( sizeof( $filtered_posts ) == 0 ) {
                $filtered_posts = $matched_products;
            } else {
                $filtered_posts = array_intersect( $filtered_posts, $matched_products );
            }
        }

        return (array) $filtered_posts;
    }

    public static function price_filter( $filtered_posts ){
        global $wpdb;

        if ( ! empty($_POST['price']) ) {
            $matched_products = array( 0 );
            $min     = floatval( $_POST['price'][0] );
            $max     = floatval( $_POST['price'][1] );

            $matched_products_query = apply_filters( 'woocommerce_price_filter_results', $wpdb->get_results( $wpdb->prepare("
                SELECT DISTINCT ID, post_parent, post_type FROM $wpdb->posts
                INNER JOIN $wpdb->postmeta ON ID = post_id
                WHERE post_type IN ( 'product', 'product_variation' ) AND post_status = 'publish' AND meta_key = %s AND meta_value BETWEEN %d AND %d
            ", '_price', $min, $max ), OBJECT_K ), $min, $max );

            if ( $matched_products_query ) {
                foreach ( $matched_products_query as $product ) {
                    if ( $product->post_type == 'product' )
                        $matched_products[] = $product->ID;
                    if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) )
                        $matched_products[] = $product->post_parent;
                }
            }

            // Filter the id's
            if ( sizeof( $filtered_posts ) == 0) {
                $filtered_posts = $matched_products;
            } else {
                $filtered_posts = array_intersect( $filtered_posts, $matched_products );
            }

        }

        return (array) $filtered_posts;
    }

    /**
     * Get template part (for templates like the slider).
     *
     * @access public
     * @param string $name (default: '')
     * @return void
     */
    public static function br_get_template_part( $name = '' ) {
        $template = '';

        // Look in your_child_theme/woocommerce-filters/name.php
        if ( $name ) {
            $template = locate_template( "woocommerce-filters/{$name}.php" );
        }

        // Get default slug-name.php
        if ( ! $template && $name && file_exists( AAPF_TEMPLATE_PATH . "{$name}.php" ) ) {
            $template = AAPF_TEMPLATE_PATH . "{$name}.php";
        }

        // Allow 3rd party plugin filter template file from their plugin
        $template = apply_filters( 'br_get_template_part', $template, $name );


        if ( $template ) {
            load_template( $template, false );
        }
    }

    public static function register_br_options() {
        register_setting( 'br_filters_plugin_options', 'br_filters_options' );
    }

    public static function br_add_defaults() {
        $tmp = get_option( 'br_filters_options' );
        if ( empty($tmp) or empty($tmp['chk_default_options_db']) or $tmp['chk_default_options_db'] == '1' or ! is_array( $tmp ) ){
            delete_option( 'br_filters_options' );
            update_option( 'br_filters_options', BeRocket_AAPF::$defaults );
        }
    }

    public static function br_delete_plugin_options() {
        delete_option( 'br_filters_options' );
    }

    public static function WPML_fix() {
        global $sitepress;
        if ( method_exists( $sitepress, 'switch_lang' ) && isset( $_POST['current_language'] ) && $_POST['current_language'] !== $sitepress->get_default_language() ) {
            $sitepress->switch_lang( $_POST['current_language'], true );
        }
    }

    public static function order_by_popularity_post_clauses( $args ) {
        global $wpdb;
        $args['orderby'] = "$wpdb->postmeta.meta_value+0 DESC, $wpdb->posts.post_date DESC";
        return $args;
    }

    public static function order_by_rating_post_clauses( $args ) {
        global $wpdb;
        $args['fields'] .= ", AVG( $wpdb->commentmeta.meta_value ) as average_rating ";
        $args['where'] .= " AND ( $wpdb->commentmeta.meta_key = 'rating' OR $wpdb->commentmeta.meta_key IS null ) ";
        $args['join'] .= "
            LEFT OUTER JOIN $wpdb->comments ON($wpdb->posts.ID = $wpdb->comments.comment_post_ID)
            LEFT JOIN $wpdb->commentmeta ON($wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id)
            ";
        $args['orderby'] = "average_rating DESC, $wpdb->posts.post_date DESC";
        $args['groupby'] = "$wpdb->posts.ID";
        return $args;
    }
    public static function get_aapf_option() {
        $options = get_option( 'br_filters_options' );
        if ( ! empty($options) && is_array ( $options ) ) {
            $options = array_merge( BeRocket_AAPF::$defaults, $options );
        } else {
            $options = BeRocket_AAPF::$defaults;
        }
        return $options;
    }
    public static function sanitize_aapf_option( $input ) {
        $default = BeRocket_AAPF::$defaults;
        $result = self::recursive_array_set( $default, $input );
        return $result;
    }
    public static function recursive_array_set( $default, $options ) {
        foreach( $default as $key => $value ) {
            if( array_key_exists( $key, $options ) ) {
                if( is_array( $value ) ) {
                    if( is_array( $options[$key] ) ) {
                        $result[$key] = self::recursive_array_set( $value, $options[$key] );
                    } else {
                        $result[$key] = self::recursive_array_set( $value, array() );
                    }
                } else {
                    $result[$key] = $options[$key];
                }
            } else {
                if( is_array( $value ) ) {
                    $result[$key] = self::recursive_array_set( $value, array() );
                } else {
                    $result[$key] = '';
                }
            }
        }
        foreach( $options as $key => $value ) {
            if( ! array_key_exists( $key, $result ) ) {
                $result[$key] = $value;
            }
        }
        return $result;
    }
    public static function br_custom_user_css() {
        $options     = self::get_aapf_option();
        $replace_css = array(
            '#widget#'       => '.berocket_aapf_widget',
            '#widget-title#' => '.berocket_aapf_widget-title'
        );
        $result_css  = ( empty($options['user_custom_css']) ? '' : $options['user_custom_css'] );
        foreach ( $replace_css as $key => $value ) {
            $result_css = str_replace( $key, $value, $result_css );
        }
        if( ! empty($result_css) ) {
            echo '<style type="text/css">' . $result_css . '</style>';
        }
    }
}

new BeRocket_AAPF;

berocket_admin_notices::generate_subscribe_notice();

/**
 * Creating admin notice if it not added already
 */
if( ! function_exists('BeRocket_generate_sales_2018') ) {
    function BeRocket_generate_sales_2018($data = array()) {
        if( time() < strtotime('-7 days', $data['end']) ) {
            $close_text = 'hide this for 7 days';
            $nothankswidth = 115;
        } else {
            $close_text = 'not interested';
            $nothankswidth = 90;
        }
        $data = array_merge(array(
            'righthtml'  => '<a class="berocket_no_thanks">'.$close_text.'</a>',
            'rightwidth'  => ($nothankswidth+20),
            'nothankswidth'  => $nothankswidth,
            'contentwidth'  => 400,
            'subscribe'  => false,
            'priority'  => 15,
            'height'  => 50,
            'repeat'  => '+7 days',
            'repeatcount'  => 3,
            'image'  => array(
                'local' => plugin_dir_url( __FILE__ ) . 'images/44p_sale.jpg',
            ),
        ), $data);
        new berocket_admin_notices($data);
    }
    BeRocket_generate_sales_2018(array(
        'start'         => 1529532000,
        'end'           => 1530392400,
        'name'          => 'SALE_LABELS_2018',
        'for_plugin'    => array('id' => 18, 'version' => '2.0', 'onlyfree' => true),
        'html'          => 'Save <strong>$20</strong> with <strong>Premium Product Labels</strong> today!
     &nbsp; <span>Get your <strong class="red">44% discount</strong> now!</span>
     <a class="berocket_button" href="https://berocket.com/product/woocommerce-advanced-product-labels" target="_blank">Save $20</a>',
    ));
    BeRocket_generate_sales_2018(array(
        'start'         => 1530396000,
        'end'           => 1531256400,
        'name'          => 'SALE_MIN_MAX_2018',
        'for_plugin'    => array('id' => 9, 'version' => '2.0', 'onlyfree' => true),
        'html'          => 'Save <strong>$20</strong> with <strong>Premium Min/Max Quantity</strong> today!
     &nbsp; <span>Get your <strong class="red">44% discount</strong> now!</span>
     <a class="berocket_button" href="https://berocket.com/product/woocommerce-minmax-quantity" target="_blank">Save $20</a>',
    ));
    BeRocket_generate_sales_2018(array(
        'start'         => 1531260000,
        'end'           => 1532120400,
        'name'          => 'SALE_LOAD_MORE_2018',
        'for_plugin'    => array('id' => 3, 'version' => '2.0', 'onlyfree' => true),
        'html'          => 'Save <strong>$20</strong> with <strong>Premium Load More Products</strong> today!
     &nbsp; <span>Get your <strong class="red">44% discount</strong> now!</span>
     <a class="berocket_button" href="https://berocket.com/product/woocommerce-load-more-products" target="_blank">Save $20</a>',
    ));
}
