<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Resto') }}</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

@auth
{{-- Navbar --}}
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <span class="nav-link font-weight-bold">🍽️ {{ config('app.name', 'Resto') }}</span>
        </li>
    </ul>
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="fas fa-user-circle mr-1"></i>{{ auth()->user()->name }}
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <span class="dropdown-item-text text-muted small">{{ ucfirst(auth()->user()->role->value) }}</span>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt mr-2"></i> Logout</button>
                </form>
            </div>
        </li>
    </ul>
</nav>

{{-- Sidebar --}}
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="/" class="brand-link">
        <span class="brand-text font-weight-light">🍽️ RestoApp</span>
    </a>
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                {{-- Admin --}}
                @if(auth()->user()->isAdmin())
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-header">MANAJEMEN</li>
                <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Users<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Semua User</p></a></li>
                        <li class="nav-item"><a href="{{ route('admin.users.create') }}" class="nav-link {{ request()->routeIs('admin.users.create') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Tambah User</p></a></li>
                    </ul>
                </li>
                <li class="nav-item {{ request()->routeIs('admin.menu-categories.*') || request()->routeIs('admin.menu-items.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.menu-categories.*') || request()->routeIs('admin.menu-items.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-utensils"></i>
                        <p>Menu<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="{{ route('admin.menu-categories.index') }}" class="nav-link {{ request()->routeIs('admin.menu-categories.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Kategori</p></a></li>
                        <li class="nav-item"><a href="{{ route('admin.menu-items.index') }}" class="nav-link {{ request()->routeIs('admin.menu-items.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Menu Items</p></a></li>
                    </ul>
                </li>
                <li class="nav-item {{ request()->routeIs('admin.tables.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.tables.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-th"></i>
                        <p>Meja<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="{{ route('admin.tables.index') }}" class="nav-link {{ request()->routeIs('admin.tables.index') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Semua Meja</p></a></li>
                        <li class="nav-item"><a href="{{ route('admin.tables.create') }}" class="nav-link {{ request()->routeIs('admin.tables.create') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Tambah Meja</p></a></li>
                    </ul>
                </li>
                @endif

                {{-- POS & Laporan --}}
                @if(auth()->user()->isAdmin() || auth()->user()->isKasir())
                <li class="nav-header">OPERASIONAL</li>
                <li class="nav-item">
                    <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-cash-register"></i>
                        <p>POS Kasir</p>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('reports.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>Laporan<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="{{ route('reports.sales') }}" class="nav-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Penjualan</p></a></li>
                        <li class="nav-item"><a href="{{ route('reports.popular-menu') }}" class="nav-link {{ request()->routeIs('reports.popular-menu') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Menu Terlaris</p></a></li>
                        <li class="nav-item"><a href="{{ route('reports.tables') }}" class="nav-link {{ request()->routeIs('reports.tables') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Meja</p></a></li>
                    </ul>
                </li>
                @endif

                {{-- Kitchen --}}
                @if(auth()->user()->isAdmin() || auth()->user()->isDapur())
                <li class="nav-header">DAPUR</li>
                <li class="nav-item">
                    <a href="{{ route('kitchen.index') }}" class="nav-link {{ request()->routeIs('kitchen.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-fire"></i>
                        <p>Panel Dapur</p>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>

{{-- Content --}}
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h4>@yield('title', 'Dashboard')</h4></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/">Home</a></li>
                        @yield('breadcrumb')
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fas fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <ul class="mb-0 pl-3">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </div>
    </section>
</div>

{{-- Footer --}}
<footer class="main-footer">
    <div class="float-right d-none d-sm-inline"><b>RestoApp</b> v1.0</div>
    <strong>&copy; {{ date('Y') }} {{ config('app.name', 'Resto') }}.</strong> All rights reserved.
</footer>

@else
<div class="login-page">
    <div class="login-box">
        @yield('content')
    </div>
</div>
@endauth

</div>

{{-- Scripts: jQuery first, then AdminLTE (includes Bootstrap JS), then DataTables --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(function(){
        $('.datatable').DataTable({
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ s/d _END_ dari _TOTAL_',
                paginate: { first: 'Awal', last: 'Akhir', next: '&raquo;', previous: '&laquo;' }
            }
        });
    });
</script>
@stack('scripts')
@if(request()->routeIs('pos.*'))
<script>
    if ('serviceWorker' in navigator) { navigator.serviceWorker.register('/sw.js'); }
</script>
@endif
</body>
</html>
