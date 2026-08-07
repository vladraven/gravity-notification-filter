<?php
/**
 * Plugin Name: Gravity Forms Notification Filter
 * Plugin URI:  https://github.com/vladraven/gravity-notification-filter
 * Description: Allows administrators to control which Gravity Forms fields and sub-fields are included in the {all_fields} merge tag used by notification emails.
 * Version:     1.1.3
 * Author:      Vladimir Klekovkin
 * Author URI:  https://github.com/vladraven
 * License:     GPL-2.0-or-later
 * Text Domain: gravity-notification-filter
 * Requires PHP: 8.2
 * Requires at least: 6.2
 */

defined( 'ABSPATH' ) || exit;

define( 'GNF_VERSION', '1.1.3' );
define( 'GNF_PLUGIN_FILE', __FILE__ );
define( 'GNF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'GNF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once GNF_PLUGIN_PATH . 'includes/class-gnf-plugin.php';

GNF_Plugin::instance();