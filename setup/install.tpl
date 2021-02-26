<?xml version="1.0"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>i-doit Installation</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<meta http-equiv="content-language" content="de"/>
	<link rel="stylesheet" type="text/css" media="print" href="./src/themes/default/css/list.css"/>

	<style type="text/css">
	body {
	    font-family: 'Tahoma', sans-serif;
	    font-size: 12px;
	}

	#mainContainer {
		width:100%;
	}

	#mainTable {
		margin:50px auto;
	    border: 1px #000000 solid;
		width:818px;
	}

	#mainMenu {
	    vertical-align: top;
	    width: 190px;
	    border-top: 1px solid #fff;
	    border-bottom: 1px solid #333;
	    background-color: #171717;
	}

	div.mainMenuButton {
	    vertical-align: middle;
	    width: 190px;
	    height: 22px;
	    padding-top: 6px;
	    background-color: #444;
	    border-bottom: 1px solid #ccc;
	    cursor: pointer;
	    color: #eee;
	}

	div.mainMenuButton:hover {
	    background-color: #fff;
	    color: #000;
	}

	div.mainMenuButton img {
	    padding-left: 3px;
	    padding-right: 3px;
	    vertical-align: middle;
	}

	div.mainMenuButtonActive {
	    background-color: #eee !important;
	    color: #aa2222;

	}

	#mainContent {
	    background-color: #eee;
	    padding: 5px;
	    vertical-align: top;
	    border-bottom: 1px solid #ccc;
	    border-top: 1px solid #fff;
	}

	#mainContentInner {
	    height: 500px;
	    overflow: auto;
	}

	#mainDialog {
	    text-align: right;
	    padding: 15px;
	}

	#mainCopyright {
	    text-align: center;
	    font-size: 9px;
	    background-color: #000;
	    color: #fff;
	}

	#mainBanner {
	    vertical-align: top;
	    height: 72px;
		background:black;
		padding-left:12px;
	    border-bottom: 1px #000 solid;
	}

	h2 {
	    border-bottom: 1px #888 solid;
	    margin: 0px;
	    font-size: 14px;
	}

	td.stepHeadline {
	    padding-top: 6px;
	    font-weight: bold;
	}

	td.stepLineData {
	    max-width: 500px;
        overflow:hidden;
        word-break: break-all;
	}

	td.stepLineStatusGood {
	    width: 150px;
	    text-align: right;
	    padding-right: 10px;
	    font-weight: bold;
	    color: #00CC00;
	}

	td.stepLineStatusBad {
	    width: 100px;
	    text-align: right;
	    padding-right: 10px;
	    font-weight: bold;
	    color: #CC0000;
	}

	td.stepLineStatusBoth {
	    width: 100px;
	    text-align: right;
	    padding-right: 10px;
	    font-weight: bold;
	    color: #FF9900;
	}

	td.stepLineSeperator {
	    border-top: 1px dotted #aaa;
	}

	input.mainButton {
	    width: 100px;
	    background-color: #eee;
	    color: #000000;
	    border: 1px solid #888888;
	    height: 20px;
	}

	input.mainButton:hover,
	input.mainButton:focus {
	    background-color: #bbb;
	    border: 1px solid #444;
	}

	input.mainButton[disabled] {
	    width: 100px;
	    background-color: #ccc;
	    color: #666666;
	    border: 1px solid #888888;
	    padding: 0px;
	    margin: 0px;
	    height: 20px;
	}

	td.stepConfTitle {
	    width: 150px;
	}

	.stepConfDescription {

	}

	td.stepConfContent {
	    text-align: right;
	    vertical-align: middle;
	}

	select.confInputDir {
	    width: 302px;
	    border: 1px #777 solid;
	}

	input.confInputDir {
	    width: 400px;
	    border: 1px #777 solid;
	}

	input.confInputDir:hover,
	select.confInputDir:hover,
	input.confInputDir:focus,
	select.confInputDir:focus {
	    border: 1px #000000 solid;
	    background-color: #eee;
	    color: #aa2222;
	}

	input.confInputDir[readonly] {
	    width: 300px;
	    border: 1px #000000 solid;
	    background-color: #eee;
	}

	table.installingTable {
	    left: 50%;
	    top: 50%;
	    position: absolute;
	    margin-left: -50px;
	    margin-top: 20px;
	    border-top: 1px dotted #888888;
	    border-bottom: 1px dotted #888888;
	}

	table.stepConfTable {
	    border: 1px solid grey;
	    margin-bottom: 10px;
	    padding-top: 5px;
	    padding-bottom: 5px;
	}

	td.stepConfBorder {
	    border: 1px solid grey;
	    padding-top: 5px;
	    padding-bottom: 5px;
	}

	[OS_VISIBILITY]

	</style>
	<script language="JavaScript" type="text/javascript">
	    <!--
	    function CheckDatabaseName(name, message) {
	        var x = document.getElementById(name);
	        var strVal = x.value;

	        if (strVal.search(/\W+/) > 0) {
	            if (!message) {
	                alert('Be aware that both database names only allow the chars 0-9, a-Z and _. Please correct your value.');
	            } else {
	                alert(message);
	            }
	            x.focus();
	            x.value = x.value;
	        }
	    }
	    //-->
	</script>
</head>
<body>
<form name="install_form" action="[FORM_ACTION]" enctype="multipart/form-data" method="POST">
    [MAIN_CONFIG_PARAMETERS]
    <div id="mainContainer">
        <table id="mainTable" cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="2" id="mainBanner">
                    <img src="setup/images/main_banner.jpg" alt="Banner" height="72"/>
                </td>
            </tr>
            <tr>
                <td id="mainMenu" nowrap="nowrap">
                    <div class="mainMenuButton" id="mainStep1" [MAIN_ACTION_STEP1]>
                       <img src="images/icons/silk/computer.png" alt="Step 1 - System Check"/>1. System Check
                    </div>
			        <div class="mainMenuButton" id="mainStep2" [MAIN_ACTION_STEP2]>
				        <img src="images/icons/silk/folder.png" alt="Step 2 - Directory Configuration"/>2. Directory Configuration
				    </div>
				    <div class="mainMenuButton" id="mainStep3" [MAIN_ACTION_STEP3]>
				        <img src="images/icons/silk/database_gear.png" alt="Step 3 - Database Configuration"/>3. Database Configuration
				    </div>
				    <div class="mainMenuButton" id="mainStep4" [MAIN_ACTION_STEP4]>
				        <img src="images/icons/silk/cog.png" alt="Step 4 - Framework Configuration"/>4. Framework Configuration
				    </div>
				    <div class="mainMenuButton" id="mainStep5" [MAIN_ACTION_STEP5]>
				        <img src="images/icons/silk/tick.png" alt="Step 5 - Config Check"/>5. Config Check
				    </div>
				    <div class="mainMenuButton" id="mainStep6" [MAIN_ACTION_STEP6]>
				        <img src="images/icons/silk/drive_disk.png" alt="Step 6 - Installation"/>6. Installation
				    </div>
                </td>
			    <td id="mainContent">
			        <div id="mainContentInner">
			            [MAIN_CONTENT]
			        </div>
			    </td>
		    </tr>
		    <tr>
		        <td id="mainCopyright">
		            copyright<br/>
		            sy<span style="color:#aa2222">net</span>ics gmbh
		        </td>
		        <td id="mainDialog" nowrap="nowrap">
		            <input type="hidden" name="install_step" value="[MAIN_CURRENT_STEP]"/>
		            <input type="hidden" name="install_now" value="0"/>
		            <input type="button" class="mainButton" name="main_mi" onClick="window.location.href='README.md';" value="Manual install"/>
		            <input type="button" class="mainButton" name="main_refresh" onClick="history.go(0);" value="Try again"/>
		            <input type="button" class="mainButton" name="main_prev" [MAIN_PREV_DISABLED] value="&lt;&lt; Previous" onClick="this.form.install_step.value=[MAIN_PREV_STEP]; this.form.submit();" />
		            <input type="button" class="mainButton" name="main_next" id="main_next" [MAIN_NEXT_DISABLED] value="Next &gt;&gt;" onClick="if ((typeof form_onsubmit != 'function') || (form_onsubmit ())) {this.form.install_step.value=[MAIN_NEXT_STEP]; this.form.submit();}" />
		        </td>
		    </tr>
		</table>
    </div>
</form>

<script type="text/javascript">
    // Activate button for current step
    var e = document.getElementById('mainStep[MAIN_CURRENT_STEP]');
    if (e) e.className += ' mainMenuButtonActive';
</script>
</body>
</html>