<div id="popup-commentary">
    <h3 class="popup-header">
        <img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]icons/close-circle.png">
        <span>[{isys type="lang" ident="LC__UNIVERSAL__TITLE_LOGBOOK"}] ([{isys type="lang" ident="LC__POPUP__COMMENTARY__TITLE"}])</span>
    </h3>

    <div class="popup-content p5">
    [{isys_group name="tom"}]
        [{isys_group name="content"}]
            [{if $error_msg != ""}]
                <p class="text-red">[{$error_msg}]</p>
            [{/if}]

            [{isys_group name="middle"}]
                <input name="object_id" type="hidden" value="[{$smarty.get.object_id|strip_tags|escape}]" />
                <input name="objTypeID" type="hidden" value="[{$smarty.get.objTypeID|strip_tags|escape}]" />

                <table style="width:100%; border-spacing: 0;">
                    <tr>
                        <td style="width: 120px;">
                            [{isys type="f_label" ident="LC__POPUP__COMMENTARY__REASON" name="LogbookReason"}]
                        </td>
                        <td>
                            [{isys type="f_popup" name="LogbookReason" p_strPopupType="dialog_plus" p_strTable="isys_logbook_reason" p_strClass="input-block" inputGroupMarginClass=""}]
                        </td>
                    </tr>
                </table>

                [{isys type="f_textarea" name="commentary" id="f_commentary" p_onChange="$('LogbookCommentary').value = $('f_commentary').value;" p_nRows="8" p_strClass="input-block mt10 noresize" p_bInfoIconSpacer=0}]
            [{/isys_group}]

            [{isys_group name="bottom"}]

            [{assign var="object_id" value=$smarty.get.object_id|escape|default:"0"|strip_tags|escape}]
            [{assign var="navmode_save" value=$smarty.const.C__NAVMODE__SAVE}]

            [{/isys_group}]
        [{/isys_group}]
    [{/isys_group}]
    </div>

    <div class="popup-footer">
        [{isys
            name="save"
            type="f_button"
            id="save_button"
            p_strAccessKey="s"
            icon="`$dir_images`icons/silk/tick.png"
            p_strValue="LC__UNIVERSAL__BUTTON_SAVE"}]
        [{isys
            name="C__UNIVERSAL__BUTTON_CANCEL"
            type="f_button"
            icon="`$dir_images`icons/silk/cross.png"
            p_strClass="popup-closer"
            p_strValue="LC__UNIVERSAL__BUTTON_CANCEL"}]
    </div>
</div>

<script type="text/javascript">
    (function () {
        'use strict';

        var $popup = $('popup-commentary'),
            $save_button = $('save_button');

        $popup.on('click', '.popup-closer', function () {
            popup_close($('popup_commentary'));
            [{if $smarty.get.callbackCancel}]
            idoit.callbackManager.triggerCallback('[{$smarty.get.callbackCancel|strip_tags|escape}]');
            [{/if}]
        });

        $save_button.on('click', function () {
            popup_close($('popup_commentary'));
            $('navMode').setValue('[{$navmode_save}]');

            // @see ID-6296 Trigger the callback AFTER the ajax request has finished.
            [{if $smarty.get.callbackAccept}]
            save_via_ajax('ajaxReturnNote', function () {idoit.callbackManager.triggerCallback('[{$smarty.get.callbackAccept|strip_tags|escape}]');});
            [{else}]
            save_via_ajax('ajaxReturnNote');
            [{/if}]
        });

        $save_button.focus();
    })();
</script>
