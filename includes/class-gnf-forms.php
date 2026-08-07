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

	public static function get_form_fields( int $form_id, string $context_key = '', bool $auto_clean = false ): array {
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

			$parent_id   = (string) $field->id;
			$label       = ! empty( $field->label ) ? $field->label : __( '(No Label)', 'gravity-notification-filter' );
			$admin_label = ! empty( $field->adminLabel ) ? $field->adminLabel : '';
			$type        = (string) $field->type;
			$visibility  = (string) $field->visibility;

			$fields[] = [
				'id'          => $parent_id,
				'label'       => $label,
				'admin_label' => $admin_label,
				'type'        => $type,
				'visibility'  => $visibility,
				'is_admin'    => 'administrative' === $visibility,
				'is_subfield' => false,
			];
			$existing_field_ids[] = $parent_id;

			// Sub-fields parsing (Complex Fields like Name, Address, etc.)
			if ( ! empty( $field->inputs ) && is_array( $field->inputs ) ) {
				foreach ( $field->inputs as $input ) {
					if ( ! empty( $input['isHidden'] ) ) {
						continue;
					}

					$sub_id    = (string) $input['id'];
					$sub_label = ! empty( $input['label'] ) ? sprintf( '%s (%s)', $label, $input['label'] ) : $label;

					$fields[] = [
						'id'          => $sub_id,
						'label'       => $sub_label,
						'admin_label' => $admin_label,
						'type'        => sprintf( '%s [sub-field]', $type ),
						'visibility'  => $visibility,
						'is_admin'    => 'administrative' === $visibility,
						'is_subfield' => true,
					];
					$existing_field_ids[] = $sub_id;
				}
			}
		}

		if ( $auto_clean && ! empty( $context_key ) ) {
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