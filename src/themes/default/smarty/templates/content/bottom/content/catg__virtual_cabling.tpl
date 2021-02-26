[{if $isPro}]
	[{if $displayCablingInfo}]
	<div class="box-white" style="margin: 10px auto; width:80%; max-width: 700px;">
		<h2 class="p5 gradient border-bottom text-shadow-white">[{isys type="lang" ident="LC__CABLING__NEW_VISUALIZATION__HEADLINE"}]</h2>
		<div class="p20">
			[{if !$isCablingInstalled}]
				<h3>[{isys type="lang" ident="LC__CABLING__NEW_VISUALIZATION__ADDON_NOT_INSTALLED"}]</h3>
				<p>[{isys type="lang" ident="LC__CABLING__NEW_VISUALIZATION__ADDON_NOT_INSTALLED_TEXT"}]</p>
				<a class="btn btn-larget mt10" href="https://login.i-doit.com/" target="_blank">[{isys type="lang" ident="LC__CABLING__NEW_VISUALIZATION__ADDON_NOT_INSTALLED_OPEN_CUSTOMER_PORTAL"}]</a>
			[{/if}]

			[{if $isCablingInstalled && !$isCablingActive}]
				<h3>[{isys type="lang" ident="LC__CABLING__NEW_VISUALIZATION__ADDON_NOT_ACTIVE"}]</h3>
				<p>[{isys type="lang" ident="LC__CABLING__NEW_VISUALIZATION__ADDON_NOT_ACTIVE_TEXT"}]</p>
				<a class="btn btn-large mt10" href="[{$baseUrl}]admin" target="_blank"><span>[{isys type="lang" ident="LC__CABLING__NEW_VISUALIZATION__ADDON_NOT_ACTIVE_OPEN_ADMIN_CENTER"}]</span></a>
			[{/if}]

			[{if $isCablingInstalled && $isCablingActive}]
				<a class="btn btn-large" href="[{$baseUrl}]cabling/visualization/[{$objectId}]">
					<img src="[{$dir_images}]icons/silk/disconnect.png" />
					<span>[{isys type="lang" ident="LC__CABLING__NEW_VISUALIZATION__ADDON_READY_OPEN_IN_ADDON"}]</span>
				</a>
				<br />
				<label>
					<input type="checkbox" id="cabling-save-option" [{if $openDirectlyInAddon}]checked="checked"[{/if}] />
					<span class="ml5">[{isys type="lang" ident="LC__CABLING__NEW_VISUALIZATION__ADDON_READY_DIRECTLY_OPEN_IN_ADDON"}]</span>
				</label>
			[{/if}]

			<div class="mt10">
				<button type="button" id="cabling-dismiss-info" class="btn btn-small">
					<img src="[{$dir_images}]icons/silk/cross.png" class="mr5" /><span>[{isys type="lang" ident="LC__CABLING__NEW_VISUALIZATION__DISMISS_INFO"}]</span>
				</button>
			</div>
		</div>
	</div>
	[{/if}]

	[{if $isCablingInstalled && $isCablingActive}]
		<script>
	        (function () {
	            "use strict";

	            var $checkbox = $('cabling-save-option'),
	                $button = $('cabling-dismiss-info');

	            if ($checkbox) {
	                $checkbox.on('change', function () {
	                    new Ajax.Request('[{$ajaxUrl}]', {
	                        parameters: {
	                            directlyRedirect: $checkbox.checked ? 1 : 0
	                        },
	                        onComplete: function (xhr) {
	                            var json = xhr.responseJSON;

	                            if (json.success) {
	                                idoit.Notify.success("[{isys type="lang" ident="LC__INFOBOX__DATA_WAS_SAVED"}]", {life: 5});
	                            } else {
	                                idoit.Notify.success(json.message || xhr.responseText, {sticky: true});
	                            }
	                        }
	                    });
	                });
	            }

	            if ($button) {
                    $button.on('click', function () {
                        $button.disable()
	                        .down('img').writeAttribute('src', window.dir_images + 'ajax-loading.gif')
	                        .next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]}');

                        new Ajax.Request('[{$ajaxUrl}]', {
                            parameters: {
                                dismissCablingInfo: 1
                            },
                            onComplete: function (xhr) {
                                var json = xhr.responseJSON;

                                if (json.success) {
                                    idoit.Notify.success("[{isys type="lang" ident="LC__INFOBOX__DATA_WAS_SAVED"}]", {life: 5});

                                    $button.up('.box-white').addClassName('hide');
                                } else {
                                    idoit.Notify.success(json.message || xhr.responseText, {sticky: true});
                                }
                            }
                        });
                    })
	            }

				[{if $openDirectlyInAddon}]
	            document.location.href = '[{$baseUrl}]cabling/visualization/[{$objectId}]';
				[{/if}]
	        })();
		</script>
	[{/if}]
[{/if}]

<table class="listing" cellpadding="0" cellspacing="0">
	<colgroup>
		<col width="10%">
	</colgroup>
	<thead>
	<tr>
		<th>[{isys type="lang" ident="LC__CATS__CABLE__CONNECTION"}]</th>
		<th>[{isys type="lang" ident="LC__CATG__CONNECTOR__CABLERUN"}]</th>
	</tr>
	</thead>
	<tbody>
	[{foreach $cablingData as $data}]
	<tr>
		<td>[{$data.connection}]</td>
		<td>
			[{if !$data.leftConnections && !$data.rightConnections}]
				[{isys type="lang" ident="LC__CATG__CONNECTOR__NO_CABLERUN"}]
			[{else}]
				[{$data.leftConnections}]
				[{$data.rightConnections}]
			[{/if}]
		</td>
	</tr>
	[{/foreach}]
	</tbody>
</table>