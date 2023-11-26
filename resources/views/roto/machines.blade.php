@extends('layouts.' . $layout)
@section('header')
    <link rel="stylesheet" href="{{ asset('assets/global/vendor/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/vendor/datatables.net-fixedheader-bs4/dataTables.fixedheader.bootstrap4.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/vendor/datatables.net-fixedcolumns-bs4/dataTables.fixedcolumns.bootstrap4.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/vendor/datatables.net-rowgroup-bs4/dataTables.rowgroup.bootstrap4.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/vendor/datatables.net-scroller-bs4/dataTables.scroller.bootstrap4.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/vendor/datatables.net-select-bs4/dataTables.select.bootstrap4.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/vendor/datatables.net-responsive-bs4/dataTables.responsive.bootstrap4.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/vendor/datatables.net-buttons-bs4/dataTables.buttons.bootstrap4.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/remark/examples/css/tables/datatable.css') }}">
    <!-- Fonts -->
    <link rel="stylesheet" href="{{ asset('assets/global/fonts/font-awesome/font-awesome.css') }}">
@endsection
@section('body')
    <div class="page">
        <div class="page-header">
            <h1 class="page-title">All Machines</h1>
            <ol class="breadcrumb">
                Currently enrolled in RotoEye cloud.
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
                    <h3 class="panel-title">Users</h3>
                </header>
                <div class="panel-body">
                    <table class="table table-hover dataTable table-striped w-full" id="exampleTableSearch">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>

                                <th>Max Speed</th>
                                <th>IP</th>
                                {{-- <th>Bin1</th>
                                <th>Bin2</th> --}}
                                <th>Hardware</th>
                                <th>Time UOM</th>
                                <th>Qty UOM</th>
                                <th>Waste Speed</th>
                                <!-- <th>Auto Downtime</th> -->
                                <!-- <th>Graph Span</th> -->
                                <th>Roller Circumference</th>

                                <th>Section</th>

                                <th>Company</th>
                                <th>Status</th>

                                <!-- <th>Delete</th> -->
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>

                                <th>Max Speed</th>
                                <th>IP</th>
                                {{-- <th>Bin1</th>
                                <th>Bin2</th> --}}
                                <th>Hardware</th>
                                <th>Time UOM</th>
                                <th>Qty UOM</th>
                                <th>Waste Speed</th>
                                <!-- <th>Auto Downtime</th> -->
                                <!-- <th>Graph Span</th> -->
                                <th>Circumference</th>

                                <th>Section</th>

                                <th>Company</th>
                                <th>Status</th>
                                <th>Bin1</th>
                                <!-- <th>Delete</th> -->
                            </tr>
                        </tfoot>
                        <tbody>

                            @foreach ($machines as $row)
                                <tr>
                                    <td><a
                                            href="{{ URL::to('machine/update' . '/' . $row->id) }}">{{ $row->sap_code }}</a>
                                    </td>
                                    <td>{{ $row->name }}</td>
                                    <td>{{ $row->max_speed }}</td>
                                    <td class="machine-hardware" id="machine-hardware" data-ip="{{ $row->ip }}">
                                        <a href="{{ 'http://' . $row->ip }}" target="_blank">{{ $row->ip }}</a>
                                    </td>



                                    {{-- Code added by abdullah start --}}
                                    {{-- <td>
                                        <small>
                                            <a href="{{ url('machines/' . $row->sap_code . '/' . $row->bin1) }}">
                                                {{ $row->bin1 }}
                                            </a>
                                        </small>

                                    </td>
                                    <td>
                                        <small>
                                            <a href="{{ url('machines/' . $row->sap_code . '/' . $row->bin2) }}">
                                                {{ $row->bin2 }}
                                            </a>
                                        </small>

                                    </td> --}}
                                    {{-- Code added by abdullah end --}}

                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle"
                                                id="exampleGroupDrop1" data-toggle="dropdown" aria-expanded="false"><i
                                                    class="md-settings"></i> </button>
                                            <div class="dropdown-menu" aria-labelledby="exampleGroupDrop1" role="menu">
                                                <a class="dropdown-item" href="{{ 'http://' . $row->ip . '/sys1901tem' }}"
                                                    target="_blank" role="menuitem">Update Binary</a>
                                                <a class="dropdown-item" href="{{ 'http://' . $row->ip . '/SYSLog' }}"
                                                    target="_blank" role="menuitem">System Logs</a>
                                                <a class="dropdown-item" href="{{ 'http://' . $row->ip . '/json' }}"
                                                    target="_blank" role="menuitem">Local Records JSON</a>
                                                <a class="dropdown-item"
                                                    href="{{ URL::to('/import/local/data/' . $row->id) }}" target="_blank"
                                                    role="menuitem">Import Local Records</a>
                                                <a class="dropdown-item" href="{{ 'http://' . $row->ip . '/text' }}"
                                                    target="_blank" role="menuitem">Local Records Text</a>
                                                <a class="dropdown-item" href="{{ 'http://' . $row->ip . '/DELog' }}"
                                                    target="_blank" role="menuitem">Delete Local Records</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-success">{{ $row->time_uom }}</span></td>
                                    <td><span class="badge badge-info">{{ $row->qty_uom }}</span></td>
                                    <td>{{ $row->waste_speed }}</td>
                                    <!-- <td>{{ $row->auto_downtime }}</td>
                                                             <td>{{ $row->graph_span }}</td> -->
                                    <td>{{ number_format($row->roller_circumference, 2) }}</td>

                                    <td>{{ $row->section->name }}</td>

                                    <td>{{ $row->section->department->businessUnit->company->name }}</td>
                                    <td><a href="{{ URL::to('machine/updateStatus' . '/' . $row->id) }}">
                                            @if ($row->is_disabled == 1)
                                            <span class="badge badge-danger">Disable</span> @else<span
                                                    class="badge badge-success"> Active </span>
                                            @endif
                                        </a> <a href="{{ URL::to('machine/delete' . '/' . $row->id) }}"><i
                                                class="md-delete"></i></a></td>
                                    <!--<td></td>
                                                                                                                                                                                                                                                                                                                            <td> </td> -->
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('footer')
    <script src="{{ asset('assets/global/vendor/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/datatables.net-fixedheader/dataTables.fixedHeader.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/datatables.net-fixedcolumns/dataTables.fixedColumns.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/datatables.net-rowgroup/dataTables.rowGroup.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/datatables.net-scroller/dataTables.scroller.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/datatables.net-responsive/dataTables.responsive.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/datatables.net-responsive-bs4/responsive.bootstrap4.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/datatables.net-buttons/dataTables.buttons.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/datatables.net-buttons/buttons.html5.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/datatables.net-buttons/buttons.flash.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/datatables.net-buttons/buttons.print.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/datatables.net-buttons/buttons.colVis.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/datatables.net-buttons-bs4/buttons.bootstrap4.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/asrange/jquery-asRange.min.js') }}"></script>
    <script src="{{ asset('assets/global/vendor/bootbox/bootbox.js') }}"></script>
    <script src="{{ asset('assets/global/js/Plugin/datatables.js') }}"></script>

    <script src="{{ asset('assets/remark/examples/js/tables/datatable.js') }}"></script>
    <script src="{{ asset('assets/remark/examples/js/uikit/icon.js') }}"></script>

    <script>
        function checkHardwareStatus() {
            /*$('.machine-hardware').each(function(i, obj) {
                var ip = $(this).data('ip');
                $.ajax({
                    timeout: 10000,
                    type: 'GET',
                    dataType: 'jsonp',
                    url: "http://" + ip,
                    cache: false,
                    "error":function(XMLHttpRequest,textStatus, errorThrown) {
                        if(errorThrown == "timeout") {
                            result.push({
                                'url': ip,
                                'status': "woah, there we got a timeout..."
                            });
                            $(obj).closest('tr').find('#hardwareStatus').remove();
                            $(obj).closest('tr').find('.machine-hardware').append('<span id="hardwareStatus" class="badge badge-danger">Not Live</span>');
                        }
                    },
                    complete: function(){
                        if(result.length == list.length) {
                            alert("All url's checked. Check the console for 'result' varialble");
                            console.log(result);
                        }
                    },
                    statusCode: {
                        404:function(){
                            result.push({
                                'url': ip,
                                'status': "404!"
                            });
                        },
                        0:function(){
                            result.push({
                                'url': ip,
                                'status': "0!"
                            });
                        },
                        500:function(){
                            result.push({
                                'url': ip,
                                'status': "500"
                            });
                        },
                        200:function(){
                            result.push({
                                'url': ip,
                                'status': "it worked!"
                            });
                            $(obj).closest('tr').find('#hardwareStatus').remove();
                            $(obj).closest('tr').find('.machine-hardware').append('<span id="hardwareStatus" class="badge badge-success">Live</span>');
                        }
                    }
                });
            });*/
        }
        // setInterval(function(){checkHardwareStatus()}, 5000);
        // var result = new Array;
    </script>
@endsection
