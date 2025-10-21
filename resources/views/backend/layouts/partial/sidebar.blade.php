<ul class="menu-inner py-1">
  {{-- Header: User roles --}}
  <li class="menu-header small text-uppercase">
    <span class="menu-header-text">
      User Role: {{ implode(', ', auth()->user()->roles->pluck('name')->toArray()) }}
    </span>
  </li>

  {{-- Dashboard --}}
  <li class="menu-item {{ Route::currentRouteNamed('dashboard') ? 'active' : '' }}">
    <a href="{{ route('dashboard') }}" class="menu-link">
      <i class="menu-icon tf-icons ti ti-smart-home"></i>
      <div data-i18n="Dashboard">Dashboard</div>
    </a>
  </li>  
  @canany(['fund management'])
  <li class="menu-item 
  {{ Route::currentRouteNamed('fund.index') 
  || Route::currentRouteNamed('catagories.index') 
  || Route::currentRouteNamed('subCatagories.index') 
  || Route::currentRouteNamed('sub-Catagories-list.index')
  || Route::currentRouteNamed('product.index')
  || Route::currentRouteNamed('Supplier.index') ? 'active open' : '' }}">
  
  <a href="javascript:void(0);" class="menu-link menu-toggle">
    <i class="menu-icon tf-icons ti ti-users"></i>
    <div data-i18n="Fund Management">Fund Management</div>
  </a>

  <ul class="menu-sub">

    @can('fund')
    <li class="menu-item {{ Route::currentRouteNamed('fund.index') ? 'active' : '' }}">
      <a href="{{ route('fund.index') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-smart-home"></i>
        <div data-i18n="fund Index">Fund</div>
      </a>
    </li>
    @endcan

    @can('catagories')
    <li class="menu-item {{ Route::currentRouteNamed('catagories.index') ? 'active' : '' }}">
      <a href="{{ route('catagories.index') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-smart-home"></i>
        <div data-i18n="fund Catagories">Categories</div>
      </a>
    </li>
    @endcan

    @can('subCatagories')
    <li class="menu-item {{ Route::currentRouteNamed('subCatagories.index') ? 'active' : '' }}">
      <a href="{{ route('subCatagories.index') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-smart-home"></i>
        <div data-i18n="fund subCatagories">Subcatagories</div>
      </a>
    </li>
    @endcan
    @can('product')
    <li class="menu-item {{ Route::currentRouteNamed('product.index') ? 'active' : '' }}">
      <a href="{{ route('product.index') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-smart-home"></i>
        <div data-i18n="Add Product">Product</div>
      </a>
    </li>
    @endcan

      @can('Supplier')
    <li class="menu-item {{ Route::currentRouteNamed('Supplier.index') ? 'active' : '' }}">
      <a href="{{ route('Supplier.index') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-smart-home"></i>
        <div data-i18n="Add Item">Supplier</div>
      </a>
    </li>
    @endcan 

<!--     @can('sub-Catagories-list')
    <li class="menu-item {{ Route::currentRouteNamed('sub-Catagories-list.index') ? 'active' : '' }}">
      <a href="{{ route('sub-Catagories-list.index') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-smart-home"></i>
        <div data-i18n="Sub Catagories List">SubCategories List</div>
      </a>
    </li>
    @endcan -->

  </ul>
</li>
@endcanany

  @canany(['stock management'])
  <li class="menu-item 
  {{ Route::currentRouteNamed('fund.index') ? 'active open' : '' }}">
  
  <a href="javascript:void(0);" class="menu-link menu-toggle">
    <i class="menu-icon tf-icons ti ti-users"></i>
    <div data-i18n="Fund Management">Stock In</div>
  </a>

  <ul class="menu-sub">

    @can('stock')
    <li class="menu-item {{ Route::currentRouteNamed('stock-create.index') ? 'active' : '' }}">
      <a href="{{ route('stock-create.index') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-smart-home"></i>
        <div data-i18n="Stock Create">Add Stock</div>
      </a>
    </li>
    @endcan  
    @can('stock list')
    <li class="menu-item {{ Route::currentRouteNamed('stock-list.index') ? 'active' : '' }}">
      <a href="{{ route('stock-list.index') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-smart-home"></i>
        <div data-i18n="Stock Create">Stock Records</div>
      </a>
    </li>
    @endcan
  </ul>
</li>
@endcanany

{{-- User Management --}}
@canany(['user-index','user-create','user-edit','user-delete'])
<li class="menu-item {{ Route::currentRouteNamed('user.index') || Route::currentRouteNamed('user.create') || Route::currentRouteNamed('user.edit') ? 'active open' : '' }}">
  <a href="javascript:void(0);" class="menu-link menu-toggle">
    <i class="menu-icon tf-icons ti ti-users"></i>
    <div data-i18n="User">User</div>
  </a>
  <ul class="menu-sub">
    @can('user-create')
    <li class="menu-item {{ Route::currentRouteNamed('user.create') ? 'active' : '' }}">
      <a href="{{ route('user.create') }}" class="menu-link">
        <div data-i18n="Add New">Add New</div>
      </a>
    </li>
    @endcan

    @canany(['user-index','user-edit','user-delete'])
    <li class="menu-item {{ Route::currentRouteNamed('user.index') || Route::currentRouteNamed('user.edit') ? 'active' : '' }}">
      <a href="{{ route('user.index') }}" class="menu-link">
        <div data-i18n="User list">User List</div>
      </a>
    </li>
    @endcanany
  </ul>
</li>
@endcanany

{{-- Basic Setting --}}
@canany(['setting-index','setting-create','setting-edit','setting-delete'])
<li class="menu-item {{ Request::is('backend/setting/*') ? 'active open' : '' }}">
  <a href="javascript:void(0);" class="menu-link menu-toggle">
    <i class="menu-icon tf-icons ti ti-settings"></i>
    <div data-i18n="Basic Setting">Basic Setting</div>
  </a>
  <ul class="menu-sub">
    @canany(['setting-create','setting-index','setting-edit','setting-delete'])
    <li class="menu-item {{ Request::segment(3) == 'attendance_setting' ? 'active' : '' }}">
      <a href="{{ route('setting.index', 'attendance_setting') }}" class="menu-link">
        <div data-i18n="Attendance Setting">Attendance Setting</div>
      </a>
    </li>
    <li class="menu-item {{ Request::segment(3) == 'web_setting' ? 'active' : '' }}">
      <a href="{{ route('setting.index', 'web_setting') }}" class="menu-link">
        <div data-i18n="Web Setting">Web Setting</div>
      </a>
    </li>
    <li class="menu-item {{ Request::segment(3) == 'logo_setting' ? 'active' : '' }}">
      <a href="{{ route('setting.index', 'logo_setting') }}" class="menu-link">
        <div data-i18n="Logo Setting">Logo Setting</div>
      </a>
    </li>
    @endcanany
  </ul>
</li>
@endcanany

{{-- Confidential --}}
@canany(['permission-index','permission-create','permission-edit','permission-delete','role-index','role-create','role-edit','role-delete'])
<li class="menu-item {{ Route::currentRouteNamed('permission.index') || Route::currentRouteNamed('permission.create') || Route::currentRouteNamed('permission.edit') || Route::currentRouteNamed('role.index') || Route::currentRouteNamed('role.create') || Route::currentRouteNamed('role.edit') ? 'active open' : '' }}">
  <a href="javascript:void(0);" class="menu-link menu-toggle">
    <i class="menu-icon tf-icons ti ti-lock"></i>
    <div data-i18n="Confidential">Confidential</div>
  </a>
  <ul class="menu-sub">
    @canany(['role-index','role-create','role-edit','role-delete'])
    <li class="menu-item {{ Route::currentRouteNamed('role.index') || Route::currentRouteNamed('role.create') || Route::currentRouteNamed('role.edit') ? 'active' : '' }}">
      <a href="{{ route('role.index') }}" class="menu-link">
        <div data-i18n="Role">Role</div>
      </a>
    </li>
    @endcanany

    @canany(['permission-index','permission-create','permission-edit','permission-delete'])
    <li class="menu-item {{ Route::currentRouteNamed('permission.index') || Route::currentRouteNamed('permission.create') || Route::currentRouteNamed('permission.edit') ? 'active' : '' }}">
      <a href="{{ route('permission.index') }}" class="menu-link">
        <div data-i18n="Permission">Permission</div>
      </a>
    </li>
    @endcanany
  </ul>
</li>
@endcanany

{{-- Profile --}}
<li class="menu-item {{ Route::currentRouteNamed('profile') ? 'active' : '' }}">
  <a href="{{ route('profile') }}" class="menu-link">
    <i class="menu-icon tf-icons ti ti-user-edit"></i>
    <div data-i18n="Profile">Profile</div>
  </a>
</li>

{{-- Change Password --}}
<li class="menu-item {{ Route::currentRouteNamed('change-password') ? 'active' : '' }}">
  <a href="{{ route('change-password') }}" class="menu-link">
    <i class="menu-icon tf-icons ti ti-lock-code"></i>
    <div data-i18n="Change Password">Change Password</div>
  </a>
</li>

{{-- Logout --}}
<li class="menu-item">
  <div class="d-grid px-2 pt-2 pb-1">
    <a class="btn btn-sm btn-danger d-flex" href="{{ route('logout') }}"
    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
    <small class="align-middle">Logout</small>
    <i class="ti ti-logout ms-2 ti-14px"></i>
  </a>
  <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
</div>
</li>
</ul>