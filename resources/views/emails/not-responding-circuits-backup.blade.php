<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Project Manager</title>
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/email/email.css')}}" />
    <style>
        hr {
            display: block;
            height: 1px;
            border: 0;
            border-top: 1px solid #ccc;
            margin: 1em 0;
            padding: 0;
        }
        .table{
            width: 100%;
            max-width: 100%;
            border-spacing: 0;
            border-collapse: collapse;
            box-sizing: inherit;
            font-size: 15px;
            line-height: 1.5;
        }
        .table>thead>tr>th{
            vertical-align: bottom;
            border-bottom: 2px solid #ddd;
            padding: 8px;
            line-height: 1.42857143;
        }
        th{
            text-align: left;
        }
        .table>tbody>tr>td{
            padding: 8px;
            line-height: 1.42857143;
            vertical-align: top;
            border-top: 1px solid #ddd;
        }
    </style>
</head>

<body bgcolor="#FFFFFF">
<table class="body-wrap">
    <tr>
        <td></td>
        <td class="container" bgcolor="#FFFFFF">
            <div class="content">
                <table>
                    <tr>
                        <td>
                            <h2 style="background-color: red; padding: 13px; text-align: center; color: white">Not Responding Circuits</h2>
                            <h3>Hello, Digitilization Coordinator</h3>
                            <hr>
                            <p>The below mentioned cloud circuits are not responding. Please attend the issue. <br><br></p>
                            <p>Circuit details are as under:-</p><br>
                            <table class="table">
                                <thead style="background: lightgrey">
                                <tr>
                                    <th>Machine #</th>
                                    <th>Machine Name</th>
                                    <th>IP Address</th>
                                    <th>Last Run Date & Time</th>
                                    <th>Not Responding Age</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($machines as $machine)
                                    <tr>
                                        <td>{{$machine['machine_id']}}</td>
                                        <td>{{$machine['machine_name']}}</td>
                                        <td>{{$machine['ip_address']}}</td>
                                        <td>{{date('d M, Y H:i:s', strtotime($machine['last_run_date_time']))}}</td>
                                        <td>{{$machine['last_received']}}</td>
                                        @if( isset($machine['curl_error']) || !is_null($machine['curl_error']) )
                                            <td>{{$machine['curl_error']}}</td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <br>
                            <br>
                            <small>This is a system generated email. Please do not reply.</small>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
        <td></td>
    </tr>
</table>

</body>
</html>
