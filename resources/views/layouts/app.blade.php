<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gojo Shop') | CRM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    @stack('head')
</head>
<body>

@auth
<div class="app-shell">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">G</div>
            <span class="brand-name">Gojo Shop</span>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Dashboard
            </a>

            @if(auth()->user()->isManager())
            <a href="{{ route('import.show') }}" class="nav-item {{ request()->routeIs('import.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Import Customers
            </a>
            @endif

            <a href="{{ route('customers.index') }}" class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                {{ auth()->user()->isManager() ? 'Customers' : 'My Customers' }}
            </a>

            @if(auth()->user()->isManager())
            <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
                Settings
            </a>
            @else
            <a href="{{ route('contractor.performance') }}" class="nav-item {{ request()->routeIs('contractor.performance') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                My Performance
            </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <a href="{{ auth()->user()->isManager() ? route('settings.index') . '#admin-profile' : '#' }}" class="user-info" style="text-decoration:none; flex:1; min-width:0;" title="{{ auth()->user()->isManager() ? 'Admin Profile Settings' : auth()->user()->name }}">
                <div class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                <div style="min-width:0;">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn" title="Sign out">
                    <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main content -->
    <div class="main-content">
        <header class="top-bar">
            <button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')" aria-label="Menu">
                <svg viewBox="0 0 24 24" width="20" height="20"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div class="top-bar-right">
                @if(auth()->user()->isManager())
                <a href="{{ route('settings.index') }}#admin-profile" class="top-greeting" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px; color:inherit;" title="Admin Profile & Settings">
                    <svg viewBox="0 0 24 24" width="15" height="15"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>{{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})</span>
                </a>
                @else
                <span class="top-greeting">
                    {{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})
                </span>
                @endif
            </div>
        </header>

        <main class="page-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error" style="display:block;">
                    <div style="font-weight:600; margin-bottom:4px;">Please correct the errors below:</div>
                    <ul style="margin:0; padding-left:18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@else
    @yield('content')
@endauth

@stack('scripts')
</body>
</html>
