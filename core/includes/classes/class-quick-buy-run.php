<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Quick_Buy_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package		QUICKBUY
 * @subpackage	Classes/Quick_Buy_Run
 * @author		Jesús Morales
 * @since		1.0.0
 */
class Quick_Buy_Run{

	/**
	 * Our Quick_Buy_Run constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct(){
		$this->add_hooks();
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOKS
	 * ###
	 * ######################
	 */

	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access	private
	 * @since	1.0.0
	 * @return	void
	 */
	private function add_hooks(){
	
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend_scripts_and_styles' ), 20 );
		add_action( 'init', array( $this, 'qb_quantity_handler' ) );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
		add_action( 'woocommerce_after_shop_loop_item', array( $this, 'qb_show_variations_loop_products' ), 10 );
		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'qb_add_quantity_fields' ), 10, 2 );
		add_filter( 'woocommerce_quantity_input_args', array( $this, 'qb_woocommerce_quantity_input_args' ), 10, 2 );
	
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOK CALLBACKS
	 * ###
	 * ######################
	 */

	/**
	 * Enqueue the backend related scripts and styles for this plugin.
	 * All of the added scripts andstyles will be available on every page within the backend.
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function enqueue_backend_scripts_and_styles() {
		wp_enqueue_style( 'quickbuy-backend-styles', QUICKBUY_PLUGIN_URL . 'core/includes/assets/css/backend-styles.css', array(), QUICKBUY_VERSION, 'all' );
		wp_enqueue_script( 'quickbuy-backend-scripts', QUICKBUY_PLUGIN_URL . 'core/includes/assets/js/backend-scripts.js', array(), QUICKBUY_VERSION, false );
		wp_localize_script( 'quickbuy-backend-scripts', 'quickbuy', array(
			'plugin_name'   	=> __( QUICKBUY_NAME, 'quick-buy' ),
		));
	}

	public function qb_show_variations_loop_products()
	{
		global $product;
		 
	    if ( ! $product->is_type( 'variable' ) ) {
	        woocommerce_template_loop_add_to_cart();
	        remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	        return;
	    }
	 
	    remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
	    add_action( 'woocommerce_single_variation', array( $this, 'qb_loop_variation_add_to_cart_button' ), 20 );
	 
	    woocommerce_template_single_add_to_cart();
	}

	public function qb_loop_variation_add_to_cart_button() {
	    global $product; ?>
	    <div class="woocommerce-variation-add-to-cart variations_button">
            <button type="submit" class="single_add_to_cart_button button"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
            <input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>">
            <input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>">
            <input type="hidden" name="variation_id" class="variation_id" value="0">
        </div>
	    <?php
	}

	public function qb_add_quantity_fields($html, $product) {
		$html = '<form action="' . esc_url( $product->add_to_cart_url() ) . '" class="cart" method="post" enctype="multipart/form-data">';
		$html .= woocommerce_quantity_input( array(), $product, false );
		$html .= '<button type="submit" data-quantity="1" data-product_id="' . $product->get_id() . '" class="button alt ajax_add_to_cart add_to_cart_button product_type_simple">' . esc_html( $product->add_to_cart_text() ) . '</button>';
		$html .= '</form>';

		return $html;
	}

	function qb_woocommerce_quantity_input_args( $args, $product ) {
		$args['input_value'] 	= 1;
		$args['max_value'] 	= $product->get_stock_quantity();
		$args['min_value'] 	= 0;
		$args['step'] 		= 1;
		return $args;
	}

	public function qb_quantity_handler() {
		wc_enqueue_js( '
		jQuery(function($) {
		$("form.cart").on("change", "input.qty", function() {
        $(this.form).find("[data-quantity]").attr("data-quantity", this.value);  //used attr instead of data, for WC 4.0 compatibility
		});
		' );

		wc_enqueue_js( '
		$(document.body).on("adding_to_cart", function() {
			$("a.added_to_cart").remove();
		});
		});
		' );
	}

}

//add_filter( 'acf/settings/show_admin', '__return_true', 50 );