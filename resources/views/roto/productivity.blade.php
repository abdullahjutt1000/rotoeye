@extends('layouts.'.$layout)
@section('header')
    <link rel="stylesheet" href="{{asset('assets/global/vendor/gauge-js/gauge.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/fonts/material-design/material-design.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/fonts/brand-icons/brand-icons.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/chartist/chartist.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/widgets/chart.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/formvalidation/formValidation.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/select2/select2.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/forms/advanced.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/ladda/ladda.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/uikit/buttons.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/uikit/modals.css')}}">
    <link rel="stylesheet" href="{{asset('global/vendor/nprogress/nprogress.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/widgets/chart.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/alertify/alertify.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/notie/notie.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/advanced/alertify.css')}}">
    <style>
        .card-header{
            background-color: #ed1b23;
        }
    </style>
    <style>
        .row:after {
            content: "";
            display: table;
            clear: both;
        }
        .col {
            float: left;
            width: 33.33%;
            height: 100px;
        }
    </style>
@endsection
@section('body')
    <div class="page">
        <div class="page-content container-fluid">
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
            <div class="row mt-30">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header text-light">
                            <div class="row">
                                <div class="col-3">
                                    @if(Session::get('rights') == 1)
                                        <select class="form-control" id="company">
                                            <option  value="" checked>Group</option>
                                            @foreach($companies as $company)
                                                <option value="{{$company->id}}">{{$company->name}}</option>
                                            @endforeach
                                        </select>
                                    @elseif(Session::get('rights') == 2)
                                        <select class="form-control" id="allowed-machines">
                                            <option  value="" checked>All</option>
                                            @foreach($allowed_machines as $allowed_machine)
                                                <option value="{{$allowed_machine->id}}">{{$allowed_machine->sap_code}}-{{$allowed_machine->name}}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-primary float-right invisible" id="backButton">Back</button>
                            <div id="chartContainer" style="height: 370px; width: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('footer')
    <script src="{{asset('assets/global/vendor/sparkline/jquery.sparkline.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/chartist/chartist.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/matchheight/jquery.matchHeight-min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/gauge-js/gauge.min.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/gauge.js')}}"></script>
    <script src="{{asset('assets/remark/examples/js/charts/gauges.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/matchheight.js')}}"></script>
    <script src="{{asset('assets/global/vendor/jquery-placeholder/jquery.placeholder.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/jquery-placeholder.js')}}"></script>

    <script src="{{asset('assets/global/vendor/select2/select2.full.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootstrap-select/bootstrap-select.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/select2.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/bootstrap-select.js')}}"></script>

    <script src="{{asset('assets/global/vendor/asprogress/jquery-asProgress.js')}}"></script>
    <script src="{{asset('assets/global/vendor/jquery-appear/jquery.appear.js')}}"></script>
    <script src="{{asset('assets/global/vendor/nprogress/nprogress.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/jquery-appear.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/nprogress.js')}}"></script>

    <script src="{{asset('assets/global/vendor/alertify/alertify.js')}}"></script>
    <script src="{{asset('assets/global/vendor/notie/notie.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/alertify.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/notie-js.js')}}"></script>
    <script src="{{asset('assets/remark/custom/canvas.js')}}"></script>
    <script src="{{asset('assets/remark/custom/jquery.canvasjs.min.js')}}"></script>
    <script src="{{asset('assets/custom/loadoee.js')}}"></script>
    {{--    <script src="{{asset('assets/custom/oee-dashboard.js')}}"></script>--}}
    <script>
        window.onload = function () {
            //Chart Initialization
            var chart = null;
            chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                theme: "light2",
                axisX: {
                    // labelFormatter:  e => CanvasJS.formatDate( e.label, "MMM YYYY"),
                },
                axisY: {
                    suffix: "%",
                },
                legend:{
                    cursor: "pointer",
                    fontSize: 16,
                    itemclick:  (e)=>{
                        if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                            e.dataSeries.visible = false;
                        }
                        else{
                            e.dataSeries.visible = true;
                        }
                        chart.render();
                    }
                },
                toolTip:{
                    shared: true
                },
                data: []
            });
            //Chart Initialization
            //Functions
            function onClick(e)
            {
                $.ajax({
                    url: '{!! URL::to('/api/productivity/group_productivity') !!}',
                    method: 'GET',
                    async: 'FASLE',
                    data:{
                        month:new Date(e.dataPoint.label).getMonth()+1,
                        rights:{{$user->rights}},
                    },
                    statusCode: {
                        200:function (response)
                        {
                            e.chart.options.axisX ={
                                // labelFormatter:  e => CanvasJS.formatDate( e.label, "MMM YYYY"),
                            },
                                e.chart.options.data = [{
                                    type: "column",
                                    showInLegend: true,
                                    legendText: "Availability",
                                    name: "Availability",
                                    dataPoints: response.records.map(data=>{return {y:data.availability,label:data.companies_name}})
                                },
                                    {
                                        type: "column",
                                        showInLegend: true,
                                        legendText: "Performance",
                                        name: "Performance",
                                        dataPoints: response.records.map(data=>{return {y:data.performance,label:data.companies_name}})
                                    },
                                    {
                                        type: "column",
                                        showInLegend: true,
                                        legendText: "OEE",
                                        name: "OEE",
                                        dataPoints: response.records.map(data=>{return {y:data.OEE,label:data.companies_name}})
                                    }]
                            e.chart.render();
                        }
                    }
                });
            }

            @if(Session::get('rights') == 1)

                $.ajax({
                    url: '{!! URL::to('/api/productivity/group_productivity') !!}',
                    method: 'GET',
                    async: 'FASLE',
                    data:{
                        company_id:null,
                        rights: {!! Session::get('rights') !!},
                    },
                    statusCode: {
                        200:function (response)
                        {
                            chart.options.axisX= {
                                labelFormatter:  e => CanvasJS.formatDate( e.label, "MMM YYYY"),
                            };
                            chart.options.data = [

                                {
                                    type: "column",
                                    showInLegend: true,
                                    legendText: "Performance",
                                    name: "Performance",
                                    dataPoints: response.records.map(data=>{return {y:data.performance,label:new Date(data.date).toLocaleDateString()}})
                                },
                                {
                                    type: "column",
                                    markerSize: 15,
                                    showInLegend: true,
                                    legendText: "Availability",
                                    name: "Availability",
                                    dataPoints: response.records.map(data=>{return {y:data.availability,label:new Date(data.date).toLocaleDateString()}})
                                },
                                {
                                    type: "line",
                                    markerSize: 15,
                                    showInLegend: true,
                                    click:onClick,
                                    legendText: "OEE",
                                    name: "OEE",
                                    dataPoints: response.records.map(data=>{return {y:data.OEE,label:new Date(data.date).toLocaleDateString()}})
                                }

                            ]
                            chart.render();
                        }
                    }
                });
                $("#company").change(function (){
                    $.ajax({
                        url: '{!! URL::to('/api/productivity/group_productivity') !!}',
                        method: 'GET',
                        async: 'FASLE',
                        data:{
                            company_id:$("#company").val(),
                            rights: {!! Session::get('rights') !!},
                        },
                        statusCode: {
                            200:function (response)
                            {
                                chart.options.axisX= {
                                    // labelFormatter:  e => CanvasJS.formatDate( e.label, "MMM YYYY"),
                                };
                                chart.options.data = [
                                    {
                                        type: "column",
                                        showInLegend: true,
                                        legendText: "Performance",
                                        name: "Performance",
                                        dataPoints: response.records.map(data=>{return {y:data.performance,label:new Date(data.date).toLocaleDateString()}})
                                    },
                                    {
                                        type: "column",
                                        showInLegend: true,
                                        legendText: "Availability",
                                        name: "Availability",
                                        dataPoints: response.records.map(data=>{return {y:data.availability,label:new Date(data.date).toLocaleDateString()}})
                                    },
                                    {
                                        type: "line",
                                        showInLegend: true,
                                        markerSize: 15,
                                        // click:onClick,
                                        legendText: "OEE",
                                        name: "OEE",
                                        dataPoints: response.records.map(data=>{return {y:data.OEE,label:new Date(data.date).toLocaleDateString()}})
                                    }
                                ]
                                chart.render();
                            }
                        }
                    });

                });
            @elseif(Session::get('rights') == 2)
                $.ajax({
                    url: '{!! URL::to('/api/productivity/group_productivity') !!}',
                    method: 'GET',
                    async: 'FASLE',
                    data:{
                        allowed_machines: {!! $allowed_machines !!},
                        rights: {!! Session::get('rights') !!},
                    },
                    statusCode: {
                        200:function (response)
                        {
                            chart.options.axisX= {
                                labelFormatter:  e => CanvasJS.formatDate( e.label, "MMM YYYY"),
                            };
                            chart.options.data = [

                                {
                                    type: "column",
                                    showInLegend: true,
                                    legendText: "Performance",
                                    name: "Performance",
                                    dataPoints: response.records.map(data=>{return {y:data.performance,label:new Date(data.date).toLocaleDateString()}})
                                },
                                {
                                    type: "column",
                                    markerSize: 15,
                                    showInLegend: true,
                                    legendText: "Availability",
                                    name: "Availability",
                                    dataPoints: response.records.map(data=>{return {y:data.availability,label:new Date(data.date).toLocaleDateString()}})
                                },
                                {
                                    type: "line",
                                    markerSize: 15,
                                    showInLegend: true,
                                    // click:onClick,
                                    legendText: "OEE",
                                    name: "OEE",
                                    dataPoints: response.records.map(data=>{return {y:data.OEE,label:new Date(data.date).toLocaleDateString()}})
                                }

                            ]
                            chart.render();
                        }
                    }
                });
                $("#allowed-machines").change(function (){
                    $.ajax({
                        url: '{!! URL::to('/api/productivity/group_productivity') !!}',
                        method: 'GET',
                        async: 'FASLE',
                        data:{
                            rights: {!! Session::get('rights') !!},
                            allowed_machines: $("#allowed-machines").val().length === 0?{!! $allowed_machines !!}:[{id:$("#allowed-machines").val()}]
                        },
                        statusCode: {
                            200:function (response)
                            {
                                chart.options.axisX= {
                                    labelFormatter:  e => CanvasJS.formatDate( e.label, "MMM YYYY"),
                                };
                                chart.options.data = [
                                    {
                                        type: "column",
                                        showInLegend: true,
                                        legendText: "Performance",
                                        name: "Performance",
                                        dataPoints: response.records.map(data=>{return {y:data.performance,label:new Date(data.date).toLocaleDateString()}})
                                    },
                                    {
                                        type: "column",
                                        showInLegend: true,
                                        legendText: "Availability",
                                        name: "Availability",
                                        dataPoints: response.records.map(data=>{return {y:data.availability,label:new Date(data.date).toLocaleDateString()}})
                                    },
                                    {
                                        type: "line",
                                        showInLegend: true,
                                        markerSize: 15,
                                        // click:onClick,
                                        legendText: "OEE",
                                        name: "OEE",
                                        dataPoints: response.records.map(data=>{return {y:data.OEE,label:new Date(data.date).toLocaleDateString()}})
                                    }
                                ]
                                chart.render();
                            }
                        }
                    });

                });

            @endif
        }
    </script>
@endsection
