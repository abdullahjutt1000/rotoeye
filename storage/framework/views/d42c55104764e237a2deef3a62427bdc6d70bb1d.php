<?php $__env->startSection('header'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/gauge-js/gauge.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/fonts/material-design/material-design.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/fonts/brand-icons/brand-icons.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/chartist/chartist.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/remark/examples/css/widgets/chart.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/formvalidation/formValidation.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/select2/select2.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/remark/examples/css/forms/advanced.css')); ?>">

    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/ladda/ladda.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/remark/examples/css/uikit/buttons.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/remark/examples/css/uikit/modals.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('global/vendor/nprogress/nprogress.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/remark/examples/css/widgets/chart.css')); ?>">

    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/alertify/alertify.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/notie/notie.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/remark/examples/css/advanced/alertify.css')); ?>">
    <style>
        .customSelect2 {
            z-index: 0 !important;
        }
    </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('body'); ?>
    <div class="page">
        <div class="page-content container-fluid">
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
            <div class="row" data-plugin="matchHeight" data-by-row="true">
                <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="card-group">
                        <div class="card card-block p-0">
                            <div class="vertical-align text-center red-roto white p-20 h-250">
                                <div class="vertical-align-middle">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="counter counter-md text-left">
                                                <div class="counter-label grey-200">Product Number</div>
                                                <div class="counter-number-group">
                                                    <span
                                                        class="counter-number white"><?php echo e($running_job->product->id); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="counter counter-md text-right">
                                                <div class="counter-label grey-200">Job Number</div>
                                                <?php if(Session::get('rights') != 3): ?>
                                                    <div class="counter-number-group">
                                                        <a
                                                            href="<?php echo e(URL::to('select/job' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>"><span
                                                                class="counter-number white"><?php echo e($running_job->id); ?></span></a>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="counter-number white"><?php echo e($running_job->id); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="counter counter-md text-left">
                                                <div class="counter-label grey-200">Product Name</div>
                                                <div class="counter-number-group">
                                                    <span class="counter-number white"
                                                        title="<?php echo e($running_job->product->name); ?>"><?php echo e(substr($running_job->product->name, 0, 30) . ' ...'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-8">
                                            <div class="counter counter-md text-left">
                                                <div class="counter-label grey-200">Material</div>
                                                <div class="counter-number-group">
                                                    <span
                                                        class="counter-number white"><?php echo e($record->process && count($record->process->materialCombination($running_job->product->id)) > 0 ? $record->process->materialCombination($running_job->product->id)[0]->name : ''); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="counter counter-md text-left">
                                                <div class="counter-label grey-200">Color/Adh</div>
                                                <div class="counter-number-group">
                                                    <span class="counter-number white">
                                                        <?php if($record->process && count($record->process->materialCombination($running_job->product->id)) > 0): ?>
                                                            <?php if(
                                                                !isset($record->process->materialCombination($running_job->product->id)[0]->pivot->adhesive) ||
                                                                    $record->process->materialCombination($running_job->product->id)[0]->pivot->adhesive == ''): ?>
                                                                <?php echo e($record->process->materialCombination($running_job->product->id)[0]->pivot->color); ?>

                                                            <?php elseif(
                                                                !isset($record->process->materialCombination($running_job->product->id)[0]->pivot->color) ||
                                                                    $record->process->materialCombination($running_job->product->id)[0]->pivot->color == ''): ?>
                                                                <?php echo e($record->process->materialCombination($running_job->product->id)[0]->pivot->adhesive); ?>

                                                            <?php else: ?>
                                                                <small>N/A</small>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-7">
                                            <div class="counter counter-md text-right">
                                                <div class="counter-label grey-200">
                                                    <a class="white text-" id="see-more" href="#" data-toggle="modal"
                                                        data-target="#exampleModal"><strong><u>See more</u></strong></a>
                                                </div>
                                                <div class="counter-label grey-200">Job Length
                                                    <small>(<?php echo e($record->machine->qty_uom); ?>)</small>
                                                </div>
                                                <div class="counter-number-group">
                                                    <span
                                                        class="counter-number white"><?php echo e(number_format($running_job->job_length)); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card card-block p-0">
                            <div class="vertical-align text-center white p-10 h-250" style="background-color: #fcfcfc"
                                id="meter">
                                <div class="counter-number-group vertical-align-middle">
                                    <div class="gauge" id="Gauge" data-plugin="gauge" data-value="870"
                                        data-max-value="<?php echo e($record->machine->max_speed); ?>" data-stroke-color="#e1e1e1"
                                        style="bottom: 10px;">
                                        <div class="gauge-label"></div>
                                        <div
                                            style="position: absolute;width: 100%;bottom: 1%; color: #000; font-size: 12px">
                                            <?php echo e($record->machine->qty_uom . '/' . $record->machine->time_uom); ?></div>
                                        <canvas width="200" height="150"></canvas>
                                    </div>
                                    <div style="color: #336699; position: relative;bottom: 18px;"><strong
                                            id="meters"></strong> <?php echo e($record->machine->qty_uom); ?></div>
                                    <div style="position: relative;bottom: 245px; left:190px"><span
                                            class="badge badge-danger" id="hardwareStatus"></span></div>
                                    <div style="position: relative;bottom: 282px; right:170px">
                                        <span class="badge" style="color: #336699">Last Updated At</span><br>
                                        <span class="badge" style="background-color: #336699" id="lastUpdated"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if($user->rights == 0): ?>
                            <div class="card card-block p-0">
                                <div class="vertical-align text-center blue-roto white p-20 h-250">
                                    <div class="vertical-align-middle">
                                        <div class="row" style="margin-bottom: -5px">
                                            <div class="col-6">
                                                <div class="counter counter-md text-left">
                                                    <div class="counter-label white">
                                                        <select class="form-control form-control-sm" data-plugin="select2"
                                                            id="allocation-type" name="allocation_type" disabled>
                                                            <option value = "downtime" selected>Downtime</option>
                                                            <option value = "waste">Waste</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <hr>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group form-material">
                                                    <div>
                                                        <div class="radio-custom radio-default radio-inline">
                                                            <input type="radio" id="SingleDowntime" name="inputGender"
                                                                onclick="singleDowntime()" checked />
                                                            <label for="inputBasicMale"
                                                                style="font-size: 12px">Single</label>
                                                        </div>
                                                        <div class="radio-custom radio-default radio-inline">
                                                            <input type="radio" id="MultiDowntime" name="inputGender"
                                                                onclick="multipleDowntime()" />
                                                            <label for="inputBasicFemale"
                                                                style="font-size: 12px">Multiple</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="example round-input-control">
                                                    <div class="input-group">
                                                        <input class="form-control form-control-sm downtime-from"
                                                            type="text" name="downtime-from" placeholder="From"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="example round-input-control">
                                                    <div class="input-group">
                                                        <input class="form-control form-control-sm downtime-to"
                                                            id="downtime-to" type="text" name="downtime-to"
                                                            placeholder="To" readonly>
                                                        <input class="form-control form-control-sm" style="display: none"
                                                            id="waste-meters" type="text" placeholder="Waste Meters"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="example round-input-control" style="margin-top: 0px">
                                                    <div class="input-group">
                                                        <input class="form-control form-control-sm downtimeDescription"
                                                            type="text" id="downtime-description"
                                                            name="downtime-description"
                                                            placeholder="Downtime Description">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-8">
                                                <div class="input-group">
                                                    <div id="waste-codes" style="display: none">
                                                        <select class="form-control form-control-sm" data-plugin="select2"
                                                            id="selectWasteError" name="waste-error">

                                                            <?php $__currentLoopData = $rotoErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <?php if($error->category == 'Waste'): ?>
                                                                    <option data-category="<?php echo e($error->category); ?>"
                                                                        value="<?php echo e($error->id); ?>">
                                                                        <?php echo e($error->id . ' - ' . $error->name); ?></option>
                                                                <?php endif; ?>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </select>
                                                    </div>
                                                    <div id="downtime-codes">
                                                        <select class="form-control form-control-sm" data-plugin="select2"
                                                            id="selectError" name="error">
                                                            <?php $__currentLoopData = $rotoErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <?php if($error->category != 'Waste'): ?>
                                                                    <option data-category="<?php echo e($error->category); ?>"
                                                                        value="<?php echo e($error->id); ?>">
                                                                        <?php echo e($error->id . ' - ' . $error->name); ?></option>
                                                                <?php endif; ?>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <button type="button" class="btn btn-block btn-danger"
                                                    id="allocateDowntime">Done</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card card-block p-0">
                                <div class="vertical-align text-center blue-roto white p-20 h-250">
                                    <div class="vertical-align-middle">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="counter counter-md text-left">
                                                    <?php if(!$machine->app_type): ?>
                                                        <a class="float-right text-light text-uppercase"
                                                            href="<?php echo e(URL::to('/dashboard/manual' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>"><u>Rotoeye
                                                                manual</u></a>
                                                    <?php endif; ?>
                                                    <div class="counter-label grey-200">Machine</div>
                                                    <div class="counter-number-group">
                                                        <span
                                                            class="counter-number white"><?php echo e($record->machine->name . ' - ' . $record->machine->sap_code); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="counter counter-md text-left">
                                                    <div class="counter-label grey-200">Business Unit</div>
                                                    <div class="counter-number-group">
                                                        <span
                                                            class="counter-number white"><?php echo e($record->machine->section->department->businessUnit->business_unit_name); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="counter counter-md text-left">
                                                    <div class="counter-label grey-200">Department</div>
                                                    <div class="counter-number-group">
                                                        <span
                                                            class="counter-number white"><?php echo e($record->machine->section->department->name); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="counter counter-md text-left">
                                                    <div class="counter-label grey-200">Max Speed</div>
                                                    <div class="counter-number-group">
                                                        <span
                                                            class="counter-number white"><?php echo e($record->machine->max_speed . ' ' . $record->machine->qty_uom . '/' . $record->machine->time_uom); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="counter counter-md text-left">
                                                    <div class="counter-label grey-200">Waste Speed</div>
                                                    <div class="counter-number-group">
                                                        <span
                                                            class="counter-number white"><?php echo e($record->machine->waste_speed . ' ' . $record->machine->qty_uom . '/' . $record->machine->time_uom); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="counter counter-md text-left">
                                                    <div class="counter-label grey-200">Operator Name</div>
                                                    <div class="counter-number-group">
                                                        <span
                                                            class="counter-number white"><?php echo e($record->user->name); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xxl-12 col-md-12">
                    <div class="card card-block p-0">
                        <div class="text-center white p-30">
                            <div class="h-200" id="chartContainer"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger  ">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title text-white ">Production Detail</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xl-6 form-group">
                                <label>Slitted Reel Width</label>
                                <input type="text" class="form-control" name="slitted_reel_width"
                                    value="<?php echo e(isset($running_job->product->slitted_reel_width) ? $running_job->product->slitted_reel_width * 1000 : ''); ?>"
                                    placeholder="Slitted Reel Width(mm)" disabled>
                            </div>
                            <div class="col-xl-6 form-group">
                                <label>No. of UP's</label>
                                <input type="text" class="form-control" name="ups"
                                    value="<?php echo e(isset($running_job->product->ups) ? $running_job->product->ups : ''); ?>"
                                    placeholder="No. of UP's" disabled>
                            </div>

                            <div class="col-xl-6 form-group">
                                <label>COL</label>
                                <input type="text" class="form-control" name="col"
                                    value="<?php echo e(isset($running_job->product->col) ? $running_job->product->col * 1000 : ''); ?>"
                                    placeholder="COL" disabled>
                            </div>

                            <div class="col-xl-6 form-group">
                                <label>GSM(Kg)</label>
                                <input type="text" class="form-control" name="gsm"
                                    value="<?php echo e(isset($running_job->product->gsm) ? $running_job->product->gsm * 1000 : ''); ?>"
                                    placeholder="GSM(mm)" disabled>
                            </div>

                            <div class="col-xl-6 form-group">
                                <label>Trim Width</label>
                                <input type="text" class="form-control" name="trim_width"
                                    value="<?php echo e(isset($running_job->product->trim_width) ? $running_job->product->trim_width * 1000 : ''); ?>"
                                    placeholder="Trim Width(mm)" disabled>
                            </div>

                            <div class="col-xl-6 form-group">
                                <label>Thickness</label>
                                <input type="text" class="form-control" name="thickness"
                                    value="<?php echo e(isset($running_job->product->thickness) ? $running_job->product->thickness * 1000000 : ''); ?>"
                                    placeholder="Thickness(mm)" disabled>
                            </div>

                            <div class="col-xl-6 form-group">
                                <label>Density</label>
                                <input type="text" class="form-control" name="density"
                                    value="<?php echo e(isset($running_job->product->density) ? $running_job->product->density : ''); ?>"
                                    placeholder="Density(kg/m3)" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"></div>
                </div>
            </div>
        </div>
        <div class="modal fade " id="examplePositionSidebar" aria-hidden="true" aria-labelledby="examplePositionSidebar"
            role="dialog" tabindex="-1">
            <div class="modal-dialog modal-simple modal-sidebar modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title">OH! Session Expired</h4>
                    </div>
                    <div class="modal-body">
                        <small>Hey <strong><?php echo e($user->name); ?></strong>, your login session has expired for machine
                            <strong><?php echo e($machine->name . ' - ' . $machine->sap_code); ?></strong>. Please login again to
                            continue.</small>
                        <hr>
                        <form action="<?php echo e(URL::to('login')); ?>" method="post" id="loginForm" name="loginForm"
                            class="mt-50">

                            <div class="form-group form-material floating" data-plugin="formMaterial">
                                <input type="text" class="form-control" name="username" value="<?php echo e($user->login); ?>"
                                    autofocus required="required" id="userName" />
                                <label class="floating-label">Employee ID</label>
                            </div>
                            <div class="form-group form-material floating" data-plugin="formMaterial">
                                <input type="password" class="form-control" name="password" required="required" />
                                <label class="floating-label">Password</label>
                            </div>
                            <div class="form-group form-material floating" data-plugin="formMaterial"
                                id="machineSelection">
                                <select class="form-control" id="selectMachine" data-plugin="select2" name="machine">
                                    <option value="<?php echo e($machine->id); ?>" selected>
                                        <?php echo e($machine->name . ' - ' . $machine->sap_code); ?></option>
                                </select>
                            </div>
                            <div class="form-group form-material floating" hidden>
                                <input type="text" id="ipAddress" name="ipAddress">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm btn-block btn-lg mt-20"
                                id="submitButton">Sign in</button>
                            <a class="btn btn-success btn-sm btn-block btn-lg" href="<?php echo e(URL::to('login')); ?>">Go To Login
                                Page</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('footer'); ?>
    <script src="<?php echo e(asset('assets/global/vendor/sparkline/jquery.sparkline.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/chartist/chartist.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/matchheight/jquery.matchHeight-min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/gauge-js/gauge.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/gauge.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/remark/examples/js/charts/gauges.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/matchheight.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/jquery-placeholder/jquery.placeholder.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/jquery-placeholder.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/global/vendor/select2/select2.full.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/bootstrap-select/bootstrap-select.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/select2.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/bootstrap-select.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/global/vendor/asprogress/jquery-asProgress.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/jquery-appear/jquery.appear.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/nprogress/nprogress.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/jquery-appear.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/nprogress.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/global/vendor/alertify/alertify.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/notie/notie.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/alertify.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/notie-js.js')); ?>"></script>

    <script>
        $(document).ready(function() {
            var container = $('.removeIndex').closest('div').find('.select2-container');
            container.addClass('customSelect2');
        });
        window.onload = function() {
            var maximumGraphSpeed = '<?php echo $record->machine->max_speed; ?>';
            var waste_speed = '<?php echo $record->machine->waste_speed; ?>';
            var dps = [];
            var meeter_from = 0;
            <?php $__currentLoopData = $graphRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $graphRecord): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                dps.push({
                    x: new Date('<?php echo e($graphRecord->run_date_time); ?>'),
                    y: parseInt('<?php echo e($graphRecord->speed); ?>'),
                    hidden: <?php echo e($graphRecord->length); ?>

                });
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            function onClick(e) {
                if ($('#allocation-type').val() == 'waste') {
                    if ($('.downtime-from').is(':focus')) {
                        for (var i = 0; i < dps.length; i++) {
                            if (dps[i].x == e.dataPoint.x) {
                                if (dps[i].y >= waste_speed) {
                                    for (var j = i; j > 0; j--) {
                                        if (dps[j].y >= waste_speed) {
                                            $('.downtime-from').val(dps[j].x.toLocaleTimeString() + ' ' + dps[j].x
                                                .toLocaleDateString());
                                            meeter_from = e.dataSeries.dataPoints[e.dataPointIndex - 1].hidden;
                                            break;
                                        }
                                    }
                                } else {
                                    for (var j = i + 1; j < dps.length; j++) {
                                        if (dps[j].y >= waste_speed) {
                                            $('.downtime-from').val(dps[j].x.toLocaleTimeString() + ' ' + dps[j].x
                                                .toLocaleDateString());
                                            meeter_from = e.dataSeries.dataPoints[e.dataPointIndex - 1].hidden;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    } else if ($('#waste-meters').is(':focus')) {
                        for (var i = 0; i < dps.length; i++) {
                            if (dps[i].x == e.dataPoint.x) {
                                if (dps[i].y >= waste_speed) {
                                    for (var j = i; j < dps.length; j++) {
                                        if (dps[j].y >= waste_speed) {
                                            if (dps[j].hidden - meeter_from >= 0) {
                                                $('.downtime-to').val(dps[j].x.toLocaleTimeString() + ' ' + dps[j].x
                                                    .toLocaleDateString());
                                                $('#waste-meters').val(dps[j].hidden - meeter_from);
                                                break;
                                            }
                                        } else {
                                            if (dps[j].hidden - meeter_from >= 0) {
                                                $('.downtime-to').val(dps[j].x.toLocaleTimeString() + ' ' + dps[j].x
                                                    .toLocaleDateString());
                                                $('#waste-meters').val(dps[j].hidden - meeter_from);
                                                break;
                                            }
                                        }
                                    }
                                } else {
                                    for (var j = i - 1; j < dps.length; j--) {
                                        if (dps[j].y >= waste_speed) {
                                            if (dps[j].hidden - meeter_from >= 0) {
                                                $('.downtime-to').val(dps[j].x.toLocaleTimeString() + ' ' + dps[j].x
                                                    .toLocaleDateString());
                                                $('#waste-meters').val(dps[j].hidden - meeter_from);
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if ($('.downtime-from').is(':focus')) {
                        for (var i = 0; i < dps.length; i++) {
                            if (dps[i].x == e.dataPoint.x) {
                                if (dps[i].y < waste_speed) {
                                    for (var j = i; j > 0; j--) {
                                        if (dps[j].y > waste_speed) {
                                            $('.downtime-from').val(dps[j + 1].x.toLocaleTimeString() + ' ' + dps[j + 1]
                                                .x.toLocaleDateString());
                                            break;
                                        }
                                    }
                                } else {
                                    for (var j = i + 1; j < dps.length; j++) {
                                        if (dps[j].y < waste_speed) {
                                            $('.downtime-from').val(dps[j].x.toLocaleTimeString() + ' ' + dps[j].x
                                                .toLocaleDateString());
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    } else if ($('.downtime-to').is(':focus')) {
                        for (var i = 0; i < dps.length; i++) {
                            if (dps[i].x == e.dataPoint.x) {
                                if (dps[i].y < waste_speed) {
                                    for (var j = i; j < dps.length; j++) {
                                        if (dps[j].y > waste_speed) {
                                            $('.downtime-to').val(dps[j - 1].x.toLocaleTimeString() + ' ' + dps[j - 1].x
                                                .toLocaleDateString());
                                            break;
                                        } else {
                                            $('.downtime-to').val(dps[j].x.toLocaleTimeString() + ' ' + dps[j].x
                                                .toLocaleDateString());
                                        }
                                    }
                                } else {
                                    for (var j = i - 1; j < dps.length; j--) {
                                        if (dps[j].y < waste_speed) {
                                            $('.downtime-to').val(dps[j].x.toLocaleTimeString() + ' ' + dps[j].x
                                                .toLocaleDateString());
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

            }
            var chart = new CanvasJS.Chart("chartContainer", {
                interactivityEnabled: true,
                exportEnabled: true,
                zoomEnabled: true,
                theme: 'light2',
                toolTip: {
                    enabled: true, //disable here
                    animationEnabled: false, //disable here
                    contentFormatter: function(e) {
                        var content = " ";
                        for (var i = 0; i < e.entries.length; i++) {
                            content += "Speed: " + "<strong>" + e.entries[i].dataPoint.y + "</strong><br>" +
                                "Time: " + "<strong>" + e.entries[i].dataPoint.x.toLocaleTimeString() +
                                "</strong><br>" + "Date: " + "<strong>" + e.entries[i].dataPoint.x
                                .toLocaleDateString() + "</strong>";
                            content += "<br/>";
                            if ($('.downtime-from').val() != '' && $('.downtime-from').val() != null) {
                                if ($('#allocation-type').val() == 'waste') {
                                    var downtimeFrom = $('.downtime-from').val();
                                    var downtimeTo = e.entries[i].dataPoint.x.toLocaleDateString() + ' ' + e
                                        .entries[i].dataPoint.x.toLocaleTimeString();
                                    if (parseInt(new Date(downtimeTo).getTime() / 1000) >= parseInt(
                                            new Date(downtimeFrom).getTime() / 1000)) {
                                        if (e.entries[i].dataPoint.hidden - meeter_from >= 0) {
                                            let diff = e.entries[i].dataPoint.hidden - meeter_from;
                                            content += "Meters: " + "<strong>" + diff + "<strong>";
                                            content += "<br/>"
                                        } else {
                                            content += "<strong>Job Change <strong>";
                                            content += "<br/>"
                                        }
                                    } else {
                                        content += "<strong>Invalid Selection<strong>";
                                        content += "<br/>"
                                    }
                                }
                            }
                        }
                        return content;
                    }
                },
                axisX: {
                    title: "Time",
                    valueFormatString: "H:mm"

                },
                axisY: {
                    includeZero: false,
                    title: "speed",
                    maximum: maximumGraphSpeed,
                    minimum: 0,
                    interval: parseInt('<?php echo $record->machine->max_speed; ?>') / 4,
                    stripLines: [{
                        value: parseInt('<?php echo $record->machine->waste_speed; ?>'),
                        label: "Waste Speed"
                    }]
                },
                data: [{
                    type: "line",
                    lineThickness: 3,
                    click: onClick,
                    dataPoints: dps
                }]
            });
            var oldDate = "";
            var oldSpeed;
            var updateInterval = 10000;
            var graph_span = parseInt('<?php echo $record->machine->graph_span; ?>') + 10;
            var dataLength = (graph_span * 60 * 60) / 10;
            var updateChart = function(count) {
                count = count || 1;
                $.ajax({
                    url: '<?php echo URL::to('get/record'); ?>',
                    method: 'POST',
                    async: 'FALSE',
                    data: {
                        machine: "<?php echo \Illuminate\Support\Facades\Crypt::encrypt($machine->id); ?>"
                    },
                    statusCode: {
                        //getting the latest record from the database
                        200: function(response) {
                            var responsee = JSON.parse(response);
                            console.log(responsee);
                            //pushing the new record in the chart
                            dps.push({
                                x: new Date(responsee.record.run_date_time),
                                y: responsee.record.speed,
                                hidden: responsee.record.length
                            });
                            if (dps.length > dataLength) {
                                dps.shift();
                            }
                            chart.render();
                            //storing old record
                            oldDate = responsee.record.run_date_time;
                            oldSpeed = responsee.record.speed;

                            //setting the speed to the speed gauge
                            var dynamicGauge = $("#Gauge").data('gauge');
                            var gauge = document.getElementById('Gauge');
                            var maxSpeed = $('.gauge').data('max-value');

                            var options = {

                            };

                            //setting the speed value in the gauge
                            if (responsee.record.speed <= maxSpeed) {
                                //conditional setting of gauge if old record is equal to new record
                                if (responsee.record.speed == 0) {
                                    dynamicGauge.setOptions(options).set(1);
                                } else if (oldSpeed - responsee.record.speed == 0) {
                                    dynamicGauge.setOptions(options).set(responsee.record.speed - 1);
                                }
                                dynamicGauge.setOptions(options).set(responsee.record.speed);
                            } else {
                                $('.gauge-label').text(responsee.record.speed);
                                $('.gauge-label').css('color', '#ed1b23 ');
                                maximumGraphSpeed = responsee.record.speed;
                                chart.options.axisY = {
                                    includeZero: false,
                                    title: "speed",
                                    maximum: maximumGraphSpeed,
                                    minimum: 0,
                                    interval: parseInt('<?php echo $record->machine->max_speed; ?>') / 4,
                                    stripLines: [{
                                        value: parseInt('<?php echo $record->machine->waste_speed; ?>'),
                                        label: "Waste Speed"
                                    }]
                                };
                                chart.render();
                            }

                            $('#meters').text(parseInt(responsee.record.length).toLocaleString());
                            if (responsee.status == 'Live') {
                                $('#hardwareStatus').html('Live');
                                $('#hardwareStatus').removeClass('bg-danger');
                                $('#hardwareStatus').addClass('bg-success');
                            }
                            if (responsee.status == 'Not Live') {
                                $('#hardwareStatus').html('Not Live');
                                $('#hardwareStatus').removeClass('bg-success');
                                $('#hardwareStatus').addClass('bg-danger');
                            }
                            $('#lastUpdated').html(responsee.lastUpdatedDate + '<br>' + responsee
                                .lastUpdatedTime);
                        },
                        500: function(response) {

                            gauge.setAttribute('data-max-value', responsee.record.speed);
                            dynamicGauge.setOptions(options).set(responsee.record.speed);
                        },
                        505: function(response) {
                            $('#examplePositionSidebar').modal('show');
                        }
                    }
                });
            };
            //interval call to get the record aftet specied time
            updateChart(dataLength);
            if (!$('#examplePositionSidebar').hasClass('in')) {
                setInterval(function() {
                    updateChart()
                }, updateInterval);
            }
            var result = new Array;

        };

        function changeJob() {
            var machine_id = '<?php echo $record->machine->id; ?>';
            var job_id = $('#selectJob').val();
            $.ajax({
                url: '<?php echo URL::to('change/job'); ?>',
                method: 'POST',
                async: 'FASLE',
                data: {
                    machine_id: machine_id,
                    job_id: job_id
                },
                statusCode: {
                    //getting the latest record from the database
                    200: function(response) {
                        window.location.reload();
                    },
                    500: function(response) {

                    }
                }
            });
        }

        function changeUser() {
            var machine_id = '<?php echo $record->machine->id; ?>';
            var user_name = $('#inputUserNameOne').val();
            var password = $('#inputPasswordOne').val();

            $.ajax({
                url: '<?php echo URL::to('change/user'); ?>',
                method: 'GET',
                async: 'FALSE',
                data: {
                    machine_id: machine_id,
                    user_name: user_name,
                    password: password
                },
                statusCode: {
                    200: function(response) {
                        window.location.reload();
                    },
                    500: function(response) {}
                }
            });
        }
    </script>
    <script>
        $('#allocation-type').change(function() {
            $('#SingleDowntime').prop("checked", true);
            $('.downtime-from').val(null);
            $('.downtime-to').val(null);
            if ($('#allocation-type').val() == 'waste') {
                $('#downtime-description').attr("placeholder", "Waste Description").placeholder();
                $('#waste-codes').show();
                $('#downtime-codes').hide();
                $('.downtime-to').hide();
                $('#waste-meters').show();
                $('#waste-meters').val(null);
            } else {
                $('#downtime-description').attr("placeholder", "Downtime Description").placeholder();
                $('#waste-codes').hide();
                $('#downtime-codes').show();
                $('.downtime-to').show();
                $('#downtime-to').replaceWith(
                    '<input class="form-control form-control-sm downtime-to" id="downtime-to" type="text" name="downtime-to" placeholder="To" readonly>'
                );
                $('#waste-meters').hide();
                $('#waste-meters').val(null);
            }
        });
        $('#allocateDowntime').click(function() {
            var downtimeFrom = $('.downtime-from').val();
            var downtimeTo = $('.downtime-to').val();
            var downtimeID = $('#selectError').val();
            var description = $('.downtimeDescription').val();
            $.ajax({
                url: '<?php echo URL::to('allocate/downtime'); ?>',
                method: 'GET',
                async: 'FASLE',
                data: {
                    downtimeTo: downtimeTo,
                    downtimeFrom: downtimeFrom,
                    downtimeID: downtimeID,
                    downtimeDescription: description,
                    machine_id: '<?php echo $record->machine->id; ?>'
                },
                statusCode: {
                    200: function(response) {
                        var responsee = JSON.parse(response);
                        alert('Allocated');
                    },
                    500: function(response) {

                    }
                }
            });
            if ($('#MultiDowntime').prop('checked')) {
                // $('.downtime-from').val(downtimeTo);
                $('.downtime-from').val($('.downtime-to option:selected').next().val());
                $('.downtime-to').val($('.downtime-to option:last-child').val());
                multipleDowntime();
            } else {
                $('.downtime-from').val('');
                $('.downtime-to').val('');
                $('.downtimeDescription').val('');
            }
        })

        function singleDowntime() {
            var downtimeTo = $('.downtime-to option:last-child').val();
            $('#downtime-to').replaceWith(
                '<input class="form-control form-control-sm downtime-to" id="downtime-to" type="text" name="downtime-to" placeholder="To" readonly>'
            );
            $('.downtime-to').val(downtimeTo);
            if ($('#allocation-type').val() == 'waste') {
                $('#waste-meters').show();
                $('.downtime-to').hide();
            }
        }

        function multipleDowntime() {
            var downtimeFrom = $('.downtime-from').val();
            var downtimeTo = $('.downtime-to').val();
            if ($('#allocation-type').val() == 'waste') {
                $('#waste-meters').hide();
            }
            if (downtimeFrom != '' && downtimeTo != '') {
                if ($('#MultiDowntime').prop('checked')) {
                    $('#downtime-to').replaceWith(
                        '<select class="form-control form-control-sm downtime-to" id="downtime-to" name="downtime-to"></select>'
                    );
                    $.ajax({
                        url: '<?php echo URL::to('get/multiple/time'); ?>',
                        method: 'GET',
                        async: 'FASLE',
                        data: {
                            downtimeTo: downtimeTo,
                            downtimeFrom: downtimeFrom,
                            machine_id: '<?php echo $machine->id; ?>'
                        },
                        statusCode: {
                            200: function(response) {
                                var responsee = JSON.parse(response);
                                var arr = responsee.times;

                                if ($('#allocation-type').val() == 'waste') {
                                    for (var i = 0; i < arr.length; i++) {
                                        $('#downtime-to').append(new Option(arr[i].length, new Date(arr[i].time)
                                            .toLocaleTimeString() + ' ' + new Date(arr[i].time)
                                            .toLocaleDateString(), new Date(arr[i].time)
                                            .toLocaleTimeString() + ' ' + new Date(arr[i].time)
                                            .toLocaleDateString()))
                                    }
                                } else {
                                    for (var i = 0; i < arr.length; i++) {
                                        $('#downtime-to').append(new Option(new Date(arr[i].time)
                                            .toLocaleTimeString() + ' ' + new Date(arr[i].time)
                                            .toLocaleDateString(), new Date(arr[i].time)
                                            .toLocaleTimeString() + ' ' + new Date(arr[i].time)
                                            .toLocaleDateString()))
                                    }
                                }
                            },
                            500: function(response) {

                            }
                        }
                    });
                }
            } else {
                $('#SingleDowntime').click();
                alert('Please Enter Start Time and End Time');
            }
        }
    </script>
    <?php if(!$machine->app_type): ?>
        <script>
            $('.manual-hr').change(function() {
                var date = new Date($('.manual-downtime-from').val());
                date.setTime(date.getTime() + parseInt($('.manual-hr').val() * 60 * 60 * 1000));
                var formated_date = date.getFullYear() + '-' + (date.getMonth() + 1).toString().padStart(2, '0') + '-' +
                    date.getDate().toString().padStart(2, '0') + ' ' + date.getHours().toString().padStart(2, '0') +
                    ':' + date.getMinutes().toString().padStart(2, '0') + ':' + date.getSeconds().toString().padStart(2,
                        '0');
                $('.manual-downtime-to').val(formated_date).change();
            })

            function getLatestRecord(machine_id) {
                $.ajax({
                    url: '<?php echo URL::to('/latest/record/'); ?>/' + machine_id,
                    method: 'GET',
                    async: 'FASLE',
                    statusCode: {
                        200: function(response) {
                            $('.manual-downtime-from').val(response['latest_record'][0].run_date_time);
                        },
                        500: function(response) {
                            console.log(response)
                        }
                    }
                });
            }
            getLatestRecord(<?php echo $machine->id; ?>)

            $('#manualAllocateDowntime').click(function() {
                var manualDowntimeFrom = $('.manual-downtime-from').val();
                var manualDowntimeTo = $('.manual-downtime-to').val();
                var manualLength = $('.manual-length').val();
                var manualDowntimeID = $('#manualSelectError').val();
                var manualDescription = $('.manualDowntimeDescription').val();

                $.ajax({
                    url: '<?php echo URL::to('/manual/allocate/downtime'); ?>',
                    method: 'POST',
                    async: 'FASLE',
                    data: {
                        downtimeTo: manualDowntimeTo,
                        downtimeFrom: manualDowntimeFrom,
                        length: manualLength,
                        downtimeID: manualDowntimeID,
                        downtimeDescription: manualDescription,
                        machine_id: '<?php echo $record->machine->id; ?>'
                    },
                    statusCode: {
                        200: function(response) {
                            $('.manual-downtime-from').val(response['latest_record']);
                            $('.manual-downtime-to').val('');
                            $('.manual-length').val('');
                            $('.manual-hr').val('');
                            $('.manualDowntimeDescription').val('');
                            updateChart(dataLength);
                        },
                        500: function(response) {
                            alert("Error: " + response.responseJSON.error);
                        }
                    }
                });
            });
        </script>
    <?php endif; ?>

    <script src="<?php echo e(asset('assets/remark/custom/canvas.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.' . $layout, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\rotoeye\resources\views/roto/dashboard.blade.php ENDPATH**/ ?>