@extends('layouts.'.$layout)
@section('header')
    <link rel="stylesheet" href="{{asset('assets/global/vendor/gauge-js/gauge.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/fonts/material-design/material-design.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/fonts/brand-icons/brand-icons.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/chartist/chartist.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/widgets/chart.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/formvalidation/formValidation.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/select2/select2.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/forms/advanced.css')}}">

    <link rel="stylesheet" href="{{asset('assets/global/vendor/ladda/ladda.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/uikit/buttons.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/uikit/modals.css')}}">
    <link rel="stylesheet" href="{{asset('global/vendor/nprogress/nprogress.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/widgets/chart.css')}}">

    <link rel="stylesheet" href="{{asset('assets/global/vendor/alertify/alertify.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/notie/notie.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/advanced/alertify.css')}}">
    <style>
        .customSelect2 {
            z-index: 0 !important;
        }
        .list-group {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -ms-flex-direction: column;
    flex-direction: column;
    padding-left: 0;
    margin-bottom: 0;
}
.list-group-item {
    position: relative;
    display: block;
    padding: 0.75rem 1.25rem;
    margin-bottom: -1px;
    background-color: #fff;
    border: 1px solid rgba(0,0,0,.125);
}

/* Remove default bullets */
ul, #myUL {
  list-style-type: none;
}

/* Remove margins and padding from the parent ul */
#myUL {
  margin: 0;
  padding: 0;
}

/* Style the caret/arrow */
.caret {
  cursor: pointer;
  user-select: none; /* Prevent text selection */
}

/* Create the caret/arrow with a unicode, and style it */
.caret::before {
  content: "\002B";
  color: black;
  display: inline-block;
  margin-right: 6px;
  padding-left: 5px;
}

/* Rotate the caret/arrow icon when clicked on (using JavaScript) */
.caret-down::before {
    content: "\002D";
    color: black;
  display: inline-block;
}

/* Hide the nested list */
.nested {
  display: none;
}

/* Show the nested list when the user clicks on the caret/arrow (with JavaScript) */
.active {
  display: block;
}
    </style>
@endsection
@section('body')
     @php 
     $company_id =isset($_REQUEST['company_id'])?$_REQUEST['company_id']:0;
     $business_unit_id =isset($_REQUEST['business_unit_id'])?$_REQUEST['business_unit_id']:0;
     $department_id =isset($_REQUEST['department_id'])?$_REQUEST['department_id']:0;
     $section_id =isset($_REQUEST['section_id'])?$_REQUEST['section_id']:0;
     $machine_id =isset($_REQUEST['machine_id'])?$_REQUEST['machine_id']:0;
     use App\Helper\Helper;
     $hp = new Helper();
     @endphp
    <div class="page">
        <div class="row">
            <div class="col-2" style="padding-left: 0;padding-right: 0;">
                <div class="page-content" style="padding-right: 0; height:100%">
                    <div class="panel">
                    <ul id="myUL">
                @foreach($companies as $company)
                    <li><a href ='{{url('group-dashboard-report?daterange='.$daterange.'&grp=business_unit_id&company_id='.$company->id)}}' style="color:#000;" ><span class="caret @if($company_id==$company->id) caret-down @endif">{{$company->name}}</span></a>
                     @if($company->businessUnits->count()>0)
                      <ul class="nested @if($company_id==$company->id) active @endif" style="padding-left: 10px;">
                      @foreach($company->businessUnits as $bu)  
                        <li><a href ='{{url('group-dashboard-report?daterange='.$daterange.'&company_id='.$company->id.'&business_unit_id='.$bu->id)}}' style="color:#000;" ><span class="caret  @if($business_unit_id==$bu->id) caret-down @endif">{{$bu->business_unit_name}}</span></a>
                        @if($bu->departments->count()>0) 
                            <ul class="nested @if($business_unit_id==$bu->id) active @endif" style="padding-left: 10px;">
                             @foreach($bu->departments as $dept) 
                                <li><a href ='{{url('group-dashboard-report?daterange='.$daterange.'&company_id='.$company->id.'&business_unit_id='.$bu->id.'&department_id='.$dept->id)}}' style="color:#000;"><span class="caret @if($department_id==$dept->id) caret-down @endif">{{$dept->name}}</span></a>
                                @if($dept->sections->count()>0) 
                                    <ul class="nested @if($department_id==$dept->id) active @endif" style="padding-left: 10px;">
                                    @foreach($dept->sections as $section) 
                                    <li><a href ='{{url('group-dashboard-report?daterange='.$daterange.'&company_id='.$company->id.'&business_unit_id='.$bu->id.'&department_id='.$dept->id.'&section_id='.$section->id)}}' style="color:#000;" ><span class="caret @if($department_id==$dept->id) caret-down @endif ">{{$section->name}}</span></a>
                                        @if($section->machines->count()>0) 
                                        <ul class="nested @if($section_id==$section->id) active @endif" style="padding-left: 10px;">
                                            @foreach($section->machines as $machine) 
                                             @php
                                             // dd($machine->id);
                                             $uss = $hp->getMachineUsers($machine->id,$daterange);
                                              
                                            // dd($uss);
                                             @endphp
                                            <li><a href ='{{url('group-dashboard-report?daterange='.$daterange.'&company_id='.$company->id.'&business_unit_id='.$bu->id.'&department_id='.$dept->id.'&section_id='.$section->id.'&machine_id='.$machine->id)}}' style="color:#000;"><span class="caret @if($machine_id==$machine->id) caret-down @endif">{{$machine->name}}</span></a>
                                             @if(count($uss)>0) 
                                           
                                                <ul class="nested @if($machine_id==$machine->id) active @endif" style="padding-left: 10px;">
                                                
                                                @for($i=0;$i< count($uss);$i++) 
                                                
                                                     @if(isset($uss[$i]->operator_name) && $uss[$i]->operator_name!='')
                                                    {{-- <li><a href ='{{url('group-dashboard-report?daterange='.$daterange.'&company_id='.$company->id.'&business_unit_id='.$bu->id.'&department_id='.$dept->id.'&section_id='.$section->id.'&machine_id='.$machine->id.'&operator_id='.$uss[$i]->operator_id)}}' style="color:#000;"><span class="caret-down ">{{$uss[$i]->operator_name}}</span></a></li> --}}
                                                    <li><span class="caret-down ">{{$uss[$i]->operator_name}}</span></li>
                                                    @endif
                                                    @endfor
                                                </ul>
                                               @endif
                                            </li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </li>
                                    @endforeach   
                                    
                                    </ul>
                                @endif    
                                </li>
                             @endforeach   
                            </ul>
                        @endif    
                        </li>
                       @endforeach 
                      </ul>
                     @endif 
                    </li>
                @endforeach
                    </ul>
    </div>
                </div>
    </div>
    <div class="col-10">
    <div class="page-content container-fluid" style="padding-left: 0;">
            @if(Session::has("success"))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    SUCCESS : {{ Session::get("success") }}
                </div>
            @endif
            @if (count($errors) > 0)
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <p>Please fix the following issues to continue</p>
                    <ul class="error">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(Session::has("error"))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    ERROR : {!! Session::get("error") !!}
                </div>
            @endif
            <div class="panel">
                @php 
                 $date_range = explode(" - ",$daterange);
                @endphp
                <header class="panel-heading">
                <button style="margin: 20px 0px 0px 20px;"  onclick="exportTableToExcel('exampleTableSearch', 'group-dashboard-report-table')" class="btn btn-sm btn-icon btn-success btn-round" data-toggle="tooltip" data-original-title="Export to Excel">Export to Excel</button>
                    <h3 class="panel-title" style="padding-bottom: 6px;">Group Dashboard</h3>
                    <small  style="padding: 20px 30px;">{{ date('M d, Y',strtotime($date_range[0])).' to '.date('M d, Y',strtotime($date_range[1]))}}</small>
                </header>
                
                
             
                <div class="panel-body table-responsive     ">
                    <table class="table table-hover dataTable table-striped w-full text-nowrap" id="exampleTableSearch">
                        <thead>
                        <tr>
                            <th>Machine</th>
                            <th>Operator</th>
                            <th>Company</th>
                            <!-- <th>Month</th> -->
                            <th>Date</th>
                            <th>BU</th>
                            <th>Dept</th>
                            <th>Section</th>
                            <th>OEE</th>
                            <th>Availability</th>
                            <th>Availability(EE) </th>
                            <th>Performance</th>
                            <th>Quality</th>
                            <th>Total Time</th>
                            <th>Running Time </th>
                            <th>Idle Time </th>
                            <th>Budgeted Time </th>
                            <th>Job Waiting  </th>
                            <th>Budgeted Time(EE)  </th>
                            <th>Actual Speed </th>
                            <th>Ideal Production </th>
                            <th>Production </th>
                            <th>Design Speed  </th>
                            <th>Total Production  </th>
                        </tr>
                        </thead>
                        <tfoot>
                        
                        </tfoot>
                        <tbody>
                                   
                                @php 
                                $colective_oee =0;
                                $colective_running_time=0;
                                $colective_budgeted_time=0;
                                $colective_actual_speed=0;
                                $colective_designed_speed=0;
                                $colective_actual_speed=0;
                                $colective_total_production=0;
                                $colective_ideal_production=0;
                                $colective_total_time=0;
                                $colective_prodcution=0;
                                $colective_budgeted_time_ee=0;
                                $colective_idle_time=0;
                                $colective_job_waiting=0;

                               
                                @endphp 
                                @if(count($records))
                                @foreach($records as $employee)
                                @php 
                                $colective_total_time = $colective_total_time + $employee['total_time'];
                                $colective_running_time = $colective_running_time + $employee['total_running_time'];
                                
                                $colective_budgeted_time = $colective_budgeted_time + $employee['budgeted_time'];
                               
                                $colective_total_production = $colective_total_production + $employee['total_production'];
                               
                                $colective_designed_speed = $colective_designed_speed + $employee['designed_speed'];
                                
                              
                              
                                $colective_ideal_production = $colective_ideal_production + $employee['ideal_production'];
                               
                                $colective_prodcution = $colective_prodcution + $employee['total_production'];
                               
                                $colective_budgeted_time_ee = $colective_budgeted_time_ee + $employee['budgeted_time_ee'];
                                $colective_idle_time = $colective_idle_time + $employee['idle_time'];
                                $colective_job_waiting = $colective_job_waiting + $employee['job_waiting'];
                                
                               // dd($colective_budgeted_time_ee);
                                @endphp 
                              <tr>
                                    <td>{{$employee['machine_no']}} </td>
                                    <td>{{$employee['operator']}} </td>
                                    <td>{{$employee['company']}}</td>
                                    <!-- <td></td> -->
                                    <td>{{$employee['date']}}</td>
                                    <td>{{$employee['bu']}}</td>
                                    <td>{{$employee['dept']}}</td>
                                    <td>{{$employee['section']}}</td>
                                    <td>{{number_format($employee['oee']*100,2)}}</td>
                                    <td>{{number_format($employee['availability']*100,2)}}</td>
                                    <td>{{number_format($employee['availability_ee']*100,2)}}</td>
                                    <td>{{number_format($employee['performance']*100,2)}}</td>
                                    <td>{{number_format($employee['quality']*100)}}</td>
                                    <td>{{$employee['total_time']}}</td>
                                    <td>{{number_format($employee['total_running_time'],0)}}</td>
                                    <td>{{number_format($employee['idle_time'],0)}}</td>
                                    <td>{{number_format($employee['budgeted_time'],0)}}</td>
                                    <td>{{$employee['job_waiting']}}</td>
                                    <td>{{number_format($employee['budgeted_time_ee'],0)}}</td>
                                    <td>{{number_format($employee['actual_speed'],0)}}</td>
                                    <td>{{number_format($employee['ideal_production'],0)}}</td>
                                    <td>{{$employee['total_production']}}</td>
                                    <td>{{number_format($employee['designed_speed'],0)}}</td>
                                    <td>{{$employee['total_production']}}</td>
                                   
                                    
                                </tr>

                               
                            @endforeach  
                            @php 
                         //dd($colective_total_time);
                              $collective_availibilty = ($colective_budgeted_time>0)?$colective_running_time / $colective_budgeted_time:0;
                               $colective_actual_speed = ($colective_running_time>0)?$colective_total_production / $colective_running_time:0;
                               $colective_designed_speed = ($colective_total_time>0)?($colective_ideal_production / $colective_total_time):0;
                               $colective_performace =($colective_designed_speed>0)? $colective_actual_speed / $colective_designed_speed:0;
                               $colective_quality = ($colective_total_production>0)?$colective_total_production / $colective_total_production:0;
                               $colective_oee = ($colective_performace * $collective_availibilty * $colective_quality);
                               $collective_availibilty_ee = ($colective_budgeted_time_ee>0)?($colective_running_time / $colective_budgeted_time_ee):0 ; 
                            //dd($colective_oee);   

                            @endphp

                             <tr>
                                    <th>  </th>
                                    <th>  </th>
                                   
                                    <th> </th>
                                    <th> </th>
                                    <th> </th>
                                    <th> </th>
                                    <th>Total </th>
                                    <th>{{number_format($colective_oee*100,2) }} </th>
                                    <th>{{number_format($collective_availibilty*100,2)}} </th>
                                    <th>{{number_format($collective_availibilty_ee*100,2)}} </th>
                                    <th>{{number_format($colective_performace*100,2)}} </th>
                                    <th>{{$colective_quality*100}}</th>
                                    <th>{{$colective_total_time}}</th>
                                    <th>{{number_format($colective_running_time,0)}}</th>
                                    <th>{{number_format($colective_idle_time,0)}}</th>
                                    <th>{{number_format($colective_budgeted_time,0)}}</th>
                                    <th>{{$colective_job_waiting}}</th>
                                    <th>{{number_format($colective_budgeted_time_ee,0)}}</th>
                                    <th>{{number_format($colective_actual_speed,0)}}</th>
                                    <th>{{number_format($colective_ideal_production,0)}}</th>
                                    <th>{{$colective_prodcution}}</th>
                                    <th>{{number_format($colective_designed_speed,0)}} </th>
                                    <th>{{$colective_total_production}} </th>
                                   
                                    
                                </tr>  






                        @endif
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
    <script src="{{asset('assets/global/vendor/sparkline/jquery.sparkline.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/chartist/chartist.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/matchheight/jquery.matchHeight-min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/gauge-js/gauge.min.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/gauge.js')}}"></script>
    <script src="{{asset('assets/remark/examples/js/charts/gauges.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/matchheight.js')}}"></script>
    <script src="{{asset('assets/global/vendor/jquery-placeholder/jquery.placeholder.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/jquery-placeholder.js')}}"></script>

    <script src="{{asset('assets/global/vendor/select2/select2.full.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootstrap-select/bootstrap-select.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/select2.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/bootstrap-select.js')}}"></script>

    <script src="{{asset('assets/global/vendor/asprogress/jquery-asProgress.js')}}"></script>
    <script src="{{asset('assets/global/vendor/jquery-appear/jquery.appear.js')}}"></script>
    <script src="{{asset('assets/global/vendor/nprogress/nprogress.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/jquery-appear.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/nprogress.js')}}"></script>

    <script src="{{asset('assets/global/vendor/alertify/alertify.js')}}"></script>
    <script src="{{asset('assets/global/vendor/notie/notie.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/alertify.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/notie-js.js')}}"></script>

   
    <script src="{{asset('assets/remark/custom/canvas.js')}}"></script>
    <script>
        var toggler = document.getElementsByClassName("caret");
var i;

for (i = 0; i < toggler.length; i++) {
  toggler[i].addEventListener("click", function() {
    this.parentElement.querySelector(".nested").classList.toggle("active");
    this.classList.toggle("caret-down");
  });
}
        function exportTableToExcel(tableID, filename){
            alert(filename);
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
