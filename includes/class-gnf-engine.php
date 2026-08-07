<?php

defined( 'ABSPATH' ) || exit;

final class GNF_Engine {

	private static ?GNF_Engine $instance = null;

	public static function instance(): GNF_Engine {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init(): void {
		add_filter( 'gform_merge_tag_filter', [ $this, 'filter_all_fields_merge_tag' ], 10, 6 );
	}

	/**
	 * Filters the value of fields inside {all_fields}.
	 *
	 * @param string $value      The field value HTML/Text.
	 * @param string $merge_tag  The merge tag name (e.g., 'all_fields').
	 * @param string $modifier   Modifiers passed to tag.
	 * @param object $field      The GF_Field object.
	 * @param array  $raw_value  Raw field value.
	 * @param array  $format     Format type.
	 * @return string Filtered content (empty string if excluded).
	 */
	public function filter_all_fields_merge_tag( string $value, string $merge_tag, string $modifier, object $field, mixed $raw_value, mixed $format ): string {
		if ( 'all_fields' !== $merge_tag ) {
			return $value;
		}

		if ( ! isset( $field->formId, $field->id ) ) {
			return $value;
		}

		$form_id  = (int) $field->formId;
		$field_id = (int) $field->id;

		if ( GNF_Storage::is_field_excluded( $form_id, $field_id ) ) {
			return '';
		}

		return $value;
	}
}