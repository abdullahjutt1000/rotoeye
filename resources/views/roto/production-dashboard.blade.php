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
    <link rel="stylesheet" href="{{asset('assets/global/vendor/slidepanel/slidePanel.css')}}">

    <link rel="stylesheet" href="{{asset('assets/global/vendor/owl-carousel/owl.carousel.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/slick-carousel/slick.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/uikit/carousel.css')}}">

    <link rel="stylesheet" href="{{asset('assets/global/vendor/nprogress/nprogress.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/widgets/chart.css')}}">

    <style>
        .slick-prev:before {
            color: #000 !important;
            background-color: #9c9c9c;
            padding: 5px;
            border-radius: 30px;
        }
        .slick-next:before {
            color: #000 !important;
            background-color: #9C9C9C;
            padding: 5px;
            border-radius: 30px;
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
            <div class="example" data-plugin="matchHeight" data-by-row="true">
                <div class="slider" id="exampleMultipleItems">
                    @foreach($user->allowedMachines as $userMachine)
                        <div class="meterContainer animation-scale-up" id="Machine{{$userMachine->id}}" data-machine="{{\Illuminate\Support\Facades\Crypt::encrypt($userMachine->id)}}" data-ip="{{$userMachine->ip}}">
                            <div class="card-group">
                                <div class="card card-block p-0">
                                    <div class="col-12 red-roto">
                                        <div class="counter counter-md text-left">
                                            <div class="counter-number-group">
                                                <form method="POST" action="{{URL::to('select/machine')}}">

                                                    <input value="{{\Illuminate\Support\Facades\Crypt::encrypt($userMachine->id)}}" name="machine" hidden>
                                                    <button class=" btn counter-number bg-transparent white" style="margin: 0px; padding: 0px" type="submit"><strong>{{$userMachine->name.' - '.$userMachine->sap_code}}</strong></button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 bg-dark ">
                                        <div class="counter counter-md text-left">
                                            <div class="counter-number-group">
                                                <span class="counter-number white font-size-14" id="operator">Loading ...</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="vertical-align text-center white p-10" style="background-color: #fcfcfc; height: 280px" id="meter">
                                        <div class="counter-number-group vertical-align-middle">
                                            <div class="gauge" id="Gauge" data-plugin="gauge" data-value="870" data-max-value="{{$userMachine->max_speed}}" data-stroke-color="#e1e1e1" style="bottom: 10px;">
                                                <div class="gauge-label"></div>
                                                <div style="position: absolute;width: 100%;bottom: 1%; color: #000; font-size: 12px">{{$userMachine->qty_uom.'/'.$userMachine->time_uom}}</div>
                                                <canvas width="200" height="150"></canvas>
                                            </div>
                                            <div style="color: #336699; position: relative;bottom: 18px;"><strong id="meters"></strong> {{$userMachine->qty_uom}}</div>
                                            <div style="position: relative;bottom: 245px; left:175px" ><span class="badge badge-danger" id="hardwareStatus">Loading ...</span></div>
                                            <div style="position: relative;bottom: 282px; right:160px" >
                                                <span class="badge" style="color: #336699">Last Updated At</span><br>
                                                <span class="badge" style="background-color: #336699" id="lastUpdated"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 bg-light">
                                        <div class="counter counter-md text-left">
                                            <div class="counter-number-group text-center">
                                                <span class="dark font-size-10" id="statusCode"></span>
                                                <span class="counter-number dark font-size-20 font-weight-bold" id="statusTime">Loading ...</span>
                                                <span class="dark font-size-10" id="statusDate"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 bg-warning ">
                                        <div class="counter counter-md text-left">
                                            <div class="counter-number-group">
                                                <span class="counter-number dark font-size-14" id="product">Loading ...</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 white" style="background-color: #336699">
                                        <div class="counter counter-md text-left">
                                            <div class="counter-number-group">
                                                <span class="counter-number white font-size-14" id="substrate">Loading ...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="bg-light text-center">
                    <p class="p-5 font-size-10"><strong>Please click on Machine Name to view complete Dashboard</strong></p>
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
    <script src="{{asset('assets/global/vendor/select2/select2.full.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootstrap-select/bootstrap-select.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/select2.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/bootstrap-select.js')}}"></script>
    <script src="{{asset('assets/global/vendor/asprogress/jquery-asProgress.js')}}"></script>
    <script src="{{asset('assets/global/vendor/jquery-appear/jquery.appear.js')}}"></script>
    <script src="{{asset('assets/global/vendor/nprogress/nprogress.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/jquery-appear.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/nprogress.js')}}"></script>

    <script src="{{asset('assets/global/vendor/screenfull/screenfull.js')}}"></script>
    <script src="{{asset('assets/global/vendor/slidepanel/jquery-slidePanel.js')}}"></script>
    <script src="{{asset('assets/global/vendor/owl-carousel/owl.carousel.js')}}"></script>
    <script src="{{asset('assets/global/vendor/slick-carousel/slick.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/asscrollable.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/slidepanel.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/owl-carousel.js')}}"></script>
    <script src="{{asset('assets/remark/examples/js/uikit/carousel.js')}}"></script>
    <script>
        var machines = [];
        $('.meterContainer').each(function(i, obj) {
            var machine = $(this).data(machine);
            machines.push(machine.machine);
        });
        function live(){
            var machine = $(this).data(machine);
            var ip = $(this).data('ip');

            var oldDate = "";
            var result = new Array;

            $.ajax({
                url: '{!! URL::to('get/record') !!}',
                method:'POST',
                async: 'FASLE',
                data: {
                    machines_arr: machines
                },
                statusCode: {
                    //getting the latest record from the database
                    200: function (response) {
                        var responsee = JSON.parse(response);
                       console.log("response",response);
                        for(var i=0; i<responsee.length; i++){
                            var machine = $('#Machine'+responsee[i].machineID);
                            var dynamicGauge = $(machine).find("#Gauge").data('gauge');
                            var meters = machine.find("#meters");
                            var hardwareStatus = machine.find('#hardwareStatus');
                            var lastUpdated = machine.find('#lastUpdated');
                            var operator = machine.find('#operator');
                            var product = machine.find('#product');
                            var substrate = machine.find('#substrate');
                            var statusCode = machine.find('#statusCode');
                            var statusDate = machine.find('#statusDate');
                            var statusTime = machine.find('#statusTime');
                            var gauge = machine.find(".gauge-label");
                            var maxSpeed = machine.find('.gauge').data('max-value');

                            oldDate = responsee[i].record.run_date_time;
                            oldSpeed = responsee[i].record.speed;
                            //setting the speed to the speed gauge
                            var options = {

                            };
                            //setting the speed value in the gauge
                            if (responsee[i].record.speed <= maxSpeed){
                                //conditional setting of gauge if old record is equal to new record
                                if(responsee[i].record.speed == 0){
                                    dynamicGauge.setOptions(options).set(1);
                                }
                                else if (oldSpeed-responsee[i].record.speed == 0){
                                    dynamicGauge.setOptions(options).set(responsee[i].record.speed-1);
                                }
                                dynamicGauge.setOptions(options).set(responsee[i].record.speed);
                            }
                            else{
                                gauge.text(responsee[i].record.speed);
                                gauge.css('color', '#ed1b23 ');
                            }

                            meters.text(parseInt(responsee[i].record.length).toLocaleString());
                            if(responsee[i].status == 'Live'){
                                hardwareStatus.html('Live');
                                hardwareStatus.removeClass('bg-danger');
                                hardwareStatus.addClass('bg-success');
                            }
                            if(responsee[i].status == 'Not Live'){
                                hardwareStatus.html('Not Live');
                                hardwareStatus.removeClass('bg-success');
                                hardwareStatus.addClass('bg-danger');
                            }
                            lastUpdated.html(responsee[i].lastUpdatedDate+'<br>'+responsee[i].lastUpdatedTime);
                            operator.html('<small>Operator: </small><strong>'+responsee[i].record.user.name+' ('+responsee[i].record.user.id+')</strong>');
                            product.html('<small>Product: </small><strong>'+responsee[i].record.job.product.name.toUpperCase()+'</strong>');
                            substrate.html('<small>Substrate: </small><strong>'+responsee[i].substrate+' ('+responsee[i].record.process.process_name+')'+'</strong>');
                            statusCode.html(responsee[i].statusCode+' Since');
                            statusTime.html(responsee[i].statusTime);
                            statusDate.html(responsee[i].statusDate);
                        }
                    },
                    500: function (response) {
                        gauge.setAttribute('data-max-value', responsee.record.speed);
                        dynamicGauge.setOptions(options).set(responsee.record.speed);
                    }
                }
            }).then(function (){ setTimeout(function (){},30000); live(); });
        }

        $('document').ready(function(){
                live();
        });
    </script>
    <script src="{{asset('assets/remark/custom/canvas.js')}}"></script>
@endsection
