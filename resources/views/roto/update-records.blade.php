@extends('layouts.'.$layout)
@section('header')
    <link rel="stylesheet" href="{{asset('assets/global/fonts/material-design/material-design.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/fonts/brand-icons/brand-icons.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/uikit/buttons.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/tables/datatable.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/fonts/font-awesome/font-awesome.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/select2/select2.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/forms/advanced.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/ladda/ladda.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/uikit/buttons.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/uikit/modals.css')}}">
    <style>
        .panel{
            page-break-before: always;
        }
        table caption{
            flex-wrap: nowrap !important;
        }
        table caption button{
            margin-right: 10px;
        }
        .date{
            cursor: pointer;
            color: #336699;
        }
    </style>
@endsection
@section('body')
    <div class="page">
        <div class="page-aside">
            <div class="page-aside-switch">
                <i class="icon md-chevron-left" aria-hidden="true"></i>
                <i class="icon md-chevron-right" aria-hidden="true"></i>
            </div>
            <div class="page-aside-inner page-aside-scroll">
                <div data-role="container">
                    <div data-role="content">
                        <section class="page-aside-section">
                            <h5 class="page-aside-title" style="padding-left: 15px">Enter Process Information</h5>
                            <form action="{{URL::to('update/date-wise/machine/records')}}" method="post" autocomplete="off">
                                <div class="col-12">
                                    <div class="example round-input-control">
                                        <div class="input-group">
                                            <input class="form-control form-control-sm" type="text" name="job" value="{{$job->id}}" placeholder="Job #" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="example round-input-control">
                                        <div class="input-group">
                                            <input class="form-control form-control-sm" type="text" value="{{$machine->sap_code.' - '.$machine->name}}" placeholder="Machine #" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="example round-input-control">
                                        <div class="input-group">
                                            <input class="form-control form-control-sm" type="text" name="machine" value="{{$machine->id}}" placeholder="Machine #" readonly hidden>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group form-material">
                                        <div>
                                            <div class="radio-custom radio-default radio-inline">
                                                <input type="radio" id="from" name="date-selection-radio" checked/>
                                                <label for="from" style="font-size: 12px">From</label>
                                            </div>
                                            <div class="radio-custom radio-default radio-inline">
                                                <input type="radio" id="to" name="date-selection-radio" />
                                                <label for="to" style="font-size: 12px">To</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="example round-input-control">
                                        <div class="input-group">
                                            <input class="form-control form-control-sm" id="from-input" type="text" name="from" value="{{old('from')}}" placeholder="From" readonly required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="example round-input-control">
                                        <div class="input-group">
                                            <input class="form-control form-control-sm" id="to-input" type="text" name="to" value="{{old('to')}}" placeholder="To" readonly required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="input-group">
                                        <select class="form-control form-control-sm" data-plugin="select2" name="process" data-placeholder="Select Process" required>
                                            <option value=""></option>
                                            @foreach($processes as $process)
                                                <option value="{{$process->id}}">{{$process->process_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12" style="padding-top: 15px">
                                    <button class="btn btn-block btn-primary" type="submit">Update Process</button>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-main">
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
                <div class="row" data-plugin="matchHeight" data-by-row="true">
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="panel" id="print-panel" style="min-height: 842px">
                            <header class="panel-heading">
                                <h3 class="panel-title">
                                    <strong>Updating Records for Job #</strong> - <small>{{$job->id}}</small><br>
                                    <small>{{$machine->sap_code.' - '.$machine->name.', '.$machine->section->name.', Dated '}}{{$date}}</small><br>
                                </h3>
                            </header>
                            <div class="panel-body">
                                <table class="table table-hover dataTable table-striped w-full" id="production-report-table">
                                    <thead>
                                    <tr>
                                        <th>Group</th>
                                        <th>User</th>
                                        <th>Err No</th>
                                        <th>Err Name</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th style="text-align: center;">Duration <br>(Min)</th>
                                        <th style="text-align: right">{{$machine->qty_uom}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {{--*/ $alreadyDoneProducts = [] /*--}}
                                    {{--*/ $alreadyDoneMaterials = [] /*--}}
                                    @foreach($records as $row)
                                        <tr>
                                            <td>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <strong>Job No: </strong>{{$row['job_id'].' - '.$row['product_number']}}<br>
                                                        <strong>Job Name: </strong>{{$row['job_name']}}
                                                    </div>
                                                    <div class="col-6">
                                                        <strong>Substrate: </strong>{{$row['material_combination'].' ('.$row['process_name'].')'}}<br>{{$row['nominal_speed']}}
                                                    </div>
                                                </div>
                                            </td>
                                            <td><strong>{{$row['user_name']}}</strong></td>
                                            <td>{{$row['error_id']}}</td>
                                            <td style="width: 20%;">{{$row['error_name']}}</td>
                                            <td><a class="date" data-value="{{date('Y-m-d H:i:s',strtotime($row['from']))}}">{{date('d M, Y H:i',strtotime($row['from']))}}</a></td>
                                            <td><a class="date" data-value="{{date('Y-m-d H:i:s',strtotime($row['to']))}}">{{date('d M, Y H:i',strtotime($row['to']))}}</a></td>
                                            <td style="text-align: center">{{number_format($row['duration'],0)}}</td>
                                            <td style="text-align: right">{{$row['length']}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('footer')
    <script>
        $(document).ready(function() {
            var table = $('#production-report-table').DataTable({
                "columnDefs": [
                    { "visible": false, "targets": 0 },
                    { "visible": false, "targets": 1 }
                ],
                "sort":null,
                "info":null,
                "paginate":null,
                "searching":null,
                buttons: [
                    {
                        extend: 'pdfHtml5',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        download: 'open',
                        title: 'Project Manager',
                        customize: function(doc) {
                            doc.defaultStyle.fontSize = 8; //<-- set fontsize to 16 instead of 10
                            doc.styles.tableHeader.fontSize = 8;
                        }
                    },
                    'copy', 'csv', 'excel', 'print'
                ],
                "drawCallback": function ( settings ) {
                    var api = this.api();
                    var rows = api.rows( {page:'current'} ).nodes();
                    var last=null;

                    api.column(0, {page:'current'} ).data().each( function ( group, i ) {
                        if ( last !== group ) {
                            $(rows).eq( i ).before(
                                    '<tr class="group" style="background-color: #F6F600"><td colspan="20" style="color: #302e2e">'+group+'</td></tr>'
                            );
                            last = group;
                        }
                    } );
                    api.column(1, {page:'current'} ).data().each( function ( group, i ) {
                        if ( last !== group ) {
                            $(rows).eq( i ).before(
                                    '<tr class="group"><td colspan="20" style="background-color: #FFF; color: #ed1b23">'+group+'</td></tr>'
                            );
                            last = group;
                        }
                    } );
                }
            } );
        });

        $('.date').on('click', function(){
            var datetime = $(this).data('value');
            if($('#from').prop('checked')){
                $('#from-input').val(datetime);
            }
            else if($('#to').prop('checked')){
                $('#to-input').val(datetime);
            }
        });
    </script>

    <script src="{{asset('assets/global/vendor/sparkline/jquery.sparkline.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/matchheight/jquery.matchHeight-min.js')}}"></script>
    <script src="{{asset('assets/remark/examples/js/charts/gauges.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/matchheight.js')}}"></script>
    <script src="{{asset('assets/global/vendor/jquery-placeholder/jquery.placeholder.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/jquery-placeholder.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/datatables.js')}}"></script>

    <script src="{{asset('assets/remark/examples/js/tables/datatable.js')}}"></script>
    <script src="{{asset('assets/remark/examples/js/uikit/icon.js')}}"></script>

    <script src="{{asset('assets/remark/js/Section/Menubar.js')}}"></script>
    <script src="{{asset('assets/remark/js/Section/Sidebar.js')}}"></script>
    <script src="{{asset('assets/remark/js/Section/PageAside.js')}}"></script>
    <script src="{{asset('assets/remark/js/Plugin/menu.js')}}"></script>

    <script src="{{asset('assets/global/vendor/select2/select2.full.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootstrap-select/bootstrap-select.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/select2.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/bootstrap-select.js')}}"></script>

    <script src="{{asset('assets/custom/jquery.dataTables.min.js')}}"></script>

    <script>
        $('body').addClass('page-aside-fixed page-aside-right');
    </script>
@endsection
