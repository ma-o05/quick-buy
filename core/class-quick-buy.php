<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'Quick_Buy' ) ) :

	/**
	 * Main Quick_Buy Class.
	 *
	 * @package		QUICKBUY
	 * @subpackage	Classes/Quick_Buy
	 * @since		1.0.0
	 * @author		Jesús Morales
	 */
	final class Quick_Buy {

		/**
		 * The real instance
		 *
		 * @access	private
		 * @since	1.0.0
		 * @var		object|Quick_Buy
		 */
		private static $instance;

		/**
		 * QUICKBUY helpers object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Quick_Buy_Helpers
		 */
		public $helpers;

		/**
		 * QUICKBUY settings object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Quick_Buy_Settings
		 */
		public $settings;

		/**
		 * Throw error on object clone.
		 *
		 * Cloning instances of the class is forbidden.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'quick-buy' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'quick-buy' ), '1.0.0' );
		}

		/**
		 * Main Quick_Buy Instance.
		 *
		 * Insures that only one instance of Quick_Buy exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access		public
		 * @since		1.0.0
		 * @static
		 * @return		object|Quick_Buy	The one true Quick_Buy
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Quick_Buy ) ) {
				self::$instance					= new Quick_Buy;
				self::$instance->base_hooks();
				self::$instance->includes();
				self::$instance->helpers		= new Quick_Buy_Helpers();
				self::$instance->settings		= new Quick_Buy_Settings();

				//Fire the plugin logic
				new Quick_Buy_Run();

				/**
				 * Fire a custom action to allow dependencies
				 * after the successful plugin setup
				 */
				do_action( 'QUICKBUY/plugin_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Include required files.
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function includes() {
			require_once QUICKBUY_PLUGIN_DIR . 'core/includes/classes/class-quick-buy-helpers.php';
			require_once QUICKBUY_PLUGIN_DIR . 'core/includes/classes/class-quick-buy-settings.php';

			require_once QUICKBUY_PLUGIN_DIR . 'core/includes/classes/class-quick-buy-run.php';
		}

		/**
		 * Add base hooks for the core functionality
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function base_hooks() {
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'quick-buy', FALSE, dirname( plugin_basename( QUICKBUY_PLUGIN_FILE ) ) . '/languages/' );
		}

	}

endif; // End if class_exists check.