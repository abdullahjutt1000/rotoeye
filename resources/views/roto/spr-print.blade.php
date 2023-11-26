<!DOCTYPE html>
<html class="no-js css-menubar" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="bootstrap material admin template">
    <meta name="author" content="">

    <title>Dashboard | Roto Eye</title>

    <link rel="apple-touch-icon" href="{{asset('logo2.png')}}">
    <link rel="shortcut icon" href="{{asset('assets/icons/Logo_RotoEye_Version_160 cross 160_PNG.png')}}">

    <link rel="stylesheet" href="{{asset('assets/global/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/css/bootstrap-extend.min.css')}}">

    <link rel="stylesheet" href="{{asset('assets/global/vendor/animsition/animsition.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/asscrollable/asScrollable.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/switchery/switchery.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/intro-js/introjs.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/slidepanel/slidePanel.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/flag-icon-css/flag-icon.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/waves/waves.css')}}">

    <link rel="stylesheet" href="{{asset('assets/global/vendor/chartist/chartist.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/jvectormap/jquery-jvectormap.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/chartist-plugin-tooltip/chartist-plugin-tooltip.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/dashboard/v1.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/fonts/material-design/material-design.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/fonts/brand-icons/brand-icons.min.css')}}">
    <link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,300italic'>

    <link rel="stylesheet" href="{{asset('assets/global/fonts/material-design/material-design.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/fonts/brand-icons/brand-icons.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/uikit/buttons.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/datatables.net-bs4/dataTables.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/datatables.net-fixedheader-bs4/dataTables.fixedheader.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/datatables.net-fixedcolumns-bs4/dataTables.fixedcolumns.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/datatables.net-scroller-bs4/dataTables.scroller.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/datatables.net-select-bs4/dataTables.select.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/datatables.net-responsive-bs4/dataTables.responsive.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/datatables.net-buttons-bs4/dataTables.buttons.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/tables/datatable.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/fonts/font-awesome/font-awesome.css')}}">

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
<body onload="window.print()">
<div class="page">
    <div class="page-content">
        <div class="row" data-plugin="matchHeight" data-by-row="true">
            <div class="col-xl-12 col-lg-12 col-md-12">
                <div class="panel">
                    <header class="panel-heading">
                        <h3 class="panel-title">
                            <strong>Daily Shift Production Report</strong> - <small>{{date('d/m/Y')}}</small><br>
                            <small>{{$machine->name.', '.$machine->section->name}}</small>
                        </h3>
                    </header>
                    <div class="panel-body">
                        <table class="table table-hover dataTable table-striped w-full" id="production-report-table">
                            <thead>
                            <tr>
                                <th>Group</th>
                                <th>User</th>
                                <th>Err No</th>
                                <th>Err Name</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Duration</th>
                                <th>Meters</th>
                                <th>Comments</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($records as $record)
                                <tr>
                                    <td>
                                        <div class="row">
                                            <div class="col-4">
                                                <strong>Job No: </strong>{{$record['job_id'].' - '.$record['product_number']}}<br>
                                                <strong>Job Name: </strong>{{$record['job_name']}}
                                            </div>
                                            <div class="col-4">
                                                <strong>Substrate: </strong>{{$record['material_combination']}}<br>{{$record['nominal_speed']}}
                                            </div>
                                            <div class="col-4">
                                                <strong>Required: </strong>{{$record['job_length']}}<br>
                                                <strong>Produced: </strong><br>
                                                <strong>Produced (EAs): </strong>
                                            </div>
                                        </div>

                                    </td>
                                    <td><strong>{{$record['user_name']}}</strong></td>
                                    <td>{{$record['error_id']}}</td>
                                    <td>{{$record['error_name']}}</td>
                                    <td>{{date('d-M-Y H:i:s',strtotime($record['from']))}}</td>
                                    <td>{{date('d-M-Y H:i:s',strtotime($record['to']))}}</td>
                                    <td>{{date_diff(date_create(date('d-M-Y H:i:s',strtotime($record['from']))), date_create(date('d-M-Y H:i:s',strtotime($record['to']))))->format("%i Min")}}</td>
                                    <td>{{$record['length']}}</td>
                                    <td>{{$record['comments']}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
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

<script>
    $(document).ready(function() {
        var table = $('#production-report-table').DataTable({
            "columnDefs": [
                { "visible": false, "targets": 0 },
                { "visible": false, "targets": 1 }
            ],
            "sort":null,
            "info":null,
            "paginate":null,
            "searching":null,
            "drawCallback": function ( settings ) {
                var api = this.api();
                var rows = api.rows( {page:'current'} ).nodes();
                var last=null;

                api.column(0, {page:'current'} ).data().each( function ( group, i ) {
                    if ( last !== group ) {
                        $(rows).eq( i ).before(
                                '<tr class="group"><td colspan="20" style="background-color: #F6F600; color: #302e2e">'+group+'</td></tr>'
                        );
                        last = group;
                    }
                } );
                api.column(1, {page:'current'} ).data().each( function ( group, i ) {
                    if ( last !== group ) {
                        $(rows).eq( i ).before(
                                '<tr class="group"><td colspan="20" style="background-color: #FFF; color: #ed1b23">'+group+'</td></tr>'
                        );
                        last = group;
                    }
                } );
            }
        } );
    });

</script>
<script src="{{asset('assets/global/vendor/sparkline/jquery.sparkline.min.js')}}"></script>
<script src="{{asset('assets/global/vendor/matchheight/jquery.matchHeight-min.js')}}"></script>
<script src="{{asset('assets/remark/examples/js/charts/gauges.js')}}"></script>
<script src="{{asset('assets/global/js/Plugin/matchheight.js')}}"></script>
<script src="{{asset('assets/global/vendor/datatables.net/jquery.dataTables.js')}}"></script>
<script src="{{asset('assets/global/vendor/datatables.net-bs4/dataTables.bootstrap4.js')}}"></script>
<script src="{{asset('assets/global/vendor/datatables.net-fixedheader/dataTables.fixedHeader.js')}}"></script>
<script src="{{asset('assets/global/vendor/datatables.net-fixedcolumns/dataTables.fixedColumns.js')}}"></script>
<script src="{{asset('assets/global/vendor/datatables.net-rowgroup/dataTables.rowGroup.js')}}"></script>
<script src="{{asset('assets/global/vendor/datatables.net-scroller/dataTables.scroller.js')}}"></script>
<script src="{{asset('assets/global/vendor/datatables.net-responsive/dataTables.responsive.js')}}"></script>
<script src="{{asset('assets/global/vendor/datatables.net-responsive-bs4/responsive.bootstrap4.js')}}"></script>
<script src="{{asset('assets/global/vendor/datatables.net-buttons/dataTables.buttons.js')}}"></script>
<script src="{{asset('assets/global/vendor/datatables.net-buttons/buttons.html5.js')}}"></script>
<script src="{{asset('assets/global/vendor/datatables.net-buttons/buttons.flash.js')}}"></script>
<script src="{{asset('assets/global/vendor/datatables.net-buttons/buttons.print.js')}}"></script>
<script src="{{asset('assets/global/vendor/datatables.net-buttons/buttons.colVis.js')}}"></script>
<script src="{{asset('assets/global/vendor/datatables.net-buttons-bs4/buttons.bootstrap4.js')}}"></script>
<script src="{{asset('assets/global/vendor/asrange/jquery-asRange.min.js')}}"></script>
<script src="{{asset('assets/global/vendor/bootbox/bootbox.js')}}"></script>
<script>
    window.onload = function () {
        window.print();
    }
</script>
<script src="{{asset('assets/remark/custom/canvas.js')}}"></script>
<script src="{{asset('assets/global/js/Plugin/datatables.js')}}"></script>

<script src="{{asset('assets/remark/examples/js/tables/datatable.js')}}"></script>
<script src="{{asset('assets/remark/examples/js/uikit/icon.js')}}"></script>

<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="{{asset('assets/custom/jquery.dataTables.min.js')}}"></script>