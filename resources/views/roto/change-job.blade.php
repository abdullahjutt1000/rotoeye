
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
                        <div class="col-md-4">
                            <div class="example-wrap">
                                <h4 class="example-title">Chanage Job</h4>
                                <div class="example">
                                    <form method="post" action="{{URL::to('change/job')}}" autocomplete="off">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control" name="product" id="product" data-plugin="select2" data-placeholder="Select Product" required>
                                                            <option value=""></option>
                                                            <optgroup label="Available Products">
                                                                @foreach($products as $product)
                                                                    <option value="{{$product->id}}">{{$product->id.' - '.$product->name}}</option>
                                                                @endforeach
                                                            </optgroup>
                                                            <optgroup label="Not Available">
                                                                <option>Not Available</option>
                                                            </optgroup>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12" style="margin-top: -15px">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control" name="job_id" id="job" data-plugin="select2" data-placeholder="Select Job" required>

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12" style="margin-top: -15px">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control" name="process_id" id="process" data-plugin="select2" data-placeholder="Select Process" required>

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group form-material">
                                            <button type="submit" class="btn btn-primary change-job-button" id="submitProductionReport">Change Job</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 new-production" hidden>
                            <div class="example-wrap">
                                <h4 class="example-title">Add New Production Order</h4>
                                <div class="example">
                                    <form method="post" action="{{URL::to('new/production')}}" autocomplete="off">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control" name="product" id="product_selection" data-plugin="select2" data-placeholder="Select Product" required>

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label></label>
                                                <input class="form-control" style="margin-top: -2px" name="product" id="product_number_input" placeholder="Product Number" required disabled>
                                            </div>
                                            <div class="col-md-4">
                                                <label></label>
                                                <input class="form-control" style="margin-top: -2px" name="product" id="product_name_input" placeholder="Product Name" required disabled>
                                            </div>
                                            <div class="col-md-4" style="margin-top: -15px">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control" name="job" id="job_selection" data-plugin="select2" data-placeholder="Select Job" required>

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label></label>
                                                <input class="form-control" style="margin-top: -17px" name="product" id="job_number_input" placeholder="Job Number" required disabled>
                                            </div>
                                            <div class="col-md-4">
                                                <label></label>
                                                <input class="form-control" style="margin-top: -17px" name="product" id="job_length_input" placeholder="Job Length" required disabled>
                                            </div>
                                            <div class="col-md-4" style="margin-top: -15px">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control" name="process" id="process_selection" data-plugin="select2" data-placeholder="Select Process" required>
                                                            <option value=""></option>
                                                            @foreach($machine->section->processes as $process)
                                                                <option value="{{$process->id}}">{{$process->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4" style="margin-top: -15px">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control" name="material" id="material_selection" data-plugin="select2" data-placeholder="Select Material" required>
                                                            <option value=""></option>
                                                            @foreach($materialCombinations as $material)
                                                                <option value="{{$material->id}}">{{$material->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group form-material">
                                            <button type="submit" class="btn btn-primary" id="submitProductionReport">Add New</button>
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
        $('#product').on('change', function(){
            if($(this).val() == 'Not Available'){
                $('#product_selection').attr('disabled', true);
                $('#product_selection').next('.col-md-4').hide();
                $('#product_number_input').attr('disabled', false);
                $('#product_name_input').attr('disabled', false);
                $('#product_number_input').next('.col-md-4').hide();
                $('#product_name_input').next('.col-md-4').hide();


                $('#job_selection').attr('disabled', true);
                $('#job_number_input').attr('disabled', false);
                $('#job_length_input').attr('disabled', false);

                $('#job').attr('disabled', true);
                $('#process').attr('disabled', true);

                $('.change-job-button').attr('disabled', true);

                $('.new-production').attr('hidden', false);
            }
            else{

                $('#product_selection').attr('disabled', false);
                $('#product_number_input').attr('disabled', true);
                $('#product_name_input').attr('disabled', true);

                $('#job_selection').attr('disabled', false);
                $('#job_number_input').attr('disabled', true);
                $('#job_length_input').attr('disabled', true);

                $('.change-job-button').attr('disabled', false);

                $('#job').attr('disabled', false);
                $('#process').attr('disabled', false);
                $('.new-production').attr('hidden', true);

                var product_id = $(this).val();
                $.ajax({
                    url: "{!! URL::to('get/product-wise/jobs') !!}/"+product_id,
                    method: "GET",
                    success:function(response){
                        var res = JSON.parse(response);
                        console.log(res);
                        $('#job option').each(function(){
                            $(this).remove();
                        });
                        $('#job optgroup').each(function(){
                            $(this).remove();
                        });
                        $('#job').append('<option value=""></option>');
                        if(res == 'Not Found'){

                        }
                        else{
                            for(var i=0; i<res.length; i++){
                                $('#job').append('<option value="'+res[i].id+'">'+res[i].id+'</option>');
                            }
                            $('#job').append('<optgroup label="Not Available"><option>Not Available</option></optgroup>');

                            $('#process option').each(function(){
                                $(this).remove();
                            });
                            $('#product_selection option').each(function(){
                                $(this).remove();
                            });
                            $('#product_selection').append('<option value="'+product_id+'">'+product_id+'</option>');
                            $.ajax({
                                url:"{!! URL::to('get/product-wise/processes') !!}/"+product_id,
                                method: "GET",
                                success:function(response){
                                    var res = JSON.parse(response);
                                    $('#process option').each(function(){
                                        $(this).remove();
                                    });
                                    $('#process optgroup').each(function(){
                                        $(this).remove();
                                    });
                                    $('#process').append('<option value=""></option>');
                                    if(res == 'Not Found'){

                                    }
                                    else{
                                        for(var i=0; i<res.length; i++){
                                            $('#process').append('<option value="'+res[i].id+'">'+res[i].name+'</option>');
                                        }
                                        $('#process').append('<optgroup label="Not Available"><option>Not Available</option></optgroup>');
                                    }
                                },
                                failure:function(response){

                                }
                            })
                        }
                    },
                    failure:function(response){

                    }
                })
            }
        });
        $('#job').on('change', function(){
            if($(this).val() == 'Not Available'){
                $('#job_selection').attr('disabled', true);
                $('#job_number_input').attr('disabled', false);
                $('#job_length_input').attr('disabled', false);
                $('#process').attr('disabled', true);
                $('.new-production').attr('hidden', false);
                $('.change-job-button').attr('disabled', true);
            }
            else{
                $('#job_selection').attr('disabled', false);
                $('#job_number_input').attr('disabled', true);
                $('#job_length_input').attr('disabled', true);
                $('#process').attr('disabled', false);
                $('.new-production').attr('hidden', true);
                $('.change-job-button').attr('disabled', false);
                $('#job_selection').append('<option value="'+$(this).val()+'">'+$(this).val()+'</option>')
            }
        });

        $('#process').on('change',function(){
            if($(this).val() == 'Not Available'){
                $('.new-production').attr('hidden', false);
                $('.change-job-button').attr('disabled', true);
            }
            else{
                $('.new-production').attr('hidden', true);
                $('.change-job-button').attr('disabled', false);
            }
        })
    </script>
@endsection