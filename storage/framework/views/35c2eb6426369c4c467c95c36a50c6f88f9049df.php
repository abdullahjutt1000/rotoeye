<?php $__env->startSection('header'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/datatables.net-bs4/dataTables.bootstrap4.css')); ?>">
    <link rel="stylesheet"
        href="<?php echo e(asset('assets/global/vendor/datatables.net-fixedheader-bs4/dataTables.fixedheader.bootstrap4.css')); ?>">
    <link rel="stylesheet"
        href="<?php echo e(asset('assets/global/vendor/datatables.net-fixedcolumns-bs4/dataTables.fixedcolumns.bootstrap4.css')); ?>">
    <link rel="stylesheet"
        href="<?php echo e(asset('assets/global/vendor/datatables.net-rowgroup-bs4/dataTables.rowgroup.bootstrap4.css')); ?>">
    <link rel="stylesheet"
        href="<?php echo e(asset('assets/global/vendor/datatables.net-scroller-bs4/dataTables.scroller.bootstrap4.css')); ?>">
    <link rel="stylesheet"
        href="<?php echo e(asset('assets/global/vendor/datatables.net-select-bs4/dataTables.select.bootstrap4.css')); ?>">
    <link rel="stylesheet"
        href="<?php echo e(asset('assets/global/vendor/datatables.net-responsive-bs4/dataTables.responsive.bootstrap4.css')); ?>">
    <link rel="stylesheet"
        href="<?php echo e(asset('assets/global/vendor/datatables.net-buttons-bs4/dataTables.buttons.bootstrap4.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/remark/examples/css/tables/datatable.css')); ?>">
    <!-- Fonts -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/fonts/font-awesome/font-awesome.css')); ?>">
<?php $__env->stopSection(); ?>
<?php $__env->startSection('body'); ?>
    <div class="page">
        <div class="page-header">
            <h1 class="page-title">All Companies</h1>
            <ol class="breadcrumb">
                All companies currently available in Roto Eye
            </ol>
        </div>
        <div class="page-content">
            <?php if(Session::has('success')): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    SUCCESS : <?php echo e(Session::get('success')); ?>

                </div>
            <?php endif; ?>
            <?php if(count($errors) > 0): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <p>Please fix the following issues to continue</p>
                    <ul class="error">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if(Session::has('error')): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    ERROR : <?php echo Session::get('error'); ?>

                </div>
            <?php endif; ?>
            <div class="panel">
                <header class="panel-heading">
                    <h3 class="panel-title">Companies</h3>
                </header>
                <div class="panel-body">
                    <table class="table table-hover dataTable table-striped w-full" id="exampleTableSearch">
                        <thead>
                            <tr>
                                <th>Company ID</th>
                                <th>Company Name</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>Country</th>
                                <th>Business Units</th>
                                <th>Shifts</th>
                                
                                <th>Businessunits_shifts</th>
                                
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Company ID</th>
                                <th>Company Name</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>Country</th>
                                <th>Business Units</th>
                                <th>Shifts</th>
                                
                                <th>Businessunits_shifts</th>
                                
                                <th>Delete</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><a
                                            href="<?php echo e(URL::to('company/update' . '/' . $company->id . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>"><?php echo e($company->id); ?></a>
                                    </td>
                                    <td><?php echo e($company->name); ?></td>
                                    <td><?php echo e($company->address); ?></td>
                                    <td><?php echo e($company->city); ?></td>
                                    <td><?php echo e($company->country); ?></td>
                                    <td>
                                        <ul style="list-style: decimal">
                                            <?php $__currentLoopData = $company->businessUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $businessUnit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li><?php echo e($businessUnit->business_unit_name); ?></li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    </td>

                                    

                                    <td>
                                        

                                        <ul style="list-style: none; padding-left:0px">
                                            
                                            <?php $__currentLoopData = $company->shifts->where('business_unit_id', null); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li><?php echo e($shift->shift_number . ' / ' . date('H:i', strtotime(date('00:00') . ' + ' . $shift->min_started . ' minute')) . ' to ' . date('H:i', strtotime(date('00:00') . ' + ' . $shift->min_ended . ' minute'))); ?>

                                                </li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            

                                        </ul>

                                    </td>

                                    <td>
                                        <ul style="list-style: none">

                                            <?php $__currentLoopData = $company->businessUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $businessUnit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li><?php echo e($businessUnit->business_unit_name); ?></li>
                                                <ul style="list-style: none; padding-left:0px">
                                                    
                                                    <?php $__currentLoopData = $company->shifts->where('business_unit_id', $businessUnit->id); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <li><?php echo e($shift->shift_number . ' / ' . date('H:i', strtotime(date('00:00') . ' + ' . $shift->min_started . ' minute')) . ' to ' . date('H:i', strtotime(date('00:00') . ' + ' . $shift->min_ended . ' minute'))); ?>

                                                        </li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    

                                                </ul>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                        </ul>
                                    </td>

                                    

                                    <td><a href="<?php echo e(URL::to('company/delete' . '/' . $company->id)); ?>"><i
                                                class="md-delete"></i></a></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('footer'); ?>
    <script src="<?php echo e(asset('assets/global/vendor/datatables.net/jquery.dataTables.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/datatables.net-bs4/dataTables.bootstrap4.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/datatables.net-fixedheader/dataTables.fixedHeader.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/datatables.net-fixedcolumns/dataTables.fixedColumns.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/datatables.net-rowgroup/dataTables.rowGroup.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/datatables.net-scroller/dataTables.scroller.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/datatables.net-responsive/dataTables.responsive.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/datatables.net-responsive-bs4/responsive.bootstrap4.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/datatables.net-buttons/dataTables.buttons.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/datatables.net-buttons/buttons.html5.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/datatables.net-buttons/buttons.flash.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/datatables.net-buttons/buttons.print.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/datatables.net-buttons/buttons.colVis.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/datatables.net-buttons-bs4/buttons.bootstrap4.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/asrange/jquery-asRange.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/bootbox/bootbox.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/datatables.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/remark/examples/js/tables/datatable.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/remark/examples/js/uikit/icon.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.' . $layout, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\rotoeye\resources\views/roto/companies.blade.php ENDPATH**/ ?>