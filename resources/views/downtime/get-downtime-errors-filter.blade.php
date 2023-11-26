@extends('layouts.'.$layout)
@section('header')
    <link rel="stylesheet" href="{{asset('assets/global/fonts/material-design/material-design.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/fonts/brand-icons/brand-icons.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/widgets/chart.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/formvalidation/formValidation.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/select2/select2.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/forms/advanced.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/ladda/ladda.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/uikit/buttons.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/uikit/modals.css')}}">
@endsection
@section('body')
    <div class="page">
        <div class="page-content">
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
                <div class="panel-body container-fluid">
                    <div class="row row-lg">
                        <div class="col-md-12">
                            <div class="example-wrap">
                                <h4 class="example-title">Downtime Report</h4>
                                <div class="example">
                                    <form action="{{URL::to('downtimewitherrorsfilters/update/report'.'/'.\Illuminate\Support\Facades\Crypt::encrypt($machine->id))}}" method="post" enctype="multipart/form-data" autocomplete="off">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="example">
                                                    <div class="input-group">
                                                    <span class="input-group-addon">
                                                        <i class="icon md-calendar" aria-hidden="true"></i>
                                                    </span>
                                                        <input type="text" class="form-control" name="date" value="{{date('m/d/Y')}}" data-plugin="datepicker" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control shiftSelection" name="shiftSelection[]" multiple data-plugin="select2" data-placeholder="Select Shift" required>
                                                            @foreach($machine->section->department->businessUnit->company->shifts as $shift)
                                                                <option value="{{$shift->id}}">{{$shift->shift_number}}</option>
                                                            @endforeach
                                                                <option value="All-Day">All Day</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control shiftSelection" name="errorsCodes[]" multiple data-plugin="select2" data-placeholder="Select ErorCode" required>
                                                            @foreach($errorCodes as $errorCode)
                                                                <option value="{{$errorCode->id}}">{{$errorCode->id}}-{{$errorCode->name}}</option>
                                                            @endforeach
                                                               
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 to-date" hidden>
                                                <div class="example">
                                                    <div class="input-group">
                                                    <span class="input-group-addon">
                                                        <i class="icon md-calendar" aria-hidden="true"></i>
                                                    </span>
                                                        <input type="text" class="form-control" name="to_date" value="{{date('m/d/Y')}}" data-plugin="datepicker">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group form-material">
                                            <button type="submit" class="btn btn-primary" id="submitProductionReport">Generate</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('footer')
    <script src="{{asset('assets/remark/examples/js/charts/gauges.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/matchheight.js')}}"></script>
    <script src="{{asset('assets/global/vendor/jquery-placeholder/jquery.placeholder.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/jquery-placeholder.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/input-group-file.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/bootstrap-datepicker.js')}}"></script>
    <script src="{{asset('assets/global/vendor/select2/select2.full.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootstrap-select/bootstrap-select.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/select2.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/bootstrap-select.js')}}"></script>
    <script>
        $('.shiftSelection').change(function(){
            if($('.shiftSelection').val() == 'All-Day'){
                $('.to-date').removeAttr('hidden');
            }
            else{
                $('.to-date').attr('hidden', 'true');
            }
        });
        $('#reportType').change(function(){
            if($(this).val() == 'operator-wise-oee') {
                $('.job-wise-performance').attr('hidden', 'hidden');
                $('#operator').removeAttr('hidden');
                $('#operator').select2();
                if ($('#operator').val() == 0) {
                    $('#submitProductionReport').attr('disabled', 'disabled');
                }
            }
            else if($(this).val() == 'job-wise-performance'){
                if($('#operator').select2()){
                    $('#operator').select2('destroy');
                }
                $('#operator').attr('hidden', 'hidden');
                $('.job-wise-performance').removeAttr('hidden');
                $('#submitProductionReport').removeAttr('disabled');
            }
            else{
                if($('#operator').select2()){
                    $('#operator').select2('destroy');
                }
                $('#operator').attr('hidden', 'hidden');
                $('.job-wise-performance').attr('hidden', 'hidden');
                $('#submitProductionReport').removeAttr('disabled');
            }
        });

        $('#operator').change(function(){
            if($(this).val() == 0){
                $('#submitProductionReport').attr('disabled','disabled');
            }
            else{
                $('#submitProductionReport').removeAttr('disabled');
            }
        })

    </script>
@endsection