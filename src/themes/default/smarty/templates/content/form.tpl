[{strip}]
<form method="post" name="isys_form" id="isys_form"[{if !empty($encType)}] enctype="[{$encType}]"[{/if}] [{$formAdditionalAction}]>

<input type="hidden" name="navMode" id="navMode" value="[{$navMode}]" />
<input type="hidden" name="sort" id="sort" value="[{$sort}]" />
<input type="hidden" name="dir" id="dir" value="[{$dir}]" />
<input type="hidden" name="id" value="[{$id}]" />
<input type="hidden" name="navPageStart" id="navPageStart" value="[{$navPageStart}]" />
<input type="hidden" name="navTemplateDetailView" value="[{$navTemplateDetailView}]" />
<input type="hidden" name="template" value="[{$smarty.post.template}]" />
<input type="hidden" name="useTemplate" value="[{$smarty.post.useTemplate|strip_tags|escape}]" id="useTemplate" />
<input type="hidden" name="[{$smarty.const.C__POST__POPUP_RECEIVER}]" id="[{$smarty.const.C__POST__POPUP_RECEIVER}]" value="[{$g_popup_receiver}]" />
<input type="hidden" name="_csrf_token" value="[{$csrf_value}]" id="_csrf_token" />

<div class="cb"></div>
[{/strip}]

<script type="text/javascript">
	"use strict";

	(function () {
		/**
		 * Retrieve created id after saving to prevent saving duplicate entries
		 */
		document.on('form:saved', function(ev) {
		    var $idField = $('body').down('[name="[{$smarty.const.C__GET__ID}]"]');

			if ($idField) {
				if (ev.memo && ev.memo.response && ev.memo.response.responseJSON) {
					var response = ev.memo.response.responseJSON;

					if (response.id) {
                        $idField.setValue(response.id);
					}
				}
			}
		});
	}());
</script>
