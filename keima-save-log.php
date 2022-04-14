<?php
/**
 * Plugin Name: keima | Save log
 * Description:  Add saving log function that has page view, login, video event.
 * Version: 1.0.0
 * Plugin URI:
 * Author: keima.co
 * Author URI: https://www.keima.co/
 * Text Domain: keima-save-log
 * Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

define( 'KEIMA_SAVE_LOG_FILE', __FILE__ );
define( 'KEIMA_SAVE_LOG_DIR', plugin_dir_path( __FILE__ ) );
define( 'KEIMA_SAVE_LOG_VER', '1.0.0' );

if ( ! class_exists( 'KEIMA_SAVE_LOG' ) ) :

  class KEIMA_SAVE_LOG {

    function __construct() {
      // Do nothing.
    }

    function initialize() {

      add_action( 'plugins_loaded', function () {
        load_plugin_textdomain( 'keima-save-log', false, 'keima-save-log/languages/' );
      });

      include_once KEIMA_SAVE_LOG_DIR . 'includes/ksl-set-db.php';
      include_once KEIMA_SAVE_LOG_DIR . 'includes/ksl-admin-list-page.php';
      include_once KEIMA_SAVE_LOG_DIR . 'includes/ksl-export-log.php';
      include_once KEIMA_SAVE_LOG_DIR . 'includes/ksl-functions.php';
      include_once KEIMA_SAVE_LOG_DIR . 'includes/ksl-save-page-log.php';
      include_once KEIMA_SAVE_LOG_DIR . 'includes/ksl-save-vimeo-log.php';

    }

  }

  function keima_save_log() {
    global $keima_save_log;

    // Instantiate only once.
    if ( ! isset( $keima_save_log ) ) {
      $keima_save_log = new KEIMA_SAVE_LOG();
      $keima_save_log->initialize();
    }
    return $keima_save_log;
  }

  // Instantiate.
  keima_save_log();

endif; // class_exists check
