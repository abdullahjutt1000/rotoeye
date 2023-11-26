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
        .dtrg-level-0{
            background-color: #F6F600 !important;
            color: #302e2e !important;
        }
        .dtrg-level-1{
            background-color: lightgrey !important;
            color: #302e2e !important;
        }
        .dtrg-level-2{
            background-color: #FFF !important;
            color: #ed1b23 !important;
        }
    </style>
@endsection
@section('body')  
    <div class="page">
        <div class="page-header">
            <div class="page-header-actions" style="left: 30px; display:flex;">
            <!-- <div class="row"> -->
                <!-- <div class="col"> -->
                <button id="metaData" type="button" class="btn btn-sm btn-icon btn-primary btn-round" data-toggle="tooltip" data-original-title="Print" onclick="javascript:printContent('print-panel');">
                    <i class="icon md-print" aria-hidden="true"></i> Print
                </button>
                <!-- <button onclick="check()" class="btn btn-sm btn-icon btn-success btn-round" data-toggle="tooltip" data-original-title="Export to Excel">Export to Excel</button> -->
                {{-- <button onclick="exportToExcel()" class="btn btn-sm btn-icon btn-success btn-round" data-toggle="tooltip" data-original-title="Export to Excel">Export to Excel</button> --}}
            
               
                <!-- </div> -->
                <!-- <div class="col"> -->
                
                <form action="{{URL::to('/export-version'.'/'.\Illuminate\Support\Facades\Crypt::encrypt($machine->id))}}" method="post" enctype="multipart/form-data" autocomplete="off">
                    <input type='hidden' name="date" value={{$from}}>
                    <input type='hidden' name="to_date" value={{$to}}>
                    <input type='hidden' name="shiftSelection" value={{urlencode(json_encode($shft))}}>
                    <button  type="submit"  class="btn btn-sm btn-icon btn-success btn-round" data-toggle="tooltip" data-original-title="Export to Excel">Export to Excel</button>
                </form>
                <!-- </div> -->
                <!-- </div> -->
            </div>
        </div>
        <div class="page-content">
            <div class="row" data-plugin="matchHeight" data-by-row="true">
                <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="panel" id="print-panel" style="min-height: 842px">
                        <header class="panel-heading">
                            <h3 class="panel-title">
                                <strong>Daily Shift Production Report - Summarized</strong> - <small><strong>{{isset($from)?$from.' to '.$to:$date}}</strong></small>
                                <small style="float: right">{{$current_time}}</small><br>
                                <small><strong>{{$machine->sap_code}}</strong>{{' - '.$machine->name.', '.$machine->section->name.', Shift '}}{{count($shift) == 1 ? $shift[0]:$shift[0].' to '.$shift[count($shift)-1]}}</small><br>
                                <small>Day Production: <strong>{{number_format($produced, 0)}}</strong></small>
                            </h3>
                        </header>
                        <div class="panel-body">
                            <table class="table table-striped table-bordered zero-configuration" id="production-report-table">
{{--                            <table class="table table-hover dataTable table-striped w-full production-report-table" id="production-report-table">--}}
                                <thead>
                                <tr>
                                    <th>Err No</th>
                                    <th></th>
                                    <th></th>
                                    <th style="text-align: center;">Duration <br>(Min)</th>
                                    <th style="text-align: right">{{$machine->qty_uom}}</th>
                                    <th style="width: 35%;">Comments</th>
                                    <th style="text-align: right">Duration <br>(Hr)</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                  $alreadyDoneJobs = []  ;
                                  $alreadyDoneErrors = []  ;
                                  $alreadyDoneUsers = []  ;
                                  $errorDuration = 0  ;
                                  $userDuration = 0  ;
                                  $errorLength = 0  ;
                                  $userLength = 0  ;
                                @endphp
                                @for($i = 0; $i<count($records); $i++)
                                    @php
                                      $errorDuration += $records[$i]['duration'];
                                      $userDuration += $records[$i]['duration'];
                                      $errorLength += $records[$i]['length'];
                                      $userLength += $records[$i]['length'];
                                    @endphp
                                    @if(!in_array($records[$i]['job_id'], $alreadyDoneJobs))
                                        <tr style="background-color: #F6F600">
                                            <td colspan="8" style="color: #302e2e">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <strong>Job No: </strong>{{$records[$i]['job_id'].' - '.$records[$i]['product_number']}}<br>
                                                        <strong>Job Name: </strong>{{$records[$i]['job_name']}}
                                                    </div>
                                                    <div class="col-4">
                                                        <strong>Substrate: </strong>{{$records[$i]['material_combination']}}<br>{{$records[$i]['process_name']}}
                                                    </div>
                                                    <div class="col-4">
                                                        <strong>Required: </strong>{{number_format($records[$i]['job_length'], 0).' '.$machine->qty_uom}}<br>
                                                        <strong>Produced: </strong>{{number_format($records[$i][0]['jobProduction'], 0).' '.$machine->qty_uom}}<br>
                                                        <strong>Produced (EAs): </strong>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @php
                                          array_push($alreadyDoneJobs,$records[$i]['job_id']);
                                          $alreadyDoneErrors = [];
                                          $alreadyDoneUsers = [];
                                          $errorUsers = [];
                                          @endphp
                                    @endif
                                    @if(isset($records[$i+1]['user_id']))
                                        @if($records[$i]['user_id'] != $records[$i+1]['user_id'] || $records[$i]['error_id'] != $records[$i+1]['error_id'] || $records[$i]['job_id'] != $records[$i+1]['job_id'])
                                            @php
                                            array_push($errorUsers,[
                                                "user_id"=>$records[$i]['user_id'],
                                                "user_name"=>$records[$i]['user_name'],
                                                "duration"=>$userDuration,
                                                "length"=>$userLength
                                            ])  ;
                                              $userDuration = 0  ;
                                              $userLength = 0  ;
                                              @endphp
                                        @endif
                                    @else
                                        @php
                                          array_push($errorUsers,[
                                                "user_id"=>$records[$i]['user_id'],
                                                "user_name"=>$records[$i]['user_name'],
                                                "duration"=>$userDuration,
                                                "length"=>$userLength
                                            ])  ;
                                          $userDuration = 0  ;
                                          $userLength = 0  ;
                                          @endphp
                                    @endif

                                    @if(isset($records[$i+1]['error_id']))
                                        @if($records[$i]['error_id'] != $records[$i+1]['error_id'] || $records[$i]['job_id'] != $records[$i+1]['job_id'])
                                            @if(!in_array($records[$i]['error_id'], $alreadyDoneErrors))
                                                <tr style="background-color: #FFF">
                                                    <td colspan="3" style="color: #ed1b23"><strong>{{$records[$i]['error_id'].' - '.$records[$i]['error_name']}}</strong></td>
                                                    <td style="text-align: center; color: #ed1b23"><strong>{{number_format($errorDuration,0)}}</strong></td>
                                                    <td style="text-align: right; color: #ed1b23"><strong>{{number_format($errorLength,0)}}</strong></td>
                                                    <td colspan="2" style="color: #ed1b23; text-align: right"><strong>{{number_format($errorDuration/60,2)}}</strong></td>
                                                </tr>
                                                @php
                                                  array_push($alreadyDoneErrors,$records[$i]['error_id'])  ;
                                                  $errorDuration = 0  ;
                                                  $errorLength = 0  ;
                                                @endphp
                                                @foreach($errorUsers as $errorUser)
                                                    <tr style="background-color: #FFF">
                                                        <td colspan="3">{{$errorUser['user_id'].' - '.$errorUser['user_name']}}</td>
                                                        <td style="text-align: center">{{number_format($errorUser['duration'],0)}}</td>
                                                        <td style="text-align: right">{{number_format($errorUser['length'],0)}}</td>
                                                        <td colspan="2" style="text-align: right">{{number_format($errorUser['duration']/60,2)}}</td>
                                                    </tr>
                                                @endforeach
                                                @php
                                                    $errorUsers = []  ;
                                                @endphp
                                            @endif
                                        @endif
                                    @else
                                        @if(!in_array($records[$i]['error_id'], $alreadyDoneErrors))
                                            <tr style="background-color: #FFF">
                                                <td colspan="3" style="color: #ed1b23"><strong>{{$records[$i]['error_id'].' - '.$records[$i]['error_name']}}</strong></td>
                                                <td style="text-align: center; color: #ed1b23"><strong>{{number_format($errorDuration,0)}}</strong></td>
                                                <td style="text-align: right; color: #ed1b23"><strong>{{number_format($errorLength,0)}}</strong></td>
                                                <td colspan="2" style="color: #ed1b23; text-align: right"><strong>{{number_format($errorDuration/60,2)}}</strong></td>
                                            </tr>
                                            @php
                                              array_push($alreadyDoneErrors,$records[$i]['error_id'])  ;
                                              $errorDuration = 0  ;
                                              $errorLength = 0  ;
                                              @endphp
                                            @foreach($errorUsers as $errorUser)
                                                <tr style="background-color: #FFF">
                                                    <td colspan="3">{{$errorUser['user_id'].' - '.$errorUser['user_name']}}</td>
                                                    <td style="text-align: center">{{number_format($errorUser['duration'],0)}}</td>
                                                    <td style="text-align: right">{{number_format($errorUser['length'],0)}}</td>
                                                    <td colspan="2" style="text-align: right">{{number_format($errorUser['duration']/60,2)}}</td>
                                                </tr>
                                            @endforeach
                                            @php
                                              $errorUsers = []  ;
                                            @endphp
                                        @endif
                                    @endif
                                @endfor
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
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/rowgroup/1.1.1/js/dataTables.rowGroup.min.js"></script>

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
    <script src="{{asset('assets/global/vendor/matchheight/jquery.matchHeight-min.js')}}"></script>
    <script src="{{asset('assets/remark/examples/js/charts/gauges.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/matchheight.js')}}"></script>
    <script src="{{asset('assets/global/vendor/asrange/jquery-asRange.min.js')}}"></script>

    <script src="{{asset('assets/custom/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/custom/jQuery.print.js')}}"></script>
    <script src="{{asset('assets/js-xlsx/xlsx.core.js')}}"></script>
    <script src="{{asset('assets/file-saver/FileSaver.min.js')}}"></script>
    <script src="{{asset('assets/table-export/js/tableexport.js')}}"></script>
    <script>
        $('#production-report-table-export').tableExport({
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
            // alert("hello");
            var downloadLink;
            var dataType = 'application/vnd.ms-excel';
            var tableSelect = document.getElementById('production-report-table');
            // console.log(tableSelect);
            var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
            // console.log(tableHTML);
            // alert(tableHTML);
            var filename ='SPR Summary';

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

        function exportToExcel(){

            var tab_text="<table border='2px'><tr bgcolor='#87AFC6'>";
            var textRange; var j=0;
            tab = document.getElementById('production-report-table'); // id of table
            console.log(tab_text);
            for(j = 0 ; j < tab.rows.length ; j++) {
                tab_text=tab_text+tab.rows[j].innerHTML+"</tr>";
            }

            tab_text=tab_text+"</table>";
            tab_text= tab_text.replace(/<A[^>]*>|<\/A>/g, "");//remove if u want links in your table
            tab_text= tab_text.replace(/<img[^>]*>/gi,""); // remove if u want images in your table
            tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params

            var ua = window.navigator.userAgent;
            var msie = ua.indexOf("MSIE ");

            if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)){
                txtArea1.document.open("txt/html","replace");
                txtArea1.document.write(tab_text);
                txtArea1.document.close();
                txtArea1.focus();
                sa=txtArea1.document.execCommand("SaveAs",true,"Report.xls");
            }
            else                 //other browser not tested on IE 11
                sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text));

            return (sa);
        }
    </script>
@endsection
