document.addEventListener('DOMContentLoaded', () => {
	const state = {
		currentFormId: null,
		currentContextKey: '',
		fields: [],
		notifications: [],
		excludedIds: new Set(),
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
			notificationSelect.value = 'global';
			loadFormFields(formId, formId.toString());
		});
	});

	notificationSelect.addEventListener('change', () => {
		const selectedVal = notificationSelect.value;
		state.currentContextKey = selectedVal === 'global' ? state.currentFormId.toString() : `${state.currentFormId}_n_${selectedVal}`;
		loadFormFields(state.currentFormId, state.currentContextKey, false);
	});

	function loadFormFields(formId, contextKey = '', shouldRepopulatedDropdown = true) {
		state.currentFormId = parseInt(formId, 10);
		state.currentContextKey = contextKey || state.currentFormId.toString();

		loadingIndicator.style.display = 'block';
		workspace.style.opacity = '0.5';

		const formData = new FormData();
		formData.append('action', 'gnf_get_form_fields');
		formData.append('nonce', gnfAdmin.nonce);
		formData.append('form_id', state.currentFormId);
		formData.append('context_key', state.currentContextKey);

		fetch(gnfAdmin.ajaxUrl, {
			method: 'POST',
			body: formData
		})
		.then(res => res.json())
		.then(response => {
			loadingIndicator.style.display = 'none';
			workspace.style.opacity = '1';

			if (response.success) {
				state.fields = response.data.fields;
				state.notifications = response.data.notifications;
				state.excludedIds = new Set(response.data.excluded.map(String));

				if (shouldRepopulatedDropdown) {
					populateNotificationsDropdown();
				}
				renderTable();
				renderPreview();
			} else {
				alert(response.data.message || gnfAdmin.i18n.error);
			}
		})
		.catch(() => {
			loadingIndicator.style.display = 'none';
			workspace.style.opacity = '1';
			alert(gnfAdmin.i18n.error);
		});
	}

	function populateNotificationsDropdown() {
		notificationSelect.innerHTML = `<option value="global">Global (All Notifications for this form)</option>`;

		state.notifications.forEach(notif => {
			const option = document.createElement('option');
			option.value = notif.id;
			option.textContent = `Notification: ${notif.name} (${notif.to || 'No recipient'})`;
			notificationSelect.appendChild(option);
		});

		notificationSelect.value = 'global';
	}

	function renderTable() {
		fieldsBody.innerHTML = '';

		const filteredFields = state.fields.filter(field => {
			const isExcluded = state.excludedIds.has(String(field.id));
			
			if (state.activeFilter === 'hidden' && !isExcluded) return false;
			if (state.activeFilter === 'visible' && isExcluded) return false;
			if (state.activeFilter === 'admin' && !field.is_admin) return false;

			if (state.searchQuery) {
				const query = state.searchQuery.toLowerCase();
				return field.label.toLowerCase().includes(query) || 
					   field.admin_label.toLowerCase().includes(query) || 
					   field.id.toString().includes(query);
			}

			return true;
		});

		if (filteredFields.length === 0) {
			fieldsBody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding: 20px;">No fields match criteria.</td></tr>`;
			return;
		}

		filteredFields.forEach(field => {
			const isChecked = state.excludedIds.has(String(field.id));
			const tr = document.createElement('tr');

			tr.innerHTML = `
				<th class="check-column">
					<input type="checkbox" class="gnf-field-cb" data-field-id="${field.id}" ${isChecked ? 'checked' : ''} />
				</th>
				<td><strong>${escapeHtml(field.label)}</strong></td>
				<td>${escapeHtml(field.admin_label)}</td>
				<td><code>${escapeHtml(field.type)}</code></td>
				<td>${field.id}</td>
			`;

			fieldsBody.appendChild(tr);
		});

		document.querySelectorAll('.gnf-field-cb').forEach(cb => {
			cb.addEventListener('change', (e) => {
				const fieldId = String(e.target.getAttribute('data-field-id'));
				if (e.target.checked) {
					state.excludedIds.add(fieldId);
				} else {
					state.excludedIds.delete(fieldId);
				}
				renderPreview();
			});
		});
	}

	function renderPreview() {
		previewList.innerHTML = '';

		state.fields.forEach(field => {
			const isExcluded = state.excludedIds.has(String(field.id));
			const div = document.createElement('div');
			div.className = `gnf-preview-item ${isExcluded ? 'excluded' : 'included'}`;

			div.innerHTML = `
				<span>${isExcluded ? '✗' : '✓'}</span>
				<span style="font-weight:600;">${escapeHtml(field.label)}</span>
			`;

			previewList.appendChild(div);
		});
	}

	presetHideAdminBtn.addEventListener('click', () => {
		state.fields.forEach(field => {
			if (field.is_admin) {
				state.excludedIds.add(String(field.id));
			}
		});
		renderTable();
		renderPreview();
	});

	presetShowAllBtn.addEventListener('click', () => {
		state.excludedIds.clear();
		renderTable();
		renderPreview();
	});

	filterTabs.forEach(tab => {
		tab.addEventListener('click', () => {
			filterTabs.forEach(t => t.classList.remove('active'));
			tab.classList.add('active');
			state.activeFilter = tab.getAttribute('data-filter');
			renderTable();
		});
	});

	searchInput.addEventListener('input', (e) => {
		state.searchQuery = e.target.value.trim();
		renderTable();
	});

	saveBtn.addEventListener('click', () => {
		saveBtn.disabled = true;
		saveNotice.textContent = 'Saving...';

		const formData = new FormData();
		formData.append('action', 'gnf_save_context_exclusions');
		formData.append('nonce', gnfAdmin.nonce);
		formData.append('context_key', state.currentContextKey);

		if (state.excludedIds.size === 0) {
			formData.append('field_ids[]', '');
		} else {
			Array.from(state.excludedIds).forEach(id => {
				formData.append('field_ids[]', id);
			});
		}

		fetch(gnfAdmin.ajaxUrl, {
			method: 'POST',
			body: formData
		})
		.then(res => res.json())
		.then(response => {
			saveBtn.disabled = false;
			if (response.success) {
				saveNotice.textContent = gnfAdmin.i18n.saved;
				setTimeout(() => { saveNotice.textContent = ''; }, 3000);
			} else {
				alert(response.data.message || gnfAdmin.i18n.error);
				saveNotice.textContent = '';
			}
		})
		.catch(() => {
			saveBtn.disabled = false;
			saveNotice.textContent = '';
			alert(gnfAdmin.i18n.error);
		});
	});

	exportBtn.addEventListener('click', () => {
		const formData = new FormData();
		formData.append('action', 'gnf_export_config');
		formData.append('nonce', gnfAdmin.nonce);

		fetch(gnfAdmin.ajaxUrl, {
			method: 'POST',
			body: formData
		})
		.then(res => res.json())
		.then(response => {
			if (response.success) {
				const jsonStr = JSON.stringify(response.data.config, null, 4);
				downloadJson(jsonStr, `gnf-config-${new Date().toISOString().slice(0,10)}.json`);
			}
		});
	});

	importBtn.addEventListener('click', () => {
		const jsonString = importTextarea.value.trim();
		if (!jsonString) return;

		if (jsonString.length > 1000000) {
			alert('Import payload exceeds maximum size limit (1MB).');
			return;
		}

		if (!confirm(gnfAdmin.i18n.confirmImport)) return;

		const formData = new FormData();
		formData.append('action', 'gnf_import_config');
		formData.append('nonce', gnfAdmin.nonce);
		formData.append('json_data', jsonString);

		fetch(gnfAdmin.ajaxUrl, {
			method: 'POST',
			body: formData
		})
		.then(res => res.json())
		.then(response => {
			if (response.success) {
				alert(response.data.message);
				importTextarea.value = '';
				loadFormFields(state.currentFormId, state.currentContextKey, false);
			} else {
				alert(response.data.message);
			}
		});
	});

	function escapeHtml(str) {
		return str.replace(/[&<>"']/g, function(m) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
		});
	}

	function downloadJson(text, filename) {
		const blob = new Blob([text], { type: 'application/json' });
		const url = URL.createObjectURL(blob);
		const a = document.createElement('a');
		a.href = url;
		a.download = filename;
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
		URL.revokeObjectURL(url);
	}
});