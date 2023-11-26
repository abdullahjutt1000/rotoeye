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
    <link rel="stylesheet" href="{{asset('assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/uikit/modals.css')}}">
    <link rel="stylesheet" href="{{asset('global/vendor/nprogress/nprogress.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/widgets/chart.css')}}">
    <style>
        .customSelect2 {
            z-index: 0 !important;
        }
    </style>
@endsection
@section('body')
    <div class="page">
        <div class="page-content container-fluid">
            <div class="progress" hidden>
                <div class="progress-bar progress-bar-striped active" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100" style="width: 100%" role="progressbar"></div>
            </div>
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
                    <button type="button" class="cloese" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    ERROR : {!! Session::get("error") !!}
                </div>
            @endif

            <div class="row" data-plugin="matchHeight" data-by-row="true">
                <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="card-group">
                        <div class="card card-block p-0">
                            <div class="vertical-align text-center white p-20 h-250" style="background-color: white">
                                <div class="vertical-align-middle" style="vertical-align: top;">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="counter counter-md text-left">
                                                <div class="counter-label grey-600"><strong>Select Date & Shift</strong></div>
                                            </div>
                                            <hr>
                                        </div>
                                        <div class="col-md-4">

                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="icon md-calendar" aria-hidden="true"></i>
                                            </span>
                                                <input type="text" class="form-control" name="date" id="date" value="{{date('m/d/Y')}}" data-plugin="datepicker" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="shift-s">
                                            <select  class="form-control shiftSelection" name="shiftSelection[]" multiple data-plugin="select2"  data-placeholder="Select Shift" required>
                                                @foreach($machine->section->department->businessUnit->company->shifts as $shift)
                                                    <option value="{{$shift->id}}">{{$shift->shift_number}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">

                                        </div>
                                        <div class="col-4" style="padding-top: 15px">
                                            <button type="button" class="btn btn-block btn-primary" id="getRecords">Get Records</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card card-block p-0">
                            <div class="vertical-align text-center blue-roto white p-20 m-0 h-250">
                                <div class="vertical-align-middle">
                                    <div class="row">
                                        <div class="col-4 h-0">
                                            <div class="example round-input-control">
                                                <div class="input-group">
                                                    <input class="form-control form-control-sm downtime-from" id="from" type="text" name="from" placeholder="From" >
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4 h-0">
                                            <div class="example round-input-control">
                                                <div class="input-group">
                                                    <input class="form-control form-control-sm downtime-to" id="to" type="text" name="to" placeholder="To" >
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-4">
                                            <div class="example round-input-control">
                                                <div class="input-group">
                                                    <button class="btn btn-danger" id="fetch-record">Fetch Record</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="hidden-attributes" id="hidden-attributes" style="display: none;" >
                                        <div class="row"  >
                                            <div class="col-4">
                                                <div class="example round-input-control h5 grey-200">
                                                    <a class="text-light h4" href="#" id="job-id" data-dismiss="modal" data-toggle="modal" data-target="#change-job" style="" type="button"></a>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="example round-input-control">
                                                    <select class="form-control" aria-label="Default select example" id="user-id" name="user-id">
                                                        @foreach($user_id as $us_id)
                                                            <option value="{{$us_id->id}}">{{$us_id->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="example round-input-control">
                                                    <select class="form-control" aria-label="Default select example" id="process-id" name="process-id">
                                                        @foreach($process_id as $pros_id)
                                                            <option value="{{$pros_id->id}}">{{$pros_id->process_name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-4">
                                                <div class="example round-input-control">
                                                    <select class="form-control" aria-label="Default select example" id="error-id" name="error-id">
                                                        @foreach($error_id as $err_id)
                                                            <option value="{{$err_id->id}}">{{$err_id->id}}-{{$err_id->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="example round-input-control">
                                                    <div class="input-group">
                                                        <input class="form-control form-control-sm rpm"  id="mtr" type="text" name="mtr" placeholder="Missing Count" required>
                                                        <input  id="cal-speed" type="text" hidden>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="example round-input-control">
                                                    <div class="input-group">
                                                        <button type="button"  class="btn btn-block btn-danger" id="update-manual-records">Done</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mx-0  text-light" id="hidden-attributes-2" style="display: none ">
                <div class="col-12 pb-20 badge-light" >
                    <div class="db_data">
                        <div class="row">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Operator</th>
                                    <th scope="col">Error</th>
                                    <th scope="col">Job</th>
                                    <th scope="col">Machine</th>
                                    <th scope="col">Comment</th>
                                    <th scope="col">Speed</th>
                                    <th scope="col">Run Date Time</th>
                                    <th scope="col">Length</th>
                                    <th scope="col">Process</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <th scope="row">From Limit</th>
                                    <td  id="from-user_id" ></td>
                                    <td  id="from-error_id" ></td>
                                    <td  id="from-job_id" ></td>
                                    <td  id="from-machine_id" ></td>
                                    <td  id="from-err_comments" ></td>
                                    <td  id="from-speed" ></td>
                                    <td  id="from-run_date_time" ></td>
                                    <td  id="from-length" ></td>
                                    <td  id="from-process_id" ></td>
                                </tr>
                                <tr>
                                    <th scope="row">To Limit</th>
                                    <td  id="to-user_id" ></td>
                                    <td  id="to-error_id" ></td>
                                    <td  id="to-job_id" ></td>
                                    <td  id="to-machine_id" ></td>
                                    <td  id="to-err_comments" ></td>
                                    <td  id="to-speed" ></td>
                                    <td  id="to-run_date_time" ></td>
                                    <td  id="to-length" ></td>
                                    <td  id="to-process_id"></td>
                                </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xxl-12 col-md-12">
                    <div class="card card-block p-0">
                        <div class="text-center white p-30">
                            <div class="h-200" id="chartContainer"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <div class="modal example-modal-lg fade" id="change-job" aria-hidden="false" role="dialog" >
        <div class="modal-dialog modal-simple">
            <div class="modal-content" >
                <div class="modal-header bg-danger">
                    <button type="button" class="close white" data-dismiss="modal" aria-label="Close">
                        <span id="changejob-close" aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title white">Change Job</h4>
                </div>
                <div class="modal-body">
                    <div id="changeJob" >
                        <div class="panel-body">
                            <div class="row">
                                <div class="form-group col-md-12" data-plugin="formMaterial">
                                    <select class="form-control selectJob getData removeIndex" id="changejob-job" data-plugin="select2" data-placeholder="Please Select Job" name="job">
                                        <option></option>
                                        @foreach($jobs as $job)
                                            @if(isset($job->product))
                                                <option value="{{$job->id}}" data-product="{{$job->product->id}}">{{$job->id.' - '.$job->product->id.' - '.$job->product->name}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-12" data-plugin="formMaterial">
                                    <select class="form-control selectProcess getData removeIndex" id="changejob-process" data-plugin="select2" data-tags="true" data-placeholder="Select / Add New Process" name="process" required>
                                        <option></option>
                                        @foreach($machine->section->processes as $process)
                                            <option value="{{$process->id}}">{{$process->process_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-12" data-plugin="formMaterial">
                                    <select class="form-control selectMaterialCombination removeIndex" id="changejob-materialcomination" data-plugin="select2" data-tags="true" data-placeholder="Select / Add New Material Combination" name="material_combination" required>
                                        <option></option>
                                        @foreach($materialCombinations as $materialCombination)
                                            <option value="{{$materialCombination->id}}">{{$materialCombination->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xl-6 form-group mb-0">
                                    <input type="text" id="changejob-color" class="form-control" name="color" placeholder="Color">
                                </div>
                                <div class="col-xl-6 form-group mb-0">
                                    <input type="text" id="change-jobadhesive" class="form-control" name="adhesive" placeholder="Adhesive">
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <div class="row">
                                <button type="submit" class="btn btn-primary btn-block col-md-5"  id="submit-job">Submit Job</button>
                                <div class="col-md-2"></div>
                                <button class="btn btn-success btn-block col-md-5" data-dismiss="modal" data-toggle="modal" data-target="#add-new-job" style="margin-top: 0px" type="button">Add New</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>

    </div>
    <div class="modal example-modal-lg fade" id="add-new-job"  aria-hidden="false" role="dialog" >
        <div class="modal-dialog modal-simple">
            <div class="modal-content" >
                <div class="modal-header bg-danger">
                    <button type="button" class="close white" data-dismiss="modal" aria-label="Close">
                        <span id="close" aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title white">New Production Order</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xl-4 form-group">
                            <h4 class="example-title text-left">Product #</h4>
                            <select class="form-control getData selectJob" id="selectProduct" data-plugin="select2" data-tags="true" data-placeholder="Select / Add New" name="product_number" required>
                                <option></option>
                                @foreach($products as $product)
                                    <option value="{{$product->id}}" data-name="{{$product->name}}" data-product="{{$product->id}}">{{$product->id}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-8 form-group">
                            <h4 class="example-title text-left">Product Name</h4>
                            <input type="text" id="productName" class="form-control" name="product_name" required>
                        </div>
                        <div class="col-xl-4 form-group">
                            <h4 class="example-title text-left">Job Card #</h4>
                            <input type="text" class="form-control" id="job" name="job" required>
                        </div>
                        <div class="col-xl-4 form-group">
                            <h4 class="example-title text-left">Job Length</h4>
                            <input type="text" class="form-control" id="job_length" name="job_length" required>
                        </div>
                        <div class="col-xl-4 form-group">
                            <h4 class="example-title text-left">Process</h4>

                            <select class="form-control getData selectProcess" id="process" data-plugin="select2" data-tags="true" data-placeholder="Select / Add New"  name="process" required>
                                <option></option>
                                @foreach($machine->section->processes as $process)
                                    <option value="{{$process->id}}">{{$process->process_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-xl-6 form-group">
                            <h4 class="example-title text-left">Material Combination</h4>
                            <select class="form-control selectMaterial selectMaterialCombination" data-plugin="select2" data-tags="true" data-placeholder="Select / Add New"  id="material_combination" name="material_combination" required>
                                <option></option>
                                @foreach(\App\Models\MaterialCombination::all() as $materialCombination)
                                    <option value="{{$materialCombination->id}}">{{$materialCombination->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 form-group">
                            <h4 class="example-title text-left">Color</h4>
                            <input type="text" id="clr" class="form-control" name="color">
                        </div>
                        <div class="col-xl-4 form-group">
                            <h4 class="example-title text-left">Adhesive</h4>
                            <input type="text" id="adh" class="form-control" name="adhesive">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit" id="saveProceed">Save & Proceed</button>
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
    <script src="{{asset('assets/global/js/Plugin/input-group-file.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
    <script src="{{asset('assets/global/vendor/select2/select2.full.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootstrap-select/bootstrap-select.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/select2.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/bootstrap-select.js')}}"></script>
    <script src="{{asset('assets/global/vendor/asprogress/jquery-asProgress.js')}}"></script>
    <script src="{{asset('assets/global/vendor/jquery-appear/jquery.appear.js')}}"></script>
    <script src="{{asset('assets/global/vendor/nprogress/nprogress.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/jquery-appear.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/nprogress.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/bootstrap-datepicker.js')}}"></script>

    <script>
        $('#add-new-job').click(function (){
            if(document.getElementsByClassName("select2-container select2-container--default select2-container--open")[1].style.display != "none")
            {
                document.getElementsByClassName("select2-container select2-container--default select2-container--open")[1].style.zIndex="1701";
            }
        });
    </script>
    <script>
        $("#add-new-job").click(function (){
            $("#changejob-close").click();
        });
        $("#submit-job").click(function (){
            $.ajax({
                url: '{!! URL::to('manual/submit-job') !!}',
                method: 'POST',
                async: 'FASLE',
                data: {
                    job: $("#changejob-job").val(),
                    process: $("#changejob-process").val(),
                    machine: "{!! $machine->id !!}",
                    color:$("#changejob-color").val(),
                    adhesive:$("#change-jobadhesive").val(),
                    material_combination:$("#changejob-materialcomination").val(),
                },
                statusCode: {
                    200: function (response) {
                        $("#job-id").text(response.job_id);
                        $("#changejob-close").click();
                    },
                    500: function (response) {
                        console.log(response);
                        alert(response.responseText);
                    }
                }
            });
        });
    </script>

    <script>
        $("#saveProceed").click(function (){
            $.ajax({
                url: '{!! URL::to('manual/newjob') !!}',
                method: 'POST',
                async: 'FASLE',
                data: {
                    product_number: $("#selectProduct").val(),
                    product_name: $("#productName").val(),
                    job: $("#productName").val(),
                    job_length: $("#job_length").val(),
                    process: $("#process").val(),
                    machine: "{!! $machine->id !!}",
                    color:$("#clr").val(),
                    adhesive:$("#adh").val(),
                    material_combination:$("#material_combination").val(),
                },
                statusCode: {
                    200: function (response) {
                        $("#job-id").text(response.job_id);
                        $("#close").click();
                    },
                    500: function (response) {
                        console.log(response);
                        alert(response.responseText);
                    }
                }
            });
        })
    </script>
    <script>
        $( document ).ready(function (){
            alert("This Functionality page can alter data, so proceed with knowledge and care");
        });
        $("#fetch-record").click(function (){
            document.querySelector("#shift-s > span").setAttribute("style","z-index:auto");
            $.ajax({
                url: '{!! URL::to('manual/get/recent') !!}',
                method:'GET',
                async: 'FASLE',
                data: {
                    from: $("#from").val(),
                    to: $("#to").val(),
                    machine_id: "{!! $machine->id !!}"
                },
                statusCode: {
                    200: function (response) {
                        $("#error-id").val(response.record[0].error_id).change();
                        $("#job-id").text(response.record[0].job_id);
                        $("#user-id").val(response.record[0].user_id).change();
                        $("#process-id").val(response.record[0].process_id).change();

                        //From record
                        $("#from-user_id").text({!! $user_id !!}.find(function (value){return value.id==response.from_db[0].user_id}).name);
                        $("#from-error_id").text({!! $error_id !!}.find(function (value){return value.id==response.from_db[0].error_id}).id);
                        try{
                            $("#from-job_id").text({!! $jobs !!}.find(function (value){return value.id==response.from_db[0].job_id}).id);

                        }
                        catch (err)
                        {

                        }
                        $("#from-machine_id").text(response.from_db[0].machine_id);
                        $("#from-err_comments").text(response.from_db[0].err_comments);
                        $("#from-speed").text(response.from_db[0].speed);
                        $("#from-run_date_time").text(response.from_db[0].run_date_time);
                        $("#from-length").text(response.from_db[0].length);
                        $("#from-process_id").text({!! $process_id !!}.find(function (value){return value.id==response.from_db[0].process_id}).process_name );

                        //To record
                        $("#to-user_id").text({!! $user_id !!}.find(function (value){return value.id==response.to_db[0].user_id}).name);
                        $("#to-error_id").text({!! $error_id !!}.find(function (value){return value.id==response.to_db[0].error_id}).id);
                        try{
                        $("#to-job_id").text({!! $jobs!!}.find(function (value){return value.id==response.to_db[0].job_id}).id);
                    }
                    catch (err)
                    {

                    }$("#to-machine_id").text(response.to_db[0].machine_id);
                        $("#to-err_comments").text(response.to_db[0].err_comments);
                        $("#to-speed").text(response.to_db[0].speed);
                        $("#to-run_date_time").text(response.to_db[0].run_date_time);
                        $("#to-length").text(response.to_db[0].length).change();
                        $("#to-process_id").text({!! $process_id !!}.find(function (value){return value.id==response.to_db[0].process_id}).process_name);
                        $("#mtr").val(Math.abs(response.to_db[0].length-response.from_db[0].length));
                        $("#hidden-attributes").show();
                        $("#hidden-attributes-2").show();
                    },
                    500: function (response) {
                        console.log(response);
                        alert(response.responseText);
                        $("#hidden-attributes").hide();
                        $("#hidden-attributes-2").hide();
                    }
                }
            });

        });

        $('#update-manual-records').click(function(){
            var speed =Math.round($("#mtr").val() / Math.floor((Math.abs(new Date($("#from").val()) - new Date($("#to").val()))/1000)/60));

            if(confirm("Speed Will be "+speed+"\n Do you want to continue"))
            {
                var from = $("#from").val();
                var to = $("#to").val();
                var mtr = $("#mtr").val();
                var job_id = $("#job-id").text();
                var user_id = $("#user-id").val();
                var process_id = $("#process-id").val();
                var error_id = $("#error-id").val();
                // var err_comment = $("#err-comment").val();
                $.ajax({
                    url: '{!! URL::to('/records/manual/update') !!}',
                    method:'POST',
                    async: 'FASLE',
                    data: {
                        from: from,
                        to: to,
                        mtr: mtr,
                        job_id: job_id,
                        user_id: user_id,
                        process_id: process_id,
                        error_id: error_id,
                        // err_comment: err_comment,
                        machine_id: "{!! $machine->id !!}",
                    },
                    statusCode: {
                        200: function (response) {
                            alert(response);
                            $("#hidden-attributes").hide();
                            $("#hidden-attributes-2").hide();
                            $("#getRecords").click();
                        },
                        500: function (response) {
                            console.log(response);
                            alert(response.responseText);
                        }
                    }
                });
            }
            else {
                history.go(-1);
            }
        })

    </script>

    <script>
        window.onload = function () {
            var dps = [{x: new Date('2018-05-28 00:00:00'), y: 0}];
            var maximumGraphSpeed = '{!! $machine->max_speed !!}';
            var waste_speed = '{!! $machine->waste_speed !!}';
            function onClick(e) {

                if($('.downtime-from').is(':focus')){
                    for (var i = 0; i < dps.length; i++) {
                        if (dps[i].x == e.dataPoint.x) {
                            $('.downtime-from').val(dps[i].x.toLocaleTimeString() + ' ' + dps[i].x.toLocaleDateString());
                            break;
                        }
                    }
                }
                else if($('.downtime-to').is(':focus')){
                    for(var i=0; i<dps.length; i++){
                        if(dps[i].x == e.dataPoint.x){
                            $('.downtime-to').val(dps[i].x.toLocaleTimeString()+' '+dps[i].x.toLocaleDateString());
                        }
                    }
                }
            }
            var chart = new CanvasJS.Chart("chartContainer", {
                interactivityEnabled: true,
                exportEnabled: true,
                zoomEnabled:true,
                theme: 'light2',
                toolTip:{
                    enabled: true,       //disable here
                    animationEnabled: false, //disable here
                    contentFormatter: function (e) {
                        var content = " ";
                        for (var i = 0; i < e.entries.length; i++) {
                            content += "Speed: "+ "<strong>" + e.entries[i].dataPoint.y + "</strong><br>" + "Time: "+ "<strong>" + e.entries[i].dataPoint.x.toLocaleDateString()+' '+e.entries[i].dataPoint.x.toLocaleTimeString() + "</strong>";
                            content += "<br/>";
                        }
                        return content;
                    }
                },
                axisX:{
                    title: "Time",
                    valueFormatString: "H:mm"

                },
                axisY: {
                    includeZero: false,
                    title: "speed",
                    maximum: maximumGraphSpeed,
                    minimum: 0,
                    interval: parseInt('{!! $machine->max_speed !!}')/4,
                    stripLines:[
                        {
                            value: parseInt('{!! $machine->waste_speed !!}'),
                            label: "Waste Speed"
                        }
                    ]
                },
                data: [{
                    type: "line",
                    lineThickness: 3,
                    click: onClick,
                    dataPoints: dps
                }]
            });
            $('#getRecords').click(function(){
                var date = $('#date').val();
                var shifts = $('.shiftSelection').val();
                $(document).ajaxStart(function(){
                    $('.progress').removeAttr('hidden');
                });
                $(document).ajaxComplete(function(){
                    $('.progress').attr('hidden', 'hidden');
                });
                $.ajax({
                    url: '{!! URL::to('get/historic/records') !!}',
                    method:'GET',
                    async: 'FASLE',
                    data: {
                        date: date,
                        shifts: shifts,
                        machine_id: "{!! $machine->id !!}"
                    },
                    statusCode: {
                        //getting the latest record from the database
                        200: function (response) {
                            var responsee = JSON.parse(response);
                            var records = responsee.records;
                            dps.length = 0;
                            for(var i=0; i<records.length; i++){
                                //pushing the new record in the chart
                                dps.push({
                                    x: new Date(records[i].run_date_time),
                                    y: records[i].speed
                                });
                            }
                            chart.render();
                        },
                        500: function (response) {

                        }
                    }
                });
            });
        };
    </script>

    <script src="{{asset('assets/remark/custom/canvas.js')}}"></script>
@endsection
