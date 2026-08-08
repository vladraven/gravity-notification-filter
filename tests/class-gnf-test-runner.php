<?php

defined( 'ABSPATH' ) || exit;

final class GNF_Test_Runner {

	private array $results = [];

	private int $passed = 0;

	private int $failed = 0;

	private mixed $original_option = null;

	private bool $option_exists = false;

	public function run(): array {
		$this->results = [];
		$this->passed  = 0;
		$this->failed  = 0;

		$this->backup_storage();

		try {
			$this->run_validator_tests();
			$this->run_storage_tests();
			$this->run_forms_tests();
			$this->run_engine_tests();
		} finally {
			$this->restore_storage();
		}

		return [
			'passed'  => $this->passed,
			'failed'  => $this->failed,
			'total'   => $this->passed + $this->failed,
			'success' => 0 === $this->failed,
			'results' => $this->results,
		];
	}

	private function run_validator_tests(): void {
		$this->assert_same(
			'3',
			GNF_Validator::sanitize_field_id( '3' ),
			'Validator accepts simple field ID'
		);

		$this->assert_same(
			'3.2',
			GNF_Validator::sanitize_field_id( '3.2' ),
			'Validator accepts decimal sub-field ID'
		);

		$this->assert_same(
			'',
			GNF_Validator::sanitize_field_id( '3abc' ),
			'Validator rejects invalid field ID'
		);

		$this->assert_same(
			'',
			GNF_Validator::sanitize_field_id(
				[ 'invalid' ]
			),
			'Validator rejects array field ID'
		);

		$this->assert_same(
			'abc123_X-1',
			GNF_Validator::sanitize_notification_id(
				'abc123_X-1'
			),
			'Validator accepts notification ID'
		);

		$this->assert_same(
			'',
			GNF_Validator::sanitize_notification_id(
				'abc 123'
			),
			'Validator rejects invalid notification ID'
		);

		$this->assert_same(
			'',
			GNF_Validator::sanitize_notification_id(
				[ 'invalid' ]
			),
			'Validator rejects array notification ID'
		);

		$this->assert_same(
			'12',
			GNF_Validator::sanitize_context_key(
				'12'
			),
			'Validator accepts global context'
		);

		$this->assert_same(
			'12_n_abc123',
			GNF_Validator::sanitize_context_key(
				'12_n_abc123'
			),
			'Validator accepts notification context'
		);

		$this->assert_same(
			'',
			GNF_Validator::sanitize_context_key(
				'12_bad context'
			),
			'Validator rejects invalid context'
		);

		$this->assert_same(
			'',
			GNF_Validator::sanitize_context_key(
				[ 'invalid' ]
			),
			'Validator rejects array context'
		);

		$this->assert_same(
			[
				'12' => [
					'3',
					'3.1',
				],
			],
			GNF_Validator::sanitize_exclusions_array(
				[
					'12' => [
						'3',
						'3.1',
						'3',
					],
				]
			),
			'Validator sanitizes exclusion array'
		);

		$this->assert_same(
			[],
			GNF_Validator::sanitize_exclusions_array(
				'not-an-array'
			),
			'Validator rejects invalid exclusion structure'
		);

		$this->assert_same(
			[
				'12' => [
					'3',
					'3.1',
				],
			],
			GNF_Validator::validate_json_import(
				'{"12":["3","3.1"]}'
			),
			'Validator accepts valid JSON import'
		);

		$this->assert_true(
			is_wp_error(
				GNF_Validator::validate_json_import(
					'{invalid'
				)
			),
			'Validator rejects malformed JSON'
		);
	}

	private function run_storage_tests(): void {
		$this->assert_same(
			'12',
			GNF_Storage::make_context_key( 12 ),
			'Storage builds global context key'
		);

		$this->assert_same(
			'12_n_abc123',
			GNF_Storage::make_context_key(
				12,
				'abc123'
			),
			'Storage builds notification context key'
		);

		$this->assert_same(
			'',
			GNF_Storage::make_context_key( 0 ),
			'Storage rejects invalid form ID'
		);

		$key = GNF_Storage::make_context_key( 999991 );

		$this->assert_true(
			GNF_Storage::save_context_exclusions(
				$key,
				[
					'3',
					'3.1',
					'3.2',
					'3.2',
				]
			),
			'Storage saves global exclusions'
		);

		$this->assert_same(
			[
				'3',
				'3.1',
				'3.2',
			],
			GNF_Storage::get_excluded_fields_for_context(
				$key
			),
			'Storage reads global exclusions'
		);

		$notification_key =
			GNF_Storage::make_context_key(
				999992,
				'abc123'
			);

		$this->assert_true(
			GNF_Storage::save_context_exclusions(
				$notification_key,
				[
					'4',
					'4.1',
				]
			),
			'Storage saves notification exclusions'
		);

		$this->assert_same(
			[
				'4',
				'4.1',
			],
			GNF_Storage::get_excluded_fields_for_context(
				$notification_key
			),
			'Storage reads notification exclusions'
		);

		$form_id = 999993;

		$global_key =
			GNF_Storage::make_context_key(
				$form_id
			);

		$notification_key =
			GNF_Storage::make_context_key(
				$form_id,
				'abc123'
			);

		GNF_Storage::save_context_exclusions(
			$global_key,
			[
				'3',
				'3.1',
			]
		);

		GNF_Storage::save_context_exclusions(
			$notification_key,
			[
				'4',
				'4.1',
			]
		);

		$this->assert_same(
			[
				'3',
				'3.1',
				'4',
				'4.1',
			],
			GNF_Storage::get_effective_excluded_fields(
				$form_id,
				'abc123'
			),
			'Storage combines global and notification exclusions'
		);

		$this->assert_true(
			GNF_Storage::is_field_excluded(
				$form_id,
				'3',
				'abc123'
			),
			'Storage detects globally excluded field'
		);

		$this->assert_true(
			GNF_Storage::is_field_excluded(
				$form_id,
				'4',
				'abc123'
			),
			'Storage detects notification excluded field'
		);

		$this->assert_true(
			! GNF_Storage::is_field_excluded(
				$form_id,
				'9',
				'abc123'
			),
			'Storage detects included field'
		);

		GNF_Storage::save_context_exclusions(
			$global_key,
			[]
		);

		GNF_Storage::save_context_exclusions(
			$notification_key,
			[]
		);

		$this->assert_same(
			[],
			GNF_Storage::get_excluded_fields_for_context(
				$global_key
			),
			'Storage clears context exclusions'
		);
	}

	private function run_forms_tests(): void {
		if ( ! class_exists( 'GFAPI' ) ) {
			$this->skip(
				'Forms tests',
				'Gravity Forms API is not available.'
			);

			return;
		}

		$forms = GNF_Forms::get_all_forms();

		$this->assert_true(
			is_array( $forms ),
			'Forms returns forms array'
		);

		foreach ( $forms as $form ) {
			$this->assert_true(
				isset(
					$form['id'],
					$form['title']
				),
				'Forms entries contain ID and title'
			);

			$this->assert_true(
				is_int( $form['id'] ),
				'Forms ID is integer'
			);

			break;
		}

		$this->assert_same(
			[],
			GNF_Forms::get_form_notifications( 0 ),
			'Forms rejects invalid form ID'
		);

		$this->assert_same(
			[],
			GNF_Forms::get_form_fields( 0 ),
			'Forms rejects invalid field form ID'
		);

		if ( empty( $forms ) ) {
			$this->skip(
				'Existing form integration tests',
				'No Gravity Forms forms exist.'
			);

			return;
		}

		$form_id = (int) $forms[0]['id'];

		$fields =
			GNF_Forms::get_form_fields(
				$form_id
			);

		$this->assert_true(
			is_array( $fields ),
			'Forms returns field array'
		);

		foreach ( $fields as $field ) {
			$this->assert_true(
				isset(
					$field['id'],
					$field['label'],
					$field['admin_label'],
					$field['type'],
					$field['visibility'],
					$field['is_admin'],
					$field['is_subfield']
				),
				'Field entries contain required properties'
			);

			$this->assert_true(
				'' !== GNF_Validator::sanitize_field_id(
					$field['id']
				),
				'Field entries contain valid field IDs'
			);
		}

		$notifications =
			GNF_Forms::get_form_notifications(
				$form_id
			);

		$this->assert_true(
			is_array( $notifications ),
			'Forms returns notification array'
		);

		foreach ( $notifications as $notification ) {
			$this->assert_true(
				isset(
					$notification['id'],
					$notification['name'],
					$notification['to']
				),
				'Notification entries contain required properties'
			);

			$this->assert_true(
				'' !== GNF_Validator::sanitize_notification_id(
					$notification['id']
				),
				'Notification entries contain valid IDs'
			);
		}
	}

	private function run_engine_tests(): void {
		$engine = GNF_Engine::instance();

		$this->assert_same(
			'original value',
			$engine->filter_all_fields_merge_tag(
				'original value',
				'other_tag',
				'',
				$this->make_test_field( 999996, '3' ),
				'',
				''
			),
			'Engine leaves non-all-fields merge tag unchanged'
		);

		$this->assert_same(
			'original value',
			$engine->filter_all_fields_merge_tag(
				'original value',
				'all_fields',
				'',
				new stdClass(),
				'',
				''
			),
			'Engine leaves invalid field object unchanged'
		);

		$this->assert_same(
			'original value',
			$engine->filter_all_fields_merge_tag(
				'original value',
				'all_fields',
				'',
				$this->make_test_field( 0, '3' ),
				'',
				''
			),
			'Engine leaves invalid form ID unchanged'
		);

		$form_id = 999996;

		$global_key =
			GNF_Storage::make_context_key(
				$form_id
			);

		$notification_a_key =
			GNF_Storage::make_context_key(
				$form_id,
				'notification_a'
			);

		$notification_b_key =
			GNF_Storage::make_context_key(
				$form_id,
				'notification_b'
			);

		GNF_Storage::save_context_exclusions(
			$global_key,
			[
				'3',
			]
		);

		GNF_Storage::save_context_exclusions(
			$notification_a_key,
			[
				'4',
			]
		);

		GNF_Storage::save_context_exclusions(
			$notification_b_key,
			[
				'5',
			]
		);

		$engine->reset();

		$this->assert_same(
			'',
			$engine->filter_all_fields_merge_tag(
				'original value',
				'all_fields',
				'',
				$this->make_test_field(
					$form_id,
					'3'
				),
				'',
				''
			),
			'Engine excludes globally excluded field'
		);

		$this->assert_same(
			'original value',
			$engine->filter_all_fields_merge_tag(
				'original value',
				'all_fields',
				'',
				$this->make_test_field(
					$form_id,
					'4'
				),
				'',
				''
			),
			'Engine does not apply notification exclusion without notification context'
		);

		$engine->track_notification(
			[
				'id' => 'notification_a',
			],
			[],
			[]
		);

		$this->assert_same(
			'',
			$engine->filter_all_fields_merge_tag(
				'original value',
				'all_fields',
				'',
				$this->make_test_field(
					$form_id,
					'4'
				),
				'',
				''
			),
			'Engine excludes notification-specific field'
		);

		$this->assert_same(
			'',
			$engine->filter_all_fields_merge_tag(
				'original value',
				'all_fields',
				'',
				$this->make_test_field(
					$form_id,
					'3'
				),
				'',
				''
			),
			'Engine keeps global exclusion inside notification context'
		);

		$this->assert_same(
			'original value',
			$engine->filter_all_fields_merge_tag(
				'original value',
				'all_fields',
				'',
				$this->make_test_field(
					$form_id,
					'5'
				),
				'',
				''
			),
			'Engine does not apply another notification exclusion'
		);

		$engine->track_notification(
			[
				'id' => 'notification_b',
			],
			[],
			[]
		);

		$this->assert_same(
			'',
			$engine->filter_all_fields_merge_tag(
				'original value',
				'all_fields',
				'',
				$this->make_test_field(
					$form_id,
					'5'
				),
				'',
				''
			),
			'Engine applies second notification exclusion'
		);

		$this->assert_same(
			'original value',
			$engine->filter_all_fields_merge_tag(
				'original value',
				'all_fields',
				'',
				$this->make_test_field(
					$form_id,
					'4'
				),
				'',
				''
			),
			'Engine isolates notification contexts'
		);

		$engine->reset();

		$this->assert_same(
			'original value',
			$engine->filter_all_fields_merge_tag(
				'original value',
				'all_fields',
				'',
				$this->make_test_field(
					$form_id,
					'4'
				),
				'',
				''
			),
			'Engine reset clears notification context'
		);

		GNF_Storage::save_context_exclusions(
			$global_key,
			[]
		);

		GNF_Storage::save_context_exclusions(
			$notification_a_key,
			[]
		);

		GNF_Storage::save_context_exclusions(
			$notification_b_key,
			[]
		);
	}

	private function make_test_field(
		int $form_id,
		string $field_id
	): object {
		$field = new stdClass();

		$field->formId = $form_id;
		$field->id = $field_id;

		return $field;
	}

	private function backup_storage(): void {
		$this->option_exists = false;
		$this->original_option = null;

		$value = get_option(
			'gnf_excluded_fields',
			null
		);

		if ( null !== $value ) {
			$this->option_exists = true;
			$this->original_option = $value;
		}
	}

	private function restore_storage(): void {
		if ( $this->option_exists ) {
			update_option(
				'gnf_excluded_fields',
				$this->original_option
			);

			return;
		}

		delete_option(
			'gnf_excluded_fields'
		);
	}

	private function assert_same(
		mixed $expected,
		mixed $actual,
		string $name
	): void {
		if ( $expected === $actual ) {
			$this->passed++;

			$this->results[] = [
				'name'    => $name,
				'status'  => 'pass',
				'message' => '',
			];

			return;
		}

		$this->fail(
			$name,
			sprintf(
				'Expected %s, got %s.',
				var_export(
					$expected,
					true
				),
				var_export(
					$actual,
					true
				)
			)
		);
	}

	private function assert_true(
		bool $condition,
		string $name
	): void {
		if ( $condition ) {
			$this->passed++;

			$this->results[] = [
				'name'    => $name,
				'status'  => 'pass',
				'message' => '',
			];

			return;
		}

		$this->fail(
			$name,
			'Assertion failed.'
		);
	}

	private function skip(
		string $name,
		string $message
	): void {
		$this->results[] = [
			'name'    => $name,
			'status'  => 'skip',
			'message' => $message,
		];
	}

	private function fail(
		string $name,
		string $message
	): void {
		$this->failed++;

		$this->results[] = [
			'name'    => $name,
			'status'  => 'fail',
			'message' => $message,
		];
	}
}