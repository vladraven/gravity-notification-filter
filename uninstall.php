<?php
/**
 * Fired when the plugin is uninstalled.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'gnf_excluded_fields' );
delete_option( 'gnf_version' );

$role = get_role( 'administrator' );
if ( $role && $role->has_cap( 'gnf_manage_settings' ) ) {
	$role->remove_cap( 'gnf_manage_settings' );
}