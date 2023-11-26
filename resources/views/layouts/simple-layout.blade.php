{{-- New Layout made for rights --}}

<!DOCTYPE html>
<html class="no-js css-menubar" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="bootstrap material admin template">
    <meta name="author" content="">


    <title>Dashboard | Roto Eye</title>

    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/icons/Logo_RotoEye_Version_160 cross 160_PNG_1.0.png') }}">

    <link rel="stylesheet" href="{{ asset('assets/global/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/bootstrap-extend.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/remark/css/site.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/global/vendor/animsition/animsition.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/asscrollable/asScrollable.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/switchery/switchery.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/intro-js/introjs.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/slidepanel/slidePanel.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/flag-icon-css/flag-icon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/waves/waves.css') }}">

    @yield('formHeader')

    <link rel="stylesheet" href="{{ asset('assets/global/vendor/chartist/chartist.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/jvectormap/jquery-jvectormap.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/vendor/chartist-plugin-tooltip/chartist-plugin-tooltip.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/remark/examples/css/dashboard/v1.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/fonts/material-design/material-design.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/fonts/brand-icons/brand-icons.min.css') }}">
    <link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,300italic'>

    @yield('header')

    <!--[if lt IE 9]>
    <script src="{{ asset('assets/global/vendor/html5shiv/html5shiv.min.js') }}"></script>
    <![endif]-->

    <!--[if lt IE 10]>
    <script src="{{ asset('assets/global/vendor/media-match/media.match.min.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/respond/respond.min.js') }}"></script>
    <![endif]-->
    <script src="{{ asset('assets/global/vendor/breakpoints/breakpoints.js') }}"></script>
    <script>
        Breakpoints();
    </script>
</head>

<body class="animsition">
    <nav class="site-navbar navbar navbar-default navbar-fixed-top navbar-mega navbar-inverse" role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggler hamburger hamburger-close navbar-toggler-left hided"
                data-toggle="menubar">
                <span class="sr-only">Toggle navigation</span>
                <span class="hamburger-bar"></span>
            </button>
            <button type="button" class="navbar-toggler collapsed" data-target="#site-navbar-collapse"
                data-toggle="collapse">
                <i class="icon md-more" aria-hidden="true"></i>
            </button>
            <div class="navbar-brand navbar-brand-center site-gridmenu-toggle" data-toggle="gridmenu">
                <img class="navbar-brand-logo" src="/assets/remark/images/logo.png" title="Roto eYe">
                <span class="navbar-brand-text hidden-xs-down"> Roto eYe</span>
            </div>
            <button type="button" class="navbar-toggler collapsed" data-target="#site-navbar-search"
                data-toggle="collapse">
                <span class="sr-only">Toggle Search</span>
                <i class="icon md-search" aria-hidden="true"></i>
            </button>
        </div>

        <div class="navbar-container container-fluid">
            <!-- Navbar Collapse -->
            <div class="collapse navbar-collapse navbar-collapse-toolbar" id="site-navbar-collapse">
                <!-- Navbar Toolbar -->
                <ul class="nav navbar-toolbar">
                    <li class="nav-item hidden-float" id="toggleMenubar">
                        <a class="nav-link" data-toggle="menubar" href="#" role="button">
                            <i class="icon hamburger hamburger-arrow-left">
                                <span class="sr-only">Toggle menubar</span>
                                <span class="hamburger-bar"></span>
                            </i>
                        </a>
                    </li>
                    <li class="nav-item hidden-sm-down" id="toggleFullscreen">
                        <a class="nav-link icon icon-fullscreen" data-toggle="fullscreen" href="#" role="button">
                            <span class="sr-only">Toggle fullscreen</span>
                        </a>
                    </li>
                    @if (isset($machine))
                        <li class="nav-item dropdown">
                            <a class="nav-link">
                                <select
                                    class="padding-0 input-sm form-control form-control-sm allowedMachines bootstrap-select">
                                    @foreach ($user->allowedMachines as $allowedMachine)
                                        <option
                                            value="{{ \Illuminate\Support\Facades\Crypt::encrypt($allowedMachine->id) }}"
                                            {{ $allowedMachine->id == $machine->id ? 'selected=selected' : '' }}>
                                            {{ $allowedMachine->sap_code . ' (' . $allowedMachine->name . ')' }}
                                        </option>
                                    @endforeach
                                </select>
                            </a>
                        </li>
                    @endif
                </ul>
                @php
                    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $userIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                        $userIp = $_SERVER['HTTP_CLIENT_IP'];
                    } else {
                        $userIp = $_SERVER['REMOTE_ADDR'];
                    }
                @endphp
                <ul class="nav navbar-toolbar navbar-right navbar-toolbar-right">
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-toggle="dropdown" role="button">
                            ({{ $userIp }}) <span>{{ $user->name }}</span>
                        </a>
                        <div class="dropdown-menu" role="menu">
                            <a class="dropdown-item"
                                href="{{ URL::to('change/password' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id)) }}"
                                role="menuitem"><i class="icon md-lock" aria-hidden="true"></i> Change Password</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link navbar-avatar" data-toggle="dropdown" href="#"
                            aria-expanded="false" data-animation="scale-up" role="button">
                            <span class="avatar avatar-online">
                                <img src="/assets/global/portraits/{{ $user->photo }}" alt="...">
                                <i></i>
                            </span>
                        </a>
                    </li>
                </ul>
                <div class="navbar-brand navbar-brand-center">
                    <a
                        href="{{ URL::to('dashboard' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id)) }}">
                        <img class="navbar-brand-logo navbar-brand-logo-normal"
                            src="{{ asset('assets/remark/images/logo.png') }}" title="Roto Eye">
                        <img class="navbar-brand-logo navbar-brand-logo-special"
                            src="{{ asset('assets/remark/images/logo.png') }}" title="Roto Eye">
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <div class="site-menubar">
        <div class="site-menubar-header">
            <div class="cover overlay">
                <img class="cover-image" src="/assets/remark/examples/images/dashboard-header.jpg" alt="...">
                <div class="overlay-panel vertical-align overlay-background">
                    <div class="vertical-align-middle">
                        <a class="avatar avatar-lg" href="javascript:void(0)">
                            <img src="/assets/global/portraits/{{ $user->photo }}" alt="...">
                        </a>
                        <div class="site-menubar-info">
                            <h5 class="site-menubar-user">{{ $user->name }}</h5>
                            <p class="site-menubar-email">{{ $user->designation }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="site-menubar-body">
            <div>
                <div>
                    <ul class="site-menu" data-plugin="menu">
                        <!-- mine code  -->



                        <li
                            class="site-menu-item has-sub {{ $path == 'machines' || $path == 'machine/add' || $path == 'machine/update/{id}' ? 'active open' : '' }}">
                            <a href="javascript:void(0)">
                                <i class="site-menu-icon md-book" aria-hidden="true"></i>
                                <span class="site-menu-title">Machines</span>
                                <span class="site-menu-arrow"></span>
                            </a>
                            <ul class="site-menu-sub">
                                <li class="site-menu-item {{ $path == 'machine/add' ? 'active' : '' }}">
                                    <a class="animsition-link"
                                        href="{{ URL::to('machine/add' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id)) }}">
                                        <span class="site-menu-title">Add Machine</span>
                                    </a>
                                </li>
                                <li class="site-menu-item {{ $path == 'machines' ? 'active' : '' }}">
                                    <a class="animsition-link"
                                        href="{{ URL::to('machines' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id)) }}">
                                        <span class="site-menu-title">All Machines</span>
                                    </a>
                                    {{-- <a class="animsition-link" href="{{ URL::to('machines' . '/' . $machine->id) }}">
                                        <span class="site-menu-title">All Machines</span>
                                    </a> --}}
                                </li>
                            </ul>
                        </li>



                    </ul>
                </div>
            </div>
        </div>
    </div>
    @yield('body')
    <footer class="site-footer">
        <div class="site-footer-legal">© {{ date('Y') }} <a href="http://www.packages.com.pk"
                target="_blank">Roto eYe.cloud</a></div>
        <div class="site-footer-right">
            <a href="#">Packages Limited</a>
        </div>
    </footer>
    <script src="{{ asset('assets/global/vendor/babel-external-helpers/babel-external-helpers.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/popper-js/umd/popper.min.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/bootstrap/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/animsition/animsition.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/mousewheel/jquery.mousewheel.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/asscrollbar/jquery-asScrollbar.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/asscrollable/jquery-asScrollable.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/waves/waves.js') }}"></script>

    <script src="{{ asset('assets/global/vendor/switchery/switchery.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/intro-js/intro.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/screenfull/screenfull.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/slidepanel/jquery-slidePanel.js') }}"></script>
    @yield('graphFooter')
    @yield('formFooter')
    <script src="{{ asset('assets/global/js/Component.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin.js') }}"></script>
    <script src="{{ asset('assets/global/js/Base.js') }}"></script>
    <script src="{{ asset('assets/global/js/Config.js') }}"></script>

    <script src="{{ asset('assets/remark/js/Section/Menubar.js') }}"></script>
    <script src="{{ asset('assets/remark/js/Section/Sidebar.js') }}"></script>
    <script src="{{ asset('assets/remark/js/Section/PageAside.js') }}"></script>
    <script src="{{ asset('assets/remark/js/Plugin/menu.js') }}"></script>
    <script src="{{ asset('assets/global/js/config/colors.js') }}"></script>
    <script src="{{ asset('assets/remark/js/config/tour.js') }}"></script>
    <script src="{{ asset('assets/remark/js/Site.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/asscrollable.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/slidepanel.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/switchery.js') }}"></script>
    @yield('footer')
    <script>
        $('.allowedMachines').on('change', function() {
            var machine_id = $(this).val();
            var url = "{!! URL::to('dashboard') !!}" + "/" + machine_id;
            document.location.href = url;
        })
    </script>
</body>

</html>
