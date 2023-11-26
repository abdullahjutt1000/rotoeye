
@extends('layouts.'.$layout)
@section('formHeader')
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/forms/layouts.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/dropify/dropify.css')}}">
@endsection
@section('header')
    <link rel="stylesheet" href="{{asset('assets/global/vendor/select2/select2.css')}}">
@endsection
@section('body')
    <div class="page">
        <div class="page-header">
            <h1 class="page-title">Add Error Category</h1>
            <ol class="breadcrumb">
                Add error category as per the requirement.
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
            
            <div class="panel">
                <header class="panel-heading">
                    <h3 class="panel-title">Error Category Details</h3>
                </header>
                <div class="panel-body">
                    <div class="row row-lg">
                        <div class="col-md-6">
                            <div class="example-wrap">
                                <div class="example">
                                    <form autocomplete="off" method="post" action="{{URL::to('categories/add'.'/'.\Illuminate\Support\Facades\Crypt::encrypt($machine->id))}}" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="form-group form-material col-md-6">
                                                <label class="form-control-label" for="name">Name</label>
                                                <input type="text" class="form-control" id="name" name="name" placeholder="Error Cateogry Name" value="{{old('name')}}" autocomplete="off" required/>
                                            </div>
                                            
                                        </div>
                                        <div class="row">
                                            <div class="form-group form-material col-md-12">
                                                <label class="form-control-label" for="categories">Error Codes</label>
                                                <select class="form-control select2-primary" id="errorCodes" name="errorCodes[]" data-plugin="select2" required multiple>
                                                    @foreach($errorCodes as $errorCode)
                                                        <option value="{{$errorCode->id}}">{{$errorCode->id.' - '.$errorCode->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group form-material">
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="example-wrap">
                                <div class="example">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card card-block p-30 bg-green-600">
                                                <div class="card-watermark darker font-size-80 m-15"><i class="icon md-window-maximize" aria-hidden="true"></i></div>
                                                <div class="counter counter-md counter-inverse text-left">
                                                    <div class="counter-number-group">
                                                        <span class="counter-number">{{$categoriesCount}}</span>
                                                        <span class="counter-number-related text-capitalize">Error Ctegories</span>
                                                    </div>
                                                    <div class="counter-label text-capitalize">registered</div>
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
    </div>
@endsection
@section('formFooter')
    <script src="{{asset('assets/global/vendor/jquery-placeholder/jquery.placeholder.js')}}"></script>
    <script src="{{asset('assets/global/vendor/jquery-ui/jquery-ui.js')}}"></script>
    <script src="{{asset('assets/global/vendor/blueimp-tmpl/tmpl.js')}}"></script>
    <script src="{{asset('assets/global/vendor/blueimp-canvas-to-blob/canvas-to-blob.js')}}"></script>
    <script src="{{asset('assets/global/vendor/blueimp-load-image/load-image.all.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/dropify/dropify.min.js')}}"></script>
@endsection
@section('footer')
    <script src="{{asset('assets/global/js/Plugin/jquery-placeholder.js')}}"></script>
    <script src="{{asset('assets/global/vendor/select2/select2.full.min.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/dropify.js')}}"></script>
    <script>
        (function(document, window, $){
            'use strict';
            var Site = window.Site;
            $(document).ready(function(){
                Site.run();
            });
        })(document, window, jQuery);
    </script>
@endsection
