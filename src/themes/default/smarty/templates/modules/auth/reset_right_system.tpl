<div id="auth">
    <h3 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__MODULE__AUTH__TREE__RESET_RIGHT_SYSTEM"}]</h3>

    <table class="contentTable" style="border-top: none;">
        <tr>
            <td class="key">[{isys type="f_label" ident="LC__LOGIN__USERNAME" name="C__AUTH__RESET_RIGHT_SYSTEM__USERNAME"}]</td>
            <td class="value">
                [{isys type="f_text" name="C__AUTH__RESET_RIGHT_SYSTEM__USERNAME"}]
            </td>
        </tr>
        <tr>
            <td class="key">[{isys type="f_label" ident="LC__LOGIN__PASSWORD" name="LC__LOGIN__PASSWORD"}]</td>
            <td class="value">
                [{isys type="f_text" name="C__AUTH__RESET_RIGHT_SYSTEM__PASSWORD"}]
            </td>
        </tr>
        <tr>
            <td class="key"></td>
            <td class="value">
                <button id="reset-loader" type="button" class="btn ml20">
                    <img src="[{$dir_images}]icons/silk/arrow_refresh.png" class="mr5" /><span>[{isys type="lang" ident="LC__AUTH__RIGHT_EXECUTE"}]</span>
                </button>
            </td>
        </tr>
    </table>
    <p class="m10">[{isys type="lang" ident="LC__MODULE__AUTH__RESET_RIGHT_SYSTEM__DESCRIPTION" p_bHtmlEncode=false}]</p>
</div>
<script type="text/javascript">
	// This three lines of code prevent the global form from submittin, when we hit "enter".
	$('isys_form').on('submit', function (ev) {
		ev.preventDefault();

		$('reset-loader').simulate('click');
	});

    $('reset-loader').on('click', function(){
        new Ajax.Request('[{$ajax_url}]&func=reset_right_system',
                {
                    parameters:{
                        username:$('C__AUTH__RESET_RIGHT_SYSTEM__USERNAME').value,
                        password:$('C__AUTH__RESET_RIGHT_SYSTEM__PASSWORD').value
                    },
                    method:'post',
                    onSuccess:function (response) {
                        var json = response.responseJSON;

                        if (json.data.success)
                        {
                            idoit.Notify.success(json.data.message);
                        }
                        else
                        {
                            idoit.Notify.error(json.data.message);
                        }
                    }
                }
        );
    });
</script>