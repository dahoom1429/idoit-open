<head>
    [{if $title}]
        <title>i-doit - [{$title}]</title>
    [{else}]
        <title>i-doit - [{isys type="breadcrumb_navi" name="breadcrumb" p_plain=true p_append=" > "}]</title>
    [{/if}]

    <!--
    This can not be used at the time, because all links (with href="#") will request the dashboard.
    <base href="[{isys_application::instance()->www_path}]">
    -->

    <meta name="author" content="synetics gmbh" />
    <meta name="description" content="i-doit" />
    <meta name="keywords" content="i-doit, CMDB, ITSM, ITIL, NMS, Netzwerk, Dokumentation, Documentation" />
    <meta http-equiv="content-type" content="text/html; charset=[{$html_encoding|default:"utf-8"}];" />
    <meta name="robots" content="noindex" />

    <link rel="icon" type="image/png" href="[{$dir_images}]favicon.png">

    <!-- This meta tag will force the internet explorer to disable the "compability" mode -->
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="[{$dir_images}]favicon.ico" />
    <![endif]-->

    <link rel="stylesheet" type="text/css" media="print" href="[{$dir_theme}]css/print.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="?load=css&theme=default&token=[{isys_tenantsettings::get('system.last-change',time())}]" />
    <link rel="stylesheet" type="text/css" media="screen" href="?load=mod-css&theme=default&token=[{isys_tenantsettings::get('system.last-change',time())}]" />

    <script type="text/javascript">
        var C__CMDB__GET__OBJECT      = "[{$object}]",
            C__CMDB__GET__TREEMODE    = "[{$treemode}]",
            C__CMDB__GET__VIEWMODE    = "[{$viewmode}]",
            C__CMDB__GET__OBJECTTYPE  = "[{$objtype}]",
            C__CMDB__GET__OBJECTGROUP = "[{$objgroup}]",
            C__GET__NAVMODE           = "[{$smarty.const.C__GET__NAVMODE}]",
            C__NAVMODE__JS_ACTION     = "[{$smarty.const.C__NAVMODE__JS_ACTION}]",
            C__AUTHORIZED             = "[{isys_application::instance()->container->get('session')->is_logged_in()}]",
            C__TENANT_ID              = "[{isys_application::instance()->container->get('session')->get_mandator_id()}]";

        window.dir_images = '[{$dir_images}]';
        window.www_dir = '[{isys_application::instance()->www_path}]';

        [{$errorTrackerCode}]
    </script>

	<script type="text/javascript" src="[{$dir_tools}]js/prototype/prototype.js"></script>
	<script type="text/javascript" src="[{$dir_tools}]js/scriptaculous/src/scriptaculous.js?load=effects,dragdrop,controls"></script>
	<script type="text/javascript" src="[{$dir_tools}]js/taborder/taborder.js"></script>
	<script type="text/javascript" src="[{$dir_tools}]js/ckeditor/ckeditor.js"></script>

    <script type="text/javascript" src="[{$dir_tools}]js/compressed/scripts.js?token=[{isys_tenantsettings::get('system.last-change',time())}]"></script>

    <!--suppress JSFileReferences -->
    <script type="text/javascript">
        /* <![CDATA[ */
        globalize(C__CMDB__GET__TREEMODE, '[{$smarty.get.$treemode|strip_tags|escape}]');
        globalize(C__CMDB__GET__OBJECT, '[{$smarty.get.$object|strip_tags|escape}]');
        globalize(C__CMDB__GET__OBJECTTYPE, '[{$smarty.get.$objtype|strip_tags|escape}]');

        [{include file="lang.js"}]

        Event.observe(window, 'load', function () {
            onload_process();
        });

        idoit.Require.config({
            lastChange: '[{isys_tenantsettings::get('system.last-change',time())}]',
            paths: {
                chassis:                 "[{$dir_tools}]js/chassis/chassis.js",
                d3:                      "[{$dir_tools}]js/d3/d3-v5.16.0-min.js",
                d3cola:                  "[{$dir_tools}]js/d3/cola-v3.3.8-min.js",
                d3CmdbExplorer:          "[{$dir_tools}]js/d3/visualization/cmdb-explorer.js",
                d3VisConnection:         "[{$dir_tools}]js/d3/visualization/connections.js",
                d3ChartPie:              "[{$dir_tools}]js/d3/charts/pie.js",
                d3ChartBar:              "[{$dir_tools}]js/d3/charts/bar.js",
                d3ChartStacked:          "[{$dir_tools}]js/d3/charts/stacked.js",
                svgToPng:                "[{$dir_tools}]js/svgToPng/saveSvgAsPng.js",
                jit:                     "[{$dir_tools}]js/jit/jit.js",
                rack:                    "[{$dir_tools}]js/rack/rack.js",
                rackAssignment:          "[{$dir_tools}]js/rack/rackAssignment.js",
                qcw:                     "[{$dir_tools}]js/qcw/quick_configuration_wizard.js",
                taborder:                "[{$dir_tools}]js/taborder/taborder.js",
                ckeditor:                "[{$dir_tools}]js/ckeditor/ckeditor.js",
                authConfiguration:       "[{$dir_tools}]js/auth/configuration.js",
                simpleAuthConfiguration: "[{$dir_tools}]js/auth/simple_configuration.js",
                fileUploader:            "[{$dir_tools}]js/ajax_upload/fileuploader.js",
                fuse:                    "[{$dir_tools}]js/fuse/fuse.min.js",
                treeBase:                "[{$dir_tools}]js/tree/base.js",
                treeLocation:            "[{$dir_tools}]js/tree/location.js",
                reactBridge:             "[{$dir_tools}]js/react/bridge.min.js",
                smartyNatcompare:        "[{$dir_tools}]js/smarty/natcompare.js",
                smartyProgress:          "[{$dir_tools}]js/smarty/progress.js",
                smartyRaid:              "[{$dir_tools}]js/smarty/raid.js",
                smartyTime:              "[{$dir_tools}]js/smarty/time.js",
                smartyVWA:               "[{$dir_tools}]js/smarty/vwa.js",
                smartyFilesUpload:       "[{$dir_tools}]js/smarty/filesUpload.js",
                validation:              "[{$dir_tools}]js/validation/validation.js"
            }
        });
        /* ]]> */
    </script>

    [{foreach from=$jsFiles item="jsFile"}]
        <script type="text/javascript" src="[{$jsFile}]"></script>
    [{/foreach}]
</head>

<body id="body">
