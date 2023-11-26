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
            <h1 class="page-title">Update User</h1>
            <ol class="breadcrumb">
                Update the user as per new requirement. Please make your credentials strong.
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
                                    <form autocomplete="off" method="post" action="{{URL::to('user/update'.'/'.$employee->id.'/'.\Illuminate\Support\Facades\Crypt::encrypt($machine->id))}}" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="form-group form-material col-md-2">
                                                <label class="form-control-label" for="employeeID">ID</label>
                                                <input type="text" class="form-control" id="employeeID" name="id" placeholder="ID" value="{{$employee->id}}" autocomplete="off" required/>
                                            </div>
                                            <div class="form-group form-material col-md-4">
                                                <label class="form-control-label" for="cnic">CNIC</label>
                                                <input type="text" class="form-control" id="cnic" name="cnic" placeholder="CINC" value="{{$employee->cnic}}" autocomplete="off" required/>
                                            </div>
                                            <div class="form-group form-material col-md-6">
                                                <label class="form-control-label" for="name">Name</label>
                                                <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="{{$employee->name}}" autocomplete="off" required/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group form-material col-md-6">
                                                <label class="form-control-label" for="designation">Designation</label>
                                                <input type="text" class="form-control" id="designation" name="designation" value="{{$employee->designation}}" placeholder="Designation" autocomplete="off" required/>
                                            </div>
                                            <div class="form-group form-material col-md-6">
                                                <label class="form-control-label" for="rights">Rights</label>
                                                <select class="form-control" id="rights" name="rights" required>
                                                    <option value="0" {{$employee->rights == 0 ? 'selected':""}}>Operator</option>
                                                    <option value="2" {{$employee->rights == 2 ? 'selected':""}}>Power User</option>
                                                    <option value="1" {{$employee->rights == 1 ? 'selected':""}}>Administrator</option>
                                                    <option value="3" {{$employee->rights == 3 ? 'selected':""}}>Reporting User</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group form-material col-md-12">
                                                <label class="form-control-label" for="picture">Picture</label>
                                                <input type="file" id="picture" name="picture" data-plugin="dropify" data-default-file="{{asset('assets/global/portraits'.'/'.$employee->photo)}}"/>
                                            </div>
                                        </div>
                                        <div class="form-group form-material">
                                            <button type="submit" class="btn btn-primary">Update</button>
                                            <a href="{{URL::to('allocate/machines'.'/'.$employee->id)}}" class="btn btn-success white">Allocate Machines</a>
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
                                                    <div class="progress progress-xs  ">
                                                        <div class="progress-bar progress-bar-info bg-green-600" aria-valuenow="{{$adminCount/count($users)*100}}" aria-valuemin="0" aria-valuemax="100" style="width: {{number_format($adminCount/count($users)*100,0).'%'}}" role="progressbar">
                                                            <span class="sr-only">{{number_format($adminCount/count($users)*100,0)}}%</span>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="counter counter-sm text-left">
                                                        <div class="counter-number-group mb-10">
                                                            <span class="counter-number-related">Reporting Users - </span>
                                                            <span class="counter-number">{{number_format($reportUserCount/count($users)*100,0)}}</span>
                                                            <span class="counter-number-related">%</span>
                                                        </div>
                                                    </div>
                                                    <div class="progress progress-xs">
                                                        <div class="progress-bar progress-bar-info bg-red-600" aria-valuenow="{{$reportUserCount/count($users)*100}}" aria-valuemin="0" aria-valuemax="100" style="width: {{number_format($reportUserCount/count($users)*100,0).'%'}}" role="progressbar">
                                                            <span class="sr-only">{{number_format($reportUserCount/count($users)*100,0)}}%</span>
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