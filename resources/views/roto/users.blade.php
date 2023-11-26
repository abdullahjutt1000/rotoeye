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
            <h1 class="page-title">All Users</h1>
            <ol class="breadcrumb">
                Currently enrolled in RotoEye cloud along with their access level against each.
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
                                <th>ID</th>
                                <th>CNIC</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Login</th>
                                <th>Password</th>
                                <th>Rights</th>
                                <th>Picture</th>
                                <th>Machines</th>
                                <th>User Added On</th>
                                <th>Last Updated On</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>ID</th>
                                <th>CNIC</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Login</th>
                                <th>Password</th>
                                <th>Rights</th>
                                <th>Picture</th>
                                <th>Machines</th>
                                <th>User Added On</th>
                                <th>Last Updated On</th>
                                <th>Delete</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach ($users as $employee)
                                <tr>
                                    <td><a
                                            href="{{ URL::to('user/update' . '/' . $employee->id . '/' . \Illuminate\Support\Facades\Crypt::encrypt($machine->id)) }}">{{ $employee->id }}</a>
                                    </td>
                                    <td>{{ $employee->cnic }}</td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->designation }}</td>
                                    <td>{{ $employee->login }}</td>
                                    <td><a href="{{ URL::to('change/password' . '/' . $employee->id) }}">click here to
                                            change</a></td>
                                    @if ($employee->rights == 0)
                                        <td>
                                            <span class="badge badge-danger">Operator</span>
                                        </td>
                                    @elseif($employee->rights == 1)
                                        <td>
                                            <span class="badge badge-success">Admin</span>
                                        </td>
                                    @elseif($employee->rights == 2)
                                        <td>
                                            <span class="badge badge-info">Power User</span>
                                        </td>
                                    @elseif($employee->rights == 3)
                                        <td>
                                            <span class="badge badge-info">Reporting User</span>
                                        </td>
                                    @endif
                                    <td><a href="{{ asset('assets/global/portraits/' . $employee->photo) }}"
                                            target="_blank">{{ $employee->photo }}</a></td>
                                    <td>
                                        <ul>
                                            @foreach ($employee->allowedMachines as $allowedMachine)
                                                <li>{{ $allowedMachine->name . ' - ' . $allowedMachine->sap_code }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>{{ date('M d, Y', strtotime($employee->created_at)) }}</td>
                                    <td>{{ date('M d, Y', strtotime($employee->updated_at)) }}</td>
                                    <td><a href="{{ URL::to('user/delete' . '/' . $employee->id) }}"><i
                                                class="md-delete"></i></a></td>
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
@endsection
