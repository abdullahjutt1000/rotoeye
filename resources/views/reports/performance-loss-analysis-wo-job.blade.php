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
    <link rel="stylesheet" href="{{asset('assets/global/vendor/morris/morris.css')}}">
    <link rel="stylesheet" href="{{asset('assets/table-export/tableexport.css')}}">
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
                                <strong>Performance Loss Analysis</strong> - <small>{{date('M d, Y')}}</small><br>
                            </h3>
                        </header>
                        <div class="panel-body">
                            <div class="example">
                                <div id="exampleMorrisDonut"></div>
                            </div>
                            <table class="table table-hover dataTable table-striped w-full" id="production-report-table">
                                <colgroup>
                                    <col span="5">
                                    <col span="2" style="border: 3px solid red;">
                                    <col span="2" style="border: 3px solid yellow;">
                                </colgroup>
                                <thead>
                                <tr>
                                    <th style="width: 15%;">Name</th>
                                    <th style="width: 15%">Job #</th>
                                    <th style="text-align: center">Production (m)</th>
                                    <th style="text-align: center">Running Time (min)</th>
                                    <th style="text-align: center">Actual Speed ({{$machine->qty_uom}}/min)</th>
                                    <th style="text-align: center">Performance (%)</th>
                                    <th style="text-align: center">Performance Loss (%)</th>
                                    <th style="text-align: center">Performance (%)</th>
                                    <th style="text-align: center">Performance Loss (%)</th>
                                </tr>
                                </thead>
                                <tbody>
                                {{--*/ $alreadyDoneProducts = [] /*--}}
                                {{--*/ $alreadyDoneMaterials = [] /*--}}
                                @foreach($secondRecords as $row)
                                    @if(!in_array($row['id'],$alreadyDoneMaterials))
                                        <tr style="background-color: lightgrey; color: black">
                                            <td colspan="2" id="materialProduction" data-material-production="{{$row[1]['materialProduction']}}" data-material-name="{{$row['material_combination']}}">Substrate: <strong>{{$row['material_combination']}}</strong></td>
                                            <td colspan="2">Nominal Speed: <strong>{{$row['nominal_speed'].' '.$machine->qty_uom.'/'.$machine->time_uom}}</strong></td>
                                            <td colspan="2">Performance: <strong>{{number_format($row['nominal_speed']/$machine->max_speed*100,0).'%'}}</strong></td>
                                            <td colspan="3">Running Time: <strong>{{number_format($row[1]['materialRunTime'],0).' '.$machine->time_uom.'  '.number_format($row[1]['materialProduction'],0).' '.$machine->qty_uom}}</strong></td>
                                        </tr>
                                        {{--*/ array_push($alreadyDoneMaterials,$row['id']) /*--}}
                                    @endif
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
@section('graphFooter')
    <script src="{{asset('assets/global/vendor/raphael/raphael.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/morris/morris.min.js')}}"></script>
@endsection
@section('footer')
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
    <script src="{{asset('assets/global/vendor/asrange/jquery-asRange.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootbox/bootbox.js')}}"></script>
    <script src="{{asset('assets/remark/custom/canvas.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/datatables.js')}}"></script>

    <script src="{{asset('assets/remark/examples/js/uikit/icon.js')}}"></script>

    <script src="{{asset('assets/custom/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/custom/jQuery.print.js')}}"></script>
    <script>
        (function () {
            var data = [];
            $('#production-report-table').find('tr').each(function(){
                var material_production = ($(this).find('#materialProduction').data('material-production'));
                var material_name = ($(this).find('#materialProduction').data('material-name'));
                if(material_production != null){
                    data.push({label: material_name, value: material_production});
                }
            });
            console.log(data);
            Morris.Donut({
                element: 'exampleMorrisDonut',
                data: data,
                // barSizeRatio: 0.35,
                resize: false,
                colors: [Config.colors("red", 500), Config.colors("primary", 500), Config.colors("grey", 400), Config.colors("green", 400), Config.colors("yellow", 400)]
            });
        })();
    </script>
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