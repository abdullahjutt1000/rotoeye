@extends('layouts.login-layout')
@section('header')
    <link rel="stylesheet" href="{{asset('assets/global/fonts/material-design/material-design.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/fonts/brand-icons/brand-icons.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/jquery-wizard/jquery-wizard.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/formvalidation/formValidation.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/select2/select2.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/forms/advanced.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/uikit/modals.css')}}">
    <style>
        .customSelect2 {
            z-index: 0 !important;
        }
    </style>
@endsection
@section('body')
    <div class="page-content vertical-align-middle">
        <div class="panel">
            @if (count($errors) > 0)
                <div class="alert alert-danger bg-danger">
                    <p>Please fix the following issues to continue</p>
                    <ul class="error">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(Session::has("error"))
                <div class="alert alert-error bg-danger">
                    {{ Session::get("error") }}
                </div>
            @endif
            @if(Session::has("success"))
                <div class="alert bg-green bg-success">
                    {{ Session::get("success") }}
                </div>
            @endif
            <form action="{{URL::to('process-structure/update')}}" method="post">
                <div class="panel-body">
                    <div class="brand">
                        <img class="brand-img" src="{{asset('assets/icons/Logo_RotoEye_Version_160 cross 160_PNG_1.0.png')}}" alt="..." style="width: 40%;height: auto;">
                    </div>
                    <hr>
                    <p>Hello <strong>{{$operator->name}}</strong>, Please select product & process to update the process structure.</p>
                    <hr>
                    <input name = "machine" type="text" value="{{$machine->id}}" hidden>
                    <input name = "user" type="text" value="{{$operator->id}}" hidden>
                    <div class="row">
                        <div class="form-group col-md-12" data-plugin="formMaterial">
                            <select class="form-control selectJob getData removeIndex" data-plugin="select2" data-placeholder="Please Select Job" name="job">
                                <option></option>
                                @foreach($jobs as $job)
                                    @if(isset($job->product))
                                        <option value="{{$job->id}}">{{$job->id.' - '.$job->product->id.' - '.$job->product->name}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12" data-plugin="formMaterial">
                            <select class="form-control selectProcess getData removeIndex" data-plugin="select2" data-placeholder="Select Process" name="process">
                                <option></option>
                                @foreach($machine->section->processes as $process)
                                    <option value="{{$process->id}}">{{$process->process_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12" data-plugin="formMaterial">
                            <select class="form-control selectMaterialCombination removeIndex" data-plugin="select2" data-tags="true" data-placeholder="Select Material Combination" name="material_combination">
                                <option></option>
                                @foreach($materialCombinations as $materialCombination)
                                    <option value="{{$materialCombination->id}}">{{$materialCombination->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-5"><button type="submit" class="btn btn-success btn-block" id="updateButton">Update</button></div>
                        <div class="col-md-2"></div>
                        <div class="col-md-5"><a href="{{URL::to('dashboard'.'/'.\Illuminate\Support\Facades\Crypt::encrypt($machine->id))}}" class="btn btn-danger btn-block " style="margin-top: 0px" type="button">Go Back</a></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('footer')
    <script src="{{asset('assets/remark/examples/js/charts/gauges.js')}}"></script>
    <script src="{{asset('assets/global/vendor/formvalidation/formValidation.js')}}"></script>
    <script src="{{asset('assets/global/vendor/formvalidation/framework/bootstrap.js')}}"></script>
    <script src="{{asset('assets/global/vendor/matchheight/jquery.matchHeight-min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/jquery-wizard/jquery-wizard.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/jquery-wizard.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/matchheight.js')}}"></script>
    <script src="{{asset('assets/remark/examples/js/forms/wizard.js')}}"></script>
    <script src="{{asset('assets/global/vendor/select2/select2.full.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootstrap-select/bootstrap-select.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/select2.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/bootstrap-select.js')}}"></script>
    <script>
        $(document).ready(function(){
            var container = $('.removeIndex').closest('div').find('.select2-container');
            container.addClass('customSelect2');
        });
        $('.getData').on('change', function(){
            var jobContainer = $(this).closest('form').find('.selectJob');
            var processContainer = $(this).closest('form').find('.selectProcess');
            var materialCombinationContainer = $(this).closest('form').find('.selectMaterialCombination');
            if(processContainer.val() && jobContainer.val()){
                $.ajax({
                    url: "{!! URL::to('check/product/process') !!}",
                    method: "POST",
                    data:{
                        job_id: jobContainer.val(),
                        process_id: processContainer.val()
                    },
                    success:function(response){
                        var res = JSON.parse(response);
                        if(res == 'Structure Not Found'){
                            $('#updateButton').prop('disabled', true);
                            materialCombinationContainer.val('').trigger('change');
                        }
                        else{
                            $('#updateButton').prop('disabled', false);
                            if(res.material_combination_id){
                                materialCombinationContainer.val(res.material_combination_id).trigger('change');
                            }
                            else{
                                materialCombinationContainer.val('').trigger('change');
                            }
                        }
                    },
                    failure:function(response){
                        console.log(response);
                    }
                });
            }
        });
    </script>
@endsection