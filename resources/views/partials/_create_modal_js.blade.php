{{--
    Partial: partials/_create_modal_js.blade.php
    JS generik untuk modal "Tambah" (AJAX). Di-include dalam @push('scripts') halaman index.
    Parameter (opsional): $modalId (default 'createModal'), $bodyId (default 'createModalBody'),
                          $formId  (default 'createForm').
    Tombol pemicu: <button class="js-open-create" data-create-url="{{ route(...) }}">
    Form partial harus diberi id = $formId.
--}}
@php
    $modalId = $modalId ?? 'createModal';
    $bodyId  = $bodyId ?? 'createModalBody';
    $formId  = $formId ?? 'createForm';
@endphp
<script>
(() => {
    const modalId = '{{ $modalId }}';
    const bodyId  = '{{ $bodyId }}';
    const formId  = '{{ $formId }}';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function setModalLoading(body) {
        body.innerHTML = '<div class="text-center py-5 text-muted">' +
            '<div class="spinner-border text-primary mb-2" role="status"></div>' +
            '<div class="small">Memuat...</div></div>';
    }

    function clearFormErrors(form) {
        form.querySelectorAll('.alert.alert-danger').forEach(a => a.remove());
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function showFormAlert(form, message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show small py-2 mb-4';
        alert.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i>' + escapeHtml(message);
        form.prepend(alert);
    }

    function renderFormErrors(form, errors) {
        const errList = Object.values(errors).flat();
        if (errList.length) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show small py-2 mb-4';
            alert.innerHTML = '<strong>Terdapat ' + errList.length + ' kesalahan pada formulir:</strong><ul class="mb-0 mt-1 ps-3">' +
                errList.map(m => '<li>' + escapeHtml(m) + '</li>').join('') + '</ul>';
            form.prepend(alert);
        }

        Object.keys(errors).forEach(key => {
            const field = form.elements[key];
            if (!field) return;
            field.classList.add('is-invalid');

            let fb = field.nextElementSibling;
            if (!fb || !fb.classList.contains('invalid-feedback')) {
                fb = document.createElement('div');
                fb.className = 'invalid-feedback d-block';
                field.parentNode.insertBefore(fb, field.nextSibling);
            }
            fb.textContent = errors[key][0];
        });
    }

    function bindForm(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            clearFormErrors(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            const fd = new FormData(form);

            fetch(form.getAttribute('action'), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: fd
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                if (res.ok) {
                    location.reload();
                } else if (data.errors) {
                    if (submitBtn) submitBtn.disabled = false;
                    renderFormErrors(form, data.errors);
                } else if (data.error) {
                    if (submitBtn) submitBtn.disabled = false;
                    showFormAlert(form, data.error);
                } else {
                    if (submitBtn) submitBtn.disabled = false;
                    showFormAlert(form, 'Terjadi kesalahan. Silakan coba lagi.');
                }
            })
            .catch(() => {
                if (submitBtn) submitBtn.disabled = false;
                showFormAlert(form, 'Terjadi kesalahan jaringan. Silakan coba lagi.');
            });
        });
    }

    function openCreateModal(btn) {
        const body = document.getElementById(bodyId);
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById(modalId));
        setModalLoading(body);
        modal.show();

        fetch(btn.dataset.createUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Gagal memuat form.');
            return res.text();
        })
        .then(html => {
            body.innerHTML = html;
            if (window.initSearchableSelect) {
                body.querySelectorAll('select[data-searchable]').forEach(el => window.initSearchableSelect(el));
            }
            const form = body.querySelector('#' + formId);
            if (form) bindForm(form);
        })
        .catch(() => {
            body.innerHTML = '<div class="text-center py-5 text-muted">' +
                '<i class="bi bi-exclamation-triangle display-4 d-block mb-2"></i>' +
                '<span class="fw-medium">Gagal memuat form.</span></div>';
        });
    }

    document.getElementById(modalId)?.addEventListener('hidden.bs.modal', () => {
        document.getElementById(bodyId).innerHTML = '';
    });

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.js-open-create');
        if (btn) openCreateModal(btn);
    });
})();
</script>
