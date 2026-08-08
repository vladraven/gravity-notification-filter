<?php

defined( 'ABSPATH' ) || exit;

/**
 * @var array $forms
 */
?>

<div class="wrap gnf-wrap">

	<h1 class="gnf-title">
		<span class="dashicons dashicons-filter"></span>

		<?php
		esc_html_e(
			'Gravity Forms Notification Manager',
			'gravity-notification-filter'
		);
		?>

		<span class="gnf-badge">
			v<?php echo esc_html( GNF_VERSION ); ?>
		</span>
	</h1>

	<div class="gnf-container">

		<aside class="gnf-sidebar">

			<h2>
				<?php
				esc_html_e(
					'Forms',
					'gravity-notification-filter'
				);
				?>
			</h2>

			<?php if ( empty( $forms ) ) : ?>

				<p>
					<?php
					esc_html_e(
						'No Gravity Forms found.',
						'gravity-notification-filter'
					);
					?>
				</p>

			<?php else : ?>

				<ul class="gnf-form-list">

					<?php foreach ( $forms as $index => $form ) : ?>

						<li>

							<button
								type="button"
								class="gnf-form-btn <?php echo 0 === $index ? 'active' : ''; ?>"
								data-form-id="<?php echo esc_attr( $form['id'] ); ?>"
							>

								<span class="dashicons dashicons-feedback"></span>

								<span class="gnf-form-name">
									<?php echo esc_html( $form['title'] ); ?>
								</span>

								<span class="gnf-form-id">
									<?php
									printf(
										esc_html__(
											'ID: %d',
											'gravity-notification-filter'
										),
										(int) $form['id']
									);
									?>
								</span>

							</button>

						</li>

					<?php endforeach; ?>

				</ul>

			<?php endif; ?>

		</aside>

		<main class="gnf-main">

			<div
				id="gnf-loading"
				style="display:none;"
			>
				<span class="spinner is-active"></span>

				<?php
				esc_html_e(
					'Loading...',
					'gravity-notification-filter'
				);
				?>
			</div>

			<div id="gnf-workspace">

				<div class="gnf-context-bar">

					<label for="gnf-notification-select">
						<strong>
							<?php
							esc_html_e(
								'Notification:',
								'gravity-notification-filter'
							);
							?>
						</strong>
					</label>

					<select id="gnf-notification-select">

						<option value="global">
							<?php
							esc_html_e(
								'Global (All Notifications)',
								'gravity-notification-filter'
							);
							?>
						</option>

					</select>

				</div>

				<div class="gnf-workspace-header">

					<div class="gnf-search-box">

						<input
							type="search"
							id="gnf-field-search"
							placeholder="<?php esc_attr_e( 'Search fields...', 'gravity-notification-filter' ); ?>"
						/>

					</div>

					<div class="gnf-filter-tabs">

						<button
							type="button"
							class="gnf-tab active"
							data-filter="all"
						>
							<?php esc_html_e( 'All', 'gravity-notification-filter' ); ?>
						</button>

						<button
							type="button"
							class="gnf-tab"
							data-filter="hidden"
						>
							<?php esc_html_e( 'Hidden', 'gravity-notification-filter' ); ?>
						</button>

						<button
							type="button"
							class="gnf-tab"
							data-filter="visible"
						>
							<?php esc_html_e( 'Visible', 'gravity-notification-filter' ); ?>
						</button>

						<button
							type="button"
							class="gnf-tab"
							data-filter="admin"
						>
							<?php esc_html_e( 'Admin', 'gravity-notification-filter' ); ?>
						</button>

					</div>

				</div>

				<div class="gnf-presets-bar">

					<strong>
						<?php
						esc_html_e(
							'Presets:',
							'gravity-notification-filter'
						);
						?>
					</strong>

					<button
						type="button"
						id="gnf-preset-hide-admin"
						class="button"
					>
						<?php
						esc_html_e(
							'Hide All Admin Fields',
							'gravity-notification-filter'
						);
						?>
					</button>

					<button
						type="button"
						id="gnf-preset-show-all"
						class="button"
					>
						<?php
						esc_html_e(
							'Show All Fields',
							'gravity-notification-filter'
						);
						?>
					</button>

				</div>

				<div class="gnf-card">

					<h2>
						<?php
						esc_html_e(
							'Fields',
							'gravity-notification-filter'
						);
						?>
					</h2>

					<div class="gnf-table-wrapper">

						<table class="wp-list-table widefat fixed striped">

							<thead>

								<tr>

									<th
										class="check-column"
										style="width:50px;"
									>
										<?php
										esc_html_e(
											'Hide',
											'gravity-notification-filter'
										);
										?>
									</th>

									<th>
										<?php
										esc_html_e(
											'Label',
											'gravity-notification-filter'
										);
										?>
									</th>

									<th>
										<?php
										esc_html_e(
											'Admin Label',
											'gravity-notification-filter'
										);
										?>
									</th>

									<th>
										<?php
										esc_html_e(
											'Type',
											'gravity-notification-filter'
										);
										?>
									</th>

									<th style="width:80px;">
										<?php
										esc_html_e(
											'Field ID',
											'gravity-notification-filter'
										);
										?>
									</th>

								</tr>

							</thead>

							<tbody id="gnf-fields-body"></tbody>

						</table>

					</div>

					<div class="gnf-actions">

						<button
							type="button"
							id="gnf-save-btn"
							class="button button-primary button-hero"
						>
							<?php
							esc_html_e(
								'Save Changes',
								'gravity-notification-filter'
							);
							?>
						</button>

						<span
							id="gnf-save-notice"
							class="gnf-notice"
							aria-live="polite"
						></span>

					</div>

				</div>

				<div class="gnf-card">

					<h2>
						<?php
						esc_html_e(
							'Preview',
							'gravity-notification-filter'
						);
						?>
					</h2>

					<div
						id="gnf-preview-list"
						class="gnf-preview-list"
					></div>

				</div>

			</div>

		</main>

	</div>

	<div class="gnf-card">

		<h2>
			<?php
			esc_html_e(
				'Configuration',
				'gravity-notification-filter'
			);
			?>
		</h2>

		<div class="gnf-tools-grid">

			<div class="gnf-tool-box">

				<h3>
					<?php
					esc_html_e(
						'Export',
						'gravity-notification-filter'
					);
					?>
				</h3>

				<p>
					<?php
					esc_html_e(
						'Export the current configuration as JSON.',
						'gravity-notification-filter'
					);
					?>
				</p>

				<button
					type="button"
					id="gnf-export-btn"
					class="button"
				>
					<?php
					esc_html_e(
						'Export JSON',
						'gravity-notification-filter'
					);
					?>
				</button>

			</div>

			<div class="gnf-tool-box">

				<h3>
					<?php
					esc_html_e(
						'Import',
						'gravity-notification-filter'
					);
					?>
				</h3>

				<textarea
					id="gnf-import-textarea"
					rows="5"
					placeholder="<?php esc_attr_e( 'Paste JSON configuration here...', 'gravity-notification-filter' ); ?>"
				></textarea>

				<p>

					<button
						type="button"
						id="gnf-import-btn"
						class="button"
					>
						<?php
						esc_html_e(
							'Import JSON',
							'gravity-notification-filter'
						);
						?>
					</button>

				</p>

			</div>

		</div>

	</div>

	<div class="gnf-card gnf-tests-card">

		<h2>
			<?php
			esc_html_e(
				'Diagnostics',
				'gravity-notification-filter'
			);
			?>
		</h2>

		<p>
			<?php
			esc_html_e(
				'Run the plugin self-tests directly from WordPress. No Composer or external test runner is required.',
				'gravity-notification-filter'
			);
			?>
		</p>

		<button
			type="button"
			id="gnf-run-tests-btn"
			class="button"
		>
			<?php
			esc_html_e(
				'Test Plugin',
				'gravity-notification-filter'
			);
			?>
		</button>

		<span
			id="gnf-tests-status"
			class="gnf-notice"
			aria-live="polite"
		></span>

		<div
			id="gnf-test-results"
			class="gnf-test-results"
			style="display:none;"
		></div>

	</div>

</div>