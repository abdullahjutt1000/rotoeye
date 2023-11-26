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
            <form method="post" id="changeJob" name="loginForm">
                <div class="panel-body">
                    <div class="brand">
                        <img class="brand-img" src="{{asset('assets/icons/Logo_RotoEye_Version_160 cross 160_PNG_1.0.png')}}" alt="..." style="width: 40%;height: auto;">
                    </div>
                    <p>Hello <strong>{{$operator->name}}</strong>, Please select job for machine <strong>{{$machine->name.' - '.$machine->sap_code}}</strong></p>
                    <hr>
                    <input name = "machine" type="text" value="{{isset($machine) ? $machine->id:""}}" hidden>
                    <input name = "user" type="text" value="{{isset($operator) ? $operator->id:""}}" hidden>
                    <div class="row">
                        <div class="form-group col-md-12" data-plugin="formMaterial">
                            <select class="form-control selectJob getData removeIndex" data-plugin="select2" data-placeholder="Please Select Job" name="job">
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
                            <select class="form-control selectProcess getData removeIndex" data-plugin="select2" data-tags="true" data-placeholder="Select / Add New Process" name="process" required>
                                <option></option>
                                @foreach($machine->section->processes as $process)
                                    <option value="{{$process->id}}">{{$process->process_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12" data-plugin="formMaterial">
                            <select class="form-control selectMaterialCombination removeIndex" data-plugin="select2" data-tags="true" data-placeholder="Select / Add New Material Combination" name="material_combination" required>
                                <option></option>
                                @foreach($materialCombinations as $materialCombination)
                                    <option value="{{$materialCombination->id}}">{{$materialCombination->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-6 form-group mb-0">
                            <input type="text" id="color" class="form-control" name="color" placeholder="Color">
                        </div>
                        <div class="col-xl-6 form-group mb-0">
                            <input type="text" id="adhesive" class="form-control" name="adhesive" placeholder="Adhesive">
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <button type="submit" class="btn btn-primary btn-block col-md-5" id="submitJob">Submit Job</button>
                        <div class="col-md-2"></div>
                        <button class="btn btn-success btn-block col-md-5" data-dismiss="modal" data-toggle="modal" data-target="#newProductionOrder" style="margin-top: 0px" type="button">Add New</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal example-modal-lg fade" id="newProductionOrder" aria-hidden="false" role="dialog">
        <div class="modal-dialog modal-simple">
            <form class="modal-content" id="newProduction" method="post" enctype="multipart/form-data" autocomplete="off">
                <div class="modal-header bg-danger">
                    <button type="button" class="close white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title white">New Production Order</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xl-4 form-group">
                            <input name = "machine" type="text" value="{{isset($machine) ? $machine->id:""}}" hidden>
                            <input name = "user" type="text" value="{{isset($operator) ? $operator->id:""}}" hidden>
                            <h4 class="example-title text-left">Product #</h4>
                            <select class="form-control getData selectJob" id="selectProduct" data-plugin="select2" data-tags="true" data-placeholder="Select / Add New" name="product_number" required>
                                <option></option>
                                @foreach($products as $product)
                                    <option value="{{$product->id}}" data-ups="{{$product->ups==NULL?'-':$product->ups}}" data-col="{{$product->col==NULL?'-':$product->col*1000}}" data-srw="{{$product->slitted_reel_width==NULL?'-':$product->slitted_reel_width*1000}}"
                                            data-tw="{{$product->trim_width==NULL?'-':$product->trim_width*1000}}" data-gsm="{{$product->gsm==NULL?'-':$product->gsm*1000}}" data-thickness="{{$product->thickness==NULL?'-':$product->thickness*1000000}}" data-density="{{$product->density==NULL?'-':$product->density}}"
                                            data-sleeve = "{{count($product->sleeves)>0?$product->sleeves[0]->id:'-'}}"
                                            data-name="{{$product->name}}" data-product="{{$product->id}}">{{$product->id}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-8 form-group">
                            <h4 class="example-title text-left">Product Name</h4>
                            <input type="text" id="productName" class="form-control" name="product_name" required>
                        </div>
                        <div class="col-xl-12 my-0 text-center  form-group">
                            <div class="row">
                                <div class="col-xl-12">
                                    <a id="add-more" href="#" class="h6 float-left ml-0"><strong>ADD MORE</strong> <i class="site-menu-icon md-arrows" aria-hidden="true"></i></a>
                                </div>
                                <div class="col-xl-12 my-0 " id="show-more" style="display: none">
                                    <div>
                                        <div class="row">
                                            <div class="col-xl-8 form-group">
                                                <input type="text" id="srw" class="form-control" name="slitted_reel_width" placeholder="Slitted Reel Width(mm)" >
                                            </div>
                                            <div class="col-xl-4 form-group">
                                                <input type="text" id="ups" class="form-control" name="ups" placeholder="No. of UP's" >
                                            </div>

                                            <div class="col-xl-4 form-group">
                                                <input type="text" id="col" class="form-control" name="col" placeholder="COL (mm)" >
                                            </div>

                                            <div class="col-xl-4 form-group">
                                                <input type="text" id="gsm" class="form-control" name="gsm" placeholder="GSM (g/m2)" >
                                            </div>

                                            <div class="col-xl-4 form-group">
                                                <input type="text" id="tw" class="form-control" name="trim_width" placeholder="Trim Width (mm)" >
                                            </div>

                                            <div class="col-xl-4 form-group">
                                                <input type="text" id="thickness" class="form-control" name="thickness" placeholder="Thickness (Mic)" >
                                            </div>

                                            <div class="col-xl-4 form-group">
                                                <input type="text" id="density" class="form-control" name="density" placeholder="Density (g/m3)" >
                                            </div>
                                            <div class="col-xl-4 form-group">
                                                <select class="form-control" id="selectSleeve" data-plugin="select2" data-tags="true" data-placeholder="Select Sleeve" name="sleeve_id" >
                                                    <option></option>
                                                    @foreach($machine->sleeves as $sleeve)
                                                        <option value="{{$sleeve->id}}" data-name="{{$sleeve->circumference}}" data-product="{{$sleeve->id}}">{{$sleeve->id}} : cir-{{$sleeve->circumference}} : s-{{$sleeve->pivot->speed}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
                        <div class="col-xl-4 form-group">
                            <h4 class="example-title text-left">Job Card #</h4>
                            <input type="text" class="form-control" name="job" required>
                        </div>
                        <div class="col-xl-4 form-group">
                            <h4 class="example-title text-left">Job Length</h4>
                            <input type="text" class="form-control" name="job_length" required>
                        </div>
                        <div class="col-xl-4 form-group">
                            <h4 class="example-title text-left">Process</h4>
                            <select class="form-control getData selectProcess" data-plugin="select2" data-tags="true" data-placeholder="Select / Add New"  name="process" required>
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
                            <select class="form-control selectMaterial selectMaterialCombination" data-plugin="select2" data-tags="true" data-placeholder="Select / Add New"  name="material_combination" required>
                                <option></option>
                                @foreach($materialCombinations as $materialCombination)
                                    <option value="{{$materialCombination->id}}">{{$materialCombination->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 form-group">
                            <h4 class="example-title text-left">Color</h4>
                            <input type="text" class="form-control" name="color">
                        </div>
                        <div class="col-xl-4 form-group">
                            <h4 class="example-title text-left">Adhesive</h4>
                            <input type="text" class="form-control" name="adhesive">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit" id="saveProceed">Save & Proceed</button>
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
        $('#selectProduct').on('change', function(){
            var name = $('#selectProduct option:selected').data('name');
            var srw = $('#selectProduct option:selected').data('srw');
            var ups = $('#selectProduct option:selected').data('ups');
            var col = $('#selectProduct option:selected').data('col');
            var gsm = $('#selectProduct option:selected').data('gsm');
            var tw = $('#selectProduct option:selected').data('tw');
            var thickness = $('#selectProduct option:selected').data('thickness');
            var density = $('#selectProduct option:selected').data('density');
            var sleeve = $('#selectProduct option:selected').data('sleeve');
            if(name){
                $('#productName').val(name);
                $('#productName').prop('readonly', true);
            }
            else{
                $('#productName').val('');
                $('#productName').prop('readonly', false);
            }
            if(sleeve){
                $('#selectSleeve').val(sleeve).change();
                $('#selectSleeve').attr("disabled", true);

            }
            else{
                console.log('sleeve')
                $('#selectSleeve').val('').change();
                $('#selectSleeve').attr("disabled", false);
            }

            if(srw||ups||col||gsm||tw||thickness||density){
                $('#srw').val(srw);
                $('#srw').prop('readonly', true);

                $('#ups').val(ups);
                $('#ups').prop('readonly', true);

                $('#col').val(col);
                $('#col').prop('readonly', true);

                $('#gsm').val(gsm);
                $('#gsm').prop('readonly', true);

                $('#tw').val(tw);
                $('#tw').prop('readonly', true);

                $('#thickness').val(thickness);
                $('#thickness').prop('readonly', true);

                $('#density').val(density);
                $('#density').prop('readonly', true);

            }
            else{
                $('#srw').val(srw);
                $('#srw').prop('readonly', false);

                $('#ups').val('');
                $('#ups').prop('readonly', false);

                $('#col').val('');
                $('#col').prop('readonly', false);

                $('#gsm').val('');
                $('#gsm').prop('readonly', false);

                $('#tw').val('');
                $('#tw').prop('readonly', false);

                $('#thickness').val('');
                $('#thickness').prop('readonly', false);

                $('#density').val('');
                $('#density').prop('readonly', false);
            }
        });
        $('.getData').on('change', function(){
            var jobContainer = $(this).closest('form').find('.selectJob');
            var processContainer = $(this).closest('form').find('.selectProcess');
            var materialCombinationContainer = $(this).closest('form').find('.selectMaterialCombination');
            if(processContainer.val() && jobContainer.val()){
                var product_id = $(this).closest('form').find('.selectJob option:selected').data('product');
                $.ajax({
                    url: "{!! URL::to('check/product/process') !!}",
                    method: "POST",
                    data:{
                        product_id: product_id,
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
        $('#submitJob').on('click', function(){
            $(this).attr('disabled', true);
            $('#changeJob').attr('action',"{!! URL::to('submit/job') !!}");
            $('#changeJob').submit();

        });
        $('#saveProceed').on('click', function(){
            $(this).attr('disabled', true);
            $('#newProduction').attr('action',"{!! URL::to('new/production') !!}");
            $('#newProduction').submit();
        })
        $('#add-more').on('click', function(){
            $("#show-more").toggle(800);
        });
    </script>
@endsection
