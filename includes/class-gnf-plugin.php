<?php

defined( 'ABSPATH' ) || exit;

final class GNF_Plugin {

	private static ?GNF_Plugin $instance = null;

	public static function instance(): GNF_Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	private function load_dependencies(): void {
		require_once GNF_PLUGIN_PATH . 'includes/class-gnf-validator.php';
		require_once GNF_PLUGIN_PATH . 'includes/class-gnf-storage.php';
		require_once GNF_PLUGIN_PATH . 'includes/class-gnf-forms.php';
		require_once GNF_PLUGIN_PATH . 'includes/class-gnf-engine.php';
		require_once GNF_PLUGIN_PATH . 'includes/class-gnf-settings.php';
		require_once GNF_PLUGIN_PATH . 'includes/class-gnf-admin.php';
		require_once GNF_PLUGIN_PATH . 'includes/class-gnf-cli.php';
	}

	private function init_hooks(): void {
		register_activation_hook(
			GNF_PLUGIN_FILE,
			[ $this, 'activate' ]
		);

		register_deactivation_hook(
			GNF_PLUGIN_FILE,
			[ $this, 'deactivate' ]
		);

		add_action(
			'plugins_loaded',
			[ $this, 'init' ]
		);
	}

	public function activate(): void {
		$role = get_role( 'administrator' );

		if ( $role && ! $role->has_cap( 'gnf_manage_settings' ) ) {
			$role->add_cap( 'gnf_manage_settings' );
		}

		if ( false === get_option( 'gnf_excluded_fields' ) ) {
			add_option(
				'gnf_excluded_fields',
				[],
				'',
				false
			);
		}

		update_option(
			'gnf_version',
			GNF_VERSION
		);
	}

	public function deactivate(): void {
	}

	public function init(): void {
		if ( ! class_exists( 'GFForms' ) ) {
			add_action(
				'admin_notices',
				[ $this, 'render_gf_missing_notice' ]
			);

			return;
		}

		GNF_Engine::instance()->init();

		if ( is_admin() ) {
			GNF_Admin::instance()->init();
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			GNF_CLI::register_commands();
		}
	}

	public function render_gf_missing_notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p>';
		echo esc_html__(
			'Gravity Forms Notification Filter requires Gravity Forms to be installed and active.',
			'gravity-notification-filter'
		);
		echo '</p></div>';
	}
}