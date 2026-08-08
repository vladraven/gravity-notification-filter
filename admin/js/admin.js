document.addEventListener('DOMContentLoaded', () => {
	const state = {
		currentFormId: null,
		currentContextKey: '',
		fields: [],
		notifications: [],
		globalExcludedIds: new Set(),
		contextExcludedIds: new Set(),
		effectiveExcludedIds: new Set(),
		activeFilter: 'all',
		searchQuery: ''
	};

	const formButtons = document.querySelectorAll('.gnf-form-btn');
	const fieldsBody = document.getElementById('gnf-fields-body');
	const previewList = document.getElementById('gnf-preview-list');
	const saveBtn = document.getElementById('gnf-save-btn');
	const saveNotice = document.getElementById('gnf-save-notice');
	const searchInput = document.getElementById('gnf-field-search');
	const filterTabs = document.querySelectorAll('.gnf-tab');
	const notificationSelect = document.getElementById('gnf-notification-select');
	const presetHideAdminBtn = document.getElementById('gnf-preset-hide-admin');
	const presetShowAllBtn = document.getElementById('gnf-preset-show-all');
	const exportBtn = document.getElementById('gnf-export-btn');
	const importBtn = document.getElementById('gnf-import-btn');
	const importTextarea = document.getElementById('gnf-import-textarea');
	const loadingIndicator = document.getElementById('gnf-loading');
	const workspace = document.getElementById('gnf-workspace');

	if (formButtons.length > 0) {
		const firstFormId = formButtons[0].getAttribute('data-form-id');

		loadFormFields(firstFormId);
	}

	formButtons.forEach(btn => {
		btn.addEventListener('click', () => {
			formButtons.forEach(b => b.classList.remove('active'));
			btn.classList.add('active');

			const formId = btn.getAttribute('data-form-id');

			if (notificationSelect) {
				notificationSelect.value = 'global';
			}

			loadFormFields(
				formId,
				String(formId)
			);
		});
	});

	if (notificationSelect) {
		notificationSelect.addEventListener('change', () => {
			const selectedVal = notificationSelect.value;

			state.currentContextKey =
				selectedVal === 'global'
					? state.currentFormId.toString()
					: `${state.currentFormId}_n_${selectedVal}`;

			loadFormFields(
				state.currentFormId,
				state.currentContextKey,
				false
			);
		});
	}

	function loadFormFields(
		formId,
		contextKey = '',
		shouldRepopulateDropdown = true
	) {
		state.currentFormId = parseInt(formId, 10);
		state.currentContextKey =
			contextKey || state.currentFormId.toString();

		if (loadingIndicator) {
			loadingIndicator.style.display = 'block';
		}

		if (workspace) {
			workspace.style.opacity = '0.5';
		}

		const formData = new FormData();

		formData.append(
			'action',
			'gnf_get_form_fields'
		);

		formData.append(
			'nonce',
			gnfAdmin.nonce
		);

		formData.append(
			'form_id',
			state.currentFormId
		);

		formData.append(
			'context_key',
			state.currentContextKey
		);

		fetch(
			gnfAdmin.ajaxUrl,
			{
				method: 'POST',
				body: formData
			}
		)
			.then(res => res.json())
			.then(response => {
				if (loadingIndicator) {
					loadingIndicator.style.display = 'none';
				}

				if (workspace) {
					workspace.style.opacity = '1';
				}

				if (!response.success) {
					alert(
						response.data?.message
						|| gnfAdmin.i18n.error
					);

					return;
				}

				state.fields =
					Array.isArray(response.data.fields)
						? response.data.fields
						: [];

				state.notifications =
					Array.isArray(response.data.notifications)
						? response.data.notifications
						: [];

				state.globalExcludedIds =
					new Set(
						normalizeIds(
							response.data.globalExcluded
						)
					);

				state.contextExcludedIds =
					new Set(
						normalizeIds(
							response.data.contextExcluded
						)
					);

				state.effectiveExcludedIds =
					new Set(
						normalizeIds(
							response.data.effectiveExcluded
						)
					);

				if (shouldRepopulateDropdown) {
					populateNotificationsDropdown();
				} else {
					setNotificationDropdownValue();
				}

				renderTable();
				renderPreview();
			})
			.catch(() => {
				if (loadingIndicator) {
					loadingIndicator.style.display = 'none';
				}

				if (workspace) {
					workspace.style.opacity = '1';
				}

				alert(gnfAdmin.i18n.error);
			});
	}

	function populateNotificationsDropdown() {
		if (!notificationSelect) {
			return;
		}

		notificationSelect.innerHTML =
			'<option value="global">Global (All Notifications for this form)</option>';

		state.notifications.forEach(notif => {
			const option =
				document.createElement('option');

			option.value = notif.id;

			option.textContent =
				`Notification: ${notif.name} (${notif.to || 'No recipient'})`;

			notificationSelect.appendChild(option);
		});

		setNotificationDropdownValue();
	}

	function setNotificationDropdownValue() {
		if (!notificationSelect) {
			return;
		}

		const prefix =
			`${state.currentFormId}_n_`;

		if (
			state.currentContextKey.startsWith(prefix)
		) {
			notificationSelect.value =
				state.currentContextKey.substring(
					prefix.length
				);
		} else {
			notificationSelect.value = 'global';
		}
	}

	function renderTable() {
		if (!fieldsBody) {
			return;
		}

		fieldsBody.innerHTML = '';

		const filteredFields =
			state.fields.filter(field => {
				const fieldId = String(field.id);

				const isExcluded =
					state.effectiveExcludedIds.has(fieldId);

				if (
					state.activeFilter === 'hidden'
					&& !isExcluded
				) {
					return false;
				}

				if (
					state.activeFilter === 'visible'
					&& isExcluded
				) {
					return false;
				}

				if (
					state.activeFilter === 'admin'
					&& !field.is_admin
				) {
					return false;
				}

				if (state.searchQuery) {
					const query =
						state.searchQuery.toLowerCase();

					return String(field.label || '')
						.toLowerCase()
						.includes(query)
						||
						String(field.admin_label || '')
							.toLowerCase()
							.includes(query)
						||
						fieldId.includes(query);
				}

				return true;
			});

		if (filteredFields.length === 0) {
			fieldsBody.innerHTML =
				'<tr><td colspan="5" style="text-align:center; padding: 20px;">'
				+ 'No fields match criteria.'
				+ '</td></tr>';

			return;
		}

		filteredFields.forEach(field => {
			const fieldId = String(field.id);

			const isContextExcluded =
				state.contextExcludedIds.has(fieldId);

			const isGlobalExcluded =
				state.globalExcludedIds.has(fieldId);

			const isEffectiveExcluded =
				state.effectiveExcludedIds.has(fieldId);

			const isGlobalContext =
				state.currentContextKey
				=== state.currentFormId.toString();

			const tr =
				document.createElement('tr');

			if (field.is_subfield) {
				tr.className =
					'gnf-subfield-row';
			}

			const checkboxCell =
				document.createElement('th');

			checkboxCell.className =
				'check-column';

			const checkbox =
				document.createElement('input');

			checkbox.type = 'checkbox';
			checkbox.className = 'gnf-field-cb';
			checkbox.dataset.fieldId = fieldId;

			/*
			 * In Global context the checkbox represents
			 * the global exclusion.
			 *
			 * In Notification context it represents only
			 * the notification-specific exclusion.
			 */
			checkbox.checked =
				isGlobalContext
					? isGlobalExcluded
					: isContextExcluded;

			checkboxCell.appendChild(checkbox);

			const labelCell =
				document.createElement('td');

			labelCell.innerHTML =
				(field.is_subfield ? '└─ ' : '')
				+ `<strong>${escapeHtml(field.label)}</strong>`;

			const adminLabelCell =
				document.createElement('td');

			adminLabelCell.textContent =
				field.admin_label || '';

			const typeCell =
				document.createElement('td');

			typeCell.innerHTML =
				`<code>${escapeHtml(field.type)}</code>`;

			const idCell =
				document.createElement('td');

			idCell.textContent = fieldId;

			tr.appendChild(checkboxCell);
			tr.appendChild(labelCell);
			tr.appendChild(adminLabelCell);
			tr.appendChild(typeCell);
			tr.appendChild(idCell);

			if (isGlobalExcluded) {
				tr.classList.add(
					'gnf-global-excluded'
				);
			}

			if (isContextExcluded) {
				tr.classList.add(
					'gnf-context-excluded'
				);
			}

			if (isEffectiveExcluded) {
				tr.classList.add(
					'gnf-effective-excluded'
				);
			}

			if (
				!isGlobalContext
				&& isGlobalExcluded
				&& !isContextExcluded
			) {
				tr.title =
					'This field is excluded globally.';
			}

			checkbox.addEventListener(
				'change',
				event => {
					const checked =
						event.target.checked;

					if (isGlobalContext) {
						if (checked) {
							state.globalExcludedIds.add(
								fieldId
							);
						} else {
							state.globalExcludedIds.delete(
								fieldId
							);
						}
					} else {
						if (checked) {
							state.contextExcludedIds.add(
								fieldId
							);
						} else {
							state.contextExcludedIds.delete(
								fieldId
							);
						}
					}

					recalculateEffectiveExclusions();

					renderTable();
					renderPreview();
				}
			);

			fieldsBody.appendChild(tr);
		});
	}

	function recalculateEffectiveExclusions() {
		const effective =
			new Set(
				state.globalExcludedIds
			);

		state.contextExcludedIds.forEach(id => {
			effective.add(id);
		});

		state.effectiveExcludedIds =
			effective;
	}

	function renderPreview() {
		if (!previewList) {
			return;
		}

		previewList.innerHTML = '';

		state.fields.forEach(field => {
			const fieldId = String(field.id);

			const isExcluded =
				state.effectiveExcludedIds.has(
					fieldId
				);

			const div =
				document.createElement('div');

			div.className =
				`gnf-preview-item ${
					isExcluded
						? 'excluded'
						: 'included'
				}`;

			const status =
				document.createElement('span');

			status.textContent =
				isExcluded ? '✗' : '✓';

			const label =
				document.createElement('span');

			label.style.fontWeight = '600';
			label.textContent =
				field.label || '';

			div.appendChild(status);
			div.appendChild(label);

			previewList.appendChild(div);
		});
	}

	if (presetHideAdminBtn) {
		presetHideAdminBtn.addEventListener(
			'click',
			() => {
				const isGlobalContext =
					state.currentContextKey
					=== state.currentFormId.toString();

				state.fields.forEach(field => {
					if (!field.is_admin) {
						return;
					}

					const fieldId =
						String(field.id);

					if (isGlobalContext) {
						state.globalExcludedIds.add(
							fieldId
						);
					} else {
						state.contextExcludedIds.add(
							fieldId
						);
					}
				});

				recalculateEffectiveExclusions();

				renderTable();
				renderPreview();
			}
		);
	}

	if (presetShowAllBtn) {
		presetShowAllBtn.addEventListener(
			'click',
			() => {
				const isGlobalContext =
					state.currentContextKey
					=== state.currentFormId.toString();

				if (isGlobalContext) {
					state.globalExcludedIds.clear();
				} else {
					state.contextExcludedIds.clear();
				}

				recalculateEffectiveExclusions();

				renderTable();
				renderPreview();
			}
		);
	}

	filterTabs.forEach(tab => {
		tab.addEventListener('click', () => {
			filterTabs.forEach(t =>
				t.classList.remove('active')
			);

			tab.classList.add('active');

			state.activeFilter =
				tab.getAttribute(
					'data-filter'
				);

			renderTable();
		});
	});

	if (searchInput) {
		searchInput.addEventListener(
			'input',
			e => {
				state.searchQuery =
					e.target.value.trim();

				renderTable();
			}
		);
	}

	if (saveBtn) {
		saveBtn.addEventListener(
			'click',
			() => {
				saveBtn.disabled = true;

				if (saveNotice) {
					saveNotice.textContent =
						'Saving...';
				}

				const formData =
					new FormData();

				formData.append(
					'action',
					'gnf_save_context_exclusions'
				);

				formData.append(
					'nonce',
					gnfAdmin.nonce
				);

				formData.append(
					'context_key',
					state.currentContextKey
				);

				const isGlobalContext =
					state.currentContextKey
					=== state.currentFormId.toString();

				const ids =
					isGlobalContext
						? state.globalExcludedIds
						: state.contextExcludedIds;

				if (ids.size === 0) {
					formData.append(
						'field_ids[]',
						''
					);
				} else {
					ids.forEach(id => {
						formData.append(
							'field_ids[]',
							id
						);
					});
				}

				fetch(
					gnfAdmin.ajaxUrl,
					{
						method: 'POST',
						body: formData
					}
				)
					.then(res => res.json())
					.then(response => {
						saveBtn.disabled = false;

						if (response.success) {
							if (saveNotice) {
								saveNotice.textContent =
									gnfAdmin.i18n.saved;

								setTimeout(
									() => {
										saveNotice.textContent = '';
									},
									3000
								);
							}

							/*
							 * Reload from server so the UI
							 * reflects the persisted state.
							 */
							loadFormFields(
								state.currentFormId,
								state.currentContextKey,
								false
							);
						} else {
							alert(
								response.data?.message
								|| gnfAdmin.i18n.error
							);

							if (saveNotice) {
								saveNotice.textContent = '';
							}
						}
					})
					.catch(() => {
						saveBtn.disabled = false;

						if (saveNotice) {
							saveNotice.textContent = '';
						}

						alert(
							gnfAdmin.i18n.error
						);
					});
			}
		);
	}

	if (exportBtn) {
		exportBtn.addEventListener(
			'click',
			() => {
				const formData =
					new FormData();

				formData.append(
					'action',
					'gnf_export_config'
				);

				formData.append(
					'nonce',
					gnfAdmin.nonce
				);

				fetch(
					gnfAdmin.ajaxUrl,
					{
						method: 'POST',
						body: formData
					}
				)
					.then(res => res.json())
					.then(response => {
						if (response.success) {
							const jsonStr =
								JSON.stringify(
									response.data.config,
									null,
									4
								);

							downloadJson(
								jsonStr,
								`gnf-config-${new Date().toISOString().slice(0, 10)}.json`
							);
						} else {
							alert(
								response.data?.message
								|| gnfAdmin.i18n.error
							);
						}
					})
					.catch(() => {
						alert(
							gnfAdmin.i18n.error
						);
					});
			}
		);
	}

	if (importBtn) {
		importBtn.addEventListener(
			'click',
			() => {
				if (!importTextarea) {
					return;
				}

				const jsonString =
					importTextarea.value.trim();

				if (!jsonString) {
					return;
				}

				if (jsonString.length > 1000000) {
					alert(
						'Import payload exceeds maximum size limit (1MB).'
					);

					return;
				}

				if (
					!confirm(
						gnfAdmin.i18n.confirmImport
					)
				) {
					return;
				}

				const formData =
					new FormData();

				formData.append(
					'action',
					'gnf_import_config'
				);

				formData.append(
					'nonce',
					gnfAdmin.nonce
				);

				formData.append(
					'json_data',
					jsonString
				);

				fetch(
					gnfAdmin.ajaxUrl,
					{
						method: 'POST',
						body: formData
					}
				)
					.then(res => res.json())
					.then(response => {
						if (response.success) {
							alert(
								response.data.message
							);

							importTextarea.value = '';

							loadFormFields(
								state.currentFormId,
								state.currentContextKey,
								false
							);
						} else {
							alert(
								response.data?.message
								|| gnfAdmin.i18n.error
							);
						}
					})
					.catch(() => {
						alert(
							gnfAdmin.i18n.error
						);
					});
			}
		);
	}

	function normalizeIds(ids) {
		if (!Array.isArray(ids)) {
			return [];
		}

		return ids.map(id => String(id));
	}

	function escapeHtml(str) {
		if (str === null || str === undefined) {
			return '';
		}

		return String(str).replace(
			/[&<>"']/g,
			function(m) {
				return {
					'&': '&amp;',
					'<': '&lt;',
					'>': '&gt;',
					'"': '&quot;',
					"'": '&#039;'
				}[m];
			}
		);
	}

	function downloadJson(text, filename) {
		const blob =
			new Blob(
				[text],
				{ type: 'application/json' }
			);

		const url =
			URL.createObjectURL(blob);

		const a =
			document.createElement('a');

		a.href = url;
		a.download = filename;

		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);

		URL.revokeObjectURL(url);
	}
});

const runTestsBtn = document.getElementById('gnf-run-tests-btn');
const testsStatus = document.getElementById('gnf-tests-status');
const testResults = document.getElementById('gnf-test-results');

if (runTestsBtn) {
	runTestsBtn.addEventListener('click', () => {
		runTestsBtn.disabled = true;

		if (testsStatus) {
			testsStatus.textContent =
				gnfAdmin.i18n.testsRunning || 'Running tests...';
		}

		if (testResults) {
			testResults.style.display = 'none';
			testResults.innerHTML = '';
		}

		const formData = new FormData();

		formData.append(
			'action',
			'gnf_run_tests'
		);

		formData.append(
			'nonce',
			gnfAdmin.nonce
		);

		fetch(
			gnfAdmin.ajaxUrl,
			{
				method: 'POST',
				body: formData
			}
		)
			.then(async response => {
				const data = await response.json();

				if (!response.ok && !data) {
					throw new Error(
						`HTTP ${response.status}`
					);
				}

				return data;
			})
			.then(response => {
				runTestsBtn.disabled = false;

				if (!response.success) {
					const message =
						response.data?.message
						|| gnfAdmin.i18n.error
						|| 'Test execution failed.';

					if (testsStatus) {
						testsStatus.textContent = message;
						testsStatus.style.color = '#d63638';
					}

					return;
				}

				const result =
					response.data;

				if (testsStatus) {
					testsStatus.textContent =
						result.success
							? (
								gnfAdmin.i18n.testsPassed
								|| 'All tests passed.'
							)
							: (
								gnfAdmin.i18n.testsFailed
								|| 'Some tests failed.'
							);

					testsStatus.style.color =
						result.success
							? '#008a20'
							: '#d63638';
				}

				renderTestResults(result);
			})
			.catch(error => {
				runTestsBtn.disabled = false;

				if (testsStatus) {
					testsStatus.textContent =
						error.message
						|| gnfAdmin.i18n.error
						|| 'Test execution failed.';

					testsStatus.style.color =
						'#d63638';
				}
			});
	});
}

function renderTestResults(result) {
	if (!testResults) {
		return;
	}

	testResults.innerHTML = '';

	const summary =
		document.createElement('div');

	summary.className =
		'gnf-test-summary';

	summary.innerHTML =
		`<strong>Tests:</strong> ${result.total || 0}`
		+ ` &nbsp; `
		+ `<strong>Passed:</strong> ${result.passed || 0}`
		+ ` &nbsp; `
		+ `<strong>Failed:</strong> ${result.failed || 0}`;

	testResults.appendChild(summary);

	const results =
		Array.isArray(result.results)
			? result.results
			: [];

	results.forEach(test => {
		const row =
			document.createElement('div');

		row.className =
			`gnf-test-result gnf-test-${test.status}`;

		const icon =
			document.createElement('span');

		icon.className =
			'gnf-test-icon';

		icon.textContent =
			test.status === 'pass'
				? '✓'
				: '✗';

		const name =
			document.createElement('strong');

		name.textContent =
			test.name || 'Unnamed test';

		row.appendChild(icon);
		row.appendChild(name);

		if (test.message) {
			const message =
				document.createElement('span');

			message.className =
				'gnf-test-message';

			message.textContent =
				test.message;

			row.appendChild(message);
		}

		testResults.appendChild(row);
	});

	testResults.style.display = 'block';
}