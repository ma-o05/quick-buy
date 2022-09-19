<?php
/**
 * Quick buy
 *
 * @package       QUICKBUY
 * @author        Jesús Morales
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   Quick buy
 * Plugin URI:    https://zzani.com/
 * Description:   This is some demo short description...
 * Version:       1.0.0
 * Author:        Jesús Morales
 * Author URI:    https://zzani.com/
 * Text Domain:   quick-buy
 * Domain Path:   /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
// Plugin name
define( 'QUICKBUY_NAME',			'Quick buy' );

// Plugin version
define( 'QUICKBUY_VERSION',		'1.0.0' );

// Plugin Root File
define( 'QUICKBUY_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'QUICKBUY_PLUGIN_BASE',	plugin_basename( QUICKBUY_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'QUICKBUY_PLUGIN_DIR',	plugin_dir_path( QUICKBUY_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'QUICKBUY_PLUGIN_URL',	plugin_dir_url( QUICKBUY_PLUGIN_FILE ) );

/**
 * Load the main class for the core functionality
 */
require_once QUICKBUY_PLUGIN_DIR . 'core/class-quick-buy.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Jesús Morales
 * @since   1.0.0
 * @return  object|Quick_Buy
 */
function QUICKBUY() {
	return Quick_Buy::instance();
}

QUICKBUY();