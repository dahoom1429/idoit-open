<!DOCTYPE html>
<html>
<head>
	<title>i-doit Administration</title>
	<meta charset='utf-8'>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" type="text/css" media="screen" href="style.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="responsive.css" />
	<script type="text/javascript" src="../src/tools/js/prototype/prototype.js"></script>
  	<script type="text/javascript" src="../src/tools/js/scriptaculous/src/scriptaculous.js?load=effects"></script>
</head>
<body>

<div id="wrapper">
	<div id="header">
		[{if $smarty.session.logged_in}]
		<ul class="navigation">
			<li>
				<a href="./">Home</a>
			</li>
			<!--
			<li>
				<a href="?req=update">Update</a>
				<span>Trigger an i-doit update</span>
			</li>
			-->
			[{if $smarty.const.C__ENABLE__LICENCE}]
			<li>
				<a href="?req=mandator">Tenants</a>
				<span>Delete and add tenants</span>
			</li>
			<li>
				<a href="?req=licences">Licenses</a>
				<span>Install / Remove licences</span>
			</li>
			[{/if}]
			<li>
				<a href="?req=modules">Add-ons</a>
				<span>Install and configure add-ons</span>
			</li>
			[{if $smarty.const.C__ENABLE__LICENCE}]
			<li>
				<a href="?req=portal">Support</a>
				<span>Your licence and i-doit packages <br />are located here</span>
			</li>
			[{/if}]
			<li>
				<a href="?req=config">Config</a>
				<span>Configure System Parameters</span>
			</li>
			<li style="float:right;">
				<a href="../">i-doit</a>
				<span>Login to i-doit</span>
			</li>
		</ul>

		<br class="cb" />
		[{/if}]
	</div>
	<div id="content">
		[{if isset($system_error) && $system_error}]<div class="error p10 mt10 mb10">[{$system_error}]</div>[{/if}]

		[{if $smarty.session.username}]<div class="fr m10">Logged in as <strong>[{$smarty.session.username}]</strong> - <a href="?logout">Logout</a></div>[{/if}]
		[{include file=$request|default:"pages/main.tpl"}]
	</div>
	<div id="topfooter">
		<img class="fr" src="../images/logo.png" width="60" />
		<p>This is the administrative interface for your i-doit installation. It allows you to configure your tenants[{if $smarty.const.C__MODULE__PRO}] and provides an administration of your licences[{/if}].</p>
	</div>
	<div id="footer">
		<span>&copy; synetics gmbh [{$smarty.now|date_format:"%Y"}]. | <a href="http://www.i-doit.com">Visit www.i-doit.com</a> | <a href="#">Back to top</a></span>
	</div>
</div>

</body>
</html>