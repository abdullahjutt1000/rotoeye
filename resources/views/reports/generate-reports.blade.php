@extends('layouts.' . $layout)
@section('header')
    <link rel="stylesheet" href="{{ asset('assets/global/fonts/material-design/material-design.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/fonts/brand-icons/brand-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/remark/examples/css/widgets/chart.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/formvalidation/formValidation.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/select2/select2.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/remark/examples/css/forms/advanced.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/ladda/ladda.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/remark/examples/css/uikit/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/remark/examples/css/uikit/modals.css') }}">
@endsection
@section('body')
    <div class="page">
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
                <div class="panel-body container-fluid">
                    <div class="row row-lg">
                        <div class="col-md-6">
                            <div class="example-wrap">
                                <h4 class="example-title">Production Reports</h4>
                                <div class="example">
                                    <form
                                        action="{{ URL::to('/production/report' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id)) }}"
                                        method="post" enctype="multipart/form-data" autocomplete="off">

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="example">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <i class="icon md-calendar" aria-hidden="true"></i>
                                                        </span>
                                                        <input type="text" class="form-control" name="date"
                                                            value="{{ date('m/d/Y') }}" data-plugin="datepicker" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control shiftSelection" name="shiftSelection[]"
                                                            multiple data-plugin="select2" data-placeholder="Select Shift"
                                                            required>
                                                            {{-- @foreach ($machine->section->department->businessUnit->company->shifts as $shift)
                                                                <option value="{{ $shift->id }}">
                                                                    {{ $shift->shift_number }}</option>
                                                            @endforeach --}}

                                                            {{-- Updated by Abdullah 17/11/23 start --}}
                                                            {{-- @foreach ($machine->section->department->businessUnit->company->shifts->where('businessunit_id', null) as $shift)
                                                                <option value="{{ $shift->id }}">
                                                                    {{ $shift->shift_number }}</option>
                                                            @endforeach --}}

                                                            {{-- udated by Abdullah 22-11-23 start  --}}
                                                            @foreach ($machine->section->department->businessUnit->shifts as $shift)
                                                                <option value="{{ $shift->id }}">
                                                                    {{ $shift->shift_number }}</option>
                                                            @endforeach
                                                            {{-- udated by Abdullah 22-11-23 end  --}}


                                                            {{-- Updated by Abdullah 17/11/23 end --}}

                                                            <option value="All-Day">All Day</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 to-date" hidden>
                                                <div class="example">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <i class="icon md-calendar" aria-hidden="true"></i>
                                                        </span>
                                                        <input type="text" class="form-control" name="to_date"
                                                            value="{{ date('m/d/Y') }}" data-plugin="datepicker">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                {{-- Updated by Abdullah 14-11-2023 --}}
                                                <select class="form-control" id="reportType" data-plugin="select2"
                                                    name="reportType" required>

                                                    <!-- <option value="shift-production-report">Shift Production Report</option> -->

                                                    <option value="shift-production-report-quality-toe">Shift Production
                                                        Report - TOE</option>
                                                    <option value="shift-production-report-quality">Shift Production Report
                                                        - Quality</option>
                                                    <option value="shift-production-report-next">Shift Production Report -
                                                        Next</option>
                                                    <option value="shift-production-report-summarized">Shift Production
                                                        Report Summarized</option>
                                                    <!-- <option value="shift-production-report-raw">Shift Production Report Raw</option> -->
                                                    <option value="operator-wise-oee">Operator Wise OEE</option>
                                                    <option value="production-report">Production Report</option>
                                                    <option value="shift-production-report-job">Shift Production Report -
                                                        JOB</option>
                                                </select>
                                                {{-- Updated by Abdullah 14-11-2023 --}}

                                            </div>
                                            <div class="col-md-8">
                                                <div class="example-wrap job-wise-performance" hidden>
                                                    <div class="example" style="margin-bottom: 0px">
                                                        <input type="text" class="form-control"
                                                            placeholder="Enter Production Order #" name="productionOrder">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control" id="operator" name="operator"
                                                            hidden>
                                                            <option value="0">None</option>
                                                            @foreach ($operators as $operator)
                                                                <option value="{{ $operator->id }}">{{ $operator->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group form-material">
                                            <button type="submit" class="btn btn-primary"
                                                id="submitProductionReport">Generate</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="example-wrap">
                                <h4 class="example-title">Losses Reports</h4>
                                <div class="example">
                                    <form
                                        action="{{ URL::to('losses/report' . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id)) }}"
                                        method="post" enctype="multipart/form-data" autocomplete="off">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="example">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <i class="icon md-calendar" aria-hidden="true"></i>
                                                        </span>
                                                        <input type="text" class="form-control"
                                                            name="losses_from_date" value="{{ date('m/d/Y') }}"
                                                            data-plugin="datepicker" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control lossesShiftSelection"
                                                            name="lossesShiftSelection[]" multiple data-plugin="select2"
                                                            data-placeholder="Select Shift" required>
                                                            {{-- Updated by Abdullah 17/11/23 start --}}

                                                            {{-- @foreach ($machine->section->department->businessUnit->company->shifts as $shift)
                                                                <option value="{{ $shift->id }}">
                                                                    {{ $shift->shift_number }}</option>
                                                            @endforeach --}}


                                                            {{-- @foreach ($machine->section->department->businessUnit->company->shifts->where('businessunit_id', null) as $shift)
                                                                <option value="{{ $shift->id }}">
                                                                    {{ $shift->shift_number }}</option>
                                                            @endforeach --}}

                                                            {{-- Updated by Abdullah 22-11-23 start --}}
                                                            @foreach ($machine->section->department->businessUnit->shifts as $shift)
                                                                <option value="{{ $shift->id }}">
                                                                    {{ $shift->shift_number }}</option>
                                                            @endforeach
                                                            {{-- Updated by Abdullah 22-11-23 start --}}


                                                            {{-- Updated by Abdullah 17/11/23 end --}}
                                                            <option value="All-Day">All Day</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 losses-to-date" hidden>
                                                <div class="example">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <i class="icon md-calendar" aria-hidden="true"></i>
                                                        </span>
                                                        <input type="text" class="form-control" name="losses_to_date"
                                                            value="{{ date('m/d/Y') }}" data-plugin="datepicker">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <select class="form-control select2 chk" data-plugin="select2"
                                                    id="lossesReportType" name="lossesReportType" required>
                                                    <option value="job-wise-setting">Job Wise Setting</option>
                                                    <option value="performance=loss">Performance Loss</option>
                                                    <option value="performance-loss-next">Performance Loss Next</option>
                                                    <option value="availability-losses">% Availability Losses</option>

                                                    {{-- Updated by Abdullah 21-11-23 start  --}}
                                                    {{-- <option value="availability-losses-2">% Availability Losses Temp
                                                    </option> --}}
                                                    <option value="availability-losses-toe">% Availability Losses - TOE
                                                    </option>
                                                    {{-- Updated by Abdullah 21-11-23 end  --}}

                                                    <option value="error-history">Error History</option>
                                                    <option value="detailed-error-history">Error History - Detailed
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control" name="errorcat" id="caterrors"
                                                            hidden>
                                                            <option value="0">None</option>
                                                            @foreach ($errorCategories as $cat)
                                                                <!-- <optgroup label="{{ $cat->name }}"> -->

                                                                <option value="{{ $cat->id }}">
                                                                    {{ $cat->id . ' - ' . $cat->name }}</option>

                                                                <!-- </optgroup> -->
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control" name="report_type" id="report_type"
                                                            hidden>
                                                            <option value="0">None</option>
                                                            <option value="short_stops">Short Stop </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8 ">
                                                <div class="example-wrap">
                                                    <div class="example">
                                                        <select class="form-control" name="error" id="errors"
                                                            hidden>
                                                            <option value="0">None</option>
                                                            @foreach ($errorCodes as $error)
                                                                <option value="{{ $error->id }}">
                                                                    {{ $error->id . ' - ' . $error->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>



                                        </div>
                                        <div class="form-group form-material">
                                            <button type="submit" class="btn btn-primary">Generate</button>
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
    <script src="{{ asset('assets/remark/examples/js/charts/gauges.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/matchheight.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/jquery-placeholder/jquery.placeholder.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/jquery-placeholder.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/input-group-file.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/bootstrap-select/bootstrap-select.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/select2.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/bootstrap-select.js') }}"></script>
    <script>
        $('.shiftSelection').change(function() {
            if ($('.shiftSelection').val() == 'All-Day') {
                $('.to-date').removeAttr('hidden');
            } else {
                $('.to-date').attr('hidden', 'true');
            }
        });

        $('.lossesShiftSelection').change(function() {
            if ($('.lossesShiftSelection').val() == 'All-Day') {
                $('.losses-to-date').removeAttr('hidden');
            } else {
                $('.losses-to-date').attr('hidden', 'true');
            }
        });

        $('#reportType').change(function() {
            if ($(this).val() == 'operator-wise-oee') {
                $('.job-wise-performance').attr('hidden', 'hidden');
                $('#operator').removeAttr('hidden');
                $('#operator').select2();
                if ($('#operator').val() == 0) {
                    $('#submitProductionReport').attr('disabled', 'disabled');
                }
            } else if ($(this).val() == 'job-wise-performance') {
                if ($('#operator').select2()) {
                    $('#operator').select2('destroy');
                }
                $('#operator').attr('hidden', 'hidden');
                $('.job-wise-performance').removeAttr('hidden');
                $('#submitProductionReport').removeAttr('disabled');
            } else {
                if ($('#operator').select2()) {
                    $('#operator').select2('destroy');
                }
                $('#operator').attr('hidden', 'hidden');
                $('.job-wise-performance').attr('hidden', 'hidden');
                $('#submitProductionReport').removeAttr('disabled');
            }
        });
        $('#lossesReportType').change(function() {
            if ($(this).val() == 'error-history' || $(this).val() == 'detailed-error-history') {
                $('#errors').removeAttr('hidden');
                $('#errors').select2();
                //$(".err").show();
            } else {
                try {
                    if ($('#errors').select2()) {
                        $('#errors').select2('destroy');
                        // $(".err").hide();
                    }
                } catch (e) {

                }
                $('#errors').attr('hidden', 'hidden');
                // $(".err").hide();
            }
        });
        $('#lossesReportType').change(function() {

            if ($(this).val() == 'availability-losses') {
                $('#caterrors').removeAttr('hidden');

                $('#report_type').removeAttr('hidden');
                $('#caterrors').select2();
                $("#cater").removeClass('d-none');
                //$('#caterrors').removeAttr('style');
            } else {
                try {
                    if ($('#caterrors').select2()) {
                        $('#caterrors').select2('destroy');
                        //  $(".cterror").hide();
                        $("#cater").addClass('d-none');
                    }
                } catch (e) {

                }
                $('#caterrors').attr('hidden', 'hidden');
                $('#report_type').attr('hidden', 'hidden');
                $("#cater").addClass('d-none');
            }
        });
        $('#operator').change(function() {
            if ($(this).val() == 0) {
                $('#submitProductionReport').attr('disabled', 'disabled');
            } else {
                $('#submitProductionReport').removeAttr('disabled');
            }
        })
    </script>
@endsection
