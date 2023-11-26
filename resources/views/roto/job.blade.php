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
            <h1 class="page-title">Update Job</h1>
            <ol class="breadcrumb">
                Update job as per the required fields.
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
                    <h3 class="panel-title">Job Details</h3>
                </header>
                <div class="panel-body">
                    <div class="row row-lg">
                        <div class="col-md-6">
                            <div class="example-wrap">
                                <div class="example">
                                    <form autocomplete="off" method="post" action="{{URL::to('job/update'.'/'.$machine->loginRecord->job->id)}}">
                                        <div class="row">
                                            <div class="form-group form-material col-md-6">
                                                <label class="form-control-label" for="jobID">ID</label>
                                                <input type="text" class="form-control" id="jobID" value="{{$machine->loginRecord->job->id}}" name="jobID" placeholder="ID" autocomplete="off" required />
                                            </div>
                                            
                                        </div>
                                        <div class="row">
                                            <div class="form-group form-material col-md-3">
                                                <label class="form-control-label" for="reel_width">Reel Width</label>
                                                <input type="text" class="form-control" id="reel_width" value=                                               "{{is_null($machine->loginRecord->job->reel_width)?'':$machine->loginRecord->job->reel_width}}"
                                                " name="reel_width" placeholder="reel width" autocomplete="off" />
                                            </div>

                                            <div class="form-group form-material col-md-3">
                                                <label class="form-control-label" for="ups">UPS</label>
                                                <input type="text" class="form-control" id="ups" value={{is_null($machine->loginRecord->job->ups)?'':$machine->loginRecord->job->ups}}"" name="ups" placeholder="UPS" autocomplete="off" />
                                            </div>

                                            <div class="form-group form-material col-md-3">
                                                <label class="form-control-label" for="trimWidth">Trim Width <em>(mm)</em></label>
                                                <input type="text" class="form-control" id="trimWidth" value="{{is_null($machine->loginRecord->job->trim_width)?'':$machine->loginRecord->job->trim_width*1000}}" name="trimWidth" placeholder="trimWidth" autocomplete="off" />
                                            </div>
                                            <div class="form-group form-material col-md-3">
                                                <label class="form-control-label" for="gsm">GSM <em>(g/m2)</em></label>
                                                <input type="text" class="form-control" id="gsm" value="{{is_null($machine->loginRecord->job->gsm)?'':$machine->loginRecord->job->gsm*1000}}" name="gsm" placeholder="GSM" autocomplete="off" />
                                            </div>
                                            <div class="form-group form-material col-md-3">
                                                <label class="form-control-label" for="thickness">Thickness <em>(Mic)</em></label>
                                                <input type="text" class="form-control" id="thickness" value="{{is_null($machine->loginRecord->job->thickness)?'':$machine->loginRecord->job->thickness*1000000}}" name="thickness" placeholder="thickness" autocomplete="off" />
                                            </div>
                                            <div class="form-group form-material col-md-3">
                                                <label class="form-control-label" for="density">Density <em>(g/m3)</em></label>
                                                <input type="text" class="form-control" id="density" value="{{is_null($machine->loginRecord->job->density)?'':$machine->loginRecord->job->density}}"
                                        name="density" placeholder="density" autocomplete="off" />
                                            </div>
                                        <div class="form-group form-material">
                                            <button type="submit" class="btn btn-primary">Submit</button>
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