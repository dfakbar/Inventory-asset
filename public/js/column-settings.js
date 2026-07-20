(function () {
    const list = document.getElementById('columnSortable');
    if (!list) return;

    let dragSrcEl = null;

    function handleDragStart(e) {
        dragSrcEl = this;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
        this.classList.add('bg-light');
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        const items = list.querySelectorAll('.list-group-item');
        items.forEach(item => item.classList.remove('border-primary'));
        const target = getDragAfterElement(list, e.clientY);
        if (target) {
            target.classList.add('border-primary');
        }
    }

    function handleDragLeave() {
        this.classList.remove('bg-light');
    }

    function handleDrop(e) {
        e.preventDefault();
        const items = list.querySelectorAll('.list-group-item');
        items.forEach(item => item.classList.remove('border-primary', 'bg-light'));

        if (dragSrcEl !== this) {
            const children = Array.from(list.children);
            const srcIdx = children.indexOf(dragSrcEl);
            const targetIdx = children.indexOf(this);

            if (srcIdx < targetIdx) {
                this.parentNode.insertBefore(dragSrcEl, this.nextSibling);
            } else {
                this.parentNode.insertBefore(dragSrcEl, this);
            }
        }
    }

    function handleDragEnd() {
        const items = list.querySelectorAll('.list-group-item');
        items.forEach(item => item.classList.remove('bg-light', 'border-primary'));
    }

    function getDragAfterElement(container, y) {
        const draggableElements = container.querySelectorAll('.list-group-item:not(.dragging)');
        let closest = null;
        let closestOffset = Number.NEGATIVE_INFINITY;

        draggableElements.forEach(child => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closestOffset) {
                closestOffset = offset;
                closest = child;
            }
        });

        return closest;
    }

    const items = list.querySelectorAll('.list-group-item');
    items.forEach(item => {
        item.setAttribute('draggable', 'true');
        item.addEventListener('dragstart', handleDragStart);
        item.addEventListener('dragover', handleDragOver);
        item.addEventListener('dragleave', handleDragLeave);
        item.addEventListener('drop', handleDrop);
        item.addEventListener('dragend', handleDragEnd);
    });

    document.getElementById('saveColumnsBtn')?.addEventListener('click', function () {
        const items = list.querySelectorAll('.list-group-item');
        const visible = [];

        items.forEach(item => {
            const checkbox = item.querySelector('.column-checkbox');
            if (checkbox && checkbox.checked) {
                visible.push(checkbox.value);
            }
        });

        if (visible.length < 1) {
            alert('Setidaknya satu kolom harus dipilih.');
            return;
        }

        fetch(columnSettingsSaveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ columns: visible })
        })
        .then(res => {
            if (res.ok) {
                location.reload();
            }
        })
        .catch(() => location.reload());
    });

    document.getElementById('resetColumnsBtn')?.addEventListener('click', function () {
        const defaultCols = columnSettingsDefault;
        const items = list.querySelectorAll('.list-group-item');

        items.forEach((item, idx) => {
            const col = item.dataset.column;
            const checkbox = item.querySelector('.column-checkbox');
            checkbox.checked = defaultCols.includes(col);
        });

        fetch(columnSettingsSaveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ columns: defaultCols })
        })
        .then(res => {
            if (res.ok) {
                location.reload();
            }
        })
        .catch(() => location.reload());
    });
})();
