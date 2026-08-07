<?php

defined( 'ABSPATH' ) || exit;

final class GNF_Forms {

	public static function get_all_forms(): array {
		if ( ! class_exists( 'GFAPI' ) ) {
			return [];
		}

		$forms  = GFAPI::get_forms();
		$result = [];

		foreach ( $forms as $form ) {
			$result[] = [
				'id'    => (int) $form['id'],
				'title' => (string) $form['title'],
			];
		}

		return $result;
	}

	public static function get_form_notifications( int $form_id ): array {
		if ( ! class_exists( 'GFAPI' ) ) {
			return [];
		}

		$form = GFAPI::get_form( $form_id );
		if ( ! $form || empty( $form['notifications'] ) ) {
			return [];
		}

		$notifications = [];
		foreach ( $form['notifications'] as $notification ) {
			$notifications[] = [
				'id'   => (string) $notification['id'],
				'name' => (string) $notification['name'],
				'to'   => (string) ( $notification['to'] ?? '' ),
			];
		}

		return $notifications;
	}

	public static function get_form_fields( int $form_id, string $context_key = '' ): array {
		if ( ! class_exists( 'GFAPI' ) ) {
			return [];
		}

		$form = GFAPI::get_form( $form_id );
		if ( ! $form || empty( $form['fields'] ) ) {
			return [];
		}

		$fields             = [];
		$existing_field_ids = [];

		foreach ( $form['fields'] as $field ) {
			if ( $field->displayOnly ) {
				continue;
			}

			$id          = (string) $field->id;
			$label       = ! empty( $field->label ) ? $field->label : __( '(No Label)', 'gravity-notification-filter' );
			$admin_label = ! empty( $field->adminLabel ) ? $field->adminLabel : '';
			$type        = (string) $field->type;
			$visibility  = (string) $field->visibility;

			$fields[] = [
				'id'          => $id,
				'label'       => $label,
				'admin_label' => $admin_label,
				'type'        => $type,
				'visibility'  => $visibility,
				'is_admin'    => 'administrative' === $visibility,
			];

			$existing_field_ids[] = $id;
		}

		if ( ! empty( $context_key ) ) {
			self::auto_clean_missing_fields( $context_key, $existing_field_ids );
		}

		return $fields;
	}

	private static function auto_clean_missing_fields( string $context_key, array $existing_field_ids ): void {
		$stored_excluded = GNF_Storage::get_excluded_fields_for_context( $context_key );
		if ( empty( $stored_excluded ) ) {
			return;
		}

		$valid_excluded = array_intersect( $stored_excluded, $existing_field_ids );

		if ( count( $valid_excluded ) !== count( $stored_excluded ) ) {
			GNF_Storage::save_context_exclusions( $context_key, $valid_excluded );
		}
	}
}