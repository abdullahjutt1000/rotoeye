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
                            <h2 style="background-color: red; padding: 13px; text-align: center; color: white">CURL ERROR - COMMUNICATION</h2>
                            <h3>Hello, Digitilization Coordinator</h3>
                            <hr>
                            <p>Rotoeye Cloud has tried to communicate with the circuit to perform the below mentioned operation but there was an error during this communication. This is for your information please. <br><br></p>
                            <p>Circuit and Machine details are as under:-</p><br>
                            <table class="table">
                                <thead style="background: lightgrey">
                                <tr>
                                    <th>Operation Tried</th>
                                    <th>Machine SAP Code</th>
                                    <th>Machine Name</th>
                                    <th>Circuit IP Address</th>
                                    <th>Error Description</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>{{$operationTried}}</td>
                                    <td>{{$machine->sap_code}}</td>
                                    <td>{{$machine->name}}</td>
                                    <td>{{$machine->ip}}</td>
                                    <td>{{$error}}</td>
                                </tr>
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
