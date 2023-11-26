@extends('layouts.' . $layout)
@section('formHeader')
    <link rel="stylesheet" href="{{ asset('assets/remark/examples/css/forms/layouts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/dropify/dropify.css') }}">
@endsection
@section('header')
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/select2/select2.css') }}">
@endsection
@section('body')
    <div class="page">
        <div class="page-header">
            <h1 class="page-title">Add Machine</h1>
            <ol class="breadcrumb">
                Add a new machine as per the requirement.
            </ol>
        </div>
        <div class="page-content">
            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    SUCCESS : {{ Session::get('success') }}
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
            @if (Session::has('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    ERROR : {!! Session::get('error') !!}
                </div>
            @endif
            <div class="panel">
                <header class="panel-heading">
                    <h3 class="panel-title">Machine Details</h3>
                </header>
                <div class="panel-body">
                    <div class="row row-lg">
                        <div class="col-md-6">
                            <div class="example-wrap">
                                <div class="example">
                                    <form autocomplete="off" method="post"
                                        action="{{ URL::to('machine/add' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id)) }}"
                                        enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="form-group form-material col-md-3">
                                                <label class="form-control-label" for="code">Machine Code</label>
                                                <input type="text" class="form-control" id="code" name="code"
                                                    placeholder="Machine Code" value="{{ old('code') }}"
                                                    autocomplete="off" required />
                                            </div>
                                            <div class="form-group form-material col-md-6">
                                                <label class="form-control-label" for="name">Name</label>
                                                <input type="text" class="form-control" id="name" name="name"
                                                    placeholder="Name" value="{{ old('name') }}" autocomplete="off"
                                                    required />
                                            </div>
                                            <div class="form-group form-material col-md-3">
                                                <label class="form-control-label" for="ip">IP</label>
                                                <input type="text" class="form-control" id="ip" name="ip"
                                                    placeholder="IP" value="{{ old('ip') }}" autocomplete="off"
                                                    required />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group form-material col-md-3">
                                                <label class="form-control-label" for="hw_type">Hardware</label>
                                                <input type="text" class="form-control" id="hw_type" name="hw_type"
                                                    value="{{ old('hw_type') }}" placeholder="Hardware" autocomplete="off"
                                                    required />
                                            </div>
                                            <div class="form-group form-material col-md-3">
                                                <label class="form-control-label" for="max_speed">Max Speed</label>
                                                <input type="text" class="form-control" id="max_speed" name="max_speed"
                                                    placeholder="Max Speed" value="{{ old('max_speed') }}"
                                                    autocomplete="off" required />
                                            </div>
                                            <div class="form-group form-material col-md-3">
                                                <label class="form-control-label" for="waste_speed">Waste Speed</label>
                                                <input type="text" class="form-control" id="waste_speed"
                                                    name="waste_speed" placeholder="Waste Speed"
                                                    value="{{ old('waste_speed') }}" autocomplete="off" required />
                                            </div>
                                            <div class="form-group form-material col-md-3">
                                                <label class="form-control-label" for="auto_downtime">Auto
                                                    Downtime</label>
                                                <input type="text" class="form-control" id="auto_downtime"
                                                    name="auto_downtime" placeholder="Auto Downtime"
                                                    value="{{ old('auto_downtime') }}" autocomplete="off" required />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group form-material col-md-8">
                                                <label class="form-control-label" for="downtime_error">Downtime
                                                    Error</label>
                                                <select class="form-control" id="downtime_error" name="downtime_error"
                                                    required>
                                                    @foreach ($error_codes as $error_code)
                                                        @if ($error_code->id == 500)
                                                            <option value="{{ $error_code->id }}" selected>
                                                                {{ $error_code->name }}</option>
                                                        @else
                                                            <option value="{{ $error_code->id }}">{{ $error_code->name }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group form-material col-md-3">
                                                <label class="form-control-label" for="time_uom">Time UOM</label>
                                                <select class="form-control" id="time_uom" name="time_uom" required>
                                                    <option value="Hr">Hour</option>
                                                    <option value="Min">Minute</option>
                                                    <option value="Sec">Second</option>
                                                </select>
                                            </div>
                                            <div class="form-group form-material col-md-3">
                                                <label class="form-control-label" for="qty_uom">Qty UOM</label>
                                                <select class="form-control" id="qty_uom" name="qty_uom" required>
                                                    <option value="Meters">Meters</option>
                                                    <option value="Sheets">Sheets</option>
                                                    <option value="1">Administrator</option>
                                                </select>
                                            </div>
                                            <div class="form-group form-material col-md-6">
                                                <label class="form-control-label" for="section">Section</label>
                                                <select class="form-control" id="section" name="section" required>
                                                    @foreach ($sections as $section)
                                                        <option value="{{ $section->id }}">
                                                            {{ $section->department->businessUnit->company->name . ' - ' . $section->department->businessUnit->business_unit_name . ' - ' . $section->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group form-material col-md-3">
                                                <label class="form-control-label" for="graph_span">Graph Span</label>
                                                <input type="number" class="form-control" id="graph_span"
                                                    name="graph_span" value="{{ old('graph_span') }}"
                                                    placeholder="Graph Span" autocomplete="off" required />
                                            </div>
                                            <div class="form-group form-material col-md-4">
                                                <label class="form-control-label" for="roller_circumference">Roller
                                                    Circumference</label>
                                                <input type="text" class="form-control" id="roller_circumference"
                                                    name="roller_circumference" value="{{ old('roller_circumference') }}"
                                                    placeholder="Roller Circumference" autocomplete="off" required />
                                            </div>
                                            <div class="form-group form-material col-md-4">
                                                <label class="form-control-label" for="roller_circumference">RH IP</label>
                                                <input type="text" class="form-control" id="roller_circumference"
                                                    name="rh_ip" value="{{ old('rh_ip') }}" placeholder="RH IP"
                                                    autocomplete="off" />
                                            </div>
                                            {{-- Made fields for adding .Bin Files start by Abdullah --}}

                                            <div class="form-group col-md-4">
                                                <label class="form-control-label" for="bin_file1"
                                                    style="font-weight: 500;">Upload Binfile 1</label>
                                                <input type="file" name="bin_file1" id="bin_file1" accept=".bin">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label class="form-control-label" for="bin_file"
                                                    style="font-weight: 500;">Upload Binfile 2</label>
                                                <input type="file" name="bin_file2" id="bin_file2" accept=".bin">
                                            </div>

                                            {{-- Made fields for adding .Bin Files end  by Abdullah --}}
                                        </div>

                                        <div class="form-group form-material"> <br>
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
                                            <div class="card card-block p-30 bg-green-600">
                                                <div class="card-watermark darker font-size-80 m-15"><i
                                                        class="icon md-window-maximize" aria-hidden="true"></i></div>
                                                <div class="counter counter-md counter-inverse text-left">
                                                    <div class="counter-number-group">
                                                        <span class="counter-number">{{ $machinesCount }}</span>
                                                        <span
                                                            class="counter-number-related text-capitalize">Machines</span>
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
    <script src="{{ asset('assets/global/vendor/jquery-placeholder/jquery.placeholder.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/jquery-ui/jquery-ui.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/blueimp-tmpl/tmpl.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/blueimp-canvas-to-blob/canvas-to-blob.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/blueimp-load-image/load-image.all.min.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload-process.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload-image.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload-audio.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload-video.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload-validate.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/blueimp-file-upload/jquery.fileupload-ui.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/dropify/dropify.min.js') }}"></script>
@endsection
@section('footer')
    <script src="{{ asset('assets/global/js/Plugin/jquery-placeholder.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/dropify.js') }}"></script>
    <script src="{{ asset('assets/remark/examples/js/forms/uploads.js') }}"></script>
    <script>
        (function(document, window, $) {
            'use strict';

            var Site = window.Site;
            $(document).ready(function() {
                Site.run();
            });
        })(document, window, jQuery);
    </script>
@endsection
