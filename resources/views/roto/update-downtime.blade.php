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
    <link rel="stylesheet" href="{{asset('assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/uikit/modals.css')}}">
    <link rel="stylesheet" href="{{asset('global/vendor/nprogress/nprogress.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/widgets/chart.css')}}">
@endsection
@section('body')
    <div class="page">
        <div class="page-content container-fluid">
            <div class="progress" hidden>
                <div class="progress-bar progress-bar-striped active" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100" style="width: 100%" role="progressbar"></div>
            </div>
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
                    <div class="card-group">
                        <div class="card card-block p-0">
                            <div class="vertical-align text-center white p-20 h-250" style="background-color: white">
                                <div class="vertical-align-middle" style="vertical-align: top;">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="counter counter-md text-left">
                                                <div class="counter-label grey-600"><strong>Select Date & Shift</strong></div>
                                            </div>
                                            <hr>
                                        </div>
                                        <div class="col-md-4">

                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="icon md-calendar" aria-hidden="true"></i>
                                            </span>
                                                <input type="text" class="form-control" name="date" id="date" value="{{date('m/d/Y')}}" data-plugin="datepicker" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <select class="form-control shiftSelection" name="shiftSelection[]" multiple data-plugin="select2" data-placeholder="Select Shift" required>
                                                @foreach($machine->section->department->businessUnit->company->shifts as $shift)
                                                    <option value="{{$shift->id}}">{{$shift->shift_number}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">

                                        </div>
                                        <div class="col-4" style="padding-top: 15px">
                                            <button type="button" class="btn btn-block btn-primary" id="getRecords">Get Records</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card card-block p-0">
                            <div class="vertical-align text-center blue-roto white p-20 h-250">
                                <div class="vertical-align-middle">
                                    <div class="row" style="margin-bottom: -5px">
                                        <div class="col-6">
                                            <div class="counter counter-md text-left">
                                                <div class="counter-label white">
                                                    <select class="form-control form-control-sm" data-plugin="select2" id="allocation-type" name="allocation_type">
                                                        <option value = "downtime" selected>Downtime</option>
                                                        <option value = "waste">Waste</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <hr>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group form-material">
                                                <div>
                                                    <div class="radio-custom radio-default radio-inline">
                                                        <input type="radio" id="SingleDowntime" name="inputGender" onclick="singleDowntime()" checked/>
                                                        <label for="inputBasicMale" style="font-size: 12px">Single</label>
                                                    </div>
                                                    <div class="radio-custom radio-default radio-inline">
                                                        <input type="radio" id="MultiDowntime" name="inputGender" onclick="multipleDowntime()" />
                                                        <label for="inputBasicFemale" style="font-size: 12px">Multiple</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="example round-input-control">
                                                <div class="input-group">
                                                    <input class="form-control form-control-sm downtime-from" type="text" name="downtime-from" placeholder="From" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="example round-input-control">
                                                <div class="input-group">
                                                    <input class="form-control form-control-sm downtime-to" id="downtime-to" type="text" name="downtime-to" placeholder="To" readonly>
                                                    <input class="form-control form-control-sm" style="display: none" id="waste-meters" type="text" placeholder="Waste Meters"  readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="example round-input-control" style="margin-top: 0px">
                                                <div class="input-group">
                                                    <input class="form-control form-control-sm downtimeDescription" type="text" id="downtime-description" name="downtime-description" placeholder="Downtime Description">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-8">
                                            <div class="input-group">
                                                <div id="waste-codes" style="display: none">
                                                    <select class="form-control form-control-sm"  data-plugin="select2" id="selectWasteError" name="waste-error">
                                                        @foreach($rotoErrors as $error)
                                                            @if($error->category == 'Waste')
                                                                <option data-category="{{$error->category}}" value="{{$error->id}}">{{$error->id.' - '.$error->name}}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div id="downtime-codes" >
                                                    <select class="form-control form-control-sm"  data-plugin="select2" id="selectError" name="error">
                                                        @foreach($rotoErrors as $error)
                                                            @if($error->category != 'Waste')
                                                                <option data-category="{{$error->category}}" value="{{$error->id}}">{{$error->id.' - '.$error->name}}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <button type="button" class="btn btn-block btn-danger" id="allocateDowntime">Done</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xxl-12 col-md-12">
                    <div class="card card-block p-0">
                        <div class="text-center white p-30">
                            <div class="h-200" id="chartContainer"></div>
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
    <script src="{{asset('assets/global/js/Plugin/input-group-file.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
    <script src="{{asset('assets/global/vendor/select2/select2.full.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootstrap-select/bootstrap-select.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/select2.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/bootstrap-select.js')}}"></script>
    <script src="{{asset('assets/global/vendor/asprogress/jquery-asProgress.js')}}"></script>
    <script src="{{asset('assets/global/vendor/jquery-appear/jquery.appear.js')}}"></script>
    <script src="{{asset('assets/global/vendor/nprogress/nprogress.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/jquery-appear.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/nprogress.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/bootstrap-datepicker.js')}}"></script>
    <script src="{{asset('assets/remark/custom/canvas.js')}}"></script>
    <script>
        $('#allocation-type').change(function(){
            $('#SingleDowntime').prop("checked", true);
            $('.downtime-from').val(null);
            $('.downtime-to').val(null);
            if($('#allocation-type').val() == 'waste')
            {
                $('#downtime-description').attr("placeholder", "Waste Description").placeholder();
                $('#waste-codes').show();
                $('#downtime-codes').hide();
                $('.downtime-to').hide();
                $('#waste-meters').show();
                $('#waste-meters').val(null);
            }
            else
            {
                $('#downtime-description').attr("placeholder", "Downtime Description").placeholder();
                $('#waste-codes').hide();
                $('#downtime-codes').show();
                $('.downtime-to').show();
                $('#downtime-to').replaceWith('<input class="form-control form-control-sm downtime-to" id="downtime-to" type="text" name="downtime-to" placeholder="To" readonly>');
                $('#waste-meters').hide();
                $('#waste-meters').val(null);
            }
        });

        window.onload = function () {
            var dps = [{x: new Date('2018-05-28 00:00:00'), y: 0}];
            var meeter_from = 0;
            var maximumGraphSpeed = '{!! $machine->max_speed !!}';
            var waste_speed = '{!! $machine->waste_speed !!}';
            function onClick(e) {
                if($('#allocation-type').val() == 'waste')
                {
                    if($('.downtime-from').is(':focus')){
                        for (var i = 0; i < dps.length; i++) {
                            if (dps[i].x == e.dataPoint.x) {
                                if (dps[i].y >= waste_speed) {
                                    for(var j=i; j>0;j--){
                                        if(dps[j].y >= waste_speed){
                                            $('.downtime-from').val(dps[j].x.toLocaleTimeString() + ' ' + dps[j].x.toLocaleDateString());
                                            meeter_from = e.dataSeries.dataPoints[e.dataPointIndex-1].hidden;
                                            break;
                                        }
                                    }
                                }
                                else {
                                    for (var j = i + 1; j < dps.length; j++) {
                                        if (dps[j].y >= waste_speed) {
                                            $('.downtime-from').val(dps[j].x.toLocaleTimeString() + ' ' + dps[j].x.toLocaleDateString());
                                            meeter_from = e.dataSeries.dataPoints[e.dataPointIndex-1].hidden;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    else if($('#waste-meters').is(':focus')){
                        for(var i=0; i<dps.length; i++){
                            if(dps[i].x == e.dataPoint.x){
                                if(dps[i].y >= waste_speed){
                                    for(var j=i; j<dps.length;j++){
                                        if(dps[j].y >= waste_speed){
                                            if(dps[j].hidden-meeter_from>=0)
                                            {
                                                $('.downtime-to').val(dps[j].x.toLocaleTimeString()+' '+dps[j].x.toLocaleDateString());
                                                $('#waste-meters').val(dps[j].hidden-meeter_from);
                                                break;
                                            }
                                        }
                                        else{
                                            if(dps[j].hidden-meeter_from>=0)
                                            {
                                                $('.downtime-to').val(dps[j].x.toLocaleTimeString()+' '+dps[j].x.toLocaleDateString());
                                                $('#waste-meters').val(dps[j].hidden-meeter_from);
                                                break;
                                            }
                                        }
                                    }
                                }
                                else{
                                    for(var j=i-1; j<dps.length; j--){
                                        if(dps[j].y >= waste_speed){
                                            if(dps[j].hidden-meeter_from>=0)
                                            {
                                                $('.downtime-to').val(dps[j].x.toLocaleTimeString()+' '+dps[j].x.toLocaleDateString());
                                                $('#waste-meters').val(dps[j].hidden-meeter_from);
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                else
                {
                    if($('.downtime-from').is(':focus')){
                        for (var i = 0; i < dps.length; i++) {
                            if (dps[i].x == e.dataPoint.x) {
                                if (dps[i].y < waste_speed) {
                                    for(var j=i; j>0;j--){
                                        if(dps[j].y > waste_speed){
                                            $('.downtime-from').val(dps[j+1].x.toLocaleTimeString() + ' ' + dps[j+1].x.toLocaleDateString());
                                            break;
                                        }
                                    }
                                }
                                else {
                                    for (var j = i + 1; j < dps.length; j++) {
                                        if (dps[j].y < waste_speed) {
                                            $('.downtime-from').val(dps[j].x.toLocaleTimeString() + ' ' + dps[j].x.toLocaleDateString());
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    else if($('.downtime-to').is(':focus')){
                        for(var i=0; i<dps.length; i++){
                            if(dps[i].x == e.dataPoint.x){
                                if(dps[i].y < waste_speed){
                                    for(var j=i; j<dps.length;j++){
                                        if(dps[j].y > waste_speed){
                                            $('.downtime-to').val(dps[j-1].x.toLocaleTimeString()+' '+dps[j-1].x.toLocaleDateString());
                                            break;
                                        }
                                        else{
                                            $('.downtime-to').val(dps[j].x.toLocaleTimeString()+' '+dps[j].x.toLocaleDateString());
                                        }
                                    }
                                }
                                else{
                                    for(var j=i-1; j<dps.length; j--){
                                        if(dps[j].y < waste_speed){
                                            $('.downtime-to').val(dps[j].x.toLocaleTimeString()+' '+dps[j].x.toLocaleDateString());
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

            }
            var chart = new CanvasJS.Chart("chartContainer", {
                interactivityEnabled: true,
                exportEnabled: true,
                zoomEnabled:true,
                theme: 'light2',
                toolTip:{
                    enabled: true,       //disable here
                    animationEnabled: false, //disable here
                    contentFormatter: function (e) {
                        var content = " ";
                        for (var i = 0; i < e.entries.length; i++) {
                            content += "Speed: "+ "<strong>" + e.entries[i].dataPoint.y + "</strong><br>" + "Time: "+ "<strong>" + e.entries[i].dataPoint.x.toLocaleDateString()+' '+e.entries[i].dataPoint.x.toLocaleTimeString() + "</strong>";
                            content += "<br/>";
                            if($('.downtime-from').val()!=''&&$('.downtime-from').val()!=null)
                            {
                                if($('#allocation-type').val() == 'waste')
                                {
                                    var downtimeFrom = $('.downtime-from').val();
                                    var downtimeTo = e.entries[i].dataPoint.x.toLocaleDateString()+' '+e.entries[i].dataPoint.x.toLocaleTimeString();
                                    if(parseInt(new Date(downtimeTo).getTime()/1000) >= parseInt(new Date(downtimeFrom).getTime()/1000))
                                    {
                                        if(e.entries[i].dataPoint.hidden - meeter_from>=0)
                                        {
                                            let diff =  e.entries[i].dataPoint.hidden - meeter_from;
                                            content+="Meters: "+ "<strong>" + diff + "<strong>";
                                            content += "<br/>"
                                        }
                                        else {
                                            content+="<strong>Job Change <strong>";
                                            content += "<br/>"
                                        }
                                    }
                                    else
                                    {
                                        content+="<strong>Invalid Selection<strong>";
                                        content += "<br/>"
                                    }
                                }
                            }
                        }
                        return content;
                    }
                },
                axisX:{
                    title: "Time",
                    valueFormatString: "H:mm"

                },
                axisY: {
                    includeZero: false,
                    title: "speed",
                    maximum: maximumGraphSpeed,
                    minimum: 0,
                    interval: parseInt('{!! $machine->max_speed !!}')/4,
                    stripLines:[
                        {
                            value: parseInt('{!! $machine->waste_speed !!}'),
                            label: "Waste Speed"
                        }
                    ]
                },
                data: [{
                    type: "line",
                    lineThickness: 3,
                    click: onClick,
                    dataPoints: dps
                }]
            });
            $('#getRecords').click(function(){
                var date = $('#date').val();
                var shifts = $('.shiftSelection').val();
                $(document).ajaxStart(function(){
                    $('.progress').removeAttr('hidden');
                });
                $(document).ajaxComplete(function(){
                    $('.progress').attr('hidden', 'hidden');
                });
                $.ajax({
                    url: '{!! URL::to('get/historic/records') !!}',
                    method:'GET',
                    async: 'FASLE',
                    data: {
                        date: date,
                        shifts: shifts,
                        machine_id: "{!! $machine->id !!}"
                    },
                    statusCode: {
                        //getting the latest record from the database
                        200: function (response) {
                            var responsee = JSON.parse(response);
                            var records = responsee.records;
                            dps.length = 0;
                            for(var i=0; i<records.length; i++){
                                //pushing the new record in the chart
                                dps.push({
                                    x: new Date(records[i].run_date_time),
                                    y: records[i].speed,
                                    hidden:records[i].length
                                });
                            }
                            chart.render();
                        },
                        500: function (response) {

                        }
                    }
                });
            });
        };

        $('#allocateDowntime').click(function(){
            var downtimeFrom = $('.downtime-from').val();
            var downtimeTo = $('.downtime-to').val();
            var downtimeID = $('#allocation-type').val() == 'waste'? $('#selectWasteError').val():$('#selectError').val();
            var description = $('.downtimeDescription').val();
            var difference =parseInt(new Date(downtimeTo).getTime()/1000) - parseInt(new Date(downtimeFrom).getTime()/1000);
            if((difference/3600) <= 168)
            {
                $.ajax({
                    url: '{!! URL::to('allocate/downtime') !!}',
                    method:'GET',
                    async: 'FASLE',
                    data: {
                        downtimeTo: downtimeTo,
                        downtimeFrom: downtimeFrom,
                        downtimeID: downtimeID,
                        downtimeDescription: description,
                        machine_id: "{!! $machine->id !!}"
                    },
                    statusCode: {
                        200: function (response) {
                            var responsee = JSON.parse(response);
                            alert('Allocated');
                        },
                        500: function (response) {

                        }
                    }
                });
                if($('#MultiDowntime').prop('checked')){
                    $('.downtime-from').val($('.downtime-to option:selected').next().val());
                    // $('.downtime-from').val(downtimeTo);
                    $('.downtime-to').val($('.downtime-to option:last-child').val());
                    multipleDowntime();
                }
                else{
                    $('.downtime-from').val('');
                    $('.downtime-to').val('');
                    $('.downtimeDescription').val('');
                }
            }
            else
            {
                alert('Downtime cannot exceed 1 week. Please check date and time.');
            }
        })

        function singleDowntime(){
            var downtimeTo = $('.downtime-to option:last-child').val();
            $('#downtime-to').replaceWith('<input class="form-control form-control-sm downtime-to" id="downtime-to" type="text" name="downtime-to" placeholder="To" readonly>');
            $('.downtime-to').val(downtimeTo);
            if($('#allocation-type').val() == 'waste')
            {
                $('#waste-meters').show();
                $('.downtime-to').hide();
            }
        }

        function multipleDowntime(){
            var downtimeFrom = $('.downtime-from').val();
            var downtimeTo = $('.downtime-to').val();
            if($('#allocation-type').val() == 'waste')
            {
                $('#waste-meters').hide();
            }
            if(downtimeFrom != '' && downtimeTo != ''){
                if($('#MultiDowntime').prop('checked')){
                    $('#downtime-to').replaceWith('<select class="form-control form-control-sm downtime-to" id="downtime-to" name="downtime-to"></select>');
                    $.ajax({
                        url: '{!! URL::to('get/multiple/time') !!}',
                        method:'GET',
                        async: 'FASLE',
                        data: {
                            downtimeTo: downtimeTo,
                            downtimeFrom: downtimeFrom,
                            machine_id: '{!! $machine->id !!}'
                        },
                        statusCode: {
                            200: function (response) {

                                var responsee = JSON.parse(response);
                                var arr = responsee.times;
                                if($('#allocation-type').val() == 'waste'){
                                    for(var i=0; i<arr.length; i++){
                                        $('#downtime-to').append(new Option(arr[i].length,new Date(arr[i].time).toLocaleTimeString()+' '+new Date(arr[i].time).toLocaleDateString(), new Date(arr[i].time).toLocaleTimeString()+' '+new Date(arr[i].time).toLocaleDateString()))
                                    }
                                }
                                else
                                {
                                    for(var i=0; i<arr.length; i++){
                                        $('#downtime-to').append(new Option(new Date(arr[i].time).toLocaleTimeString()+' '+new Date(arr[i].time).toLocaleDateString(), new Date(arr[i].time).toLocaleTimeString()+' '+new Date(arr[i].time).toLocaleDateString()))
                                    }
                                }

                            },
                            500: function (response) {

                            }
                        }
                    });
                }
            }
            else{
                $('#SingleDowntime').click();
                alert('Please Enter Start Time and End Time');
            }
        }
    </script>
@endsection
