
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
                    <div class="progress" hidden>
                        <div class="progress-bar progress-bar-striped active" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100" style="width: 100%" role="progressbar"></div>
                    </div>
                    <div class="row row-lg">
                        <div class="col-md-6">
                            <div class="example-wrap">
                                <h4 class="example-title">Update Records</h4>
                                <div class="example">
                                    <form method="get" action="{{URL::to('date-wise/machine/records')}}" autocomplete="off">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="example">
                                                    <div class="input-group">
                                                    <span class="input-group-addon">
                                                        <i class="icon md-calendar" aria-hidden="true"></i>
                                                    </span>
                                                        <input type="text" class="form-control date" name="date" value="{{date('m/d/Y')}}" data-plugin="datepicker" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control machine" name="machine" data-plugin="select2" data-placeholder="Select Machine" required>
                                                            <option value=""></option>
                                                            @foreach($user->allowedMachines as $machine)
                                                                <option value="{{$machine->id}}">{{$machine->sap_code.' - '.$machine->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-10">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control job" name="job" data-plugin="select2" data-placeholder="Select Job" required>
                                                            <option value=""></option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group form-material">
                                            <button type="submit" class="btn btn-primary" id="submitProductionReport">Fetch Records</button>
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
    <script src="{{asset('assets/global/vendor/select2/select2.full.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootstrap-select/bootstrap-select.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/select2.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/bootstrap-select.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/bootstrap-datepicker.js')}}"></script>
    <script>
        $('body').on('change', '.machine, .date', function(){
            var machine = $('.machine').val();
            var date = $('.date').val();
            populateJobs(machine,date);
        });

        function populateJobs(machine, date){
            $(document).ajaxStart(function(){
                $('.progress').removeAttr('hidden');
            });
            $(document).ajaxComplete(function(){
                $('.progress').attr('hidden', 'hidden');
            });
            $.ajax({
                url:'{!! URL::to('get/date-wise/machine/jobs') !!}',
                method:'POST',
                data:{
                    machine_id: machine,
                    date: date
                },
                success:function(response){
                    var res = JSON.parse(response);
                    if(res.length > 0){
                        $('.job option').each(function(){
                            $(this).remove();
                        });
                        $('.job').append('<option value=""></option>');
                        for(var i =0; i<res.length; i++){
                            $('.job').append('<option value="'+res[i].job.id+'">'+res[i].job.id+' - '+res[i].job.product.id+' - '+res[i].job.product.name+'</option>')
                        }
                    }
                    else{
                        $('.job option').each(function(){
                            $(this).remove();
                        });
                        $('.job').append('<option value=""></option>');
                    }
                },
                failure:function(response){
                    console.log(response);
                }
            })
        }
    </script>
@endsection