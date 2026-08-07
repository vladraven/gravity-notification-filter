<?php

defined( 'ABSPATH' ) || exit;

final class GNF_Engine {

	private static ?GNF_Engine $instance = null;
	private array $current_notification = [];

	public static function instance(): GNF_Engine {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init(): void {
		add_filter( 'gform_notification', [ $this, 'track_active_notification' ], 10, 3 );
		add_filter( 'gform_merge_tag_filter', [ $this, 'filter_all_fields_merge_tag' ], 10, 6 );
	}

	public function track_active_notification( array $notification, array $form, array $entry ): array {
		$this->current_notification = $notification;
		return $notification;
	}

	public function filter_all_fields_merge_tag( string $value, string $merge_tag, string $modifier, object $field, mixed $raw_value, mixed $format ): string {
		if ( 'all_fields' !== $merge_tag ) {
			return $value;
		}

		if ( ! isset( $field->formId, $field->id ) ) {
			return $value;
		}

		$form_id         = (int) $field->formId;
		$field_id        = (string) $field->id;
		$notification_id = isset( $this->current_notification['id'] ) ? (string) $this->current_notification['id'] : '';

		$is_excluded = GNF_Storage::is_field_excluded( $form_id, $field_id, $notification_id );

		// Reset active notification tracking context after filter execution
		$this->current_notification = [];

		if ( $is_excluded ) {
			return '';
		}

		return $value;
	}
}