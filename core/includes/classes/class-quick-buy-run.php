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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts_and_styles' ), 20 );

		add_shortcode( 'qv-table-view', array( $this, 'qb_table_view' ) );	
		add_action('wp_ajax_nopriv_qb_get_variation_id',  array( $this, 'qb_get_variation_id') );
		add_action('wp_ajax_qb_get_variation_id',  array( $this, 'qb_get_variation_id') );
		add_action('wp_ajax_nopriv_qb_custom_add_to_cart',  array( $this, 'qb_custom_add_to_cart') );
		add_action('wp_ajax_qb_custom_add_to_cart',  array( $this, 'qb_custom_add_to_cart') );
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
		/*
		wp_enqueue_style( 'quickbuy-backend-styles', QUICKBUY_PLUGIN_URL . 'core/includes/assets/css/backend-styles.css', array(), QUICKBUY_VERSION, 'all' );
		wp_enqueue_script( 'quickbuy-backend-scripts', QUICKBUY_PLUGIN_URL . 'core/includes/assets/js/backend-scripts.js', array(), QUICKBUY_VERSION, false );
		wp_localize_script( 'quickbuy-backend-scripts', 'quickbuy', array(
			'plugin_name'   	=> __( QUICKBUY_NAME, 'quick-buy' ),
		));
		*/
	}

	public function enqueue_frontend_scripts_and_styles()
	{
		wp_enqueue_style( 'quickbuy-frontend-styles', QUICKBUY_PLUGIN_URL . 'core/includes/assets/css/frontend-styles.css', array(), QUICKBUY_VERSION, 'all' );
		wp_enqueue_script( 'quickbuy-frontend-scripts', QUICKBUY_PLUGIN_URL . 'core/includes/assets/js/frontend-scripts.js', array( 'jquery' ), QUICKBUY_VERSION, true );
		wp_localize_script( 'quickbuy-frontend-scripts', 'quickbuy', array( 'plugin_name' => __( QUICKBUY_NAME, 'quick-buy' ) ));
		wp_localize_script( 'quickbuy-frontend-scripts', 'ajax_var', array( 'url'    => admin_url( 'admin-ajax.php' ) ) );
	}

	function find_matching_product_variation_id($product_id, $attributes)
	{
	    return (new \WC_Product_Data_Store_CPT())->find_matching_product_variation(
	        new \WC_Product($product_id),
	        $attributes
	    );
	}

	public function qb_get_variation_id()
	{
		$qb_variations  = isset( $_POST['qb_variations'] ) ? $_POST['qb_variations'] : false;
		$product_id = array_pop($qb_variations);		
		$match_attributes = array();
		foreach ($qb_variations as $qb_variation) {
			$match_attributes[$qb_variation['name']] = $qb_variation['value'];
		}
		$data_store   = WC_Data_Store::load( 'product' );
		$variation_id = $data_store->find_matching_product_variation(
		  new \WC_Product( $product_id['value']),$match_attributes
		);

		echo $variation_id;
		die();
	}

	public function qb_custom_add_to_cart()
	{
		$product_data  = isset( $_POST['product_data'] ) ? $_POST['product_data'] : false;
		$cart_item_key = WC()->cart->add_to_cart( $product_data[1]['value'], $product_data[0]['value'], $product_data[2]['value'] );
		$items_count = WC()->cart->get_cart_contents_count();
		echo $items_count;
		die();
	}

	/**
	 * Returns the parsed shortcode.
	 *
	 * @param array   {
	 *     Attributes of the shortcode.
	 *
	 *     @type string $id ID of...
	 * }
	 * @param string  Shortcode content.
	 *
	 * @return string HTML content to display the shortcode.
	 */
	function qb_table_view( $atts = array(), $content = '' ) {
		$atts = shortcode_atts( array(
			'products' => '',
		), $atts, 'qv-table-view' );

		$products_id = explode(',', $atts['products']);

		$args = array(
		    'include' => $products_id,
		);
		$products = wc_get_products( $args );
		$items_count = WC()->cart->get_cart_contents_count();
	
		ob_start();
		?>
		<table>
			<thead>
				<tr>
					<th><?php _e('Image','quick-buy') ?></th>
					<th><?php _e('Name','quick-buy') ?></th>
					<th><?php _e('Variations','quick-buy') ?></th>
					<th><?php _e('Quantity','quick-buy') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($products as $key => $product): ?>
					<tr>
						<td><?php echo wp_get_attachment_image( $product->image_id ); ?></td>
						<td><?php echo $product->name; ?></td>
						<td>
						<?php if ( $product->is_type( 'variable' ) ): 
							$attribute_keys = array_keys( $product->get_attributes() ); ?>
							<form class="qb-variation-id" action="">								
								<?php foreach ( $product->get_variation_attributes() as $attribute_name => $options ) : ?>
									<label for="<?php echo sanitize_title( $attribute_name ); ?>"><?php echo wc_attribute_label( $attribute_name ); ?></label>
									<?php
									$selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) : $product->get_variation_default_attribute( $attribute_name );
									wc_dropdown_variation_attribute_options( array( 'options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected ) );
									echo end( $attribute_keys ) === $attribute_name ? apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . __( 'Clear', 'woocommerce' ) . '</a>' ) : '';
									?>
								<?php endforeach ?>
								<input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
							</form>
						<?php endif ?>
						</td>							
						<td>
							<form id="qb-add-<?php echo $product->id; ?>" class="qb-add-to-cart" action="">
								<?php echo woocommerce_quantity_input( array(), $product, false ); ?>
								<button class="single_add_to_cart_button button alt"><?php _e('Add to cart','quick-buy') ?></button>
								<input type="hidden" name="add-to-cart" value="<?php echo $product->id; ?>">
								<?php if ( $product->is_type( 'variable' ) ):
									echo '<input type="hidden" name="variation-id" value="">';
								endif; ?>
							</form>							
						</td>
					</tr>					
				<?php endforeach ?>
			</tbody>
		</table>
		<a href="<?php echo wc_get_cart_url(); ?>" class="qb-float-cart button"><span class="qb-float-cart-items-count"><?php echo $items_count; ?></span><span class="dashicons dashicons-cart"></span></a>
		<?php
		return ob_get_clean();
	}

}

//add_filter( 'acf/settings/show_admin', '__return_true', 50 );