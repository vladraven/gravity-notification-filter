document.addEventListener('DOMContentLoaded', () => {
	const state = {
		currentFormId: null,
		fields: [],
		excludedIds: new Set(),
		activeFilter: 'all',
		searchQuery: ''
	};

	// DOM Elements
	const formButtons = document.querySelectorAll('.gnf-form-btn');
	const fieldsBody = document.getElementById('gnf-fields-body');
	const previewList = document.getElementById('gnf-preview-list');
	const saveBtn = document.getElementById('gnf-save-btn');
	const saveNotice = document.getElementById('gnf-save-notice');
	const searchInput = document.getElementById('gnf-field-search');
	const filterTabs = document.querySelectorAll('.gnf-tab');
	const exportBtn = document.getElementById('gnf-export-btn');
	const importBtn = document.getElementById('gnf-import-btn');
	const importTextarea = document.getElementById('gnf-import-textarea');
	const loadingIndicator = document.getElementById('gnf-loading');
	const workspace = document.getElementById('gnf-workspace');

	// Init First Form
	if (formButtons.length > 0) {
		const firstFormId = formButtons[0].getAttribute('data-form-id');
		loadFormFields(firstFormId);
	}

	// Switch Form
	formButtons.forEach(btn => {
		btn.addEventListener('click', (e) => {
			formButtons.forEach(b => b.classList.remove('active'));
			btn.classList.add('active');
			const formId = btn.getAttribute('data-form-id');
			loadFormFields(formId);
		});
	});

	// Load Form Fields via AJAX
	function loadFormFields(formId) {
		state.currentFormId = parseInt(formId, 10);
		loadingIndicator.style.display = 'block';
		workspace.style.opacity = '0.5';

		const formData = new FormData();
		formData.append('action', 'gnf_get_form_fields');
		formData.append('nonce', gnfAdmin.nonce);
		formData.append('form_id', state.currentFormId);

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
				state.excludedIds = new Set(response.data.excluded.map(Number));
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

	// Render Table Rows
	function renderTable() {
		fieldsBody.innerHTML = '';

		const filteredFields = state.fields.filter(field => {
			const isExcluded = state.excludedIds.has(field.id);
			
			// Filter Tab Logic
			if (state.activeFilter === 'hidden' && !isExcluded) return false;
			if (state.activeFilter === 'visible' && isExcluded) return false;
			if (state.activeFilter === 'admin' && !field.is_admin) return false;

			// Search Query Logic
			if (state.searchQuery) {
				const query = state.searchQuery.toLowerCase();
				const matchLabel = field.label.toLowerCase().includes(query);
				const matchAdminLabel = field.admin_label.toLowerCase().includes(query);
				const matchId = field.id.toString().includes(query);
				return matchLabel || matchAdminLabel || matchId;
			}

			return true;
		});

		if (filteredFields.length === 0) {
			fieldsBody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding: 20px;">No fields match criteria.</td></tr>`;
			return;
		}

		filteredFields.forEach(field => {
			const isChecked = state.excludedIds.has(field.id);
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

		// Attach Checkbox Listeners
		document.querySelectorAll('.gnf-field-cb').forEach(cb => {
			cb.addEventListener('change', (e) => {
				const fieldId = parseInt(e.target.getAttribute('data-field-id'), 10);
				if (e.target.checked) {
					state.excludedIds.add(fieldId);
				} else {
					state.excludedIds.delete(fieldId);
				}
				renderPreview();
			});
		});
	}

	// Render Live Preview
	function renderPreview() {
		previewList.innerHTML = '';

		state.fields.forEach(field => {
			const isExcluded = state.excludedIds.has(field.id);
			const div = document.createElement('div');
			div.className = `gnf-preview-item ${isExcluded ? 'excluded' : 'included'}`;

			div.innerHTML = `
				<span>${isExcluded ? '✗' : '✓'}</span>
				<span style="font-weight:600;">${escapeHtml(field.label)}</span>
			`;

			previewList.appendChild(div);
		});
	}

	// Filter Tabs Event
	filterTabs.forEach(tab => {
		tab.addEventListener('click', () => {
			filterTabs.forEach(t => t.classList.remove('active'));
			tab.classList.add('active');
			state.activeFilter = tab.getAttribute('data-filter');
			renderTable();
		});
	});

	// Search Event
	searchInput.addEventListener('input', (e) => {
		state.searchQuery = e.target.value.trim();
		renderTable();
	});

	// Save Settings via AJAX
	saveBtn.addEventListener('click', () => {
		saveBtn.disabled = true;
		saveNotice.textContent = 'Saving...';

		const formData = new FormData();
		formData.append('action', 'gnf_save_form_exclusions');
		formData.append('nonce', gnfAdmin.nonce);
		formData.append('form_id', state.currentFormId);

		Array.from(state.excludedIds).forEach(id => {
			formData.append('field_ids[]', id);
		});

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

	// Export Configuration JSON
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

	// Import Configuration JSON
	importBtn.addEventListener('click', () => {
		const jsonString = importTextarea.value.trim();
		if (!jsonString) return;

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
				loadFormFields(state.currentFormId);
			} else {
				alert(response.data.message);
			}
		});
	});

	// Helper Utils
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