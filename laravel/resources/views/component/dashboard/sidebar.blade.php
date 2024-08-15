<nav class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('./assets/images/favicon.png') }}" class="w-50 h-40" alt="PT.AKM">
        </a>
        <div class="sidebar-toggler not-active">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="sidebar-body">
        <ul class="nav">
            <li class="nav-item nav-category">Halaman</li>
            <li class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#home" role="button" aria-expanded="false"
                    aria-controls="emails">
                    <i class="link-icon" data-feather="grid"></i>
                    <span class="link-title">Pages</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse {{ request()->routeIs('products.*') ? 'show' : '' }}" id="home">
                    <ul class="nav sub-menu">

                        <li class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                            <a href="{{ route('products.index') }}" class="nav-link">Product</a>
                        </li>
                    </ul>
                </div>
            </li>

        </ul>
    </div>
</nav>
