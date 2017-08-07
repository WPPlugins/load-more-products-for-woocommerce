<?php
/**
 * Plugin Name: Load More Products for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/load-more-products-for-woocommerce/
 * Description: Load products from next page via AJAX with infinite scrolling or load more products button
 * Version: 1.0.7
 * Author: BeRocket
 * Requires at least: 4.0
 * Author URI: http://berocket.com
 */
define( "BeRocket_Load_More_Products_version", '1.0.7' );
define( "BeRocket_LMP_domain", 'BRaapf' ); 

define( "LMP_TEMPLATE_PATH", plugin_dir_path( __FILE__ ) . "templates/" );

require_once(plugin_dir_path( __FILE__ ).'includes/admin_notices.php');
require_once dirname( __FILE__ ) . '/includes/functions.php';
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

class BeRocket_LMP {

    public static $defaults = array(
        'br_lmp_general_settings'   => array(
            'type'                      => 'infinity_scroll',
            'use_mobile'                => '',
            'mobile_type'               => 'more_button',
            'mobile_width'              => '767',
            'products_per_page'         => '',
            'loading_image'             => 'fa-spinner',
            'buffer'                    => '50',
        ),
        'br_lmp_button_settings'    => array(
            'button_text'               => 'Load More',
            'custom_class'              => '',
            'background-color'          => '#aaaaff',
            'color'                     => '#333333',
            'font-size'                 => '22',
            'padding-left'              => '25',
            'padding-right'             => '25',
            'padding-top'               => '15',
            'padding-bottom'            => '15',
            'hover'                     => array(
                'background-color'          => '#9999ff',
                'color'                     => '#111111',
            ),
        ),
        'br_lmp_selectors_settings' => array(
            'use_filters_settings'      => '',
            'products'                  => 'ul.products',
            'item'                      => 'li.product',
            'pagination'                => '.woocommerce-pagination',
            'next_page'                 => '.woocommerce-pagination a.next',
        ),
        'br_lmp_javascript_settings'=> array(
            'before_update'             => '',
            'after_update'              => '',
        ),
    );
    public static $values = array(
        'settings_name' => '',
        'option_page'   => 'br-load-more-products',
        'premium_slug'  => 'woocommerce-load-more-products',
    );

    function __construct() {
        if ( ! @ is_network_admin() ) {

            if ( ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) && br_get_woocommerce_version() >= 2.1 ) {
                add_action ( 'wp_head', array( __CLASS__, 'wp_header' ) );
                add_action ( 'init', array( __CLASS__, 'include_front' ) );
                add_action ( 'admin_init', array( __CLASS__, 'include_admin' ) );
                add_action ( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
                add_action ( 'admin_menu', array( __CLASS__, 'lmp_options' ) );
                add_filter ( 'berocket_aapf_user_func', array( __CLASS__, 'filters_compatibility' ) );
                add_filter ( 'berocket_lgv_user_func', array( __CLASS__, 'list_grid_compatibility' ) );
                add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
                $plugin_base_slug = plugin_basename( __FILE__ );
                add_filter( 'plugin_action_links_' . $plugin_base_slug, array( __CLASS__, 'plugin_action_links' ) );
                add_filter( 'is_berocket_settings_page', array( __CLASS__, 'is_settings_page' ) );
            }
        }
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

    public static function wp_header() {
        $options = BeRocket_LMP::get_lmp_option ( 'br_lmp_button_settings' );
        echo '<style>
            .lmp_load_more_button .lmp_button:hover {
                background-color: '.$options['hover']['background-color'].'!important;
                color: '.$options['hover']['color'].'!important;
            }
            .lazy{opacity:0;}
        </style>';
    }

    public static function list_grid_compatibility( $user_func ) {
        $after_set = 'jQuery(window).scrollTop(jQuery(window).scrollTop() + 1).scrollTop(jQuery(window).scrollTop() - 1);';
        $user_func['after_style_set'] = $after_set . $user_func['after_style_set'];
        return $user_func;
    }

    public static function filters_compatibility( $user_func ) {
        $after_update = "lmp_update_state();";
        $user_func['after_update'] = $after_update . $user_func['after_update'];
        return $user_func;
    }

    public static function get_load_more_button() {
        $options = BeRocket_LMP::get_lmp_option ( 'br_lmp_button_settings' );
        $button = '<div class="lmp_load_more_button">';
        $button .= '<a class="lmp_button '.$options['custom_class'].'" style="';
        $button .= 'background-color: '.$options['background-color'].'; color: '.$options['color'].'; font-size: '.$options['font-size'].'px;';
        $button .= 'padding: '.$options['padding-top'].'px '.$options['padding-right'].'px '.$options['padding-bottom'].'px '.$options['padding-left'].'px;';
        $button .= '" href="#load_next_page">'.$options['button_text'].'</a>';
        $button .= '</div>';
        return $button;
    }

    public static function add_javascript_data() {
        $general_options = BeRocket_LMP::get_lmp_option ( 'br_lmp_general_settings' );
        $button_options = BeRocket_LMP::get_lmp_option ( 'br_lmp_button_settings' );
        $selectors_options = BeRocket_LMP::get_lmp_option ( 'br_lmp_selectors_settings' );
        $javascript_options = BeRocket_LMP::get_lmp_option ( 'br_lmp_javascript_settings' );
        if ( $selectors_options['use_filters_settings'] && br_is_plugin_active( 'filters', '2.0.5' ) ) {
            $filters_settings = apply_filters( 'berocket_aapf_listener_br_options', get_option('br_filters_options') );
            $products_selector = @ $filters_settings['products_holder_id'];
            $item_selector = @ $filters_settings['products_holder_id'].' .product';
            $pagination_selector = @ $filters_settings['woocommerce_pagination_class'];
            $next_page_selector = @ $filters_settings['woocommerce_pagination_class'].' .next';
        } else {
            $products_selector = $selectors_options['products'];
            $item_selector = $selectors_options['item'];
            $pagination_selector = $selectors_options['pagination'];
            $next_page_selector = $selectors_options['next_page'];
        }
        $image_class = 'lmp_rotate';
        $image = '<div class="lmp_products_loading">';
        if ( $general_options['loading_image'] ) {
            $image .= '<i class="fa '.$general_options['loading_image'].' '.$image_class.'"></i>';
        } else {
            $image .= '<i class="fa fa-spinner '.$image_class.'"></i>';
        }
        $image .= '</div>';
        $load_more_button = self::get_load_more_button();
        wp_localize_script(
            'berocket_lmp_js',
            'the_lmp_js_data',
            array(
                'type'          => $general_options['type'],
                'use_mobile'    => $general_options['use_mobile'],
                'mobile_type'   => $general_options['mobile_type'],
                'mobile_width'  => $general_options['mobile_width'],
                'is_AAPF'       => br_is_plugin_active( 'filters', '2.0.5' ),
                'buffer'        => $general_options['buffer'],

                'load_image'    => $image,
                'load_img_class'=> '.lmp_products_loading',

                'load_more'     => $load_more_button,

                'lazy_load'     => false,

                'end_text'      => '',

                'javascript'    => $javascript_options,

                'products'      => $products_selector,
                'item'          => $item_selector,
                'pagination'    => $pagination_selector,
                'next_page'     => $next_page_selector,
            )
        );
    }

    public static function include_front() {
        load_plugin_textdomain( BeRocket_LMP_domain, false, WP_PLUGIN_DIR . '/languages' );
        wp_enqueue_script( 'berocket_lmp_js', plugins_url( 'js/load_products.js', __FILE__ ), array( 'jquery' ), BeRocket_Load_More_Products_version );
        wp_register_style( 'berocket_lmp_style', plugins_url( 'css/load_products.css', __FILE__ ), "", BeRocket_Load_More_Products_version );
        wp_enqueue_style( 'berocket_lmp_style' );
        wp_register_style( 'font-awesome', plugins_url( 'css/font-awesome.min.css', __FILE__ ) );
        wp_enqueue_style( 'font-awesome' );
        self::add_javascript_data();
    }

    public static function admin_enqueue_scripts() {
        if ( function_exists( 'wp_enqueue_media' ) ) {
            wp_enqueue_media();
        } else {
            wp_enqueue_style( 'thickbox' );
            wp_enqueue_script( 'media-upload' );
            wp_enqueue_script( 'thickbox' );
        }
    }

    public static function include_admin() {
        if( @ $_GET['page'] == 'br-load-more-products' ) {
            wp_register_style( 'berocket_lmp_admin_style', plugins_url( 'css/admin.css', __FILE__ ), "", BeRocket_Load_More_Products_version );
            wp_enqueue_style( 'berocket_lmp_admin_style' );
            wp_register_style( 'berocket_lmp_fa_select_style', plugins_url( 'css/select_fa.css', __FILE__ ), "", BeRocket_Load_More_Products_version );
            wp_enqueue_style( 'berocket_lmp_fa_select_style' );
            wp_enqueue_script( 'berocket_lmp_admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), BeRocket_Load_More_Products_version );
            wp_enqueue_script( 'berocket_lmp_admin_fa', plugins_url( 'js/admin_select_fa.js', __FILE__ ), array( 'jquery' ), BeRocket_Load_More_Products_version );
            wp_enqueue_script( 'berocket_aapf_widget-colorpicker', plugins_url( 'js/colpick.js', __FILE__ ), array( 'jquery' ) );
            wp_register_style( 'berocket_aapf_widget-colorpicker-style', plugins_url( 'css/colpick.css', __FILE__ ) );
            wp_enqueue_style( 'berocket_aapf_widget-colorpicker-style' );
        }
        
        register_setting('br_lmp_general_settings', 'br_lmp_general_settings', array( __CLASS__, 'sanitize_lmp_option' ));
        register_setting('br_lmp_button_settings', 'br_lmp_button_settings', array( __CLASS__, 'sanitize_lmp_option' ));
        register_setting('br_lmp_selectors_settings', 'br_lmp_selectors_settings', array( __CLASS__, 'sanitize_lmp_option' ));
        register_setting('br_lmp_lazy_load_settings', 'br_lmp_lazy_load_settings', array( __CLASS__, 'sanitize_lmp_option' ));
        register_setting('br_lmp_messages_settings', 'br_lmp_messages_settings', array( __CLASS__, 'sanitize_lmp_option' ));
        register_setting('br_lmp_javascript_settings', 'br_lmp_javascript_settings', array( __CLASS__, 'sanitize_lmp_option' ));
        register_setting('br_lmp_license_settings', 'br_lmp_license_settings', array( __CLASS__, 'sanitize_lmp_option' ));
        add_settings_section( 
            'br_lgv_general_page',
            'General Settings',
            'br_lmp_general_callback',
            'br_lmp_general_settings'
        );

        add_settings_section( 
            'br_lgv_button_page',
            'Button Settings',
            'br_lmp_button_callback',
            'br_lmp_button_settings'
        );

        add_settings_section( 
            'br_lgv_selectors_page',
            'Selectors Settings',
            'br_lmp_selectors_callback',
            'br_lmp_selectors_settings'
        );

        add_settings_section( 
            'br_lgv_lazy_load_page',
            'Lazy Load Settings',
            'br_lmp_lazy_load_callback',
            'br_lmp_lazy_load_settings'
        );

        add_settings_section( 
            'br_lgv_messages_page',
            'Messages Settings',
            'br_lmp_messages_callback',
            'br_lmp_messages_settings'
        );

        add_settings_section( 
            'br_lgv_javascript_page',
            'JavaScript Settings',
            'br_lmp_javascript_callback',
            'br_lmp_javascript_settings'
        );

        add_settings_section( 
            'br_lgv_license_page',
            'License Settings',
            'br_lmp_license_callback',
            'br_lmp_license_settings'
        );
    }
    /**
     * Function add options button to admin panel
     *
     * @access public
     *
     * @return void
     */
    public static function lmp_options() {
        add_submenu_page( 'woocommerce', __('Load More Products settings', BeRocket_LMP_domain), __('Load More Products', BeRocket_LMP_domain), 'manage_options', 'br-load-more-products', array(
            __CLASS__,
            'lmp_option_form'
        ) );
    }
    /**
     * Function add options form to settings page
     *
     * @access public
     *
     * @return void
     */
    public static function lmp_option_form() {
        $plugin_info = get_plugin_data(__FILE__, false, true);
        include LMP_TEMPLATE_PATH . "settings.php";
    }

    public static function get_lmp_default( $option_name ) {
        $options = BeRocket_LMP::$defaults[$option_name];
        return $options;
    }
    public static function sanitize_lmp_option( $input ) {
        $default = BeRocket_LMP::$defaults[$input['settings_name']];
        $result = self::recursive_array_set( $default, $input );
        return $result;
    }
    public static function recursive_array_set( $default, $options ) {
        $result = array();
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
    public static function get_lmp_option( $option_name ) {
        $options = get_option( $option_name );
        if ( @ $options && is_array ( $options ) ) {
            $options = array_merge( BeRocket_LMP::$defaults[$option_name], $options );
        } else {
            $options = BeRocket_LMP::$defaults[$option_name];
        }
        return $options;
    }
}
new BeRocket_LMP;

berocket_admin_notices::generate_subscribe_notice();
new berocket_admin_notices(array(
    'start' => 1498413376, // timestamp when notice start
    'end'   => 1504223940, // timestamp when notice end
    'name'  => 'name', //notice name must be unique for this time period
    'html'  => 'Only <strong>$10</strong> for <strong>Premium</strong> WooCommerce Load More Products plugin!
        <a class="berocket_button" href="http://berocket.com/product/woocommerce-load-more-products" target="_blank">Buy Now</a>
         &nbsp; <span>Get your <strong class="red">50% discount</strong> and save <strong>$10</strong> today</span>
        ', //text or html code as content of notice
    'righthtml'  => '<a class="berocket_no_thanks">No thanks</a>', //content in the right block, this is default value. This html code must be added to all notices
    'rightwidth'  => 80, //width of right content is static and will be as this value. berocket_no_thanks block is 60px and 20px is additional
    'nothankswidth'  => 60, //berocket_no_thanks width. set to 0 if block doesn't uses. Or set to any other value if uses other text inside berocket_no_thanks
    'contentwidth'  => 400, //width that uses for mediaquery is image_width + contentwidth + rightwidth
    'subscribe'  => false, //add subscribe form to the righthtml
    'priority'  => 10, //priority of notice. 1-5 is main priority and displays on settings page always
    'height'  => 50, //height of notice. image will be scaled
    'repeat'  => false, //repeat notice after some time. time can use any values that accept function strtotime
    'repeatcount'  => 1, //repeat count. how many times notice will be displayed after close
    'image'  => array(
        'local' => plugin_dir_url( __FILE__ ) . 'images/ad_white_on_orange.png', //notice will be used this image directly
    ),
));
