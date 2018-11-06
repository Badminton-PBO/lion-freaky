<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <title>@yield('subject')</title>
</head>
<body background="#fafafa">
<style type="text/css">
    <!--
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        background-color: #fafafa;
        margin: 20px;
    }
    body,td,th {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        line-height: 18px;
    }
    a {
        color: #12c;
        text-decoration: none;
    }
    a:hover {
        color: #81d644;
        text-decoration: underline;
    }
    h2.h2title {
        font: bold 14px Arial, Helvetica, sans-serif;
        color: #81d644;
        margin: 6px 0 6px 0;
    }
    p { margin: 6px 0 6px 0; }
    table { border-collapse: collapse; }
    td { padding: 2px; }
    hr {
        height: 1px;
        border: none;
        border-top: 1px dashed #d9d9d9;
    }
    .content {
        font: normal 12px Arial, Helvetica, sans-serif;
        line-height: 20px;
        padding: 10px 10px 20px 10px;
    }
    .logoheader {
        background-color:#FFFFFF;
        border-bottom:20px solid #81d644;
    }
    -->
</style>
<center>
    <div style="font: 16px normal Arial, Helvetica, sans-serif;">

    </div>
    <table class="tblContainer" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" style="font: 12px normal Arial, Helvetica, sans-serif; border:1px solid #ccc;">
        <tr>
            <td align="left" class="logoheader" style="background-color:#FFFFFF;border-bottom:20px solid #81d644;"><img src="<?php echo $message->embed(base_path('resources/views/emails/logo.png')); ?>" alt="Logo" width="358" height="95"></td>

        </tr>
        <tr>
            <td align="left" class="content" style="font:normal 12px Arial,Helvetica,sans-serif;line-height:20px;padding:10px 10px 20px 10px;">
                <h2 class="h2title">@yield('subject')</h2>

                @yield('content')

                <br />
                <br />PBO Sportcommissie
                <br /><a href="http://www.badminton-pbo.be/">http://www.badminton-pbo.be</a>
                <br />
            </td>
        </tr>
    </table>
</center>
</body>
</html>
