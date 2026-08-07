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
	}

	private function get_required_capability(): string {
		return (string) apply_filters( 'gnf_required_capability', 'manage_options' );
	}

	private function check_user_permission(): bool {
		$cap = $this->get_required_capability();
		return current_user_can( $cap ) || current_user_can( 'gnf_manage_settings' );
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
				],
			]
		);
	}

	public function render_settings_page(): void {
		if ( ! $this->check_user_permission() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'gravity-notification-filter' ) );
		}

		$forms = GNF_Forms::get_all_forms();
		require_once GNF_PLUGIN_PATH . 'admin/views/settings.php';
	}

	public function ajax_get_form_fields(): void {
		check_ajax_referer( 'gnf_admin_nonce', 'nonce' );

		if ( ! $this->check_user_permission() ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'gravity-notification-filter' ) ], 403 );
		}

		$form_id     = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		$context_key = isset( $_POST['context_key'] ) ? sanitize_text_field( $_POST['context_key'] ) : (string) $form_id;

		if ( $form_id <= 0 ) {
			wp_send_json_error( [ 'message' => __( 'Invalid Form ID.', 'gravity-notification-filter' ) ], 400 );
		}

		$fields        = GNF_Forms::get_form_fields( $form_id, $context_key );
		$notifications = GNF_Forms::get_form_notifications( $form_id );
		$excluded      = GNF_Storage::get_excluded_fields_for_context( $context_key );

		wp_send_json_success(
			[
				'fields'        => $fields,
				'notifications' => $notifications,
				'excluded'      => $excluded,
			]
		);
	}

	public function ajax_save_context_exclusions(): void {
		check_ajax_referer( 'gnf_admin_nonce', 'nonce' );

		if ( ! $this->check_user_permission() ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'gravity-notification-filter' ) ], 403 );
		}

		$context_key = isset( $_POST['context_key'] ) ? sanitize_text_field( $_POST['context_key'] ) : '';
		$field_ids   = isset( $_POST['field_ids'] ) && is_array( $_POST['field_ids'] ) ? $_POST['field_ids'] : [];

		if ( empty( $context_key ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid Context Key.', 'gravity-notification-filter' ) ], 400 );
		}

		GNF_Storage::save_context_exclusions( $context_key, $field_ids );

		wp_send_json_success( [ 'message' => __( 'Settings saved successfully.', 'gravity-notification-filter' ) ] );
	}

	public function ajax_export_config(): void {
		check_ajax_referer( 'gnf_admin_nonce', 'nonce' );

		if ( ! $this->check_user_permission() ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'gravity-notification-filter' ) ], 403 );
		}

		$exclusions = GNF_Storage::get_all_exclusions();
		wp_send_json_success( [ 'config' => $exclusions ] );
	}

	public function ajax_import_config(): void {
		check_ajax_referer( 'gnf_admin_nonce', 'nonce' );

		if ( ! $this->check_user_permission() ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'gravity-notification-filter' ) ], 403 );
		}

		$json_data = isset( $_POST['json_data'] ) ? wp_unslash( $_POST['json_data'] ) : '';
		$validated = GNF_Validator::validate_json_import( $json_data );

		if ( is_wp_error( $validated ) ) {
			wp_send_json_error( [ 'message' => $validated->get_error_message() ], 400 );
		}

		GNF_Storage::update_all_exclusions( $validated );

		wp_send_json_success( [ 'message' => __( 'Configuration imported successfully.', 'gravity-notification-filter' ) ] );
	}
}