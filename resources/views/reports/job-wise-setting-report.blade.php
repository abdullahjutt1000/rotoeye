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
            display: none !important;
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
                <button onclick="check()" class="btn btn-sm btn-icon btn-success btn-round" data-toggle="tooltip" data-original-title="Export to Excel">Export to Excel</button>
            </div>
        </div>
        <div class="page-content">
            <div class="row" data-plugin="matchHeight" data-by-row="true">
                <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="panel" id="print-panel" style="min-height: 842px">
                        <header class="panel-heading">
                            <h3 class="panel-title">
                                <strong>Job Wise Setting Report</strong> -
                                @if($shift[0] == 'All-Day')
                                    <small>{{ date('M d, Y',strtotime($from)).' to '.date('M d, Y',strtotime($to))}}</small><br>
                                    <small>{{$machine->sap_code.' - '.$machine->name.', '.$machine->section->name.', Shift '}}{{count($shift) == 1 ? $shift[0]:""}}</small><br>
                                @else
                                    <small>{{ date('M d, Y',strtotime($from)) }}</small><br>
                                    <small>{{$machine->sap_code.' - '.$machine->name.', '.$machine->section->name.', Shift '}}{{count($shift) == 1 ? \App\Models\Shift::find($shift[0])->shift_number:\App\Models\Shift::find($shift[0])->shift_number.' to '.\App\Models\Shift::find($shift[count($shift)-1])->shift_number}}</small><br>
                                @endif
                            </h3>
                        </header>
                        <div class="panel-body">
                            <table class="table table-hover dataTable table-striped w-full" id="job-wise-setting">
                                <thead>
                                <tr>
                                    <th>Job Desc #</th>
                                    <th>Job #</th>
                                    <th>Process</th>
                                    <th>Estimated Time</th>
                                    <th>Job Name</th>
                                    <th>Material</th>
                                    <th>Colors</th>
                                    <th style="text-align: center;">Avg. Duration per Color <br>(Min)</th>
                                    <th>Adhesive</th>
                                    <th style="text-align: center;">Duration <br>(Min)</th>
                                    <th style="text-align: right">{{$machine->qty_uom}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($records as $row)
                                <tr>
                                    <td>{{$row['product_number']}}</td>
                                    <td>{{$row['job_id']}}</td>
                                    <td>{{$row['process_name']}}</td>
                                    <td>{{date('d-M-Y H:i' ,strtotime($row['estimated_time']))}}</td>
                                    <td style="width: 30%;">{{$row['job_name']}}</td>
                                    <td>{{$row['material_combination']}}</td>
                                    <td>{{$row['colors']}}</td>
                                    <td style="text-align: center;">{{$row['colors'] == 0?0:number_format($row['duration']/$row['colors'],2)}}</td>
                                    <td>{{$row['adhesive']}}</td>
                                    <td style="text-align: center;">{{number_format($row['duration'],0)}}</td>
                                    <td style="text-align: right;">{{number_format($row['length'],0)}}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td></td>
                                    <td><strong>{{count($records)}}</strong> Jobs</td>
                                    <td></td>
                                    <td colspan="3"></td>
                                    <td><strong>{{number_format($totalColors,0)}}</strong></td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align: center;"><strong>{{number_format($totalSettingMinutes,0)}}</strong></td>
                                    <td style="text-align: right;"><strong>{{number_format($totalSettingMeters,0)}}</strong></td>
                                </tr>
                                </tbody>
                            </table>
                            <hr style="border-top: 4px solid #336699">
                            @if(isset($row['colors']) && $row['colors'] != '')
                                <div class="row col-12">
                                    <div class="col-3">
                                        <u>Average Meter per Color:</u> <strong>{{$totalColors == 0?0:number_format($totalSettingMeters/$totalColors, 2)}} min</strong>
                                    </div>
                                    <div class="col-1"></div>
                                    <div class="col-3">
                                        <u>Average Min per Color:</u> <strong>{{$totalColors == 0?0:number_format($totalSettingMinutes/$totalColors, 2)}} min</strong>
                                    </div>
                                    <div class="col-5" style=" text-align: right">
                                        <h4 style="text-align: right; margin-bottom: 0; color: #336699; font-size: 18px">Setting on average per Job: <strong>{{$totalColors == 0?0:number_format($totalSettingMeters/count($records), 0).' '.$machine->qty_uom}}</strong></h4>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('footer')
    <script>
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
    <script src="{{asset('assets/global/vendor/asrange/jquery-asRange.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootbox/bootbox.js')}}"></script>
    <script src="{{asset('assets/remark/custom/canvas.js')}}"></script>
    <script src="{{asset('assets/remark/examples/js/uikit/icon.js')}}"></script>

    <script src="{{asset('assets/custom/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/custom/jQuery.print.js')}}"></script>
    <script src="{{asset('assets/js-xlsx/xlsx.core.js')}}"></script>
    <script src="{{asset('assets/file-saver/FileSaver.min.js')}}"></script>
    <script src="{{asset('assets/table-export/js/tableexport.js')}}"></script>
    <script>
        $('#job-wise-setting').tableExport({
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
        function check(){
            $('.xlsx').click();
        }
    </script>

@endsection
