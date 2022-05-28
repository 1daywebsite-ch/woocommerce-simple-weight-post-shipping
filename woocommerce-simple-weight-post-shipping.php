<?php
/**
 * Plugin Name: WooCommerce Simple Weight post.ch Shipping 
 * Plugin URI: 
 * Description: Enable Shipping Methods for Swiss Post PostPac
 * Version: 1.0.0
 * Author: AFB
 * Author URI: 
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /lang
 * Text Domain: wc-simple-weight
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));

if ( wc_simple_weight_post_method_active()) {
	/**
	* Add new shipment methods:
	* WC_Simple_Weight_Post_Economy_Method
	* WC_Simple_Weight_Post_Priority_Method
	* WC_Simple_Weight_Post_Express_Method
	*/
	add_filter( 'woocommerce_shipping_methods', 'add_wc_simple_weight_post_methods' );	
	function add_wc_simple_weight_post_methods( $methods ) {
		$wc_simple_weight_post_economy_method = new WC_Simple_Weight_Post_Economy_Method();
		$wc_simple_weight_post_priority_method = new WC_Simple_Weight_Post_Priority_Method();
		$wc_simple_weight_post_express_method = new WC_Simple_Weight_Post_Express_Method();

        if ( $wc_simple_weight_post_economy_method->settings['enabled'] = 'yes' ) {
			$methods['wcw_post_economy'] = 'WC_Simple_Weight_Post_Economy_Method';
		}	
        if ( $wc_simple_weight_post_priority_method->settings['enabled'] = 'yes' ) {
			$methods['wcw_post_priority'] = 'WC_Simple_Weight_Post_Priority_Method';
		}
        if ( $wc_simple_weight_post_express_method->settings['enabled'] = 'yes' ) {
			$methods['wcw_post_express'] = 'WC_Simple_Weight_Post_Express_Method';
		}
		return $methods;
	}
	/**
	* Load classes and plugin text domain
	*/
	add_action('plugins_loaded', 'wc_simple_weight_post_init_classes');
	function wc_simple_weight_post_init_classes(){
		require 'includes/class-wc-simple-weight-post-economy.php';
		require 'includes/class-wc-simple-weight-post-priority.php';
		require 'includes/class-wc-simple-weight-post-express.php';
	}
	add_action( 'plugins_loaded', 'wc_simple_weight_post_load_plugin_textdomain' );
	function wc_simple_weight_post_load_plugin_textdomain() {
		load_plugin_textdomain( 'wc-simple-weight', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

    function wc_simple_weight_post_economy_validate_order( $posted )   {
 		$chosen_shipping_method = WC()->session->get('chosen_shipping_methods');
		$max_weight = 30;
		
		if (substr($chosen_shipping_method[0], 0, 8) == 'wcw_post') {
			if ( WC()->cart->cart_contents_weight > $max_weight ) {
				$notice = sprintf( __( 'Sorry, your cart exceeds the maximum weight of %d %s for PostPac (Swiss Post) Shipping. You can reduce items in your cart to complete this order. If you want parcels over the maximum weight for PostPac sent to you, please contact us via the contact form. Thank you!', 'wc-simple-weight' ), $max_weight, get_option( 'woocommerce_weight_unit' ) );
				wc_add_notice( $notice, 'error' );
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
			}
		}	
    }
	//add_action( 'woocommerce_update_cart_action_cart_updated', 'wc_simple_weight_post_economy_validate_order' , 10 );
	//add_action( 'woocommerce_review_order_before_cart_contents', 'wc_simple_weight_post_economy_validate_order' , 10 );
	//add_action( 'woocommerce_after_checkout_validation', 'wc_simple_weight_post_economy_validate_order' , 10 );
	add_action( 'woocommerce_check_cart_items', 'wc_simple_weight_post_economy_validate_order' , 10 );
}
/**
* Check if WooCommerce is active, otherwise don't run plugin
* @return bool
*/
function wc_simple_weight_post_method_active() {
	$active_plugins = (array) get_option('active_plugins', array());
	if (is_multisite()) {
		$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	}
	return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
}