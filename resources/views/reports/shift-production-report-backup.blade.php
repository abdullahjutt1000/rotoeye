@extends('layouts.'.$layout)
@section('header')
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
    <style>
        .panel{
            page-break-before: always;
        }
        table caption{
            flex-wrap: nowrap !important;
        }
        table caption button{
            margin-right: 10px;
        }
    </style>
@endsection
@section('body')
    <div class="page">
        <div class="page-header">
            <div class="page-header-actions" style="left: 30px">
                <button id="metaData" type="button" class="btn btn-sm btn-icon btn-primary btn-round" data-toggle="tooltip" data-original-title="Print" onclick="javascript:printContent('print-panel');">
                    <i class="icon md-print" aria-hidden="true"></i> Print
                </button>
            </div>
        </div>
        <div class="page-content">
            <div class="row" data-plugin="matchHeight" data-by-row="true">
                <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="panel" id="print-panel" style="min-height: 842px">
                        <header class="panel-heading">
                            <h3 class="panel-title">
                                <strong>Daily Shift Production Report</strong> - <small>{{$date}}</small><br>
                                <small>{{$machine->name.', '.$machine->section->name.', Shift '}}{{count($shift) == 1 ? $shift[0]:$shift[0].' to '.$shift[count($shift)-1]}}</small><br>
                                <small>Day Production: <strong>{{number_format($produced, 0)}}</strong></small>
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
                                    <th style="text-align: center;">Duration <br>(Min)</th>
                                    <th style="text-align: right">{{$machine->qty_uom}}</th>
                                    <th style="width: 35%;">Comments</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                {{--*/ $alreadyDoneProducts = [] /*--}}
                                {{--*/ $alreadyDoneMaterials = [] /*--}}
                                @foreach($records as $row)
                                <tr>
                                    <td>
                                        <div class="row">
                                            <div class="col-4">
                                                <strong>Job No: </strong>{{$row['job_id'].' - '.$row['product_number']}}<br>
                                                <strong>Job Name: </strong>{{$row['job_name']}}
                                            </div>
                                            <div class="col-4">
                                                <strong>Substrate: </strong>{{$row['material_combination']}}<br>{{$row['nominal_speed']}}
                                            </div>
                                            <div class="col-4">
                                                <strong>Required: </strong>{{number_format($row['job_length'], 0).' '.$machine->qty_uom}}<br>
                                                <strong>Produced: </strong>{{number_format($row[0]['jobProduction'], 0).' '.$machine->qty_uom}}<br>
                                                <strong>Produced (EAs): </strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td><strong>{{$row['user_name']}}</strong></td>
                                    <td>{{$row['error_id']}}</td>
                                    <td style="width: 20%;">{{$row['error_name']}}</td>
                                    <td>{{date('H:i',strtotime($row['from']))}}</td>
                                    <td>{{date('H:i',strtotime($row['to']))}}</td>
                                    <td style="text-align: center">{{number_format($row['duration'],0)}}</td>
                                    <td style="text-align: right">{{$row['length']}}</td>
                                    <td style="width: 35%;">{{$row['comments']}}</td>
                                    <td style="text-align: right"><small>{{number_format($row['instantSpeed'],1)}}</small></td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <hr style="border-top: 4px solid #336699">
                            <div class="row col-12">
                                <div class="col-2">
                                    <u>Budgeted Time:</u> <strong>{{number_format($budgetedTime, 0)}} min</strong>
                                </div>
                                <div class="col-1"></div>
                                <div class="col-2">
                                    <u>Running Time:</u> <strong>{{number_format($run_time, 0)}} min</strong>
                                </div>
                                <div class="col-1"></div>
                                <div class="col-2">
                                    <u>Total Downtime:</u> <strong>{{number_format($totalDowntime, 0)}} min</strong>
                                </div>
                                <div class="col-4" style=" text-align: right">
                                    <p style="text-align: right; margin-bottom: 0;"><u >Availability Rate:</u> <strong>{{number_format($availability, 2)}} %</strong></p>
                                    <p style="text-align: right; margin-bottom: 0;"><u>Availability Rate (EE):</u> <strong>{{number_format($availability_ee, 2)}} %</strong></p>
                                    <p style="text-align: right; margin-bottom: 0;"><u>Performance Rate:</u> <strong>{{number_format($performance, 2)}} %</strong></p>
                                    <h4 style="text-align: right; margin-bottom: 0; margin-top: 10px; color: #336699; font-size: 18px">EE: <strong>{{number_format($ee, 2)}} %</strong></h4>
                                    <h4 style="text-align: right; margin-bottom: 0; color: #336699; font-size: 18px">OEE: <strong>{{number_format($oee, 2)}} %</strong></h4>
                                </div>
                            </div>
                        </div>
                        <div class="row col-12" style="padding-top: 100px">
                            <div class="col-1"></div>
                            <div class="col-3 text-center">
                                <hr>
                                <p>Operator's Signature</p>
                            </div>
                            <div class="col-4"></div>
                            <div class="col-3 text-center">
                                <hr>
                                Supervisor's Signature
                            </div>
                            <div class="col-1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('footer')
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
                buttons: [
                    {
                        extend: 'pdfHtml5',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        download: 'open',
                        title: 'Project Manager',
                        customize: function(doc) {
                            doc.defaultStyle.fontSize = 8; //<-- set fontsize to 16 instead of 10
                            doc.styles.tableHeader.fontSize = 8;
                        }
                    },
                    'copy', 'csv', 'excel', 'print'
                ],
                "drawCallback": function ( settings ) {
                    var api = this.api();
                    var rows = api.rows( {page:'current'} ).nodes();
                    var last=null;

                    api.column(0, {page:'current'} ).data().each( function ( group, i ) {
                        if ( last !== group ) {
                            $(rows).eq( i ).before(
                                    '<tr class="group" style="background-color: #F6F600"><td colspan="20" style="color: #302e2e">'+group+'</td></tr>'
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
    <script>
        function printContent(id){
            var data = document.getElementById(id).innerHTML;
            var popupWindow = window.open('','printwin', 'left=100,top=100,width=1000,height=400');
            popupWindow.document.write('<HTML>\n<HEAD>\n');
            popupWindow.document.write('<TITLE></TITLE>\n');
            popupWindow.document.write('<URL></URL>\n');
            popupWindow.document.write("<link href='/assets/custom/print.css' media='print' rel='stylesheet' type='text/css' />\n");
            popupWindow.document.write("<link href='/assets/custom/print.css' media='screen' rel='stylesheet' type='text/css' />\n");
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
        function print_win(){
            window.print();
            window.close();
        }
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

        }
    </script>
    <script src="{{asset('assets/remark/custom/canvas.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/datatables.js')}}"></script>

    <script src="{{asset('assets/remark/examples/js/tables/datatable.js')}}"></script>
    <script src="{{asset('assets/remark/examples/js/uikit/icon.js')}}"></script>

    <script src="{{asset('assets/custom/jquery-1.12.4.js')}}"></script>
    <script src="{{asset('assets/custom/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/custom/jQuery.print.js')}}"></script>
    <script src="{{asset('assets/js-xlsx/xlsx.core.js')}}"></script>
    <script src="{{asset('assets/file-saver/FileSaver.min.js')}}"></script>
    <script src="{{asset('assets/table-export/js/tableexport.js')}}"></script>
    <script>
        $('#production-report-table').tableExport({
            headings: true,                    // (Boolean), display table headings (th/td elements) in the <thead>
            footers: true,                     // (Boolean), display table footers (th/td elements) in the <tfoot>
            formats: ["xlsx", "csv", "txt"],    // (String[]), filetypes for the export
            fileName: "id",                    // (id, String), filename for the downloaded file
            bootstrap: true,                   // (Boolean), style buttons using bootstrap
            position: "top",                 // (top, bottom), position of the caption element relative to table
            ignoreRows: null,                  // (Number, Number[]), row indices to exclude from the exported file(s)
            ignoreCols: null,                  // (Number, Number[]), column indices to exclude from the exported file(s)
            ignoreCSS: ".tableexport-ignore",  // (selector, selector[]), selector(s) to exclude from the exported file(s)
            emptyCSS: ".tableexport-empty",    // (selector, selector[]), selector(s) to replace cells with an empty string in the exported file(s)
            trimWhitespace: false              // (Boolean), remove all leading/trailing newlines, spaces, and tabs from cell text in the exported file(s)
        });
    </script>
@endsection