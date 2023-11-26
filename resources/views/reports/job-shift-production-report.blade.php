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
                <button id="metaData" type="button" class="btn btn-sm btn-icon btn-primary btn-round" data-toggle="tooltip" data-original-title="Print" onclick="javascript:printContent('print-panel');">
                    <i class="icon md-print" aria-hidden="true"></i> Print
                </button>
                <button onclick="exportTableToExcel('production-report-table', '{{$machine->sap_code.'-'.$machine->name.' Shift Production Report'}}')" class="btn btn-sm btn-icon btn-success btn-round" data-toggle="tooltip" data-original-title="Export to Excel">Export to Excel</button>
            </div>
        </div>
        <div class="page-content">
            <div class="row" data-plugin="matchHeight" data-by-row="true">
                <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="panel" id="print-panel" style="min-height: 842px">
                        <header class="panel-heading">
                            <h3 class="panel-title">
                                <strong>Daily Shift Production Report - Next (Job) </strong> - <small>{{isset($from)?$from.' to '.$to:$date}}</small>
                                <small style="float: right">{{$current_time}}</small><br>
                                @if($shift[0] == 'All-Day')
                                    <small>{{$machine->sap_code.' - '.$machine->name.', '.$machine->section->name.', Shift '}}{{count($shift) == 1 ? $shift[0]:""}}</small><br>
                                @else
                                    <small>{{$machine->sap_code.' - '.$machine->name.', '.$machine->section->name.', Shift '}}{{count($shift) == 1 ? \App\Models\Shift::find($shift[0])->shift_number:\App\Models\Shift::find($shift[0])->shift_number.' to '.\App\Models\Shift::find($shift[count($shift)-1])->shift_number}}</small><br>
                                @endif
                                <small>Day Production: <strong>{{number_format($produced, 0)}}</strong></small><br>

                            </h3>
                        </header>
                        <div class="panel-body" id="export-panel">
                            <table class="table table-hover dataTable table-striped w-full production-report-table" id="production-report-table">
                                <thead>
                                <tr>
                                    <th>Err No</th>
                                    <th>Err Name</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th style="text-align: center;">Duration <br>(Min)</th>
                                    <th style="text-align: right">{{$machine->qty_uom}}</th>
                                    <th style="text-align: right">EA's</th>
                                    <th style="text-align: right">Kgs</th>
                                    <th style="text-align: right">Kgs/Hr</th>
                                    <th style="text-align: right">Tons/Hr</th>
                                    <th style="width: 35%;text-align: center">Comments</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @for($i = 0; $i<count($records); $i++)
                                    @if(isset($records[$i-1]['job_id']))
                                        @if($records[$i]['job_id'] != $records[$i-1]['job_id'])
                                            {{--                                            Work Here--}}
                                            <tr style="background-color:rgb(246,246,0); color: black">
                                                <td colspan="3">
                                                    <strong>Job No: </strong>{{$records[$i]['job_id'].' - '.$records[$i]['product_number']}}<br>
                                                    <strong>Job Name: </strong>{{$records[$i]['job_name']}}<br>
                                                    <strong>Target Hours: </strong>{{number_format($records[$i][0]['jobTargetHour'],2)}} min<br>
                                                </td>
                                                <td colspan="6">
                                                    <strong>Substrate: </strong>{{$records[$i]['material_combination']}}<br>{{$records[$i]['process_name']}}<br>
                                                    <strong>Color: </strong>{{$records[$i]['process_structure_color']}}
                                                </td>
                                                <td colspan="2">
                                                    <strong>Required: </strong>{{number_format($records[$i]['job_length'], 0).' '.$machine->qty_uom}}<br>
                                                    <strong>Produced: </strong>{{number_format($records[$i][0]['jobProduction'], 0).' '.$machine->qty_uom}}<br>
                                                    <strong>Produced (EAs): </strong>{{number_format($records[$i][0]['jobEa'], 0)}}<br>
                                                    <strong>Produced (GSM): </strong>{{number_format($records[$i][0]['jobGsm'], 0)}}<br>
                                                </td>
                                            </tr>
                                            <tr style="background-color:black; color: white">
                                                <td colspan="3">
                                                    <strong>No. of UP's (Job): </strong>{{isset($records[$i]['job_ups'])?$records[$i]['job_ups']:'-'}}<br>
                                                    <strong>Trim Width (mm) (Job): </strong>{{isset($records[$i]['job_trimwidth'])?$records[$i]['job_trimwidth']*1000:'-'}}<br>
                                                    <strong>Slitted Reel Width (mm) (Job): </strong>{{isset($records[$i]['job_reelwidth'])?$records[$i]['job_reelwidth']*1000:'-'}}<br>
                                                    <strong>Possible Addition of UP's (Job): </strong> @if(isset($machine->machine_width)&&isset($records[$i]['job_trimwidth'])&&isset($records[$i]['job_reelwidth'])&&isset($records[$i]['job_ups']))

                                                        {{number_format(($machine->machine_width-($records[$i]['job_trimwidth']+($records[$i]['job_reelwidth']*$records[$i]['job_ups'])))/$records[$i]['job_reelwidth'],0)}}
                                                    @else
                                                        -
                                                    @endif
                                                    <br>
                                                </td>
                                                <td colspan="6">
                                                    <strong>Thickness (mm) (Job): </strong>{{isset($records[$i]['job_thickness'])?$records[$i]['job_thickness']*1000:'-'}}<br>
                                                    <strong>Density (Job): </strong>{{isset($records[$i]['job_density'])?$records[$i]['job_density']:'-'}}<br>
                                                    <strong>GSM(Kg's) (Job): </strong>{{isset($records[$i]['job_gsm'])?$records[$i]['job_gsm']*1000:'-'}}<br>
                                                </td>
                                                <td colspan="2">
                                                    <strong>COL: </strong>{{isset($records[$i]['product_col'])?$records[$i]['product_col']*1000:'-'}}<br>
                                                    <strong>Sleeve speed: </strong>{{isset($records[$i]['product_sleeve_speed'])?$records[$i]['product_sleeve_speed']:'-'}}<br>
                                                    <strong>Sleeve Cirumference: </strong>{{isset($records[$i]['product_sleeve_circumference'])?$records[$i]['product_sleeve_circumference']:'-'}}
                                                </td>
                                            </tr>
                                            <tr style="background-color: lightgrey; color: black">
                                                <td colspan="2" >
                                                    <p style="margin-bottom: 0px">Job Runtime:</p> <strong style="color: darkred;"> {{number_format($records[$i][0]['jobRuntime'],0)}} min</strong>
                                                </td>
                                                <td colspan="3">
                                                    <p style="margin-bottom: 0px">Utilization:</p> <strong style="color: darkred;"> {{number_format($records[$i][0]['jobUtilization'],2)}}% </strong>
                                                </td>
                                                <td colspan="4">
                                                    <p style="margin-bottom: 0px">Job Performance:</p> <strong style="color: darkred;"> {{number_format($records[$i][0]['jobPerformance'],2)}}%</strong>
                                                </td>
                                                <td colspan="2">
                                                    <p style="margin-bottom: 0px">Average Running Speed:</p> <strong style="color: darkred;"> {{number_format($records[$i][0]['jobAverageSpeed'],0).' '.$machine->qty_uom.'/'.$machine->time_uom}}</strong>
                                                </td>
                                            </tr>
                                        @endif
                                    @else
                                        <tr style="background-color:rgb(246,246,0); color: black">
                                            <td colspan="3">
                                                <strong>Job No: </strong>{{$records[$i]['job_id'].' - '.$records[$i]['product_number']}}<br>
                                                <strong>Job Name: </strong>{{$records[$i]['job_name']}}<br>
                                                <strong>Target Hours: </strong>{{number_format($records[$i][0]['jobTargetHour'],2)}} min<br>
                                            </td>
                                            <td colspan="6">
                                                <strong>Substrate: </strong>{{$records[$i]['material_combination']}}<br>{{$records[$i]['process_name']}}<br>
                                                <strong>Color: </strong>{{$records[$i]['process_structure_color']}}
                                            </td>
                                            <td colspan="2">
                                                <strong>Required: </strong>{{number_format($records[$i]['job_length'], 0).' '.$machine->qty_uom}}<br>
                                                <strong>Produced: </strong>{{number_format($records[$i][0]['jobProduction'], 0).' '.$machine->qty_uom}}<br>
                                                <strong>Produced (EAs): </strong>{{number_format($records[$i][0]['jobEa'], 0)}}<br>
                                                <strong>Produced (GSM): </strong>{{number_format($records[$i][0]['jobGsm'], 0)}}<br>
                                            </td>
                                        </tr>
                                        <tr style="background-color:black; color: white">
                                            <td colspan="3">
                                                <strong>No. of UP's (Job): </strong>{{isset($records[$i]['job_ups'])?$records[$i]['job_ups']:'-'}}<br>
                                                <strong>Trim Width (mm) (Job): </strong>{{isset($records[$i]['job_trimwidth'])?$records[$i]['job_trimwidth']*1000:'-'}}<br>
                                                <strong>Slitted Reel Width (mm) (Job): </strong>{{isset($records[$i]['job_reelwidth'])?$records[$i]['job_reelwidth']*1000:'-'}}<br>
                                                <strong>Possible Addition of UP's: </strong> @if(isset($machine->machine_width)&&isset($records[$i]['job_trimwidth'])&&isset($records[$i]['job_reelwidth'])&&isset($records[$i]['job_ups']))

                                                    {{number_format(($machine->machine_width-($records[$i]['job_trimwidth']+($records[$i]['job_reelwidth']*$records[$i]['job_ups'])))/$records[$i]['job_reelwidth'],0)}}
                                                @else
                                                    -
                                                @endif
                                                <br>
                                            </td>
                                            <td colspan="6">
                                                <strong>Thickness (mm) (Job): </strong>{{isset($records[$i]['job_thickness'])?$records[$i]['job_thickness']*1000:'-'}}<br>
                                                <strong>Density (Job):  </strong>{{isset($records[$i]['job_density'])?$records[$i]['job_density']:'-'}}<br>
                                                <strong>GSM(Kg's) (Job) : </strong>{{isset($records[$i]['job_gsm'])?$records[$i]['job_gsm']*1000:'-'}}<br>
                                            </td>
                                            <td colspan="2">
                                                <strong>COL: </strong>{{isset($records[$i]['product_col'])?$records[$i]['product_col']*1000:'-'}}<br>
                                                <strong>Sleeve speed: </strong>{{isset($records[$i]['product_sleeve_speed'])?$records[$i]['product_sleeve_speed']:'-'}}<br>
                                                <strong>Sleeve Cirumference: </strong>{{isset($records[$i]['product_sleeve_circumference'])?$records[$i]['product_sleeve_circumference']:'-'}}
                                            </td>
                                        </tr>
                                        <tr style="background-color: lightgrey; color: black">
                                            <td colspan="2" >
                                                <p style="margin-bottom: 0px">Job Runtime:</p> <strong style="color: darkred;"> {{number_format($records[$i][0]['jobRuntime'],0)}} min</strong>
                                            </td>
                                            <td colspan="3">
                                                <p style="margin-bottom: 0px">Utilization:</p> <strong style="color: darkred;"> {{number_format($records[$i][0]['jobUtilization'],2)}}% </strong>
                                            </td>
                                            <td colspan="4">
                                                <p style="margin-bottom: 0px">Job Performance:</p> <strong style="color: darkred;"> {{number_format($records[$i][0]['jobPerformance'],2)}}%</strong>
                                            </td>
                                            <td colspan="2">
                                                <p style="margin-bottom: 0px">Average Running Speed:</p> <strong style="color: darkred;"> {{number_format($records[$i][0]['jobAverageSpeed'],0).' '.$machine->qty_uom.'/'.$machine->time_uom}}</strong>
                                            </td>
                                        </tr>
                                    @endif
                                    @if(isset($records[$i-1]['user_id']))
                                        @if($records[$i]['user_id'] != $records[$i-1]['user_id'])
                                            <tr style="background-color: white; color: darkred">
                                                <td colspan="10"><strong>{{$records[$i]['user_name']}}</strong></td>
                                            </tr>
                                        @endif
                                    @else
                                        <tr style="background-color: white; color: darkred">
                                            <td colspan="10"><strong>{{$records[$i]['user_name']}}</strong></td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td >{{$records[$i]['error_id']}}</td>
                                        <td style="width: 10%;">{{$records[$i]['error_name']}}</td>
                                        <td class="text-center">{{date('H:i',strtotime($records[$i]['from']))}}<br><small>{{date('d M, Y',strtotime($records[$i]['from']))}}</small></td>
                                        <td class="text-center">{{date('H:i',strtotime($records[$i]['to']))}}<br><small>{{date('d M, Y',strtotime($records[$i]['to']))}}</small></td>
                                        <td style="text-align: center">{{number_format($records[$i]['duration'],0)}}</td>
                                        <td style="text-align: center">{{$records[$i]['length']}}</td>
                                        <td style="text-align: center">{{$records[$i]['ea']}}</td>
                                        <td style="text-align: center">{{$records[$i]['gsm']}}</td>
                                        <td style="text-align: center">{{$records[$i]['pope_production_kgs']}}</td>
                                        <td style="text-align: center">{{$records[$i]['pope_production']}}</td>
                                        
                                        <td style="width: 35%;">{{$records[$i]['comments']}}</td>
                                        <td style="text-align: right"><small>{{number_format($records[$i]['instantSpeed'],1)}}</small></td>
                                    </tr>
                                @endfor
                                </tbody>
                            </table>
                            <hr style="border-top: 4px solid #336699">
                            <div class="row col-12">
                                <div class="col-2">
                                    <u>Budgeted Time:</u> <strong>{{number_format($budgetedTime, 0)}} min</strong><br>
                                    <u>Target Hours:</u> <strong>{{number_format($targetHours, 0)}} min</strong>
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
                                    <p style="text-align: right; margin-bottom: 0;"><u >Utilization:</u> <strong>{{number_format($utilization, 2)}} %</strong></p>
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
        function printContent(id){
            var data = document.getElementById(id).innerHTML;
            var popupWindow = window.open('','printwin', 'left=100,top=100,width=1000,height=400');
            popupWindow.document.write('<HTML>\n<HEAD>\n');
            popupWindow.document.write('<TITLE></TITLE>\n');
            popupWindow.document.write('<URL></URL>\n');
            popupWindow.document.write("<link href='/assets/custom/print.css' media='print' rel='stylesheet' type='text/css' />\n");
            popupWindow.document.write("<link href='/assets/custom/print.css' media='screen' rel='stylesheet' type='text/css' />\n");
            popupWindow.document.write("<style>body{font-size: 10px}</style>\n");
            popupWindow.document.write("<style type=text/css media=print>.production-report-table thead { -webkit-print-color-adjust: exact; }</style>\n");
            popupWindow.document.write("<style type=text/css media=print>.production-report-table thead tr{ -webkit-print-color-adjust: exact; }</style>\n");
            popupWindow.document.write("<style type=text/css media=print>.production-report-table tbody tr td{ -webkit-print-color-adjust: exact; }</style>\n");
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
    <script>
        function exportTableToExcel(tableID, filename){
            var downloadLink;
            var dataType = 'application/vnd.ms-excel';
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
    <script src="{{asset('assets/custom/jsPDF.js')}}"></script>
    <script src="{{asset('assets/custom/html2canvas.js')}}"></script>
    <script>
        margins = {
            top: 70,
            bottom: 40,
            left: 30,
            width: 1050
        };
        async function exportPDF(){
            var doc = new jsPDF('p', 'mm', 'a4');
            var canvas = await html2canvas(document.getElementById('export-panel'));
            let imgData = canvas.toDataURL('image/png'); // optional
            var imgWidth = 208;
            var pageHeight = 295;
            var imgHeight = canvas.height * imgWidth / canvas.width;
            var heightLeft = imgHeight;
            doc.addImage(imgData, 'PNG', 0, 0,imgWidth, imgHeight); // imgData or canvas
            doc.save('test.pdf');
        }
        function headerFooterFormatting(doc)
        {
            var totalPages  = doc.internal.getNumberOfPages();
            for(var i = totalPages; i >= 1; i--)
            { //make this page, the current page we are currently working on.
                doc.setPage(i);
                header(doc);
            }
        }
        function header(doc)
        {
            doc.setFontSize(30);
            doc.setTextColor(40);
            doc.setFontStyle('normal');
            if (base64Img) {
                doc.addImage(base64Img, 'JPEG', margins.left, 10, 40,40);
            }
            doc.text("Report Header Template", margins.left + 50, 40 );
            doc.line(3, 70, margins.width + 43,70); // horizontal line
        }
        imgToBase64('octocat.jpg', function(base64) {
            base64Img = base64;
        });
        function imgToBase64(url, callback, imgVariable) {
            if (!window.FileReade) {
                callback(null);
                return;
            }
            var xhr = new XMLHttpRequest();
            xhr.responseType = 'blob';
            xhr.onload = function() {
                var reader = new FileReader();
                reader.onloadend = function() {
                    imgVariable = reader.result.replace('text/xml', 'image/jpeg');
                    callback(imgVariable);
                };
                reader.readAsDataURL(xhr.response);
            };
            xhr.open('GET', url);
            xhr.send();
        };
    </script>
@endsection
