<!DOCTYPE html>
<html class="no-js css-menubar" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="bootstrap material admin template">
    <meta name="author" content="">

    <title>Login | Roto Eye</title>

    <link rel="apple-touch-icon" href="{{asset('logo.png')}}">
    <link rel="shortcut icon" href="{{asset('assets/icons/Logo_RotoEye_Version_160 cross 160_PNG_1.0.png')}}">

    <link rel="stylesheet" href="{{asset('assets/global/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/css/bootstrap-extend.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/css/site.min.css')}}">

    <link rel="stylesheet" href="{{asset('assets/global/vendor/animsition/animsition.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/asscrollable/asScrollable.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/switchery/switchery.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/intro-js/introjs.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/slidepanel/slidePanel.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/flag-icon-css/flag-icon.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/waves/waves.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/pages/login-v3.css')}}">

    <link rel="stylesheet" href="{{asset('assets/global/vendor/chartist/chartist.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/jvectormap/jquery-jvectormap.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/chartist-plugin-tooltip/chartist-plugin-tooltip.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/dashboard/v1.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/fonts/material-design/material-design.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/fonts/brand-icons/brand-icons.min.css')}}">
    <link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,300italic'>

    @yield('header')

    <!--[if lt IE 9]>
    <script src="{{asset('assets/global/vendor/html5shiv/html5shiv.min.js')}}"></script>
    <![endif]-->

    <!--[if lt IE 10]>
    <script src="{{asset('assets/global/vendor/media-match/media.match.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/respond/respond.min.js')}}"></script>
    <![endif]-->
    <script src="{{asset('assets/global/vendor/breakpoints/breakpoints.js')}}"></script>
    <script>
        Breakpoints();
    </script>
</head>
<body class="animsition page-login-v3 layout-full">
<!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->
<div class="page vertical-align text-center" data-animsition-in="fade-in" data-animsition-out="fade-out">
@yield('body')
</div>

<script src="{{asset('assets/global/vendor/babel-external-helpers/babel-external-helpers.js')}}"></script>
<script src="{{asset('assets/global/vendor/jquery/jquery.js')}}"></script>
<script src="{{asset('assets/global/vendor/popper-js/umd/popper.min.js')}}"></script>
<script src="{{asset('assets/global/vendor/bootstrap/bootstrap.js')}}"></script>
<script src="{{asset('assets/global/vendor/animsition/animsition.js')}}"></script>
<script src="{{asset('assets/global/vendor/mousewheel/jquery.mousewheel.js')}}"></script>
<script src="{{asset('assets/global/vendor/asscrollbar/jquery-asScrollbar.js')}}"></script>
<script src="{{asset('assets/global/vendor/asscrollable/jquery-asScrollable.js')}}"></script>
<script src="{{asset('assets/global/vendor/waves/waves.js')}}"></script>

<script src="{{asset('assets/global/vendor/switchery/switchery.js')}}"></script>
<script src="{{asset('assets/global/vendor/intro-js/intro.js')}}"></script>
<script src="{{asset('assets/global/vendor/screenfull/screenfull.js')}}"></script>
<script src="{{asset('assets/global/vendor/slidepanel/jquery-slidePanel.js')}}"></script>
<script src="{{asset('assets/global/vendor/jquery-placeholder/jquery.placeholder.js')}}"></script>


<script src="{{asset('assets/global/js/Component.js')}}"></script>
<script src="{{asset('assets/global/js/Plugin.js')}}"></script>
<script src="{{asset('assets/global/js/Base.js')}}"></script>
<script src="{{asset('assets/global/js/Config.js')}}"></script>

<script src="{{asset('assets/remark/js/Section/Menubar.js')}}"></script>
<script src="{{asset('assets/remark/js/Section/Sidebar.js')}}"></script>
<script src="{{asset('assets/remark/js/Section/PageAside.js')}}"></script>
<script src="{{asset('assets/remark/js/Plugin/menu.js')}}"></script>

<!-- Config -->
<script src="{{asset('assets/global/js/config/colors.js')}}"></script>
<script src="{{asset('assets/remark/js/config/tour.js')}}"></script>
<script>Config.set('assets', 'assets');</script>

<!-- Page -->
<script src="{{asset('assets/remark/js/Site.js')}}"></script>
<script src="{{asset('assets/global/js/Plugin/asscrollable.js')}}"></script>
<script src="{{asset('assets/global/js/Plugin/slidepanel.js')}}"></script>
<script src="{{asset('assets/global/js/Plugin/switchery.js')}}"></script>
<script src="{{asset('assets/global/js/Plugin/jquery-placeholder.js')}}"></script>
<script src="{{asset('assets/global/js/Plugin/material.js')}}"></script>
@yield('footer')
</body>
</html>
