<!DOCTYPE html>
<html class="no-js css-menubar" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="bootstrap material admin template">
    <meta name="author" content="">

    <title>Dashboard | Roto Eye</title>

    <link rel="apple-touch-icon" href="<?php echo e(asset('logo.png')); ?>">
    <link rel="shortcut icon" href="<?php echo e(asset('assets/icons/Logo_RotoEye_Version_160 cross 160_PNG_1.0.png')); ?>">

    <link rel="stylesheet" href="<?php echo e(asset('assets/global/css/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/css/bootstrap-extend.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/remark/css/site.min.css')); ?>">

    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/animsition/animsition.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/asscrollable/asScrollable.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/switchery/switchery.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/intro-js/introjs.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/slidepanel/slidePanel.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/flag-icon-css/flag-icon.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/waves/waves.css')); ?>">

    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/chartist/chartist.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/jvectormap/jquery-jvectormap.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/chartist-plugin-tooltip/chartist-plugin-tooltip.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/remark/examples/css/dashboard/v1.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/fonts/material-design/material-design.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/fonts/brand-icons/brand-icons.min.css')); ?>">
    <link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,300italic'>

    <?php echo $__env->yieldContent('header'); ?>

    <!--[if lt IE 9]>
    <script src="<?php echo e(asset('assets/global/vendor/html5shiv/html5shiv.min.js')); ?>"></script>
    <![endif]-->

    <!--[if lt IE 10]>
    <script src="<?php echo e(asset('assets/global/vendor/media-match/media.match.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/respond/respond.min.js')); ?>"></script>
    <![endif]-->
    <script src="<?php echo e(asset('assets/global/vendor/breakpoints/breakpoints.js')); ?>"></script>
    <script>
        Breakpoints();
    </script>
</head>
<body class="animsition dashboard">
<nav class="site-navbar navbar navbar-default navbar-fixed-top navbar-mega navbar-inverse" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggler hamburger hamburger-close navbar-toggler-left hided" data-toggle="menubar">
            <span class="sr-only">Toggle navigation</span>
            <span class="hamburger-bar"></span>
        </button>
        <button type="button" class="navbar-toggler collapsed" data-target="#site-navbar-collapse" data-toggle="collapse">
            <i class="icon md-more" aria-hidden="true"></i>
        </button>
        <div class="navbar-brand navbar-brand-center site-gridmenu-toggle" data-toggle="gridmenu">
            <img class="navbar-brand-logo" src="/assets/remark/images/logo.png" title="Roto eYe">
            <span class="navbar-brand-text hidden-xs-down"> Roto eYe</span>
        </div>
        <button type="button" class="navbar-toggler collapsed" data-target="#site-navbar-search" data-toggle="collapse">
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
                <li class="nav-item dropdown">
                    <a class="nav-link" role="button">
                        <span><strong><?php echo e(isset($record) ? $record->machine->name.' - '.$record->machine->sap_code:""); ?></strong></span>
                    </a>
                </li>
            </ul>
            <!-- End Navbar Toolbar -->
            <?php 
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $userIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
             $userIp = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $userIp = $_SERVER['REMOTE_ADDR'];
            }
            ?>
            <!-- Navbar Toolbar Right -->
            <ul class="nav navbar-toolbar navbar-right navbar-toolbar-right">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-target="#userChangeModal" data-toggle="modal" role="button">
                    (<?php echo e($userIp); ?>)   <span><?php echo e($user->name); ?></span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link navbar-avatar" data-toggle="dropdown" href="#" aria-expanded="false" data-animation="scale-up" role="button">
                        <span class="avatar avatar-online">
                            <img src="/assets/global/portraits/<?php echo e($user->photo); ?>" alt="...">
                            <i></i>
                        </span>
                    </a>
                </li>
            </ul>
            <!-- End Navbar Toolbar Right -->

            <div class="navbar-brand navbar-brand-center">
                <a href="<?php echo e(URL::to('dashboard')); ?>">
                    <img class="navbar-brand-logo navbar-brand-logo-normal" src="<?php echo e(asset('assets/remark/images/logo.png')); ?>" title="Roto Eye">
                    <img class="navbar-brand-logo navbar-brand-logo-special" src="<?php echo e(asset('assets/remark/images/logo.png')); ?>" title="Roto Eye">
                </a>
            </div>
        </div>
        <!-- End Navbar Collapse -->
    </div>
</nav>
<div class="site-menubar">
    <div class="site-menubar-header">
        <div class="cover overlay">
            <img class="cover-image" src="/assets/remark/examples/images/dashboard-header.jpg"
                 alt="...">
            <div class="overlay-panel vertical-align overlay-background">
                <div class="vertical-align-middle">
                    <a class="avatar avatar-lg" href="javascript:void(0)">
                        <img src="/assets/global/portraits/<?php echo e($user->photo); ?>" alt="...">
                    </a>
                    <div class="site-menubar-info">
                        <h5 class="site-menubar-user"><?php echo e($user->name); ?></h5>
                        <p class="site-menubar-email"><?php echo e($user->designation); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php echo $__env->yieldContent('body'); ?>

<footer class="site-footer">
    <div class="site-footer-legal">© <?php echo e(date('Y')); ?> <a href="http://www.packages.com.pk" target="_blank">Roto eYe.cloud</a></div>
    <div class="site-footer-right">
        <a href="#">Packages Limited</a>
    </div>
</footer>

<script src="<?php echo e(asset('assets/global/vendor/babel-external-helpers/babel-external-helpers.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/vendor/jquery/jquery.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/vendor/popper-js/umd/popper.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/vendor/bootstrap/bootstrap.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/vendor/animsition/animsition.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/vendor/mousewheel/jquery.mousewheel.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/vendor/asscrollbar/jquery-asScrollbar.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/vendor/asscrollable/jquery-asScrollable.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/vendor/waves/waves.js')); ?>"></script>

<script src="<?php echo e(asset('assets/global/vendor/switchery/switchery.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/vendor/intro-js/intro.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/vendor/screenfull/screenfull.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/vendor/slidepanel/jquery-slidePanel.js')); ?>"></script>

<script src="<?php echo e(asset('assets/global/js/Component.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/js/Plugin.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/js/Base.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/js/Config.js')); ?>"></script>

<script src="<?php echo e(asset('assets/remark/js/Section/Menubar.js')); ?>"></script>
<script src="<?php echo e(asset('assets/remark/js/Section/Sidebar.js')); ?>"></script>
<script src="<?php echo e(asset('assets/remark/js/Section/PageAside.js')); ?>"></script>
<script src="<?php echo e(asset('assets/remark/js/Plugin/menu.js')); ?>"></script>

<!-- Config -->
<script src="<?php echo e(asset('assets/global/js/config/colors.js')); ?>"></script>
<script src="<?php echo e(asset('assets/remark/js/config/tour.js')); ?>"></script>
<script>Config.set('assets', 'assets');</script>

<!-- Page -->
<script src="<?php echo e(asset('assets/remark/js/Site.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/js/Plugin/asscrollable.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/js/Plugin/slidepanel.js')); ?>"></script>
<script src="<?php echo e(asset('assets/global/js/Plugin/switchery.js')); ?>"></script>
<?php echo $__env->yieldContent('footer'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\rotoeye\resources\views/layouts/production-dashboard-layout.blade.php ENDPATH**/ ?>