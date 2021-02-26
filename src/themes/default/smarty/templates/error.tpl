<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

[{isys_group name="tom"}]
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">

[{strip}]
[{include file="head.tpl"}]

[{include file="content/form.tpl"}]

<div id="wrapper">

	<div id="module-dropdown" class="text-shadow text-bold" style="display:none;"></div>

	<div id="nag" style="display:none;"></div>
	<div id="overlay" style="display:none;z-index:1000;"></div>
	<div id="popup" class="popup blurred-shadow slideDown" style="display:none;z-index:1200"></div>
	<div id="popup_commentary" class="popup blurred-shadow" style="display:none;z-index:1100;"></div>

	[{if is_object($session) && $session->is_logged_in()}]
		<div id="top">

			[{include file="top/searchBar.tpl"}]
			[{include file="top/mainMenu.tpl"}]

			<div class="cb"></div>
		</div>
		<div id="content">

			<div id="mydoitArea" style="display:none;"></div>
			<div id="menuTreeOn" class="fl" style="width:[{$menu_width}]px;">
				[{include file=$index_includes.leftcontent|default:"content/leftContent.tpl"}]
			</div>

			<div id="draggableBar" class="draggableBar"></div>

			<div id="contentArea" style="left:[{$menu_width}]px;">
				<ul id="breadcrumb" class="noprint">
					[{isys type="breadcrumb_navi" name="breadcrumb" p_home=1 p_prepend="<li>" p_append="</li>"}]

					[{if $trialInfo}]
						<li class="bold red">
							[{$trialInfo.message}]
						</li>
					[{/if}]
				</ul>
				<div id="main_content">

					[{if isset($index_includes.navbar)}]
						[{isys_group name="navbar"}]
							[{include file=$index_includes.navbar}]
						[{/isys_group}]
					[{/if}]

					<h3 class="m10">There was an internal error:</h3>

					<p class="m10 p10 box-red" style="border-width:2px;">
						[{$message}]

						<div id="backtrace" style="display:none;padding:20px;">
							<pre>[{$backtrace|replace:"Backtrace:":"<h2 style='color:#c00;'>Backtrace</h2>"}]</pre>
						</div>
					</p>

				</div>
			</div>
		</div>

	[{strip}]
		<script type="text/javascript">
			[{include file="main-inline.js"}]

            /**
             * Load additional inline scripts
             */
            Event.observe(window, 'load', function () {
                /*This "inline" JS can come from anywhere (categories, modules, API, ...)*/
                [{if is_array($additionalInlineJS)}]
                [{$additionalInlineJS|implode}]
                [{elseif is_string($additionalInlineJS)}]
                [{$additionalInlineJS}]
                [{/if}]
            });
		</script>
	[{/strip}]

	[{else}]
		[{include file="login.tpl"}]
	[{/if}]

</div>
</form>

[{if !empty($g_error)}]
	[{if isys_tenantsettings::get('system.devmode')}]
	<script type="text/javascript">
		document.observe('dom:loaded', function() {
			idoit.Notify.message('Usage of "$g_error" detected. Please use <strong>isys_application::instance() ->container["notify"] ->error("...");</strong> instead.', {sticky: true})
		});
	</script>
	[{/if}]

	[{include file="exception.tpl"}]
[{/if}]

</body>
</html>
[{/strip}]
[{/isys_group}]
