@extends('layouts.login-layout')
@section('header')
    <link rel="stylesheet" href="{{asset('assets/global/fonts/material-design/material-design.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/fonts/brand-icons/brand-icons.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/jquery-wizard/jquery-wizard.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/formvalidation/formValidation.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/vendor/select2/select2.css')}}">
    <link rel="stylesheet" href="{{asset('assets/remark/examples/css/forms/advanced.css')}}">
@endsection
@section('body')
    <div class="page-content vertical-align-middle">
        @if (count($errors) > 0)
            <div class="alert alert-danger bg-danger">
                <p>Please fix the following issues to continue</p>
                <ul class="error">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if(Session::has("error"))
            <div class="alert alert-error bg-danger">
                {!! Session::get("error") !!}
            </div>
        @endif
        @if(Session::has("success"))
            <div class="alert bg-green bg-success">
                {!! Session::get("success")  !!}
            </div>
        @endif
        <div class="panel">
            <div class="panel-body">
                <div class="brand">
                    <img class="brand-img" src="{{asset('assets/icons/Logo_RotoEye_Version_160 cross 160_PNG_1.0.png')}}" alt="..." style="width: 40%;height: auto;">
                </div>
                <form action="{{URL::to('store/expired/password')}}" method="post">
                    <p>Hello <strong>{{$user->name}}</strong>, Your account password has been expired. Please change.</p><hr>
                    <div class="row">
                        <div class="form-group form-material col-md-12">
                            <input type="password" class="form-control" id="NewPassword" name="new_password" value="{{old('new_password')}}" placeholder="New Password" autocomplete="off" required/>
                        </div>
                        <div class="form-group form-material col-md-12">
                            <input type="password" class="form-control" id="confirmPassword" name="new_password_confirmation" value="{{old('new_password_confirmation')}}" placeholder="Confirm New Password" autocomplete="off" required/>
                        </div>
                        <small class="bg-danger">Your password must be more than <strong>8 characters long</strong>, should contain at-least <strong>1 Uppercase</strong>, <strong>1 Lowercase</strong>, <strong>1 Numeric</strong> and <strong>1 special character</strong>.</small>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-40">Submit</button>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('footer')
    <script src="{{asset('assets/remark/examples/js/charts/gauges.js')}}"></script>
    <script src="{{asset('assets/global/vendor/formvalidation/formValidation.js')}}"></script>
    <script src="{{asset('assets/global/vendor/formvalidation/framework/bootstrap.js')}}"></script>
    <script src="{{asset('assets/global/vendor/matchheight/jquery.matchHeight-min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/jquery-wizard/jquery-wizard.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/jquery-wizard.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/matchheight.js')}}"></script>
    <script src="{{asset('assets/remark/examples/js/forms/wizard.js')}}"></script>
    <script src="{{asset('assets/global/vendor/select2/select2.full.min.js')}}"></script>
    <script src="{{asset('assets/global/vendor/bootstrap-select/bootstrap-select.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/select2.js')}}"></script>
    <script src="{{asset('assets/global/js/Plugin/bootstrap-select.js')}}"></script>
@endsection
