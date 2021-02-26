<div id="popup-browser-location">
	<h3 class="popup-header">
		<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]icons/close-circle.png">
		<span>[{isys type="lang" ident="LC__POPUP__BROWSER__LOCATION_TITLE"}]</span>
	</h3>

	<input type="hidden" id="popup-browser-location-selection-view" value="[{$selectionView}]" />
	<input type="hidden" id="popup-browser-location-selection-hidden" value="[{$selectionHidden}]" />

	<div class="popup-content p5">
		<div id="location-tree" class="p5" style="position:absolute; top:0; right:0; bottom:25px; left:0; overflow:auto"></div>

		<p style="position:absolute; right:0; bottom:0; left:0; padding:5px;">
            [{isys type="lang" ident="LC__POPUP__BROWSER__SELECTED_OBJECT"}]: <strong id="popup-browser-location-object-selection">[{$selectionView}]</strong>
        </p>
	</div>

	<div class="popup-footer">
		<button type="button" class="btn mr5" id="popup-browser-location-accept">
			<img src="[{$dir_images}]icons/silk/tick.png" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__BUTTON_SAVE"}]</span>
		</button>
		<button type="button" class="btn popup-closer">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
		</button>
	</div>
</div>

<script type="text/javascript">
    (function () {
        'use strict';

        var $popup           = $('popup-browser-location'),
            $selectionView   = $('popup-browser-location-selection-view'),
            $selectionHidden = $('popup-browser-location-selection-hidden'),
            $objectSelection = $('popup-browser-location-object-selection'),
            $treeContainer   = $('location-tree'),
            $acceptButton    = $('popup-browser-location-accept'),
            openNodes        = JSON.parse('[{$openNodes|json_encode|escape:"javascript"}]'),
            selectedNodes    = JSON.parse('[{$selectedNodes|json_encode|escape:"javascript"}]');

        $popup.select('.popup-closer').invoke('on', 'click', function () {
            popup_close();
        });

        $acceptButton.on('click', function (ev) {
            if ($('[{$return_view}]')) {
                $('[{$return_view}]').setValue($selectionView.getValue());
            }

            if ($('[{$return_hidden}]')) {
                $('[{$return_hidden}]').setValue($selectionHidden.getValue());
            }

            [{$callback_accept}]

            popup_close();
        });

        idoit.Require.requireQueue(['treeBase', 'treeLocation'], function () {
            var tree = new LocationTree($treeContainer, {
                rootNodeId: parseInt('[{$rootObjectId}]'),
                onlyContainer: JSON.parse('[{if $onlyContainer}]true[{else}]false[{/if}]'),
                considerRights: JSON.parse('[{if $considerRights}]true[{else}]false[{/if}]'),
                onSelect: function (nodeId, data) {
                    $objectSelection.update(data.nodeTypeTitle + ' >> ' + data.nodeTitle);
                    $selectionView.setValue(data.nodeTypeTitle + ' >> ' + data.nodeTitle);
                    $selectionHidden.setValue(nodeId);
                },
                nodeSorting: function(a, b) {
                    return a.nodeTitle.localeCompare(b.nodeTitle);
                }
            });

            tree.setOpenNodes(openNodes)
                .setSelectedNodes(selectedNodes)
                .process();
        });
    })();
</script>
