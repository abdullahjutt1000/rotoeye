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
            <h1 class="page-title">Allocate Machines</h1>
            <ol class="breadcrumb">
                Allocation machines to {{$employee->name}}
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
                    <h3 class="panel-title">Allocation Details</h3>
                </header>
                <form autocomplete="off" method="post" action="{{URL::to('allocate/machines'.'/'.$employee->id)}}" enctype="multipart/form-data">
                    <div class="panel-body">
                        <div class="row row-lg">
                            <div class="col-md-12">
                                <div class="example-wrap">
                                    <div class="example">
                                        <div class="row machinesContainer">
                                            @if(count($machines) > 0)
                                                <div class="form-group form-material col-md-4">
                                                    <label class="form-control-label" for="section">Select Machine</label>
                                                    <select class="form-control" id="machine" name="machines[]" required>
                                                        @foreach($machines as $machine)
                                                            <option value="{{$machine->id}}">{{$machine->section->department->businessUnit->company->name.' - '.$machine->section->department->businessUnit->business_unit_name.' - '.$machine->name.' - '.$machine->sap_code}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @else
                                                <div class="form-group form-material col-md-4">
                                                    <label class="form-control-label" for="section">Sorry! You are already allocated with all Machines</label>
                                                </div>
                                            @endif
                                        </div>
                                        @if(count($machines) > 1)
                                            <div class="form-group form-material">
                                                <a id="addMoreMachines" class="btn btn-success white">Add More</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
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
    <script>
        $('#addMoreMachines').on('click', function(){
            var employeeID = "{{$employee->id}}";
            $.ajax({
                url: "{{URL::to('add/more/machines')}}/" + employeeID,
                success: function(response){
                    var res = JSON.parse(response);
                    $('.machinesContainer').append(res.row);
                }
            })
        });
    </script>
@endsection