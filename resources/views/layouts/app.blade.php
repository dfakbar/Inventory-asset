<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'AssetMS') }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous"
          referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

    <style>
        :root {
            --sidebar-w: 250px;
            --sidebar-bg: #212529;
            --sidebar-text: #adb5bd;
            --sidebar-hover-bg: rgba(255,255,255,.08);
            --sidebar-active-bg: rgba(255,255,255,.12);
            --topbar-h: 56px;
        }

        body { background: #f1f3f5; }

        /* ── Sidebar ── */
        #sidebar {
            position: fixed; top: 0; left: 0; z-index: 1040;
            width: var(--sidebar-w); min-height: 100vh;
            background: var(--sidebar-bg);
            display: flex; flex-direction: column;
            transition: transform .25s ease;
        }
        #sidebar.collapsed { transform: translateX(calc(-1 * var(--sidebar-w))); }

        .sidebar-brand {
            display: flex; align-items: center; gap: .5rem;
            padding: 1rem 1.25rem;
            color: #fff; font-size: 1.15rem; font-weight: 700;
            text-decoration: none; white-space: nowrap;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-brand span.accent { color: #0d6efd; }

        .sidebar-nav { flex: 1; padding: .75rem 0; }

        .nav-label {
            padding: .45rem 1.25rem;
            font-size: .68rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .08em;
            color: #6c757d;
        }

        .sidebar-nav .nav-link {
            display: flex; align-items: center; gap: .65rem;
            padding: .58rem 1.25rem;
            color: var(--sidebar-text); font-size: .875rem;
            border-radius: 0; white-space: nowrap;
            transition: background .15s, color .15s;
        }
        .sidebar-nav .nav-link i { font-size: 1rem; width: 1.25rem; text-align: center; flex-shrink: 0; }
        .sidebar-nav .nav-link:hover { background: var(--sidebar-hover-bg); color: #fff; }
        .sidebar-nav .nav-link.active {
            background: var(--sidebar-active-bg); color: #fff;
            border-left: 3px solid #0d6efd;
        }

        /* ── Sidebar footer ── */
        .sidebar-footer {
            padding: .6rem 1.25rem;
            font-size: .7rem; color: #495057;
            border-top: 1px solid rgba(255,255,255,.04);
        }

        /* ── Mobile overlay ── */
        #sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.45); z-index: 1039;
        }
        #sidebar-overlay.show { display: block; }

        /* ── Main wrapper ── */
        #main-wrapper {
            margin-left: var(--sidebar-w);
            min-height: 100vh; display: flex; flex-direction: column;
            transition: margin-left .25s ease;
        }
        #main-wrapper.expanded { margin-left: 0; }

        /* ── Topbar ── */
        #topbar {
            position: sticky; top: 0; z-index: 1030;
            height: var(--topbar-h); background: #fff;
            border-bottom: 1px solid #dee2e6;
            display: flex; align-items: center; padding: 0 1.25rem; gap: 1rem;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        .topbar-title { font-size: .95rem; font-weight: 600; color: #212529; }

        /* ── Content ── */
        #main-content { flex: 1; padding: 1.5rem; }

        /* ── Flash messages ── */
        .flash-container {
            position: fixed;
            top: calc(var(--topbar-h) + .75rem);
            right: 1rem; z-index: 1055; width: 340px;
        }

        /* ── Searchable Select ── */
        .searchable-wrapper {
            position: relative;
        }
        .searchable-wrapper .searchable-input {
            cursor: pointer;
            background: #fff;
            padding-right: 2rem;
        }
        .searchable-wrapper .searchable-arrow {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #6c757d;
            font-size: .75rem;
        }
        .searchable-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1050;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: .375rem;
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
            max-height: 280px;
            display: none;
            margin-top: 2px;
        }
        .searchable-dropdown.show {
            display: block;
        }
        .searchable-dropdown .search-box {
            padding: .5rem;
            border-bottom: 1px solid #dee2e6;
            position: sticky;
            top: 0;
            background: #fff;
            border-radius: .375rem .375rem 0 0;
        }
        .searchable-dropdown .search-box input {
            width: 100%;
            border: 1px solid #dee2e6;
            border-radius: .25rem;
            padding: .375rem .75rem;
            font-size: .875rem;
            outline: none;
        }
        .searchable-dropdown .search-box input:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 .2rem rgba(13,110,253,.15);
        }
        .searchable-dropdown .options-list {
            max-height: 200px;
            overflow-y: auto;
            padding: 0;
            margin: 0;
            list-style: none;
        }
        .searchable-dropdown .options-list li {
            padding: .5rem .75rem;
            cursor: pointer;
            font-size: .875rem;
            border-bottom: 1px solid #f1f3f5;
            transition: background .1s;
        }
        .searchable-dropdown .options-list li:hover,
        .searchable-dropdown .options-list li.highlighted {
            background: #e9ecef;
        }
        .searchable-dropdown .options-list li.selected {
            background: #0d6efd;
            color: #fff;
        }
        .searchable-dropdown .options-list li.no-result {
            color: #6c757d;
            cursor: default;
            text-align: center;
            padding: 1rem;
        }
        .searchable-dropdown .options-list li.no-result:hover {
            background: transparent;
        }

        /* ── Responsive ── */
        @media (max-width: 991.98px) {
            #sidebar { transform: translateX(calc(-1 * var(--sidebar-w))); }
            #sidebar.show-mobile { transform: translateX(0); }
            #main-wrapper { margin-left: 0 !important; }
        }
        @media (min-width: 992px) {
            #sidebar-overlay { display: none !important; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ── Sidebar ── --}}
<nav id="sidebar">
    <a class="sidebar-brand" href="{{ route('dashboard') }}">
        <i class="bi bi-building-gear fs-5"></i>
        Asset<span class="accent">MS</span>
    </a>

    <div class="sidebar-nav">
        <div class="nav-label">Menu Utama</div>

        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-house-fill"></i> Dashboard
        </a>

        <a href="{{ route('assets.index') }}"
           class="nav-link {{ request()->routeIs('assets.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam-fill"></i> Manajemen Aset
        </a>

        {{-- Menu tambahan berdasarkan permission --}}
        @canany(['asset.create', 'category.viewAny', 'brand.viewAny', 'vendor.viewAny', 'location.viewAny'])
        <div class="nav-label mt-2">Akses Saya</div>
        @endcanany

        @can('asset.create')
        <a href="{{ route('assets.create') }}"
           class="nav-link {{ request()->routeIs('assets.create') ? 'active' : '' }}">
            <i class="bi bi-plus-square-fill"></i> Tambah Aset
        </a>
        @endcan

        @can('category.viewAny')
        <a href="{{ route('admin.categories.index') }}"
           class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <i class="bi bi-tags-fill"></i> Manajemen Kategori
        </a>
        @endcan

        @can('brand.viewAny')
        <a href="{{ route('admin.brands.index') }}"
           class="nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
            <i class="bi bi-bookmark-star-fill"></i> Manajemen Merek
        </a>
        @endcan

        @can('vendor.viewAny')
        <a href="{{ route('admin.vendors.index') }}"
           class="nav-link {{ request()->routeIs('admin.vendors.*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i> Manajemen Vendor
        </a>
        @endcan

        @can('location.viewAny')
        <a href="{{ route('admin.locations.index') }}"
           class="nav-link {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt-fill"></i> Manajemen Lokasi
        </a>
        @endcan

        @can('employee.viewAny')
        <a href="{{ route('admin.employees.index') }}"
           class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Manajemen Pengguna
        </a>
        @endcan

        @can('loan.viewAny')
        <a href="{{ route('loans.index') }}"
           class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right"></i> Peminjaman Aset
        </a>
        @endcan

        @can('report.viewAny')
        <a href="{{ route('reports.index') }}"
           class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-bar-graph"></i> Laporan & Ekspor
        </a>
        @endcan

        @can('asset.viewAny')
        <div class="nav-label mt-2">Log</div>
        <a href="{{ route('admin.logs.asset') }}"
           class="nav-link {{ request()->routeIs('admin.logs.asset') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i> Log Aktivitas
        </a>
        <a href="{{ route('admin.logs.mutation') }}"
           class="nav-link {{ request()->routeIs('admin.logs.mutation') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right"></i> Log Mutasi
        </a>
        @endcan

        @auth
        @if(auth()->user()->isAdmin())
        <div class="nav-label mt-2">Super Admin</div>
        <a href="{{ route('admin.users.index') }}"
           class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> Manajemen User
        </a>
        @endif
        @endauth
    </div>

    <div class="sidebar-footer">
        v1.0.0 &mdash; &copy; {{ date('Y') }} AssetMS
    </div>
</nav>

<div id="sidebar-overlay"></div>

{{-- ── Main Wrapper ── --}}
<div id="main-wrapper">

    {{-- Topbar --}}
    <header id="topbar">
        <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
            <button id="sidebar-toggle" class="btn btn-sm btn-outline-secondary flex-shrink-0" title="Toggle Sidebar">
                <i class="bi bi-list fs-5"></i>
            </button>

            <span class="topbar-title text-truncate">
                @yield('title', 'Dashboard')
            </span>

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="d-none d-md-block ms-3">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">
                            <i class="bi bi-house"></i>
                        </a>
                    </li>
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>

        {{-- User Dropdown --}}
        @auth
        <div class="dropdown flex-shrink-0">
            <button class="btn d-flex align-items-center gap-2 py-1 px-2 border-0"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    style="background:transparent;">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                     style="width:32px;height:32px;font-size:.8rem;font-weight:700;color:#fff;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <span class="d-none d-sm-inline small fw-medium text-dark">{{ auth()->user()->name }}</span>
                <i class="bi bi-chevron-down text-muted" style="font-size:.7rem"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" style="min-width:220px;">
                <li>
                    <div class="dropdown-item-text">
                        <div class="fw-semibold small">{{ auth()->user()->name }}</div>
                        <div class="text-muted small">{{ auth()->user()->email }}</div>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <span class="dropdown-item-text">
                        <span class="{{ auth()->user()->role->badgeClass() }} d-inline-block py-1 px-2 small">
                            <i class="bi {{ auth()->user()->isAdmin() ? 'bi-shield-fill' : 'bi-person-fill' }} me-1"></i>
                            {{ auth()->user()->role->label() }}
                        </span>
                    </span>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger small">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
        @endauth
    </header>

    {{-- Flash Messages --}}
    <div class="flash-container" aria-live="polite">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('info'))
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- Content --}}
    <main id="main-content">
        @yield('content')
    </main>

    <footer class="py-2 px-4 border-top bg-white">
        <small class="text-muted">
            &copy; {{ date('Y') }} Sistem Informasi Manajemen Aset Perusahaan
        </small>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"
        defer></script>
<script>
    (() => {
        const sidebar     = document.getElementById('sidebar');
        const mainWrapper = document.getElementById('main-wrapper');
        const overlay     = document.getElementById('sidebar-overlay');
        const toggleBtn   = document.getElementById('sidebar-toggle');
        const BP          = 992;

        const isMobile = () => window.innerWidth < BP;

        toggleBtn.addEventListener('click', () => {
            if (isMobile()) {
                const open = sidebar.classList.toggle('show-mobile');
                overlay.classList.toggle('show', open);
            } else {
                sidebar.classList.toggle('collapsed');
                mainWrapper.classList.toggle('expanded');
            }
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('show-mobile');
            overlay.classList.remove('show');
        });

        // Auto-dismiss flash messages setelah 4 detik
        document.querySelectorAll('.flash-container .alert').forEach(el => {
            setTimeout(() => bootstrap.Alert.getOrCreateInstance(el)?.close(), 4000);
        });

        // ── Searchable Select ──
        class SearchableSelect {
            constructor(select) {
                this.select = select;
                this.options = Array.from(select.options).filter(o => o.value !== '');
                this.wrap();
                this.render();
                this.bind();
            }

            wrap() {
                this.select.style.display = 'none';
                this.wrapper = document.createElement('div');
                this.wrapper.className = 'searchable-wrapper';
                this.select.parentNode.insertBefore(this.wrapper, this.select);
                this.wrapper.appendChild(this.select);
            }

            render() {
                this.input = document.createElement('input');
                this.input.type = 'text';
                this.input.className = 'form-control searchable-input';
                this.input.placeholder = this.select.options[0]?.text || '-- Cari --';
                this.input.value = this.getSelectedText();
                this.input.readOnly = this.select.disabled;
                this.input.autocomplete = 'off';

                this.arrow = document.createElement('span');
                this.arrow.className = 'searchable-arrow';
                this.arrow.innerHTML = '<i class="bi bi-chevron-down"></i>';

                this.dropdown = document.createElement('div');
                this.dropdown.className = 'searchable-dropdown';

                const searchBox = document.createElement('div');
                searchBox.className = 'search-box';
                this.searchInput = document.createElement('input');
                this.searchInput.type = 'text';
                this.searchInput.placeholder = 'Ketik untuk mencari...';
                searchBox.appendChild(this.searchInput);

                this.list = document.createElement('ul');
                this.list.className = 'options-list';

                this.dropdown.appendChild(searchBox);
                this.dropdown.appendChild(this.list);

                this.wrapper.appendChild(this.input);
                this.wrapper.appendChild(this.arrow);
                this.wrapper.appendChild(this.dropdown);

                this.populateList();
            }

            getSelectedText() {
                const sel = this.select.options[this.select.selectedIndex];
                return sel && sel.value !== '' ? sel.text : '';
            }

            populateList(filter = '') {
                this.list.innerHTML = '';
                const lower = filter.toLowerCase();
                const filtered = this.options.filter(o =>
                    o.text.toLowerCase().includes(lower)
                );

                if (filtered.length === 0) {
                    const li = document.createElement('li');
                    li.className = 'no-result';
                    li.textContent = 'Tidak ditemukan';
                    this.list.appendChild(li);
                    return;
                }

                const selectedVal = this.select.value;
                filtered.forEach(o => {
                    const li = document.createElement('li');
                    li.textContent = o.text;
                    li.dataset.value = o.value;
                    if (o.value === selectedVal) {
                        li.classList.add('selected');
                    }
                    li.addEventListener('click', () => this.selectOption(o.value, o.text));
                    li.addEventListener('mouseenter', () => {
                        this.list.querySelectorAll('li').forEach(l => l.classList.remove('highlighted'));
                        li.classList.add('highlighted');
                    });
                    this.list.appendChild(li);
                });
            }

            selectOption(value, text) {
                this.select.value = value;
                this.input.value = text;
                this.select.dispatchEvent(new Event('change', { bubbles: true }));
                this.close();
            }

            open() {
                this.dropdown.classList.add('show');
                this.searchInput.value = '';
                this.populateList();
                this.searchInput.focus();
            }

            close() {
                this.dropdown.classList.remove('show');
            }

            toggle() {
                this.dropdown.classList.contains('show') ? this.close() : this.open();
            }

            bind() {
                this.input.addEventListener('mousedown', (e) => {
                    if (this.select.disabled) return;
                    if (this.dropdown.classList.contains('show')) {
                        e.preventDefault();
                        this.close();
                    }
                });

                this.input.addEventListener('focus', () => {
                    if (this.select.disabled) return;
                    if (!this.dropdown.classList.contains('show')) {
                        this.open();
                    }
                });

                this.searchInput.addEventListener('input', () => {
                    this.populateList(this.searchInput.value);
                });

                this.searchInput.addEventListener('keydown', (e) => {
                    const items = this.list.querySelectorAll('li:not(.no-result)');
                    if (items.length === 0) return;

                    const current = this.list.querySelector('.highlighted');
                    let idx = current ? Array.from(items).indexOf(current) : -1;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        idx = Math.min(idx + 1, items.length - 1);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        idx = Math.max(idx - 1, 0);
                    } else if (e.key === 'Enter' && current) {
                        e.preventDefault();
                        current.click();
                        return;
                    } else if (e.key === 'Escape') {
                        this.close();
                        return;
                    }

                    items.forEach(l => l.classList.remove('highlighted'));
                    if (idx >= 0) items[idx].classList.add('highlighted');
                });

                document.addEventListener('click', (e) => {
                    if (!this.wrapper.contains(e.target)) {
                        this.close();
                    }
                });

                this.select.addEventListener('change', () => {
                    this.input.value = this.getSelectedText();
                });
            }
        }

        document.querySelectorAll('select[data-searchable]').forEach(el => {
            new SearchableSelect(el);
        });
    })();
</script>

@stack('scripts')
</body>
</html>
