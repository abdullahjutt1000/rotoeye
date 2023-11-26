@extends('layouts.'.$layout)
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
    @endsection
@section('body')
    <div class="page">
        <div class="page-header">
            <h1 class="page-title">All Products</h1>
            <ol class="breadcrumb">
                All products currently available in Roto Eye
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
                    <h3 class="panel-title">Products</h3>
                </header>
                <div class="panel-body">
                    <table class="table table-hover dataTable table-striped w-full" id="exampleTableSearch">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
{{--                            <th>Process</th>--}}
{{--                            <th>Structure</th>--}}
                            <th>UOM</th>
                            <th>UPS</th>
                            <th>SRW<em>(mm)</em></th>
                            <th>TW<em>(mm)</em></th>
                            <th>GSM<em>(g/m2)</em></th>
                            <th>Thickness<em>(Mic)</em></th>
                            <th>Density<em>(g/m3)</em></th>
                            <th>COL</th>
                            <th>Color/Adhesive</th>
                            <th>Jobs</th>
{{--                            <th>Added On</th>--}}
{{--                            <th>Last Updated At</th>--}}
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
{{--                            <th>Process</th>--}}
{{--                            <th>Structure</th>--}}
                            <th>UOM</th>
                            <th>UPS</th>
                            <th>SRW<em>(mm)</em></th>
                            <th>TW<em>(mm)</em></th>
                            <th>GSM<em>(g/m2)</em></th>
                            <th>Thickness<em>(Mic)</em></th>
                            <th>Density<em>(g/m3)</em></th>
                            <th>COL<em>(mm)</em></th>
                            <th>Color/Adhesive</th>
                            <th>Jobs</th>
{{--                            <th>Added On</th>--}}
{{--                            <th>Last Updated At</th>--}}
                            <th>Action</th>
                        </tr>
                        </tfoot>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td><a href="{{URL::to('product/update'.'/'.$product->id.'/'.\Illuminate\Support\Facades\Crypt::encrypt($machine->id))}}">{{$product->id}}</a></td>
                                    <td>{{$product->name}}</td>
{{--                                    <td>--}}
{{--                                        <ul>--}}
{{--                                            @foreach($product->process as $process)--}}
{{--                                                <li>{{$process->name}}</li>--}}
{{--                                            @endforeach--}}
{{--                                        </ul>--}}
{{--                                    </td>--}}
{{--                                    <td>--}}
{{--                                        <ul>--}}
{{--                                            @foreach($product->structure as $structure)--}}
{{--                                                <li>{{$structure->name}}</li>--}}
{{--                                            @endforeach--}}
{{--                                        </ul>--}}
{{--                                    </td>--}}
                                    <td>{{$product->uom}}</td>
                                    <td>{{$product->ups}}</td>
                                    <td>{{is_null($product->slitted_reel_width)? '-' : $product->slitted_reel_width*1000}}</td>
                                    <td>{{is_null($product->trim_width) ? '-' :$product->trim_width*1000}}</td>
                                    <td>{{is_null($product->gsm) ? '-' :$product->gsm*1000}}</td>
                                    <td>{{is_null($product->thickness)? '-' :$product->thickness*1000000}}</td>
                                    <td>{{$product->density}}</td>
                                    <td>{{is_null($product->col) ? '-' :$product->col*1000}}</td>
                                    <td>{{$product->color_adh}}</td>
                                    <td>{{count($product->jobs )}}</td>
{{--                                    <td>{{date('M d, Y', strtotime($product->created_at))}}</td>--}}
{{--                                    <td>{{date('M d, Y', strtotime($product->updated_at))}}</td>--}}
                                    <td><a href="{{URL::to('product/delete'.'/'.$product->id)}}"><i class="md-delete"></i></a></td>
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
@endsection
