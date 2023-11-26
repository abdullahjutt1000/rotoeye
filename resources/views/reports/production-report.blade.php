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
        .page-break {
            page-break-after: always;
            page-break-inside: avoid;
            clear:both;
        }
        .page-break-before {
            page-break-before: always;
            page-break-inside: avoid;
            clear:both;
        }
        #html-2-pdfwrapper{
            position: absolute;
            left: 20px;
            top: 50px;
            bottom: 0;
            overflow: auto;
            width: 600px;
        }
    </style>
@endsection
@section('body')
    <div class="page">
        <div class="page-header">
            <div class="page-header-actions" style="left: 30px">
                <button onclick="exportTableToExcel('production-report-table', '{{$machine->sap_code.'-'.$machine->name.' Shift Production Report'}}')" class="btn btn-sm btn-icon btn-success btn-round" data-toggle="tooltip" data-original-title="Export to Excel">Export to Excel</button>
            </div>
        </div>
        <div class="page-content">
            <div class="row" data-plugin="matchHeight" data-by-row="true">
                <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="panel" id="print-panel" style="min-height: 842px">
                        <div class="panel-body" id="export-panel">
                            <table class="table table-hover dataTable table-striped w-full " id="production-report-table">
                                <thead>
                                <tr>
                                    <th>Machine Sap Code</th>
                                    <th>Shift</th>
                                    <th>Job</th>
                                    <th>Err No</th>
                                    <th>Err Name</th>
                                    <th>Comments</th>
                                    <th>From </th>
                                    <th>To </th>
                                    <th style="text-align: center;">Duration <br>(Min)</th>
                                    <th style="text-align: right">Actual Production<br>({{$machine->qty_uom}})</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($records as $row)
                                    <tr>
                                        <td>{{$machine->sap_code}}</td>
                                        <td>
                                                <small>Shift-{{count(\App\Models\Shift::find_shift($machine,date('Y-m-d H:i:s',strtotime($row['from'])) , date('Y-m-d H:i:s',strtotime($row['to']))))>1 ? \App\Models\Shift::find_shift($machine,date('Y-m-d H:i:s',strtotime($row['from'])),date('Y-m-d H:i:s',strtotime($row['to'])))[0].' to '.\App\Models\Shift::find_shift($machine,date('Y-m-d H:i:s',strtotime($row['from'])),date('Y-m-d H:i:s',strtotime($row['to'])))[1]:\App\Models\Shift::find_shift($machine,date('Y-m-d H:i:s',strtotime($row['from'])),date('Y-m-d H:i:s',strtotime($row['to'])))[0]}}</small>

                                        </td>
                                        <td>{{$row['job_id']}}</td>
                                        <td>{{$row['error_id']}}</td>
                                        <td >{{$row['error_name']}}</td>
                                        <td >{{$row['comments']}}</td>
                                        <td>{{date('Y-m-d H:i:s',strtotime($row['from']))}}</td>
                                        <td>{{date('Y-m-d  H:i:s',strtotime($row['to']))}}</td>
                                        <td >{{number_format($row['duration'],0)}}</td>
                                        <td >{{$row['length']}}</td>
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
@endsection
@section('footer')
    <script src="{{asset('assets/global/vendor/matchheight/jquery.matchHeight-min.js')}}"></script>
    <script src="{{asset('assets/remark/examples/js/charts/gauges.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/matchheight.js')}}"></script>
    <script src="{{asset('assets/global/vendor/asrange/jquery-asRange.min.js')}}"></script>

    <script src="{{asset('assets/custom/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/custom/jQuery.print.js')}}"></script>
    <script>
        function exportTableToExcel(tableID, filename){
            var downloadLink;
            var dataType = 'application/vnd.ms-excel;charset=utf-8';
            var tableSelect = document.getElementById(tableID);
            var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

            // Specify file name
            filename = filename?filename+'.xls':'excel_data.xls';

            // Create download link element
            downloadLink = document.createElement("a");

            document.body.appendChild(downloadLink);

            if(navigator.msSaveOrOpenBlob){
                var blob = new Blob(['\ufeff', tableHTML], {
                    type: dataType
                });
                navigator.msSaveOrOpenBlob( blob, filename);
            }else{
                // Create a link to the file
                downloadLink.href = 'data:' + dataType + ', ' + tableHTML;

                // Setting the file name
                downloadLink.download = filename;

                //triggering the function
                downloadLink.click();
            }
        }
    </script>
@endsection
