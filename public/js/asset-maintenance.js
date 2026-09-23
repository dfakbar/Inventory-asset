(function () {
    function toggleMaintenanceForm(mode, data = null) {
        const card = document.getElementById('inlineMaintenanceCard');
        const form = document.getElementById('inlineMaintenanceForm');
        const title = document.getElementById('maintenanceFormTitle');
        const methodField = document.getElementById('maintenanceMethodField');

        if (!card || !form) return;

        if (card.style.display === 'none' || mode === 'edit') {
            card.style.display = 'block';
            card.scrollIntoView({ behavior: 'smooth' });

            if (mode === 'edit' && data) {
                title.textContent = 'Edit Catatan Maintenance';
                form.action = data.url || form.dataset.storeUrl;
                methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';

                document.getElementById('inline_action_type').value = data.action_type;
                document.getElementById('inline_component_name').value = data.component_name;
                document.getElementById('inline_previous_spec').value = data.previous_spec || '';
                document.getElementById('inline_new_spec').value = data.new_spec;
                document.getElementById('inline_maintenance_date').value = data.maintenance_date;
                document.getElementById('inline_cost').value = data.cost || '';
                document.getElementById('inline_notes').value = data.notes || '';
            } else {
                title.textContent = 'Tambah Catatan Maintenance';
                form.action = form.dataset.storeUrl || form.getAttribute('action');
                methodField.innerHTML = '';
                form.reset();
                document.getElementById('inline_maintenance_date').value = new Date().toISOString().slice(0, 10);
            }
        } else {
            card.style.display = 'none';
        }
    }

    function editMaintenance(btn) {
        const data = {
            url: btn.dataset.url,
            action_type: btn.dataset.actionType,
            component_name: btn.dataset.componentName,
            previous_spec: btn.dataset.previousSpec,
            new_spec: btn.dataset.newSpec,
            maintenance_date: btn.dataset.maintenanceDate,
            cost: btn.dataset.cost,
            notes: btn.dataset.notes,
        };
        toggleMaintenanceForm('edit', data);
    }

    function cancelMaintenanceForm() {
        const card = document.getElementById('inlineMaintenanceCard');
        if (card) card.style.display = 'none';
    }

    window.toggleMaintenanceForm = toggleMaintenanceForm;
    window.editMaintenance = editMaintenance;
    window.cancelMaintenanceForm = cancelMaintenanceForm;

    document.addEventListener('click', function (e) {
        const toggleBtn = e.target.closest('.js-toggle-maintenance');
        if (toggleBtn) {
            e.preventDefault();
            toggleMaintenanceForm(toggleBtn.dataset.mode || 'create');
            return;
        }

        const editBtn = e.target.closest('.js-edit-maintenance');
        if (editBtn) {
            e.preventDefault();
            editMaintenance(editBtn);
            return;
        }

        const cancelBtn = e.target.closest('.js-cancel-maintenance');
        if (cancelBtn) {
            e.preventDefault();
            cancelMaintenanceForm();
        }
    });
})();
