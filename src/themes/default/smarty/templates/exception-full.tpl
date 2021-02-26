<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>i-doit - Exception</title>

    <meta name="author" content="synetics gmbh" />
    <meta name="description" content="i-doit" />
    <meta name="keywords" content="i-doit, CMDB, ITSM, ITIL, NMS, Netzwerk, Dokumentation, Documentation" />
    <meta http-equiv="content-type" content="text/html; charset=[{$html_encoding|default:"utf-8"}];" />
    <meta http-equiv="content-language" content="de" />
    <meta name="robots" content="noindex" />

	<meta http-equiv="pragma" content="no-cache" />

	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />

	<link rel="stylesheet" type="text/css" media="print" href="./src/themes/default/css/print.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="?load=css&theme=default" />
	<link rel="stylesheet" type="text/css" media="screen" href="?load=mod-css&theme=default" />
</head>
<body>
	<div class="p10">
		<div style="border:1px solid #ccc;background:#eee;overflow:auto;">

			<h3 class="m10">Exception occurred:</h3>
			<p class="m10 p10 box-red" style="border-width:2px;">
				[{$message}]

				<div id="backtrace" style="display:none;padding:20px;">
					<pre>[{$backtrace|replace:"Backtrace:":"<h2 style='color:#c00;'>Backtrace</h2>"}]</pre>
				</div>
			</p>
		</div>

		<div class="fr" style="margin-top:5px;">
			<img src="images/logo.png" width="80"/>
		</div>

		<div class="m10">
			<a href="javascript:history.go(-1)">Go back</a> |
			<a href="index.php">Go to mainpage</a> |
			<a href="javascript:" onclick="$('backtrace').show();">Show backtrace</a> |
		</div>
	</div>
</body>
</html>