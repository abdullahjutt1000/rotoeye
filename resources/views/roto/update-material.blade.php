@extends('layouts.'.$layout)
@section('formHeader')
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/forms/layouts.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/dropify/dropify.css')}}">
@endsection
@section('header')
    <link rel="stylesheet" href="{{asset('assets/global/vendor/datatables.net-bs4/dataTables.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/datatables.net-fixedheader-bs4/dataTables.fixedheader.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/datatables.net-fixedcolumns-bs4/dataTables.fixedcolumns.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/datatables.net-rowgroup-bs4/dataTables.rowgroup.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/datatables.net-scroller-bs4/dataTables.scroller.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/datatables.net-select-bs4/dataTables.select.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/datatables.net-responsive-bs4/dataTables.responsive.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/datatables.net-buttons-bs4/dataTables.buttons.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/tables/datatable.css')}}">
    <!-- Fonts -->
    <link rel="stylesheet" href="{{asset('assets/global/fonts/font-awesome/font-awesome.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/select2/select2.css')}}">
@endsection
@section('body')
    <div class="page">
        <div class="page-header">
            <h1 class="page-title">Material Combinations</h1>
            <ol class="breadcrumb">
                Add a new material as per the required fields.
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
            <div class="row">
                <div class="col-md-4" style="height: 100%">
                    <div class="panel">
                        <header class="panel-heading">
                            <h3 class="panel-title">Add New <small>Material Details</small></h3>
                        </header>
                        <div class="panel-body">
                            <div class="row row-lg">
                                <div class="col-md-12">
                                    <div class="example-wrap">
                                        <div class="example">
                                            <form autocomplete="off" method="post" action="{{URL::to('material/update'.'/'.$material->id)}}">
                                                <div class="row">
                                                    <div class="form-group form-material col-md-6">
                                                        <label class="form-control-label" for="materialName">Name</label>
                                                        <input type="text" class="form-control" id="materialName" name="material_name" value="{{$material->name}}" placeholder="Name" autocomplete="off" required/>
                                                    </div>
                                                    <div class="form-group form-material col-md-6">
                                                        <label class="form-control-label" for="nominalSpeed">Nominal Speed</label>
                                                        <input type="text" class="form-control" id="nominalSpeed" name="nominal_speed" value="{{$material->nominal_speed}}" placeholder="Nominal Speed" autocomplete="off" required/>
                                                    </div>
                                                </div>
                                                <div class="form-group form-material">
                                                    <button type="submit" class="btn btn-primary">Update</button>
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

    <script src="{{asset('assets/global/vendor/datatables.net/jquery.dataTables.js')}}"></script>
    <script src="{{asset('assets/global/vendor/datatables.net-bs4/dataTables.bootstrap4.js')}}"></script>
    <script src="{{asset('assets/global/vendor/datatables.net-fixedheader/dataTables.fixedHeader.js')}}"></script>
    <script src="{{asset('assets/global/vendor/datatables.net-fixedcolumns/dataTables.fixedColumns.js')}}"></script>
    <script src="{{asset('assets/global/vendor/datatables.net-rowgroup/dataTables.rowGroup.js')}}"></script>
    <script src="{{asset('assets/global/vendor/datatables.net-scroller/dataTables.scroller.js')}}"></script>
    <script src="{{asset('assets/global/vendor/datatables.net-responsive/dataTables.responsive.js')}}"></script>
    <script src="{{asset('assets/global/vendor/datatables.net-responsive-bs4/responsive.bootstrap4.js')}}"></script>
    <script src="{{asset('assets/global/vendor/datatables.net-buttons/dataTables.buttons.js')}}"></script>
    <script src="{{asset('assets/global/vendor/datatables.net-buttons/buttons.html5.js')}}"></script>
    <script src="{{asset('assets/global/vendor/datatables.net-buttons/buttons.flash.js')}}"></script>
    <script src="{{asset('assets/global/vendor/datatables.net-buttons/buttons.print.js')}}"></script>
    <script src="{{asset('assets/global/vendor/datatables.net-buttons/buttons.colVis.js')}}"></script>
    <script src="{{asset('assets/global/vendor/datatables.net-buttons-bs4/buttons.bootstrap4.js')}}"></script>
    <script src="{{asset('assets/global/vendor/asrange/jquery-asRange.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootbox/bootbox.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/datatables.js')}}"></script>

    <script src="{{asset('assets/remark/examples/js/tables/datatable.js')}}"></script>
    <script src="{{asset('assets/remark/examples/js/uikit/icon.js')}}"></script>
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