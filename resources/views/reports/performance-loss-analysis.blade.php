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
                                <strong>Performance Loss Analysis</strong>
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
                                    <th style="text-align: center">Production ({{$machine->qty_uom}})</th>
                                    <th style="text-align: center">Running Time ({{$machine->time_uom}})</th>
                                    <th style="text-align: center">Actual Speed ({{$machine->qty_uom}}/{{$machine->time_uom}})</th>
                                    <th style="text-align: center">Performance (%)</th>
                                    <th style="text-align: center">Performance Loss (%)</th>
                                    <th style="text-align: center">Performance (%)</th>
                                    <th style="text-align: center">Performance Loss (%)</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php $alreadyDoneProducts = [] @endphp
                                @php $alreadyDoneMaterials = [] @endphp
                                @foreach($records as $row)
                                    @if(!in_array($row['id'],$alreadyDoneMaterials))
                                        <tr  onclick="hiderow({{$row['id']}})" style="background-color: lightgrey; color: black; ">
                                            <td colspan="2" id="materialProduction" data-material-production="{{$row['materialProduction']}}" data-material-name="{{$row['material_combination']}}">Substrate: <strong>{{$row['material_combination']}}</strong></td>
                                            <td class="text-center" colspan="1"><strong>{{number_format($row['materialProduction'],0)}}</strong></td>
                                            <td class="text-center" colspan="1"><strong>{{number_format($row['materialRunTime'],2)}}</strong></td>
                                            <td colspan="1"></td>
                                            <td class="text-center" colspan="1">
                                                <strong>
                                                    @if($machine->time_uom == 'Min')
                                                        {{number_format(($row['materialProduction']/$row['materialRunTime'])/$machine->max_speed*100,0)}}
                                                    @endif
                                                    @if($machine->time_uom == 'Hr')
                                                        {{number_format(($row['materialProduction']/$row['materialRunTime'])*60/$machine->max_speed*100,0)}}
                                                    @endif
                                                    @if($machine->time_uom == 'Sec')
                                                        {{number_format(($row['materialProduction']/$row['materialRunTime'])/60/$machine->max_speed*100,0)}}
                                                    @endif
                                                </strong>
                                            </td>
                                            <td colspan="3"></td>
                                        </tr>
                                        @php array_push($alreadyDoneMaterials,$row['id']) @endphp
                                    @endif
                                    @if(!in_array($row['product_number'],$alreadyDoneProducts))
                                        <tr onclick="hiderow({{$row['product_number']}})" class = "{{$row['id']}} tableexport-ignore" style="display: none; background-color: #302e2e; color: #ffffff ">
                                            <td>{{$row['job_name']}}</td>
                                            <td>{{$row['product_number']}}</td>
                                            <td style="text-align: center">{{number_format($row['productProduction'],0)}}</td>
                                            <td style="text-align: center">
                                                @if($machine->time_uom == 'Min')
                                                    {{number_format($row['productRunTime'],2)}}
                                                @endif
                                                @if($machine->time_uom == 'Hr')
                                                    {{number_format($row['productRunTime']/60,2)}}
                                                @endif
                                                @if($machine->time_uom == 'Sec')
                                                    {{number_format($row['productRunTime']*60,2)}}
                                                @endif
                                            </td>
                                            <td style="text-align: center">
                                                @if($machine->time_uom == 'Min')
                                                    {{$row['productRunTime'] == 0?0:number_format($row['productProduction']/$row['productRunTime'],0)}}
                                                @endif
                                                @if($machine->time_uom == 'Hr')
                                                    {{$row['productRunTime'] == 0?0:number_format(($row['productProduction']/$row['productRunTime'])*60,0)}}
                                                @endif
                                                @if($machine->time_uom == 'Sec')
                                                    {{$row['productRunTime'] == 0?0:number_format(($row['productProduction']/$row['productRunTime'])/60,0)}}
                                                @endif
                                            </td>
                                            <td style="text-align: center">
                                                @if($machine->time_uom == 'Min')
                                                    {{$row['productRunTime'] == 0?0:number_format(($row['productProduction']/$row['productRunTime'])/$machine->max_speed*100,0)}}
                                                @endif
                                                @if($machine->time_uom == 'Hr')
                                                    {{$row['productRunTime'] == 0?0:number_format(($row['productProduction']/$row['productRunTime'])*60/$machine->max_speed*100,0)}}
                                                @endif
                                                @if($machine->time_uom == 'Sec')
                                                    {{$row['productRunTime'] == 0?0:number_format(($row['productProduction']/$row['productRunTime'])/60/$machine->max_speed*100,0)}}
                                                @endif
                                            </td>
                                            <td style="text-align: center">
                                                @if($machine->time_uom == 'Min')
                                                    {{$row['productRunTime'] == 0?0:100-number_format(($row['productProduction']/$row['productRunTime'])/$machine->max_speed*100,0)}}
                                                @endif
                                                @if($machine->time_uom == 'Hr')
                                                    {{$row['productRunTime'] == 0?0:100-number_format(($row['productProduction']/$row['productRunTime'])*60/$machine->max_speed*100,0)}}
                                                @endif
                                                @if($machine->time_uom == 'Sec')
                                                    {{$row['productRunTime'] == 0?0:100-number_format(($row['productProduction']/$row['productRunTime'])/60/$machine->max_speed*100,0)}}
                                                @endif
                                            </td>
                                            <td style="text-align: center">{{$row['productRunTime'] == 0 || $row['nominal_speed'] == 0?0:number_format(($row['productProduction']/$row['productRunTime'])/$row['nominal_speed']*100,0)}}</td>
                                            <td style="text-align: center">{{$row['productRunTime'] == 0 || $row['nominal_speed'] == 0?0:100 - number_format(($row['productProduction']/$row['productRunTime'])/$row['nominal_speed']*100,0)}}</td>
                                        </tr>
                                        @php array_push($alreadyDoneProducts,$row['product_number']) @endphp
                                    @endif
                                    <tr class="{{$row['product_number']}} {{$row['id']}} tableexport-ignore" style="display: none">
                                        <td><a href="{{URL::to('get/job/performance'.'/'.$machine->id.'/'.date('Y-m-d', strtotime($from)).'/'.date('Y-m-d', strtotime($to)).'/'.$row['job_id'].'/'.serialize($shift))}}">{{$row['job_id']}}</a></td>
                                        <td></td>
                                        <td style="text-align: center">{{number_format($row['jobProduction'],0)}}</td>
                                        <td style="text-align: center">
                                            @if($machine->time_uom == 'Min')
                                                {{number_format($row['jobRunTime'],2)}}
                                            @endif
                                            @if($machine->time_uom == 'Hr')
                                                {{number_format($row['jobRunTime']/60,2)}}
                                            @endif
                                            @if($machine->time_uom == 'Sec')
                                                {{number_format($row['jobRunTime']*60,2)}}
                                            @endif
                                        </td>
                                        <td style="text-align: center">
                                            @if($machine->time_uom == 'Min')
                                                {{$row['jobRunTime'] == 0?0:number_format($row['jobProduction']/$row['jobRunTime'],0)}}
                                            @endif
                                            @if($machine->time_uom == 'Hr')
                                                {{$row['jobRunTime'] == 0?0:number_format(($row['jobProduction']/$row['jobRunTime'])*60,0)}}
                                            @endif
                                            @if($machine->time_uom == 'Sec')
                                                {{$row['jobRunTime'] == 0?0:number_format(($row['jobProduction']/$row['jobRunTime'])/60,0)}}
                                            @endif
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <hr style="border-top: 4px solid #336699">
                            <div class="row col-12">
                                <div class="col-2">
                                    <u>Total Production: </u> <strong>{{number_format($totalProduction, 0).' '.$machine->qty_uom}}</strong>
                                </div>
                                <div class="col-1"></div>
                                <div class="col-2">
                                    <u>Total Runtime: </u>
                                    <strong>
                                        @if($machine->time_uom == 'Min')
                                            {{number_format($totalRunTime,2).' '.$machine->time_uom}}
                                        @endif
                                        @if($machine->time_uom == 'Hr')
                                            {{number_format($totalRunTime/60,2).' '.$machine->time_uom}}
                                        @endif
                                        @if($machine->time_uom == 'Sec')
                                            {{number_format($totalRunTime*60,2).' '.$machine->time_uom}}
                                        @endif
                                    </strong>
                                </div>
                                <div class="col-1"></div>
                                <div class="col-2">
                                    <u>Total Performance: </u>
                                    <strong>
                                        @if($machine->time_uom == 'Min')
                                            {{number_format(($totalProduction/$totalRunTime)/$machine->max_speed*100, 2)}} %
                                        @endif
                                        @if($machine->time_uom == 'Hr')
                                            {{number_format(($totalProduction/$totalRunTime)*60/$machine->max_speed*100, 2)}} %
                                        @endif
                                        @if($machine->time_uom == 'Sec')
                                            {{number_format(($totalProduction/$totalRunTime)/60/$machine->max_speed*100, 2)}} %
                                        @endif
                                    </strong>
                                </div>
                                <div class="col-4" style=" text-align: right">
                                    <h4 style="text-align: right; margin-bottom: 0; margin-top: 10px; color: #336699; font-size: 18px">EE: <strong>{{number_format($ee, 2)}} %</strong></h4>
                                    <h4 style="text-align: right; margin-bottom: 0; color: #336699; font-size: 18px">OEE: <strong>{{number_format($oee, 2)}} %</strong></h4>
                                </div>
                            </div>
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
    <script>
        window.onload = function () {

        }
    </script>
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


        var ex_table = $('#production-report-table').tableExport({
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
            trimWhitespace: true              // (Boolean), remove all leading/trailing newlines, spaces, and tabs from cell text in the exported file(s)
        });

        function hiderow(rowclass)
        {
            let rows = $("."+rowclass);
            for(let i=0;i<rows.length;i++) {
                if(rows[i].style.display==="none")
                {
                    rows[i].style.display="";
                    rows[i].classList.remove("tableexport-ignore")
                }
                else
                {
                    rows[i].style.display="none";
                    rows[i].classList.add("tableexport-ignore")
                }
            }
            ex_table.reset();
        }
    </script>
@endsection
