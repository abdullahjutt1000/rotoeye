<?php $__env->startSection('header'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/fonts/material-design/material-design.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/fonts/brand-icons/brand-icons.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/remark/examples/css/uikit/buttons.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/datatables.net-bs4/dataTables.bootstrap4.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/remark/examples/css/tables/datatable.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/fonts/font-awesome/font-awesome.css')); ?>">
    <!-- mine code -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/select2/select2.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.css')); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <!-- mine code  -->

    <style>
        .panel {
            page-break-before: always;
        }

        table caption {
            flex-wrap: nowrap !important;
        }

        table caption button {
            margin-right: 10px;
        }

        #exampleMorrisBar svg {
            height: 500px;
        }

        #exampleMorrisBar {
            position: relative;
        }
    </style>
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/morris/morris.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/table-export/tableexport.css')); ?>">
<?php $__env->stopSection(); ?>
<?php $__env->startSection('body'); ?>
    <div class="page">
        <div class="page-header">
            <div class="page-header-actions" style="left: 30px">
                <button id="metaData" type="button" class="btn btn-sm btn-icon btn-primary btn-round"
                    data-toggle="tooltip" data-original-title="Print" onclick="javascript:printContent('print-panel');">
                    <i class="icon md-print" aria-hidden="true"></i> Print
                </button>
            </div>
        </div>
        <div class="page-content">
            <div class="row" data-plugin="matchHeight" data-by-row="true">
                <div class="col-md-12">
                    <div class="panel" id="print-panel">
                        <header class="panel-heading">
                            <h3 class="panel-title">
                                <strong>Availability Loss</strong>
                                <?php if($shift[0] == 'All-Day'): ?>
                                    <small><?php echo e(date('M d, Y', strtotime($from)) . ' to ' . date('M d, Y', strtotime($to))); ?></small><br>
                                    <small><?php echo e($machine->sap_code . ' - ' . $machine->name . ', ' . $machine->section->name . ', Shift '); ?><?php echo e(count($shift) == 1 ? $shift[0] : ''); ?></small><br>
                                <?php else: ?>
                                    <small><?php echo e(date('M d, Y', strtotime($from))); ?></small><br>
                                    <small><?php echo e($machine->sap_code . ' - ' . $machine->name . ', ' . $machine->section->name . ', Shift '); ?><?php echo e(count($shift) == 1 ? \App\Models\Shift::find($shift[0])->shift_number : \App\Models\Shift::find($shift[0])->shift_number . ' to ' . \App\Models\Shift::find($shift[count($shift) - 1])->shift_number); ?></small><br>
                                <?php endif; ?>
                            </h3>
                        </header>
                        <div class="panel-body">
                            <div class="example-wrap m-md-0">
                                <div class="example">
                                    <div id="exampleMorrisBar"></div>
                                    

                                    <canvas id="myChart"></canvas>

                                    

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Mine code starts -->
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="">
                                <div class="">
                                    <div class="">
                                        <h4 class="example-title">Losses Reports</h4>
                                        <div class="example">
                                            <form
                                                action="<?php echo e(URL::to('losses/report' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id))); ?>"
                                                method="post" enctype="multipart/form-data" autocomplete="off">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="input-group">
                                                            <span class="input-group-addon">
                                                                <i class="icon md-calendar" aria-hidden="true"></i>
                                                            </span>
                                                            <input type="text" class="form-control"
                                                                name="losses_from_date" value="<?php echo e($req_losses_from_date); ?>"
                                                                data-plugin="datepicker" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div> <select class="form-control lossesShiftSelection"
                                                                name="lossesShiftSelection[]" multiple data-plugin="select2"
                                                                data-placeholder="Select Shift" required>
                                                                <?php $__currentLoopData = $machine->section->department->businessUnit->company->shifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shiftts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <option value="<?php echo e($shiftts->id); ?>"
                                                                        <?php if(in_array($shiftts->id, $req_lossesShiftSelection)): ?> selected <?php endif; ?>>
                                                                        <?php echo e($shiftts->shift_number); ?></option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


                                                                <option value="All-Day"
                                                                    <?php if(in_array('All-Day', $req_lossesShiftSelection)): ?> selected <?php endif; ?>>All
                                                                    Day</option>
                                                            </select></div>
                                                    </div>
                                                    <div class="col-md-4 ">
                                                        <div class="input-group losses-to-date" hidden>
                                                            <span class="input-group-addon">
                                                                <i class="icon md-calendar" aria-hidden="true"></i>
                                                            </span>
                                                            <input type="text" class="form-control" name="losses_to_date"
                                                                value="<?php echo e($req_losses_to_date); ?>" data-plugin="datepicker">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 example">
                                                        <select class="form-control select2 chk" data-plugin="select2"
                                                            id="lossesReportType" name="lossesReportType" required>
                                                            <option value="job-wise-setting">Job Wise Setting</option>
                                                            <option value="performance=loss">Performance Loss</option>
                                                            <option value="performance-loss-next">Performance Loss Next
                                                            </option>
                                                            <option value="availability-losses" selected>% Availability
                                                                Losses</option>
                                                            <option value="availability-losses-2">% Availability Losses Temp
                                                            </option>
                                                            <option value="error-history">Error History</option>
                                                            <option value="detailed-error-history">Error History - Detailed
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 example">
                                                        <div> <select class="form-control" name="errorcat" id="caterrors"
                                                                hidden>
                                                                <option value="0">None</option>
                                                                <?php $__currentLoopData = $errorCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <option value="<?php echo e($cat->id); ?>"
                                                                        <?php if($req_errorcat == $cat->id): ?> selected <?php endif; ?>)>
                                                                        <?php echo e($cat->id . ' - ' . $cat->name); ?></option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </select></div>
                                                    </div>
                                                    <div class="col-md-4 example">
                                                        <div>
                                                            <select class="form-control" name="error" id="errors"
                                                                hidden>
                                                                <option value="0">None</option>
                                                                <?php $__currentLoopData = $errorCodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <option value="<?php echo e($error->id); ?>">
                                                                        <?php echo e($error->id . ' - ' . $error->name); ?></option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 ">
                                                        <div class="form-group form-material">
                                                            <button type="submit"
                                                                class="btn btn-primary">Generate</button>
                                                        </div>
                                                    </div>

                                                </div>

                                            </form>
                                        </div>
                                    </div>
                                </div>


                            </div>


                        </div>
                    </div>
                </div>
            </div>
            <!-- Mine code ends -->
            <div class="row" data-plugin="matchHeight" data-by-row="true">
                <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="panel" id="print-panel">
                        <header class="panel-heading">
                            <h3 class="panel-title">
                                <strong>Error History</strong>
                                <?php if($shift[0] == 'All-Day'): ?>
                                    <small><?php echo e(date('M d, Y', strtotime($from)) . ' to ' . date('M d, Y', strtotime($to))); ?></small><br>
                                    <small><?php echo e($machine->sap_code . ' - ' . $machine->name . ', ' . $machine->section->name . ', Shift '); ?><?php echo e(count($shift) == 1 ? $shift[0] : ''); ?></small><br>
                                <?php else: ?>
                                    <small><?php echo e(date('M d, Y', strtotime($from))); ?></small><br>
                                    <small><?php echo e($machine->sap_code . ' - ' . $machine->name . ', ' . $machine->section->name . ', Shift '); ?><?php echo e(count($shift) == 1 ? \App\Models\Shift::find($shift[0])->shift_number : \App\Models\Shift::find($shift[0])->shift_number . ' to ' . \App\Models\Shift::find($shift[count($shift) - 1])->shift_number); ?></small><br>
                                <?php endif; ?>
                            </h3>
                        </header>
                        <div class="panel-body">
                            <table class="table table-hover dataTable table-striped w-full" id="production-report-table">
                                <thead>
                                    <tr>
                                        <th>Error ID</th>
                                        <th>Error Name</th>
                                        <th>Err Duration (<?php echo e($machine->time_uom); ?>)</th>
                                        <th>Frequency</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($report_type == 'short_stops'): ?>
                                        <?php $__currentLoopData = $graphRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($row['errDuration'] < 10): ?>
                                                <tr id="errorHistory">
                                                    <td id="error_id" data-error-id="<?php echo e($row['error_id']); ?>"><a
                                                            href="<?php echo e(URL::to('get/error/history' . '/' . $machine->id . '/' . date('Y-m-d', strtotime($from)) . '/' . date('Y-m-d', strtotime($to)) . '/' . $row['error_id'] . '/' . serialize($shift))); ?>"><?php echo e($row['error_id']); ?></a>
                                                    </td>
                                                    <td id="error_name" data-error-name="<?php echo e($row['error_name']); ?>"
                                                        style="width: 30%;"><?php echo e($row['error_name']); ?></td>
                                                    <td id="duration" data-error-duration="<?php echo e($row['errDuration']); ?>">
                                                        <?php echo e(number_format($row['errDuration'], 2)); ?></td>
                                                    <td id="frequency" data-error-frequency="<?php echo e($row['frequency']); ?>">
                                                        <?php echo e($row['frequency']); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php else: ?>
                                        <?php $__currentLoopData = $graphRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr id="errorHistory">
                                                <td id="error_id" data-error-id="<?php echo e($row['error_id']); ?>"><a
                                                        href="<?php echo e(URL::to('get/error/history' . '/' . $machine->id . '/' . date('Y-m-d', strtotime($from)) . '/' . date('Y-m-d', strtotime($to)) . '/' . $row['error_id'] . '/' . serialize($shift))); ?>"><?php echo e($row['error_id']); ?></a>
                                                </td>
                                                <td id="error_name" data-error-name="<?php echo e($row['error_name']); ?>"
                                                    style="width: 30%;"><?php echo e($row['error_name']); ?></td>
                                                <td id="duration" data-error-duration="<?php echo e($row['errDuration']); ?>">
                                                    <?php echo e(number_format($row['errDuration'], 2)); ?></td>
                                                <td id="frequency" data-error-frequency="<?php echo e($row['frequency']); ?>">
                                                    <?php echo e($row['frequency']); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('graphFooter'); ?>
    <script src="<?php echo e(asset('assets/global/vendor/raphael/raphael.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/morris/morris.min.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('footer'); ?>
    <script>
        function printContent(id) {
            var data = document.getElementById(id).innerHTML;
            var popupWindow = window.open('', 'printwin', 'left=100,top=100,width=1000,height=400');
            popupWindow.document.write('<HTML>\n<HEAD>\n');
            popupWindow.document.write('<TITLE></TITLE>\n');
            popupWindow.document.write('<URL></URL>\n');
            popupWindow.document.write(
                "<link href='/assets/custom/print.css' media='print' rel='stylesheet' type='text/css' />\n");
            popupWindow.document.write(
                "<link href='/assets/custom/print.css' media='screen' rel='stylesheet' type='text/css' />\n");
            popupWindow.document.write("<style>body{font-size: 10px}</style>\n");
            popupWindow.document.write('<script>\n');
            popupWindow.document.write('function print_win(){\n');
            popupWindow.document.write('\nwindow.print();\n');
            popupWindow.document.write('\nwindow.close();\n');
            popupWindow.document.write('}\n');
            popupWindow.document.write('<\/script>\n');
            popupWindow.document.write('</HEAD>\n');
            popupWindow.document.write('<BODY onload="print_win()">\n');
            popupWindow.document.write(data);
            popupWindow.document.write('</BODY>\n');
            popupWindow.document.write('</HTML>\n');
            popupWindow.document.close();
        }

        function print_win() {
            window.print();
            window.close();
        }
    </script>
    <script src="<?php echo e(asset('assets/global/vendor/sparkline/jquery.sparkline.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/matchheight/jquery.matchHeight-min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/remark/examples/js/charts/gauges.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/matchheight.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/global/vendor/asrange/jquery-asRange.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/bootbox/bootbox.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/remark/custom/canvas.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/datatables.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/remark/examples/js/uikit/icon.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/custom/jquery.dataTables.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/custom/jQuery.print.js')); ?>"></script>
    <!-- mine code -->
    <script src="<?php echo e(asset('assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/bootstrap-datepicker.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/select2/select2.full.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/bootstrap-select/bootstrap-select.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/select2.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/bootstrap-select.js')); ?>"></script>
    
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.umd.min.js"></script>
    


    <script>
        $(document).ready(function() {

            if ($('.lossesShiftSelection').val() == 'All-Day') {
                $('.losses-to-date').removeAttr('hidden');

            } else {

                $('.losses-to-date').attr('hidden', 'true');
            }
            if ($('#lossesReportType').val() == 'availability-losses') {
                $('#caterrors').removeAttr('hidden');
                $('#caterrors').select2();

            } else {
                try {
                    if ($('#caterrors').select2()) {
                        $('#caterrors').select2('destroy');
                        //  $(".cterror").hide();

                    }
                } catch (e) {

                }
                $('#caterrors').attr('hidden', 'hidden');
            }

        });

        $('.lossesShiftSelection').change(function() {
            if ($('.lossesShiftSelection').val() == 'All-Day') {
                $('.losses-to-date').removeAttr('hidden');
            } else {

                $('.losses-to-date').attr('hidden', 'true');
            }


        });

        $('#lossesReportType').change(function() {
            if ($(this).val() == 'error-history' || $(this).val() == 'detailed-error-history') {
                $('#errors').removeAttr('hidden');
                $('#errors').select2();
                //$(".err").show();
            } else {
                try {
                    if ($('#errors').select2()) {
                        $('#errors').select2('destroy');
                        // $(".err").hide();
                    }
                } catch (e) {

                }
                $('#errors').attr('hidden', 'hidden');
                // $(".err").hide();
            }
        });
        $('#lossesReportType').change(function() {

            if ($(this).val() == 'availability-losses') {
                $('#caterrors').removeAttr('hidden');
                $('#caterrors').select2();

                //$('#caterrors').removeAttr('style');
            } else {
                try {
                    if ($('#caterrors').select2()) {
                        $('#caterrors').select2('destroy');
                        //  $(".cterror").hide();

                    }
                } catch (e) {

                }
                $('#caterrors').attr('hidden', 'hidden');

            }
        });
    </script>


    <!-- mine code end -->


    



    

    <script>
        // Assuming this part is already in your code
        const data = {
            labels: [],
            datasets: [{
                    label: 'Duration',
                    data: [],
                    barGap: 0,
                    barSizeRatio: 0.8,
                    smooth: true,
                    backgroundColor: ['rgb(244, 67, 54)'],
                    // borderColor: ['rgba(255, 26, 220, 1)'],
                    gridTextColor: 'yellow',
                    gridLineColor: 'yellow',
                    goalLineColors: 'yellow',
                    gridTextFamily: Config.get('fontFamily'),
                    gridTextWeight: '300',
                    numLines: 6,
                    gridtextSize: 14,
                    resize: false,
                    xLabelAngle: 45,
                    yAxisID: 'dollars',
                    order: 2,
                },
                {
                    label: 'Frequency',
                    data: [],
                    backgroundColor: ['orange'],
                    borderColor: ['orange'],
                    gridTextColor: '#474e54',
                    gridLineColor: '#eef0f2',
                    goalLineColors: '#e3e6ea',
                    borderWidth: 4,
                    type: 'line',
                    yAxisID: 'quantity',
                    order: 1,
                },
            ],
        };

        const config = {
            type: 'bar',
            data,
            options: {
                scales: {
                    dollars: {
                        beginAtZero: true,
                        type: 'linear',
                        position: 'left',
                        grid: {
                            display: false,
                        },
                    },
                    quantity: {
                        beginAtZero: true,
                        type: 'linear',
                        position: 'right',
                        grid: {
                            display: false,
                        },
                    },
                },
            },
        };

        // render init block
        const myChart = new Chart(document.getElementById('myChart'), config);

        // Fetch data from the table and populate the dynamicData array
        var dynamicData = [];
        $('#production-report-table').find('tr').each(function() {
            var error_id = $(this).find('#error_id').data('error-id');
            var error_name = $(this).find('#error_name').data('error-name');
            var error_frequency = $(this).find('#frequency').data('error-frequency');
            var duration = $(this).find('#duration').data('error-duration');
            if (error_id != null) {
                dynamicData.push({
                    y: error_name,
                    a: duration.toFixed(2),
                    b: error_frequency,
                });
            }
        });

        // Check if myChart is defined and is an instance of Chart
        if (typeof myChart !== 'undefined' && myChart instanceof Chart) {
            // Update the chart data with the dynamicData
            myChart.data.labels = dynamicData.map((item) => item.y);
            myChart.data.datasets[0].data = dynamicData.map((item) => item.a);
            myChart.data.datasets[1].data = dynamicData.map((item) => item.b);

            // Update the chart with the new data
            myChart.update();
        } else {
            console.error('No data available for chart');
        }
    </script>





    




    <script src="<?php echo e(asset('assets/js-xlsx/xlsx.core.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/file-saver/FileSaver.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/table-export/js/tableexport.js')); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <script>
        $('#production-report-table').tableExport({
            headings: true, // (Boolean), display table headings (th/td elements) in the <thead>
            footers: true, // (Boolean), display table footers (th/td elements) in the <tfoot>
            formats: ["xlsx", "csv", "txt"], // (String[]), filetypes for the export
            fileName: "id", // (id, String), filename for the downloaded file
            bootstrap: true, // (Boolean), style buttons using bootstrap
            position: "top", // (top, bottom), position of the caption element relative to table
            ignoreRows: null, // (Number, Number[]), row indices to exclude from the exported file(s)
            ignoreCols: null, // (Number, Number[]), column indices to exclude from the exported file(s)
            ignoreCSS: ".tableexport-ignore", // (selector, selector[]), selector(s) to exclude from the exported file(s)
            emptyCSS: ".tableexport-empty", // (selector, selector[]), selector(s) to replace cells with an empty string in the exported file(s)
            trimWhitespace: false // (Boolean), remove all leading/trailing newlines, spaces, and tabs from cell text in the exported file(s)
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.' . $layout, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\rotoeye\resources\views/reports/availability-loss-report.blade.php ENDPATH**/ ?>