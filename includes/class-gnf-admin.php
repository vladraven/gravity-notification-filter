<?php

defined( 'ABSPATH' ) || exit;

final class GNF_Admin {

	private static ?GNF_Admin $instance = null;

	public static function instance(): GNF_Admin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_menu_page' ], 20 );
		add_action( 'admin_init', [ 'GNF_Settings', 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		add_action( 'wp_ajax_gnf_get_form_fields', [ $this, 'ajax_get_form_fields' ] );
		add_action( 'wp_ajax_gnf_save_context_exclusions', [ $this, 'ajax_save_context_exclusions' ] );
		add_action( 'wp_ajax_gnf_export_config', [ $this, 'ajax_export_config' ] );
		add_action( 'wp_ajax_gnf_import_config', [ $this, 'ajax_import_config' ] );
		add_action( 'wp_ajax_gnf_run_tests', [ $this, 'ajax_run_tests' ] );
	}

	public function get_required_capability(): string {
		return (string) apply_filters(
			'gnf_required_capability',
			'manage_options'
		);
	}

	private function check_user_permission(): bool {
		$cap = $this->get_required_capability();

		return current_user_can( $cap )
			|| current_user_can( 'gnf_manage_settings' );
	}

	public function register_menu_page(): void {
		add_submenu_page(
			'gf_edit_forms',
			__( 'Notification Manager', 'gravity-notification-filter' ),
			__( 'Notification Manager', 'gravity-notification-filter' ),
			$this->get_required_capability(),
			'gnf-notification-manager',
			[ $this, 'render_settings_page' ]
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( false === strpos( $hook, 'gnf-notification-manager' ) ) {
			return;
		}

		wp_enqueue_style(
			'gnf-admin-css',
			GNF_PLUGIN_URL . 'admin/css/admin.css',
			[],
			GNF_VERSION
		);

		wp_enqueue_script(
			'gnf-admin-js',
			GNF_PLUGIN_URL . 'admin/js/admin.js',
			[],
			GNF_VERSION,
			true
		);

		wp_localize_script(
			'gnf-admin-js',
			'gnfAdmin',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'gnf_admin_nonce' ),
				'i18n'    => [
					'saved'         => __( 'Settings saved successfully.', 'gravity-notification-filter' ),
					'error'         => __( 'An error occurred. Please try again.', 'gravity-notification-filter' ),
					'confirmImport' => __( 'Are you sure you want to import this configuration? Current settings will be overwritten.', 'gravity-notification-filter' ),
					'testsRunning'  => __( 'Running tests...', 'gravity-notification-filter' ),
					'testsPassed'   => __( 'All tests passed.', 'gravity-notification-filter' ),
					'testsFailed'   => __( 'Some tests failed.', 'gravity-notification-filter' ),
				],
			]
		);
	}

	public function render_settings_page(): void {
		if ( ! $this->check_user_permission() ) {
			wp_die(
				esc_html__(
					'You do not have sufficient permissions to access this page.',
					'gravity-notification-filter'
				)
			);
		}

		$forms = GNF_Forms::get_all_forms();

		require_once GNF_PLUGIN_PATH . 'admin/views/settings.php';
	}

	public function ajax_get_form_fields(): void {
		check_ajax_referer( 'gnf_admin_nonce', 'nonce' );

		if ( ! $this->check_user_permission() ) {
			wp_send_json_error(
				[
					'message' => __(
						'Permission denied.',
						'gravity-notification-filter'
					),
				],
				403
			);
		}

		$form_id = isset( $_POST['form_id'] )
			? absint( $_POST['form_id'] )
			: 0;

		$context_key = isset( $_POST['context_key'] )
			? GNF_Validator::sanitize_context_key(
				wp_unslash( $_POST['context_key'] )
			)
			: (string) $form_id;

		if ( $form_id <= 0 || empty( $context_key ) ) {
			wp_send_json_error(
				[
					'message' => __(
						'Invalid Form ID or Context.',
						'gravity-notification-filter'
					),
				],
				400
			);
		}

		$fields = GNF_Forms::get_form_fields(
			$form_id,
			$context_key,
			false
		);

		$notifications =
			GNF_Forms::get_form_notifications(
				$form_id
			);

		$excluded =
			GNF_Storage::get_excluded_fields_for_context(
				$context_key
			);

		$global_excluded =
			GNF_Storage::get_excluded_fields_for_context(
				GNF_Storage::make_context_key(
					$form_id
				)
			);

		$notification_id = '';

		$prefix = $form_id . '_n_';

		if ( 0 === strpos( $context_key, $prefix ) ) {
			$notification_id = substr(
				$context_key,
				strlen( $prefix )
			);
		}

		$effective_excluded =
			GNF_Storage::get_effective_excluded_fields(
				$form_id,
				$notification_id
			);

		wp_send_json_success(
			[
				'fields'           => $fields,
				'notifications'    => $notifications,
				'excluded'         => $excluded,
				'globalExcluded'   => $global_excluded,
				'contextExcluded'  => $excluded,
				'effectiveExcluded' => $effective_excluded,
				'contextKey'       => $context_key,
			]
		);
	}

	public function ajax_save_context_exclusions(): void {
		check_ajax_referer(
			'gnf_admin_nonce',
			'nonce'
		);

		if ( ! $this->check_user_permission() ) {
			wp_send_json_error(
				[
					'message' => __(
						'Permission denied.',
						'gravity-notification-filter'
					),
				],
				403
			);
		}

		$context_key = isset( $_POST['context_key'] )
			? GNF_Validator::sanitize_context_key(
				wp_unslash( $_POST['context_key'] )
			)
			: '';

		$field_ids = isset( $_POST['field_ids'] )
			&& is_array( $_POST['field_ids'] )
			? wp_unslash( $_POST['field_ids'] )
			: [];

		if ( empty( $context_key ) ) {
			wp_send_json_error(
				[
					'message' => __(
						'Invalid Context Key.',
						'gravity-notification-filter'
					),
				],
				400
			);
		}

		$saved =
			GNF_Storage::save_context_exclusions(
				$context_key,
				$field_ids
			);

		if ( ! $saved ) {
			wp_send_json_error(
				[
					'message' => __(
						'Unable to save settings.',
						'gravity-notification-filter'
					),
				],
				500
			);
		}

		wp_send_json_success(
			[
				'message' => __(
					'Settings saved successfully.',
					'gravity-notification-filter'
				),
			]
		);
	}

	public function ajax_export_config(): void {
		check_ajax_referer(
			'gnf_admin_nonce',
			'nonce'
		);

		if ( ! $this->check_user_permission() ) {
			wp_send_json_error(
				[
					'message' => __(
						'Permission denied.',
						'gravity-notification-filter'
					),
				],
				403
			);
		}

		$exclusions =
			GNF_Storage::get_all_exclusions();

		wp_send_json_success(
			[
				'config' => $exclusions,
			]
		);
	}

	public function ajax_import_config(): void {
		check_ajax_referer(
			'gnf_admin_nonce',
			'nonce'
		);

		if ( ! $this->check_user_permission() ) {
			wp_send_json_error(
				[
					'message' => __(
						'Permission denied.',
						'gravity-notification-filter'
					),
				],
				403
			);
		}

		$json_data = isset( $_POST['json_data'] )
			? wp_unslash( $_POST['json_data'] )
			: '';

		$validated =
			GNF_Validator::validate_json_import(
				$json_data
			);

		if ( is_wp_error( $validated ) ) {
			wp_send_json_error(
				[
					'message' =>
						$validated->get_error_message(),
				],
				400
			);
		}

		$saved =
			GNF_Storage::update_all_exclusions(
				$validated
			);

		if ( ! $saved ) {
			wp_send_json_error(
				[
					'message' => __(
						'Unable to save imported configuration.',
						'gravity-notification-filter'
					),
				],
				500
			);
		}

		wp_send_json_success(
			[
				'message' => __(
					'Configuration imported successfully.',
					'gravity-notification-filter'
				),
			]
		);
	}

	public function ajax_run_tests(): void {
		check_ajax_referer(
			'gnf_admin_nonce',
			'nonce'
		);

		if ( ! $this->check_user_permission() ) {
			wp_send_json_error(
				[
					'message' => __(
						'Permission denied.',
						'gravity-notification-filter'
					),
				],
				403
			);
		}

		$runner_file =
			GNF_PLUGIN_PATH . 'tests/class-gnf-test-runner.php';

		if ( ! file_exists( $runner_file ) ) {
			wp_send_json_error(
				[
					'message' => __(
						'Test runner is not installed.',
						'gravity-notification-filter'
					),
				],
				500
			);
		}

		require_once $runner_file;

		if ( ! class_exists( 'GNF_Test_Runner' ) ) {
			wp_send_json_error(
				[
					'message' => __(
						'Test runner could not be loaded.',
						'gravity-notification-filter'
					),
				],
				500
			);
		}

		try {
			$runner = new GNF_Test_Runner();
			$result = $runner->run();

			wp_send_json_success(
				$result
			);
		} catch ( Throwable $exception ) {
			wp_send_json_error(
				[
					'message' => $exception->getMessage(),
					'type'    => get_class( $exception ),
				],
				500
			);
		}
	}
}