@extends('layouts.'.$layout)
@section('formHeader')
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/forms/layouts.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/dropify/dropify.css')}}">
@endsection
@section('header')
    <link rel="stylesheet" href="{{asset('assets/global/vendor/select2/select2.css')}}">
@endsection
@section('body')
    <div class="page">
        <div class="page-header">
            <h1 class="page-title">Add Product</h1>
            <ol class="breadcrumb">
                Add a new product as per the required fields.
            </ol>
        </div>
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
            <form autocomplete="off" method="post" action="{{URL::to('product/add')}}">
                <div class="panel">
                    <header class="panel-heading">
                        <h3 class="panel-title">Product Details</h3>
                    </header>
                    <div class="panel-body">
                        <div class="row row-lg">
                            <div class="col-md-12">
                                <div class="example-wrap">
                                    <div class="example">
                                        <div class="row">
                                            <div class="form-group form-material col-md-2">
                                                <label class="form-control-label" for="productID">ID</label>
                                                <input type="text" class="form-control" id="productID" value="{{old('id')}}" name="id" autocomplete="off" required/>
                                            </div>
                                            <div class="form-group form-material col-md-4">
                                                <label class="form-control-label" for="productName">Name</label>
                                                <input type="text" class="form-control" id="productName" value="{{old('name')}}" name="name" autocomplete="off" required/>
                                            </div>
                                            <div class="form-group form-material col-md-1">
                                                <label class="form-control-label" for="uom">UOM</label>
                                                <input type="text" class="form-control" id="uom" value="{{old('uom')}}" name="uom" autocomplete="off" required/>
                                            </div>
                                            <div class="form-group form-material col-md-1">
                                                <label class="form-control-label" for="ups">UPS</label>
                                                <input type="text" class="form-control" id="ups" value="{{old('ups')}}" name="ups" autocomplete="off" required/>
                                            </div>
                                            <div class="form-group form-material col-md-1">
                                                <label class="form-control-label" for="col">COL</label>
                                                <input type="text" class="form-control" id="col" value="{{old('col')}}" name="col" autocomplete="off" required/>
                                            </div>
                                            <div class="form-group form-material col-md-2">
                                                <label class="form-control-label" for="colorAdh">Color/Adhesive</label>
                                                <input type="text" class="form-control" id="colorAdh" value="{{old('color_adh')}}" name="color_adh" autocomplete="off" required/>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <span>Please define the structures of above product now</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <a onclick="addMoreStructures()" class="btn btn-primary white">Add More Structure</a>
                        <button type="submit" class="btn btn-success">Submit Product</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('formFooter')
    <script src="{{asset('assets/global/vendor/jquery-placeholder/jquery.placeholder.js')}}"></script>
    <script src="{{asset('assets/global/vendor/jquery-ui/jquery-ui.js')}}"></script>
    <script src="{{asset('assets/global/vendor/blueimp-tmpl/tmpl.js')}}"></script>
    <script src="{{asset('assets/global/vendor/blueimp-canvas-to-blob/canvas-to-blob.js')}}"></script>
    <script src="{{asset('assets/global/vendor/blueimp-load-image/load-image.all.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload.js')}}"></script>
    <script src="{{asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload-process.js')}}"></script>
    <script src="{{asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload-image.js')}}"></script>
    <script src="{{asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload-audio.js')}}"></script>
    <script src="{{asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload-video.js')}}"></script>
    <script src="{{asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload-validate.js')}}"></script>
    <script src="{{asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload-ui.js')}}"></script>
    <script src="{{asset('assets/global/vendor/dropify/dropify.min.js')}}"></script>
@endsection
@section('footer')
    <script src="{{asset('assets/global/js/Plugin/jquery-placeholder.js')}}"></script>
    <script src="{{asset('assets/global/vendor/select2/select2.full.min.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/dropify.js')}}"></script>
    <script src="{{asset('assets/remark/examples/js/forms/uploads.js')}}"></script>
    <script>
        (function(document, window, $){
            'use strict';

            var Site = window.Site;
            $(document).ready(function(){
                Site.run();
            });
        })(document, window, jQuery);
    </script>
    <script>
        function addMoreStructures(){
            alert('Hi');
        }
    </script>
@endsection
