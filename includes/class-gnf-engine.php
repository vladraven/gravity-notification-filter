<?php

defined( 'ABSPATH' ) || exit;

final class GNF_Engine {

	private static ?GNF_Engine $instance = null;

	private string $current_notification_id = '';

	public static function instance(): GNF_Engine {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
	}

	public function init(): void {
		add_filter(
			'gform_notification',
			[ $this, 'track_notification' ],
			10,
			3
		);

		add_filter(
			'gform_merge_tag_filter',
			[ $this, 'filter_all_fields_merge_tag' ],
			10,
			6
		);

		add_action(
			'gform_after_email',
			[ $this, 'reset_notification_context' ],
			10,
			5
		);
	}

	public function track_notification(
		array $notification,
		array $form,
		array $entry
	): array {
		$this->current_notification_id = '';

		if ( isset( $notification['id'] ) ) {
			$this->current_notification_id =
				GNF_Validator::sanitize_notification_id(
					$notification['id']
				);
		}

		return $notification;
	}

	public function filter_all_fields_merge_tag(
		string $value,
		string $merge_tag,
		string $modifier,
		object $field,
		mixed $raw_value,
		mixed $format
	): string {
		if ( 'all_fields' !== $merge_tag ) {
			return $value;
		}

		if ( ! is_object( $field ) ) {
			return $value;
		}

		if (
			! isset(
				$field->formId,
				$field->id
			)
		) {
			return $value;
		}

		$form_id = absint(
			$field->formId
		);

		$field_id =
			GNF_Validator::sanitize_field_id(
				$field->id
			);

		if (
			$form_id <= 0
			|| '' === $field_id
		) {
			return $value;
		}

		$excluded =
			GNF_Storage::get_effective_excluded_fields(
				$form_id,
				$this->current_notification_id
			);

		if (
			in_array(
				$field_id,
				$excluded,
				true
			)
		) {
			return '';
		}

		return $value;
	}

	public function reset_notification_context(
		string $email,
		array $notification,
		array $form,
		array $entry,
		?bool $is_admin = null
	): void {
		$this->current_notification_id = '';
	}

	public function reset(): void {
		$this->current_notification_id = '';
	}
}