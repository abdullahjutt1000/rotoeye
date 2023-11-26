@extends('layouts.' . $layout)
@section('header')
    <link rel="stylesheet" href="{{ asset('assets/global/fonts/material-design/material-design.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/fonts/brand-icons/brand-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/remark/examples/css/uikit/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/vendor/datatables.net-fixedheader-bs4/dataTables.fixedheader.bootstrap4.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/vendor/datatables.net-fixedcolumns-bs4/dataTables.fixedcolumns.bootstrap4.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/vendor/datatables.net-scroller-bs4/dataTables.scroller.bootstrap4.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/vendor/datatables.net-select-bs4/dataTables.select.bootstrap4.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/vendor/datatables.net-responsive-bs4/dataTables.responsive.bootstrap4.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/vendor/datatables.net-buttons-bs4/dataTables.buttons.bootstrap4.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/remark/examples/css/tables/datatable.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/fonts/font-awesome/font-awesome.css') }}">
    <style>
    </style>
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/morris/morris.css') }}">
    <style>
        .panel {
            page-break-before: always;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('assets/global/vendor/gauge-js/gauge.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/fonts/material-design/material-design.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/global/fonts/brand-icons/brand-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/chartist/chartist.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/remark/examples/css/widgets/chart.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/formvalidation/formValidation.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/select2/select2.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/remark/examples/css/forms/advanced.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/ladda/ladda.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/remark/examples/css/uikit/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/remark/examples/css/uikit/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('global/vendor/nprogress/nprogress.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/remark/examples/css/widgets/chart.css') }}">
@endsection
@section('body')
    <div class="page">
        <div class="page-header">
            <div class="page-header-actions" style="left: 30px">
                {{--                <button id="metaData" type="button" class="btn btn-sm btn-icon btn-primary btn-round" data-toggle="tooltip" data-original-title="Print" onclick="javascript:printContent('print-panel');"> --}}
                {{--                    <i class="icon md-print" aria-hidden="true"></i> Print --}}
                {{--                </button> --}}
                {{--                <button onclick="exportTableToExcel('error-history', '{{$machine->name.'-'.$error->name}}')" class="btn btn-sm btn-icon btn-success btn-round" data-toggle="tooltip" data-original-title="Export to Excel">Export to Excel</button> --}}
            </div>
        </div>
        <div class="page-content">
            <div class="row" data-plugin="matchHeight" data-by-row="true">
                <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="panel" id="print-panel">
                        <header class="panel-heading">
                            <h3 class="panel-title">
                                <strong>{{ $error->name }} History</strong> -
                                <small>{{ isset($from) ? date('d M, Y', strtotime($from)) . ' to ' . date('d M, Y', strtotime($to)) : $date }}</small><br>
                                <small>{{ $machine->name . ', ' . $machine->section->name }}</small><br>
                            </h3>
                        </header>
                        <div class="panel-body">
                            {{--                            <form autocomplete="off" method="post" action="{{URL::to('downtime/report/update'.'/'.\Illuminate\Support\Facades\Crypt::encrypt($machine->id))}}" enctype="multipart/form-data"> --}}

                            <table class="table table-hover dataTable table-striped w-full" id="error-history">
                                <thead>
                                    <tr>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Error Name</th>
                                        <th>Error Comments</th>
                                        <th>Err Duration (Min)</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($records as $rec)
                                        @if (in_array($rec['error_id'], $codes))
                                            <tr>
                                                <td>
                                                    <div class="form-group row">
                                                        <div class="col-sm-10">
                                                            <input type="text" readonly
                                                                class="form-control-plaintext text-secondary from"
                                                                name="downtime_record_form[]"
                                                                value="{{ date('Y-m-d H:i:s', strtotime($rec['from'])) }}"
                                                                hidden height="0px">
                                                            {{ date('d M, Y H:i:s', strtotime($rec['from'])) }}
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="form-group row">
                                                        <div class="col-sm-10">
                                                            <input type="text" readonly
                                                                class="form-control-plaintext text-secondary to"
                                                                name="downtime_record_to[]"
                                                                value="{{ date('Y-M-d H:i:s', strtotime($rec['to'])) }}"
                                                                height="0px" hidden>
                                                            {{ date('d M, Y H:i:s', strtotime($rec['to'])) }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>

                                                    <select class="form-control form-control-sm error_id"
                                                        data-plugin="select2" aria-label="Default select example"
                                                        name="downtime_record_error_id[]">
                                                        @foreach ($errors as $error)
                                                            @if ($error->id == $rec['error_id'])
                                                                <option selected value="{{ $error->id }}">
                                                                    {{ $error->id }} - {{ $rec['error_name'] }}
                                                                </option>
                                                            @endif
                                                            <option value="{{ $error->id }}">
                                                                {{ $error->id }} -{{ $error->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>

                                                <td>
                                                    <textarea name="downtime_record_err_comments[]" class="form-control err_comments" id="exampleFormControlTextarea1"
                                                        rows="1">{{ $rec['err_comments'] }}</textarea>
                                                </td>
                                                <td>{{ number_format($rec['duration'], 0) }}</td>
                                                @if ($rec['error_id'] != 500)
                                                    <td><button onclick="allocate_downtime($(this))"
                                                            class="btn btn-success">Save</button></td>
                                                @else
                                                    <td><button onclick="allocate_downtime($(this))"
                                                            class="btn btn-danger">Save</button></td>
                                                @endif

                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                            {{--                                <button type="submit" class="btn btn-primary">Submit</button> --}}
                            {{--                            </form> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('graphFooter')
    <script src="{{ asset('assets/global/vendor/raphael/raphael.min.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/morris/morris.min.js') }}"></script>
@endsection
@section('footer')
    <script>
        function allocate_downtime(rec) {
            var up_from = rec.parent().parent().get(0).children[0].getElementsByTagName('input')[0].value;
            var up_to = rec.parent().parent().get(0).children[1].getElementsByTagName('input')[0].value;
            var up_error_id = rec.parent().parent().get(0).children[2].getElementsByTagName('select')[0].value;
            var up_err_comment = rec.parent().parent().get(0).children[3].getElementsByTagName('textarea')[0].value;

            var downtimeFrom = up_from
            var downtimeTo = up_to
            var downtimeID = up_error_id
            var description = up_err_comment
            var difference = parseInt(new Date(downtimeTo).getTime() / 1000) - parseInt(new Date(downtimeFrom).getTime() /
                1000);

            // if((difference/3600) <= 24)
            if (1) {
                $.ajax({

                    url: '{!! URL::to('allocate/downtime/manual') !!}',
                    method: 'GET',
                    async: 'FASLE',
                    data: {
                        downtimeTo: downtimeTo,
                        downtimeFrom: downtimeFrom,
                        downtimeID: downtimeID,
                        downtimeDescription: description,
                        machine_id: "{!! $machine->id !!}"
                    },
                    statusCode: {
                        200: function(response) {
                            var responsee = JSON.parse(response);
                            console.log(responsee);
                            alert('Allocated');

                            if (rec.parent().parent().get(0).children[2].getElementsByTagName('select')[0]
                                .value != 500) {
                                rec.removeClass('btn-danger')
                                rec.addClass('btn-success')
                            }

                        },
                        500: function(response) {
                            console.log(response);
                            alert('Downtime cannot exceed 24 Hours. Please check date and time.');
                        }
                    }
                });

            } else {
                alert('Downtime cannot exceed 24 Hours. Please check date and time.');
            }
        }
    </script>
    <script src="{{ asset('assets/global/vendor/sparkline/jquery.sparkline.min.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/chartist/chartist.min.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/matchheight/jquery.matchHeight-min.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/gauge-js/gauge.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/gauge.js') }}"></script>
    <script src="{{ asset('assets/remark/examples/js/charts/gauges.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/matchheight.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/jquery-placeholder/jquery.placeholder.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/jquery-placeholder.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/input-group-file.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/bootstrap-select/bootstrap-select.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/select2.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/bootstrap-select.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/asprogress/jquery-asProgress.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/jquery-appear/jquery.appear.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/nprogress/nprogress.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/jquery-appear.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/nprogress.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/bootstrap-datepicker.js') }}"></script>


    <script src="{{ asset('assets/global/vendor/sparkline/jquery.sparkline.min.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/matchheight/jquery.matchHeight-min.js') }}"></script>
    <script src="{{ asset('assets/remark/examples/js/charts/gauges.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/matchheight.js') }}"></script>

    <script src="{{ asset('assets/global/vendor/asrange/jquery-asRange.min.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/bootbox/bootbox.js') }}"></script>
    <script src="{{ asset('assets/remark/custom/canvas.js') }}"></script>

    <script src="{{ asset('assets/remark/examples/js/tables/datatable.js') }}"></script>
    <script src="{{ asset('assets/remark/examples/js/uikit/icon.js') }}"></script>

    <script src="{{ asset('assets/custom/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/custom/jQuery.print.js') }}"></script>
@endsection
