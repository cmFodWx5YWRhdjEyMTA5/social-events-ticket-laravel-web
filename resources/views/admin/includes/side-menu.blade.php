<!-- Sidebar menu-->
  <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
  <aside class="app-sidebar">
    <div class="app-sidebar__user">
      <div>
        <p class="app-sidebar__user-name">John Doe</p>
        <p class="app-sidebar__user-designation">Frontend Developer</p>
      </div>
    </div>
    <ul class="app-menu">
      @auth('web_admin')
      <li><a class="app-menu__item {{ Route::currentRouteNamed('admin_home') ? 'active' : '' }}" href="{{ route('admin_home') }}"><i class="app-menu__icon fa fa-home"></i><span class="app-menu__label">Home</span></a></li>
      <li><a class="app-menu__item {{ Route::currentRouteNamed('admins') ? 'active' : '' }}" href="{{ route('admins') }}"><i class="app-menu__icon fa fa-user-circle"></i><span class="app-menu__label">Admins</span></a></li>
      <li><a class="app-menu__item {{ Route::currentRouteNamed('countries') ? 'active' : '' }}" href="{{ route('countries') }}"><i class="app-menu__icon fa fa-map-o"></i><span class="app-menu__label">Countries</span></a></li>
      <li><a class="app-menu__item {{ Route::currentRouteNamed('towns') ? 'active' : '' }}" href="{{ route('towns') }}"><i class="app-menu__icon fa fa-compass"></i><span class="app-menu__label">Towns</span></a></li>
      <li><a class="app-menu__item {{ Route::currentRouteNamed('adverts') ? 'active' : '' }}" href="{{ route('adverts') }}"><i class="app-menu__icon fa fa-bullhorn"></i><span class="app-menu__label">Adverts</span></a></li>
      <li><a class="app-menu__item {{ Route::currentRouteNamed('venues') ? 'active' : '' }}" href="{{ route('venues') }}"><i class="app-menu__icon fa fa-map-marker"></i><span class="app-menu__label">Venues</span></a></li>
      <li><a class="app-menu__item {{ Route::currentRouteNamed('users') ? 'active' : '' }}" href="{{ route('users') }}"><i class="app-menu__icon fa fa-group"></i><span class="app-menu__label">Users</span></a></li>
      <li><a class="app-menu__item" href="charts.html"><i class="app-menu__icon fa fa-gg"></i><span class="app-menu__label">Posts</span></a></li>
      <li><a class="app-menu__item" href="charts.html"><i class="app-menu__icon	fa fa-bug"></i><span class="app-menu__label">Abuse</span></a></li>
      @endauth
      <li class="treeview"><a class="app-menu__item" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-th-list"></i><span class="app-menu__label">Event organizer</span><i class="treeview-indicator fa fa-angle-right"></i></a>
        <ul class="treeview-menu">
          <li><a class="treeview-item" href="table-basic.html"><i class="icon fa fa-circle-o"></i> Uneverified</a></li>
          <li><a class="treeview-item" href="table-data-table.html"><i class="icon fa fa-circle-o"></i> Verified</a></li>
        </ul>
      </li>
      <li class="treeview"><a class="app-menu__item" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-calendar-check-o"></i><span class="app-menu__label">Events</span><i class="treeview-indicator fa fa-angle-right"></i></a>
        <ul class="treeview-menu">
          <li><a class="treeview-item" href="table-basic.html"><i class="icon fa fa-circle-o"></i> Uneverified</a></li>
          <li class="treeview"><a class="treeview-item" href="table-data-table.html" data-toggle="treeview"><i class="icon fa fa-circle-o"></i> Verified</a>
            <li><a class="treeview-item" href="table-basic.html"><i class="icon fa fa-circle-o"></i> Free</a></li>
          </li>
        </ul>
      </li>
    </ul>
  </aside>