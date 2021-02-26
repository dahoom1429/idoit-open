<script type="text/javascript">

function save_config() {
	$('ajax_return').hide();
	aj_submit(String($('isys_form').readAttribute('action'))+"&template=", 'post', 'ajax_return', 'isys_form');

	new Effect.Appear('ajax_return', {duration:0.4});
}

new jscolor.color(document.getElementById('color_value'));
</script>

<h3 class="p5 gradient border-top border-bottom">Template [{isys type="lang" ident="LC__CONFIGURATION"}]</h3>

<div id="ajax_return" class="m5 p5" style="display:none;"></div>
[{assign var="template_status" value="0"}]
[{assign var="template_assignments" value="0"}]
[{assign var="template_colors" value="0"}]
[{if $C__TEMPLATE__STATUS}]
[{assign var="template_status" value="1"}]
[{/if}]

[{if $C__TEMPLATE__SHOW_ASSIGNMENTS}]
[{assign var="template_assignments" value="1"}]
[{/if}]

[{if $C__TEMPLATE__COLORS}]
[{assign var="template_colors" value="1"}]
[{/if}]

<table class="contentTable" >
	<tr><td>
		<label>
            [{isys type="checkbox" name="status" p_strValue="1"   p_bChecked=$template_status}]
		[{isys type="lang" ident="LC__TEMPLATES__SHOW_FILTER_STATUS"}]</label>
	</td></tr>
	<tr><td>
		<label>
            [{isys type="checkbox" name="assignments" p_strValue="1"  p_bChecked=$template_assignments}]
		[{isys type="lang" ident="LC__TEMPLATES__SHOW_REFERENCED_TEMPLATES"}]</label>
	</td></tr>
	<tr><td>
		<label>
            [{isys type="checkbox" name="colors" p_strValue="1"  p_bChecked=$template_colors}]
		[{isys type="lang" ident="LC__TEMPLATES__COLORIZE_TEMPLATE_ASSIGNMENTS"}]</label>
	</td></tr>
    <tr><td>
	<strong class="m5">
		[{isys type="lang" ident="LC__TEMPLATES__IN_COLOR"}]:
        [{isys type="f_text" name='color_value' id='color_value' p_strValue=$C__TEMPLATE__COLOR disableInputGroup=true}]
	</strong>
	</td></tr>
</table>
