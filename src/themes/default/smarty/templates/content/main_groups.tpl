[{isys_group name="tom"}]
[{isys_group name="content"}]

[{if isset($index_includes.navbar)}]
	[{isys_group name="navbar"}]
		[{include file=$index_includes.navbar}]
	[{/isys_group}]
[{/if}]
	<div id="contentWrapper">
		<div id="ajaxReturnNote" style="display:none;" class="m5"></div>

		[{isys_group name="top"}]
		[{if $index_includes.contenttop !== false}]
			[{include file=$index_includes.contenttop|default:"content/top/main.tpl"}]
		[{/if}]
		[{/isys_group}]

		[{isys_group name="bottom"}]
			[{include file=$index_includes.contentbottom|default:"content/bottom/main.tpl"}]
		[{/isys_group}]
	</div>
[{/isys_group}]
[{/isys_group}]

[{if isset($query_string)}]
	<script type="text/javascript">
        $('isys_form').action = '[{$query_string}]';
	</script>
[{/if}]

[{if isset($encType)}]
	<script type="text/javascript">
        $('isys_form').enctype = '[{$encType}]';
	</script>
[{/if}]

[{if !empty($g_error)}]
	[{if isys_tenantsettings::get('system.devmode')}]
		<script type="text/javascript">
            document.observe('dom:loaded', function () {
                idoit.Notify.message('Usage of "$g_error" detected. Please use <strong>isys_application::instance() ->container["notify"] ->error("...");</strong> instead.', {sticky: true})
            });
		</script>
	[{/if}]

	[{include file="exception.tpl"}]
[{/if}]

<script type="text/javascript">
    document.fire('contentArea:loaded');
</script>