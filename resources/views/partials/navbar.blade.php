    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="{{ url('/') }}">TMLP Stats</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li {{ Request::is('/') ? 'class="active"' : '' }}><a href="{{ url('/') }}">Home</a></li>
            @if (Auth::check())
              @if (Auth::user()->hasRole('globalStatistician') || Auth::user()->hasRole('administrator'))
              <li {!! Request::is('import') ? 'class="active"' : '' !!}><a href="{{ url('/import') }}">Validate Stats</a></li>
              @endif
              @if (Auth::user()->hasRole('administrator'))
              <li class="dropdown {{ Request::is('admin') || Request::is('admin/*') ? 'active' : '' }}">
                <a href="{{ url('/admin/dashboard') }}" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Admin <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                  <li><a href="{{ url('/admin/dashboard') }}">Dashboard</a></li>
                  <li><a href="{{ url('/admin/users') }}">Users</a></li>
                  <li><a href="{{ url('/admin/centers') }}">Centers</a></li>
                  <li><a href="{{ url('/admin/quarters') }}">Quarters</a></li>
                  <li><a href="{{ url('/admin/import') }}">Import Sheets</a></li>
                </ul>
              </li>
              @endif
            @endif
          </ul>
          <ul class="nav navbar-nav navbar-right">
            @if (Auth::guest())
              <li {!! Request::is('/') ? 'class="active"' : '' !!}><a href="{{ url('/auth/login') }}">Login/Register</a></li>
            @else
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ Auth::user()->first_name }} <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                  <!--<li><a href="{{ url('/user/profile') }}">Profile</a></li>-->
                  <li><a href="{{ url('/auth/logout') }}">Logout</a></li>
                </ul>
              </li>
            @endif
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>