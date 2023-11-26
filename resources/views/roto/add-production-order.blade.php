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
            <h1 class="page-title">Add User</h1>
            <ol class="breadcrumb">
                Add a new user as per the requirement. Please make your credentials strong.
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
                    <h3 class="panel-title">User Details</h3>
                </header>
                <div class="panel-body">
                    <div class="row row-lg">
                        <div class="col-md-6">
                            <div class="example-wrap">
                                <div class="example">
                                    <form autocomplete="off">
                                        <div class="row">
                                            <div class="form-group form-material col-md-2">
                                                <label class="form-control-label" for="inputBasicFirstName">ID</label>
                                                <input type="text" class="form-control" id="inputBasicFirstName" name="employeeID" placeholder="ID" autocomplete="off" required/>
                                            </div>
                                            <div class="form-group form-material col-md-4">
                                                <label class="form-control-label" for="inputBasicFirstName">CNIC</label>
                                                <input type="text" class="form-control" id="inputBasicFirstName" name="cnic" placeholder="CINC" autocomplete="off" required/>
                                            </div>
                                            <div class="form-group form-material col-md-6">
                                                <label class="form-control-label" for="inputBasicFirstName">Name</label>
                                                <input type="text" class="form-control" id="inputBasicFirstName" name="name" placeholder="Name" autocomplete="off" required/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group form-material col-md-6">
                                                <label class="form-control-label" for="inputBasicPassword">Password</label>
                                                <input type="password" class="form-control" id="inputBasicPassword" name="inputPassword" placeholder="Password" autocomplete="off" required/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group form-material col-md-6">
                                                <label class="form-control-label" for="inputBasicFirstName">Designation</label>
                                                <input type="text" class="form-control" id="inputBasicFirstName" name="employeeID" placeholder="Designation" autocomplete="off" required/>
                                            </div>
                                            <div class="form-group form-material col-md-6">
                                                <label class="form-control-label" for="rights">Rights</label>
                                                <select class="form-control" id="rights" name="rights" required>
                                                    <option value="0">Operator</option>
                                                    <option value="2">Power User</option>
                                                    <option value="1">Administrator</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group form-material col-md-12">
                                                <label class="form-control-label" for="inputBasicFirstName">Picture</label>
                                                <input type="file" id="input-file-now" data-plugin="dropify" data-default-file="" required/>
                                            </div>
                                        </div>
                                        <div class="form-group form-material">
                                            <button type="submit" class="btn btn-primary">Sign Up</button>
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
                                            <div class="card card-block p-30 bg-red-600">
                                                <div class="card-watermark darker font-size-80 m-15"><i class="icon md-accounts" aria-hidden="true"></i></div>
                                                <div class="counter counter-md counter-inverse text-left">
                                                    <div class="counter-number-group">
                                                        <span class="counter-number">{{count($users)}}</span>
                                                        <span class="counter-number-related text-capitalize">people</span>
                                                    </div>
                                                    <div class="counter-label text-capitalize">registered</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12">
                                            <div class="font-size-20 mb-20 text-uppercase grey-700">Users Status</div>
                                            <ul class="list-unstyled mb-0">
                                                <li>
                                                    <div class="counter counter-sm text-left">
                                                        <div class="counter-number-group mb-10">
                                                            <span class="counter-number-related">Operators - </span>
                                                            <span class="counter-number">{{number_format($operatorCount/count($users)*100,0)}}</span>
                                                            <span class="counter-number-related">%</span>
                                                        </div>
                                                    </div>
                                                    <div class="progress progress-xs">
                                                        <div class="progress-bar progress-bar-info bg-blue-600" aria-valuenow="{{$operatorCount/count($users)*100}}" aria-valuemin="0" aria-valuemax="100" style="width: {{number_format($operatorCount/count($users)*100,0).'%'}}" role="progressbar">
                                                            <span class="sr-only">{{number_format($operatorCount/count($users)*100,0)}}%</span>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="counter counter-sm text-left">
                                                        <div class="counter-number-group mb-10">
                                                            <span class="counter-number-related">Power Users - </span>
                                                            <span class="counter-number">{{number_format($powerUserCount/count($users)*100,0)}}</span>
                                                            <span class="counter-number-related">%</span>
                                                        </div>
                                                    </div>
                                                    <div class="progress progress-xs">
                                                        <div class="progress-bar progress-bar-info bg-red-600" aria-valuenow="{{$powerUserCount/count($users)*100}}" aria-valuemin="0" aria-valuemax="100" style="width: {{number_format($powerUserCount/count($users)*100,0).'%'}}" role="progressbar">
                                                            <span class="sr-only">{{number_format($powerUserCount/count($users)*100,0)}}%</span>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="counter counter-sm text-left">
                                                        <div class="counter-number-group mb-10">
                                                            <span class="counter-number-related">Administrators - </span>
                                                            <span class="counter-number">{{number_format($adminCount/count($users)*100,0)}}</span>
                                                            <span class="counter-number-related">%</span>
                                                        </div>
                                                    </div>
                                                    <div class="progress progress-xs mb-0">
                                                        <div class="progress-bar progress-bar-info bg-green-600" aria-valuenow="{{$adminCount/count($users)*100}}" aria-valuemin="0" aria-valuemax="100" style="width: {{number_format($adminCount/count($users)*100,0).'%'}}" role="progressbar">
                                                            <span class="sr-only">{{number_format($adminCount/count($users)*100,0)}}%</span>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
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
@endsection