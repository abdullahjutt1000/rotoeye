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

    <?php echo $__env->yieldContent('formHeader'); ?>

    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/chartist/chartist.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/jvectormap/jquery-jvectormap.css')); ?>">
    <link rel="stylesheet"
        href="<?php echo e(asset('assets/global/vendor/chartist-plugin-tooltip/chartist-plugin-tooltip.css')); ?>">
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
                    <?php if(isset($machine)): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link">
                                <select
                                    class="padding-0 input-sm form-control form-control-sm allowedMachines bootstrap-select">
                                    <?php $__currentLoopData = $user->allowedMachines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $allowedMachine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option
                                            value="<?php echo e(\Illuminate\Support\Facades\Crypt::encrypt($allowedMachine->id)); ?>"
                                            <?php echo e($allowedMachine->id == $machine->id ? 'selected=selected' : ''); ?>>
                                            <?php echo e($allowedMachine->sap_code . ' (' . $allowedMachine->name . ')'); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <?php
                    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $userIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                        $userIp = $_SERVER['HTTP_CLIENT_IP'];
                    } else {
                        $userIp = $_SERVER['REMOTE_ADDR'];
                    }
                ?>
                <ul class="nav navbar-toolbar navbar-right navbar-toolbar-right">
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-toggle="dropdown" role="button">
                            (<?php echo e($userIp); ?>) <span><?php echo e($user->name); ?></span>
                        </a>
                        <div class="dropdown-menu" role="menu">
                            <a class="dropdown-item"
                                href="<?php echo e(URL::to('change/password' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>"
                                role="menuitem"><i class="icon md-lock" aria-hidden="true"></i> Change Password</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link navbar-avatar" data-toggle="dropdown" href="#"
                            aria-expanded="false" data-animation="scale-up" role="button">
                            <span class="avatar avatar-online">
                                <img src="/assets/global/portraits/<?php echo e($user->photo); ?>" alt="...">
                                <i></i>
                            </span>
                        </a>
                    </li>
                </ul>
                <div class="navbar-brand navbar-brand-center">
                    <a
                        href="<?php echo e(URL::to('dashboard' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                        <img class="navbar-brand-logo navbar-brand-logo-normal"
                            src="<?php echo e(asset('assets/remark/images/logo.png')); ?>" title="Roto Eye">
                        <img class="navbar-brand-logo navbar-brand-logo-special"
                            src="<?php echo e(asset('assets/remark/images/logo.png')); ?>" title="Roto Eye">
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
        <div class="site-menubar-body">
            <div>
                <div>
                    <ul class="site-menu" data-plugin="menu">
                        <!-- mine code  -->
                        <li class="site-menu-item <?php echo e($path == 'group-dashboard' ? 'active' : ''); ?>">
                            <a class="animsition-link" href="<?php echo e(URL::to('group-dashboard')); ?>">
                                <i class="site-menu-icon md-chart" aria-hidden="true"></i>
                                <span class="site-menu-title">Group Dashboard</span>
                            </a>
                        </li>
                        <!-- mine code  -->
                        <li class="site-menu-item <?php echo e($path == 'productivity/{id}' ? 'active' : ''); ?>">
                            <a class="animsition-link"
                                href="<?php echo e(URL::to('productivity' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                <i class="site-menu-icon md-chart" aria-hidden="true"></i>
                                <span class="site-menu-title">Productivity Dashboard</span>
                            </a>
                        </li>
                        <li class="site-menu-item <?php echo e($path == 'dashboard/{id}' ? 'active' : ''); ?>">
                            <a class="animsition-link"
                                href="<?php echo e(URL::to('dashboard' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                <i class="site-menu-icon md-apps" aria-hidden="true"></i>
                                <span class="site-menu-title">Dashboard</span>
                            </a>
                        </li>
                        <li class="site-menu-item <?php echo e($path == 'production/dashboard' ? 'active' : ''); ?>">
                            <a class="animsition-link" href="<?php echo e(URL::to('production/dashboard')); ?>">
                                <i class="site-menu-icon md-apps" aria-hidden="true"></i>
                                <span class="site-menu-title">Production Dashboard</span>
                            </a>
                        </li>
                        <li class="site-menu-item" <?php echo e($path == 'reports/{id}' ? 'active' : ''); ?>>
                            <a class="animsition-link"
                                href="<?php echo e(URL::to('reports' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                <i class="site-menu-icon md-collection-text" aria-hidden="true"></i>
                                <span class="site-menu-title">Reports</span>
                            </a>
                        </li>
                        <li class="site-menu-item <?php echo e($path == 'downtime/update/report{id}' ? 'active' : ''); ?>">
                            <a class="animsition-link"
                                href="<?php echo e(URL::to('downtime/update/report' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                <i class="site-menu-icon md-collection-text" aria-hidden="true"></i>
                                <span class="site-menu-title">Allocate Downtime</span>
                            </a>
                        </li>
                        <li
                            class="site-menu-item has-sub <?php echo e($path == 'downtime/update/{id}' || $path == 'records/update/{id}' ? 'active open' : ''); ?>">
                            <a href="javascript:void(0)">
                                <i class="site-menu-icon md-edit" aria-hidden="true"></i>
                                <span class="site-menu-title">Update</span>
                                <span class="site-menu-arrow"></span>
                            </a>
                            <ul class="site-menu-sub">
                                <li class="site-menu-item <?php echo e($path == 'downtime/update/{id}' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('downtime/update' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">Downtimes</span>
                                    </a>
                                </li>
                                <li class="site-menu-item <?php echo e($path == 'records/update/{id}' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('records/update' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">Records</span>
                                    </a>
                                </li>
                                <li
                                    class="site-menu-item <?php echo e($path == 'process-structure/update/{id}' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('process-structure/update' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">Process Structure</span>
                                    </a>
                                </li>
                                <li class="site-menu-item <?php echo e($path == 'records/manual/{id}' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('records/manual' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">Manual Records</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="site-menu-item has-sub <?php echo e($path == 'users' || $path == 'user/add' || $path == 'user/update/{id}' ? 'active open' : ''); ?>">
                            <a href="javascript:void(0)">
                                <i class="site-menu-icon md-accounts-alt" aria-hidden="true"></i>
                                <span class="site-menu-title">Users</span>
                                <span class="site-menu-arrow"></span>
                            </a>
                            <ul class="site-menu-sub">
                                <li class="site-menu-item <?php echo e($path == 'user/add/{id}' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('user/add' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">Add User</span>
                                    </a>
                                </li>
                                <li class="site-menu-item <?php echo e($path == 'users/{id}' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('users' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">All Users</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="site-menu-item has-sub <?php echo e($path == 'machines' || $path == 'machine/add' || $path == 'machine/update/{id}' ? 'active open' : ''); ?>">
                            <a href="javascript:void(0)">
                                <i class="site-menu-icon md-book" aria-hidden="true"></i>
                                <span class="site-menu-title">Machines</span>
                                <span class="site-menu-arrow"></span>
                            </a>
                            <ul class="site-menu-sub">
                                <li class="site-menu-item <?php echo e($path == 'machine/add' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('machine/add' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">Add Machine</span>
                                    </a>
                                </li>
                                <li class="site-menu-item <?php echo e($path == 'machines' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('machines' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">All Machines</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="site-menu-item has-sub <?php echo e($path == 'materials' || $path == 'material/add' || $path == 'material/update/{id}' ? 'active open' : ''); ?>">
                            <a href="javascript:void(0)">
                                <i class="site-menu-icon md-group" aria-hidden="true"></i>
                                <span class="site-menu-title">Material Combinations</span>
                                <span class="site-menu-arrow"></span>
                            </a>
                            <ul class="site-menu-sub">
                                <li class="site-menu-item <?php echo e($path == 'material/add' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('material/add' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">Add Material</span>
                                    </a>
                                </li>
                                <li class="site-menu-item <?php echo e($path == 'materials' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('materials' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">All Materials</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="site-menu-item has-sub <?php echo e($path == 'products' || $path == 'product/add' || $path == 'product/update/{id}' || $path == 'product/all' ? 'active open' : ''); ?>">
                            <a href="javascript:void(0)">
                                <i class="site-menu-icon md-tag" aria-hidden="true"></i>
                                <span class="site-menu-title">Products</span>
                                <span class="site-menu-arrow"></span>
                            </a>
                            <ul class="site-menu-sub">
                                <li class="site-menu-item <?php echo e($path == 'product/add' ? 'active' : ''); ?>">
                                    <a class="animsition-link" href="<?php echo e(URL::to('product/add')); ?>">
                                        <span class="site-menu-title">Add Product</span>
                                    </a>
                                </li>
                                <li class="site-menu-item <?php echo e($path == 'products' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('products' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">All Products</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="site-menu-item has-sub <?php echo e($path == 'production/orders' || $path == 'production/order/add' ? 'active open' : ''); ?>">
                            <a href="javascript:void(0)">
                                <i class="site-menu-icon md-file" aria-hidden="true"></i>
                                <span class="site-menu-title">Production Orders</span>
                                <span class="site-menu-arrow"></span>
                            </a>
                            <ul class="site-menu-sub">
                                <li class="site-menu-item <?php echo e($path == 'production/order/add' ? 'active' : ''); ?>">
                                    <a class="animsition-link" href="<?php echo e(URL::to('production/order/add')); ?>">
                                        <span class="site-menu-title">Add Production Order</span>
                                    </a>
                                </li>
                                <li class="site-menu-item <?php echo e($path == 'production/orders' ? 'active' : ''); ?>">
                                    <a class="animsition-link" href="<?php echo e(URL::to('production/orders')); ?>">
                                        <span class="site-menu-title">All Production Orders</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="site-menu-item has-sub <?php echo e($path == 'companies' || $path == 'company/add' ? 'active open' : ''); ?>">
                            <a href="javascript:void(0)">
                                <i class="site-menu-icon md-square-down" aria-hidden="true"></i>
                                <span class="site-menu-title">Companies</span>
                                <span class="site-menu-arrow"></span>
                            </a>
                            <ul class="site-menu-sub">
                                <li class="site-menu-item <?php echo e($path == 'company/add' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('company/add' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">Add Company</span>
                                    </a>
                                </li>
                                <li class="site-menu-item <?php echo e($path == 'companies' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('companies' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">All Companies</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="site-menu-item has-sub <?php echo e($path == 'business-units' || $path == 'business-unit/add' ? 'active open' : ''); ?>">
                            <a href="javascript:void(0)">
                                <i class="site-menu-icon md-square-down" aria-hidden="true"></i>
                                <span class="site-menu-title">Business Units</span>
                                <span class="site-menu-arrow"></span>
                            </a>
                            <ul class="site-menu-sub">
                                <li class="site-menu-item <?php echo e($path == 'business-unit/add' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('business-unit/add' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">Add Business Unit</span>
                                    </a>
                                </li>
                                <li class="site-menu-item <?php echo e($path == 'business-units' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('business-units' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">All Business Units</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="site-menu-item has-sub <?php echo e($path == 'departments' || $path == 'department/add' ? 'active open' : ''); ?>">
                            <a href="javascript:void(0)">
                                <i class="site-menu-icon md-square-down" aria-hidden="true"></i>
                                <span class="site-menu-title">Departments</span>
                                <span class="site-menu-arrow"></span>
                            </a>
                            <ul class="site-menu-sub">
                                <li class="site-menu-item <?php echo e($path == 'department/add' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('department/add' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">Add Department</span>
                                    </a>
                                </li>
                                <li class="site-menu-item <?php echo e($path == 'departments' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('departments' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">All Departments</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="site-menu-item has-sub <?php echo e($path == 'sections' || $path == 'section/add' ? 'active open' : ''); ?>">
                            <a href="javascript:void(0)">
                                <i class="site-menu-icon md-square-down" aria-hidden="true"></i>
                                <span class="site-menu-title">Sections</span>
                                <span class="site-menu-arrow"></span>
                            </a>
                            <ul class="site-menu-sub">
                                <li class="site-menu-item <?php echo e($path == 'section/add' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('section/add' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">Add Section</span>
                                    </a>
                                </li>
                                <li class="site-menu-item <?php echo e($path == 'sections' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('sections' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">All Sections</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="site-menu-item has-sub <?php echo e($path == 'sleeves' || $path == 'sleeves/add' ? 'active open' : ''); ?>">
                            <a href="javascript:void(0)">
                                <i class="site-menu-icon md-circle-o" aria-hidden="true"></i>
                                <span class="site-menu-title">Sleeves</span>
                                <span class="site-menu-arrow"></span>
                            </a>
                            <ul class="site-menu-sub">
                                <li class="site-menu-item <?php echo e($path == 'sleeves/add' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('sleeves/add' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">Add Sleeve</span>
                                    </a>
                                </li>
                                <li class="site-menu-item <?php echo e($path == 'sleeves' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('sleeves' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">All Sleeves</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- mine code -->
                        <li
                            class="site-menu-item has-sub <?php echo e($path == 'categories' || $path == 'categories/add' ? 'active open' : ''); ?>">
                            <a href="javascript:void(0)">
                                <i class="site-menu-icon md-square-down" aria-hidden="true"></i>
                                <span class="site-menu-title">Error Categories</span>
                                <span class="site-menu-arrow"></span>
                            </a>
                            <ul class="site-menu-sub">
                                <li class="site-menu-item <?php echo e($path == 'categories/add' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('categories/add' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">Add Error Category</span>
                                    </a>
                                </li>
                                <li class="site-menu-item <?php echo e($path == 'categories' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('categories' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">All Error Categories</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="site-menu-item <?php echo e($path == 'circuits/update/numberdays{id}' ? 'active' : ''); ?>">
                            <a class="animsition-link"
                                href="<?php echo e(URL::to('circuits/update/numberdays' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                <i class="site-menu-icon md-collection-text" aria-hidden="true"></i>
                                <span class="site-menu-title">Not Responding Circuts Add Days</span>
                            </a>
                        </li>
                        <!-- end mine code -->
                        <li
                            class="site-menu-item has-sub <?php echo e($path == 'processes' || $path == 'process/add' ? 'active open' : ''); ?>">
                            <a href="javascript:void(0)">
                                <i class="site-menu-icon md-square-down" aria-hidden="true"></i>
                                <span class="site-menu-title">Processes</span>
                                <span class="site-menu-arrow"></span>
                            </a>
                            <ul class="site-menu-sub">
                                <li class="site-menu-item <?php echo e($path == 'process/add' ? 'active' : ''); ?>">
                                    <a class="animsition-link" href="<?php echo e(URL::to('process/add')); ?>">
                                        <span class="site-menu-title">Add Process</span>
                                    </a>
                                </li>
                                <li class="site-menu-item <?php echo e($path == 'processes' ? 'active' : ''); ?>">
                                    <a class="animsition-link" href="<?php echo e(URL::to('processes')); ?>">
                                        <span class="site-menu-title">All Processes</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li
                            class="site-menu-item has-sub <?php echo e($path == 'error-codes' || $path == 'error-code/add' ? 'active open' : ''); ?>">
                            <a href="javascript:void(0)">
                                <i class="site-menu-icon md-alert-triangle" aria-hidden="true"></i>
                                <span class="site-menu-title">Error Codes</span>
                                <span class="site-menu-arrow"></span>
                            </a>
                            <ul class="site-menu-sub">
                                <li class="site-menu-item <?php echo e($path == 'error-code/add' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('error-code/add' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">Add Error Code</span>
                                    </a>
                                </li>
                                <li class="site-menu-item <?php echo e($path == 'error-codes' ? 'active' : ''); ?>">
                                    <a class="animsition-link"
                                        href="<?php echo e(URL::to('error-codes' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>">
                                        <span class="site-menu-title">All Error Codes</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php echo $__env->yieldContent('body'); ?>
    <footer class="site-footer">
        <div class="site-footer-legal">© <?php echo e(date('Y')); ?> <a href="http://www.packages.com.pk"
                target="_blank">Roto eYe.cloud</a></div>
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
    <?php echo $__env->yieldContent('graphFooter'); ?>
    <?php echo $__env->yieldContent('formFooter'); ?>
    <script src="<?php echo e(asset('assets/global/js/Component.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Base.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Config.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/remark/js/Section/Menubar.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/remark/js/Section/Sidebar.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/remark/js/Section/PageAside.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/remark/js/Plugin/menu.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/config/colors.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/remark/js/config/tour.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/remark/js/Site.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/asscrollable.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/slidepanel.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/switchery.js')); ?>"></script>
    <?php echo $__env->yieldContent('footer'); ?>
    <script>
        $('.allowedMachines').on('change', function() {
            var machine_id = $(this).val();
            var url = "<?php echo URL::to('dashboard'); ?>" + "/" + machine_id;
            document.location.href = url;
        })
    </script>
</body>

</html>
<?php /**PATH C:\xampp\htdocs\rotoeye\resources\views/layouts/admin-layout.blade.php ENDPATH**/ ?>