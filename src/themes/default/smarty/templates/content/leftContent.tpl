[{strip}]
	<div id="tree_content">
		[{if isset($index_includes.lefttreetop)}]
			[{include file=$index_includes.lefttreetop}]
		[{/if}]

		<table cellpadding="0" cellspacing="0">
			<tr>
				<td colspan="2">
				[{if $bShowMenuTreeButtons && $treeMode == $smarty.const.C__CMDB__VIEW__TREE_LOCATION}]
					<div id="tree_config" class="tree-config" style="display:none;">
						<div class="m10">
							<label><input type="radio" name="tree_type" value="[{$smarty.const.C__CMDB__VIEW__TREE_LOCATION__LOCATION}]" [{if $smarty.const.C__CMDB__VIEW__TREE_LOCATION__LOCATION == $treeType || null == $treeType}]checked="checked"[{/if}] /> [{isys type="lang" ident="LC__CMDB__TREE_VIEW__LOCATION"}]</label><br />
							<label><input type="radio" name="tree_type" value="[{$smarty.const.C__CMDB__VIEW__TREE_LOCATION__LOGICAL_UNITS}]" [{if $smarty.const.C__CMDB__VIEW__TREE_LOCATION__LOGICAL_UNITS == $treeType}]checked="checked"[{/if}]/> [{isys type="lang" ident="LC__CMDB__TREE_VIEW__LOGICAL_UNIT"}]</label><br />
							<label><input type="radio" name="tree_type" value="[{$smarty.const.C__CMDB__VIEW__TREE_LOCATION__COMBINED}]" [{if $smarty.const.C__CMDB__VIEW__TREE_LOCATION__COMBINED == $treeType}]checked="checked"[{/if}]/> [{isys type="lang" ident="LC__CMDB__TREE_VIEW__COMBINED"}]</label>
						</div>
					</div>

					<div id="tree_config_button" class="center ml5 mr5 tree-config-dropdown mouse-pointer"
						onclick="new Effect.toggle('tree_config', 'blind',{duration:0.2,afterFinish:function(e){$('tree_config_button').hasClassName('tree-config-dropdown-white') ? $('tree_config_button').removeClassName('tree-config-dropdown-white') : $('tree_config_button').addClassName('tree-config-dropdown-white');}});">
					</div>
				[{/if}]
				</td>
			</tr>
			<tr>
				<td valign="top" style="width:100%;">
					<div class="cb dtree" id="menuTreeJS">
					[{if ($menu_tree)}]
						[{$menu_tree}]
					[{/if}]
					</div>
				</td>
				<td valign="middle" width="5px" id="treeSwitch">

				</td>
			</tr>
		</table>

		[{if isset($index_includes.lefttreebottom)}]
			[{include file=$index_includes.lefttreebottom}]
		[{/if}]
	</div>
[{/strip}]

<script type="text/javascript">
	if (menu_tree)
	{
		[{if $bMenuTreeHideable}]
		if ('addNodesVisibility' in menu_tree) {
			menu_tree.addNodesVisibility([{$treeHide}]);
		}
		[{/if}]

		[{if $bMenuTreeSearcheable}]
		if ('addSearchCapabilities' in menu_tree) {
			menu_tree.addSearchCapabilities();
		}
		[{/if}]
	}

	[{$menu_tree_script}]

	$A(document.getElementsByName('tree_type')).invoke('on', 'change', function(e) {
		$('menuTreeJS').update();
		[{$menu_tree_script}]
	});

	window.ObjectSelected = function(objId, objTypeId, objTitle, objTypeTitle, callbackId, clickedElement) {
        // We have to build the path to the clicked Element to prevent server-side detection.
        var l_element = $(clickedElement.parentNode);
        var l_path = [];

        while (!l_element.hasClassName('dtree')) {
            if (l_element.id.startsWith('menuTreeJS_Node_')) {
                l_path.push(l_element.id.replace('menuTreeJS_Node_', ''));
            }

            l_element = l_element.up();
        }

		window.location.href = window.www_dir + '?' + C__CMDB__GET__OBJECT + '=' + objId + "&treeView="+"&treePath=" + l_path.join(',');
	};

    window.toggleTreeExpand = function () {
        var menuTreeOn = $('menuTreeOn'),
		    contentArea = $('contentArea'),
            $draggableBar = $('draggableBar');

        if (menuTreeOn.readAttribute('data-expanded') == 'true') {
            contentArea.show();
            if($draggableBar) {
                $draggableBar.show();
            }

	        if (menuTreeOn) {
		        menuTreeOn.setStyle({width:menuTreeOn.readAttribute('data-origin-width') + 'px'});
	            menuTreeOn.setAttribute('data-expanded', 'false');
	        }
        } else {
            contentArea.hide();
            if($draggableBar) {
                $draggableBar.hide();
            }

	        if (menuTreeOn) {
	            menuTreeOn.setAttribute('data-origin-width', menuTreeOn.getWidth());
                menuTreeOn.setStyle({width:'100%'});
			    menuTreeOn.setAttribute('data-expanded', 'true');
	        }
        }
    };
</script>
