<?php

defined( 'ABSPATH' ) || exit;

final class GNF_Forms {

	public static function get_all_forms(): array {
		if ( ! class_exists( 'GFAPI' ) ) {
			return [];
		}

		$forms = GFAPI::get_forms();

		if ( ! is_array( $forms ) ) {
			return [];
		}

		$result = [];

		foreach ( $forms as $form ) {
			if ( ! is_array( $form ) ) {
				continue;
			}

			$result[] = [
				'id'    => isset( $form['id'] )
					? (int) $form['id']
					: 0,
				'title' => isset( $form['title'] )
					? (string) $form['title']
					: '',
			];
		}

		return $result;
	}

	public static function get_form_notifications(
		int $form_id
	): array {
		if (
			$form_id <= 0
			|| ! class_exists( 'GFAPI' )
		) {
			return [];
		}

		$form = GFAPI::get_form( $form_id );

		if (
			! is_array( $form )
			|| empty( $form['notifications'] )
			|| ! is_array( $form['notifications'] )
		) {
			return [];
		}

		$notifications = [];

		foreach ( $form['notifications'] as $notification ) {
			if ( ! is_array( $notification ) ) {
				continue;
			}

			$id = isset( $notification['id'] )
				? GNF_Validator::sanitize_notification_id(
					$notification['id']
				)
				: '';

			if ( '' === $id ) {
				continue;
			}

			$notifications[] = [
				'id'   => $id,
				'name' => isset( $notification['name'] )
					? (string) $notification['name']
					: '',
				'to'   => isset( $notification['to'] )
					? (string) $notification['to']
					: '',
			];
		}

		return $notifications;
	}

	public static function get_form_fields(
		int $form_id,
		string $context_key = '',
		bool $auto_clean = false
	): array {
		if (
			$form_id <= 0
			|| ! class_exists( 'GFAPI' )
		) {
			return [];
		}

		$form = GFAPI::get_form( $form_id );

		if (
			! is_array( $form )
			|| empty( $form['fields'] )
			|| ! is_array( $form['fields'] )
		) {
			return [];
		}

		$fields = [];
		$existing_field_ids = [];

		foreach ( $form['fields'] as $field ) {
			if ( ! is_object( $field ) ) {
				continue;
			}

			if ( ! empty( $field->displayOnly ) ) {
				continue;
			}

			$parent_id = isset( $field->id )
				? GNF_Validator::sanitize_field_id(
					$field->id
				)
				: '';

			if ( '' === $parent_id ) {
				continue;
			}

			$label = ! empty( $field->label )
				? (string) $field->label
				: __(
					'(No Label)',
					'gravity-notification-filter'
				);

			$admin_label = ! empty( $field->adminLabel )
				? (string) $field->adminLabel
				: '';

			$type = ! empty( $field->type )
				? (string) $field->type
				: '';

			$visibility = ! empty( $field->visibility )
				? (string) $field->visibility
				: '';

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

			if (
				empty( $field->inputs )
				|| ! is_array( $field->inputs )
			) {
				continue;
			}

			foreach ( $field->inputs as $input ) {
				if ( ! is_array( $input ) ) {
					continue;
				}

				if ( ! empty( $input['isHidden'] ) ) {
					continue;
				}

				if ( ! isset( $input['id'] ) ) {
					continue;
				}

				$sub_id =
					GNF_Validator::sanitize_field_id(
						$input['id']
					);

				if ( '' === $sub_id ) {
					continue;
				}

				$sub_label = ! empty( $input['label'] )
					? sprintf(
						'%s (%s)',
						$label,
						(string) $input['label']
					)
					: $label;

				$fields[] = [
					'id'          => $sub_id,
					'label'       => $sub_label,
					'admin_label' => $admin_label,
					'type'        => sprintf(
						'%s [sub-field]',
						$type
					),
					'visibility'  => $visibility,
					'is_admin'    => 'administrative' === $visibility,
					'is_subfield' => true,
				];

				$existing_field_ids[] = $sub_id;
			}
		}

		if (
			$auto_clean
			&& '' !== $context_key
		) {
			self::auto_clean_missing_fields(
				$context_key,
				$existing_field_ids
			);
		}

		return $fields;
	}

	private static function auto_clean_missing_fields(
		string $context_key,
		array $existing_field_ids
	): void {
		$clean_context =
			GNF_Validator::sanitize_context_key(
				$context_key
			);

		if ( '' === $clean_context ) {
			return;
		}

		$stored_excluded =
			GNF_Storage::get_excluded_fields_for_context(
				$clean_context
			);

		if ( empty( $stored_excluded ) ) {
			return;
		}

		$valid_excluded = array_values(
			array_intersect(
				$stored_excluded,
				$existing_field_ids
			)
		);

		if (
			count( $valid_excluded )
			!== count( $stored_excluded )
		) {
			GNF_Storage::save_context_exclusions(
				$clean_context,
				$valid_excluded
			);
		}
	}
}