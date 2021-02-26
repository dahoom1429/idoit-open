<table class="contentTable">
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__PASSWORD__TITLE' ident="LC__CMDB__CATG__TITLE"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATG__PASSWORD__TITLE"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__PASSWORD__USERNAME' ident="LC__LOGIN__USERNAME"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATG__PASSWORD__USERNAME"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__PASSWORD__PASSWORD' ident="LC__LOGIN__PASSWORD"}]</td>
        <td class="value">
            [{isys type="f_password" maskPassword=false name="C__CATG__PASSWORD__PASSWORD"}]
            [{if isys_glob_is_edit_mode()}]
                <br class="cb" />
                <div class="mt5 ml20">
                    [{isys type="lang" ident="LC__CMDB__CATG__PASSWORD__PROPOSE_PASSWORD"}]
                    <button type="button" class="btn" data-strength="weak" title="[{isys type="lang" ident="LC__CATG__PASSWORD__DESCRIPTION_WEAK" p_bHtmlEncode=false}]"><span>[{isys type="lang" ident="LC__UNIVERSAL__WEAK"}]</span></button>
                    <button type="button" class="btn" data-strength="medium" title="[{isys type="lang" ident="LC__CATG__PASSWORD__DESCRIPTION_MEDIUM" p_bHtmlEncode=false}]"><span>[{isys type="lang" ident="LC__UNIVERSAL__MEDIUM"}]</span></button>
                    <button type="button" class="btn" data-strength="strong" title="[{isys type="lang" ident="LC__CATG__PASSWORD__DESCRIPTION_STRONG" p_bHtmlEncode=false}]"><span>[{isys type="lang" ident="LC__UNIVERSAL__STRONG"}]</span></button>
                </div>
            [{/if}]
        </td>
    </tr>
</table>

[{if isys_glob_is_edit_mode()}]
    <script type="text/javascript">
        (function () {
            'use strict';

            var $passwordButtons = $$('button[data-strength]');

            $passwordButtons.invoke('on', 'click', function (ev) {
                var $button  = ev.findElement('button'),
                    strength = $button.readAttribute('data-strength');

                $button.disable();

                new Ajax.Request('?call=password&ajax=1&strength=' + strength, {
                    onComplete: function (xhr) {
                        var json = xhr.responseJSON;

                        $button.enable();

                        if (json.success && json.data) {
                            $('C__CATG__PASSWORD__PASSWORD').setValue(json.data);
                        } else {
                            idoit.Notify.error(json.message || 'Got no password - please try again');
                        }
                    }
                });
            });
        })();
    </script>
[{/if}]