<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__AUTH_GUI__JSONRPCAPI_CONDITION"}]</h2>

<fieldset class="overview border-top-none">
    <legend><span>[{isys type="lang" ident="LC__MODULE__JDISC__CONFIGURATION__COMMON_SETTINGS"}]</span></legend>

	<table class="contentTable">
        <tr>
            <td class="key">[{isys type='f_label' name='C__SYSTEM_SETTINGS__APIKEY' ident='API-Key'}]</td>
            <td class="value">
                [{isys type='f_text' name='C__SYSTEM_SETTINGS__APIKEY'}]

                [{if isys_glob_is_edit_mode()}]
                    <button type="button" id="btn_new_key" class="btn ml5 text-normal">
                        <img src="[{$dir_images}]icons/silk/arrow_refresh.png" class="mr5" /><span>[{isys type="lang" ident="LC__SYSTEM_SETTINGS__API__CREATE_NEW_KEY"}]</span>
                    </button>
                [{/if}]
            </td>
        </tr>
		<tr>
			<td class="key"></td>
			<td class="value">[{isys type="checkbox" name="C__SYSTEM_SETTINGS__API_STATUS"}]</td>
		</tr>
		<tr>
			<td class="key"></td>
			<td class="value">[{isys type="checkbox" name="C__SYSTEM_SETTINGS__API__AUTHENTICATED_USERS_ONLY"}]</td>
		</tr>
        <tr>
            <td class="key"></td>
            <td class="value">[{isys type="checkbox" name="C__SYSTEM_SETTINGS__API__STRIP_TAGS"}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__SYSTEM_SETTINGS__LOG_LEVEL' ident='Log level'}]</td>
            <td class="value">
                [{isys type='f_dialog' name='C__SYSTEM_SETTINGS__LOG_LEVEL' p_bSort=false}]
            </td>
        </tr>
	</table>
</fieldset>

<script type="text/javascript">
    (function () {
        'use strict';

        var $apiKeyInput  = $('C__SYSTEM_SETTINGS__APIKEY'),
            $apiKeyButton = $('btn_new_key');

        if ($apiKeyInput && $apiKeyButton)
        {
            $apiKeyButton.on('click', function () {
                new Ajax.Request('?call=password&ajax=1&strength=strong', {
                    onComplete: function (xhr) {
                        var json = xhr.responseJSON;

                        if (json.success && json.data)
                        {
                            $apiKeyInput.setValue(json.data);
                        }
                        else
                        {
                            idoit.Notify.error(json.message || 'Got no password - please try again');
                        }
                    }
                });
            });
        }
    })();
</script>