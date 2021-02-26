[{assign var="mod" value=$smarty.const.C__GET__MODULE_ID}]
[{assign var="pid" value=$smarty.const.C__GET__SETTINGS_PAGE}]

<script type="text/javascript">
	function reload_combination_list () {
		new Ajax.Updater('list', '?[{$mod}]=[{$smarty.get.$mod|strip_tags|escape}]&[{$pid}]=[{$smarty.const.C__PAGE__COMBINATION_LIST}]');
	}

	function delete_combination () {
		new Ajax.Call('',
			{
				onComplete: reload_combination_list, parameters: $('isys_form').serialize(true), method: 'post'
			});
	}

	function close_combination () {
		if ($('combination').visible()) {
			$('combination').hide();
			$('list').show();

			reload_combination_list();
		}
	}
</script>

<style type="text/css">
	#app_list ul {
		list-style:none;
		margin:0;
		padding:0;
	}

	#app_list ul li {
		width:100%;
	}
	#app_list ul li a {
		display:block;
		padding: 5px;
		background:#efefef;
		border-bottom:1px solid #ccc;
	}
	#app_list ul li a:hover {
		border-bottom:1px solid #999;
		background:#fefefe;
	}
</style>

<div>

 	<div id="combination" style="[{if empty($smarty.get.objID)}]display:none;[{/if}]margin:10px;border:1px solid #ccc;">
	 	<p class="m0 fr" style="padding:5px 0 0 5px"><a class="p5" style="background:#eee;" href="javascript:void('close');" onclick="close_combination();">X</a></p>
	 	<p class="m0 fr p5">Aktuelle Auswahl: <span class="bold" id="sel_obj_text">[{$selected_object|default:"Keine"}]</span><strong id="sel_app_text"></strong></p>

 		<h3 class="m0" style="padding:4px;background:#cbcbcb;border-bottom:e5e5e5;">[{isys type="lang" ident="LC__NAVIGATION__NAVBAR__NEW"}] [{isys type="lang" ident="LC__UNIVERSAL__COMBINATION"}]</h3>

 		<div id="object_selection" style="height:500px;width:300px;overflow:auto;border-right:1px solid #ccc;" class="fl">
 			<h4 class="m0" style="padding:5px;margin-bottom:10px;background:#eee;border-bottom:1px solid #eee;">
 				1. [{isys type="lang" ident="LC__COMBINATION__SELECT_MAIN_OBJECT"}]
 			</h4>

 			<div style="padding:5px;">
 				[{$object_browser}]
 			</div>
 		</div>
 		<div id="app_selection" style="height:500px;width:450px;overflow:auto;border-right:1px solid #ccc;" class="fl">
 			<h4 class="m0" style="padding:5px;margin-bottom:10px;background:#eee;border-bottom:1px solid #eee;">
 				2. [{isys type="lang" ident="LC__COMBINATION__SELECT_APPLICATION"}]
 			</h4>

 			<div style="padding:5px;" id="app_list"></div>

 		</div>

 		<div id="comb_submit" style="display:none;" class="fl">
 			<h4 class="m0" style="padding:5px;margin-bottom:10px;background:#eee;border-bottom:1px solid #eee;">
 				3. [{isys type="lang" ident="LC__COMBINATION__SAVE"}]
 			</h4>

 			<div style="text-align:center;padding:15px;">
	 			<input type="hidden" name="object_parent" id="object_parent" value="" />
	 			<input type="hidden" name="object_child" id="object_child" value="" />

	 			<p class="m10">
	 				[{isys type="lang" ident="LC__COMBINATION__NEXT_STEP" p_bHtmlEncode="1"}]
	 			</p>

	 			<p class="m10">
		 			<label for="combination_title"><strong>Titel der Kombination: </strong></label><br />

		 			<input tyle="text" value="" size="35" name="combination_title" id="combination_title" />

	 			</p>

	 			<input type="button" name="submit_combination" onclick="save_combination();" value="Kombination Speichern" />

	 			<div class="m10" id="ajax_result"></div>
 			</div>

 		</div>

 		<div style="clear:both"></div>
 	</div>

 	<div id="list" style="[{if !empty($smarty.get.objID)}]display:none;[{/if}]">
	 	[{if $g_list}]
 			[{$g_list}]
	 	[{/if}]
	</div>

</div>

<script type="text/javascript">
//add_combination();
</script>