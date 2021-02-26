[{* Smarty template for JDisc configuration
    @ author: Benjamin Heisig <bheisig@i-doit.org>
    @ author: Leonard Fischer <lfischer@i-doit.com>
    @ copyright: synetics GmbH
    @ license: <http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3>
*}]
[{if isset($g_list)}]
    [{$g_list}]
[{else}]
<div id="jdisc-module-config">
	<h2 class="p5 gradient border-bottom">
		<span class="fr">
			<a href="[{$link_to_jdisc_import}]" title="[{isys type='lang' ident='LC__MODULE__JDISC__IMPORT'}]">[{isys type='lang' ident='LC__MODULE__JDISC__LINK_TO_IMPORT'}]</a>
		</span>
		[{isys type='lang' ident='LC__MODULE__JDISC__CONFIGURATION'}]
	</h2>

	[{isys type='f_text' name='C__MODULE__JDISC__CONFIGURATION__ID'}]

	<h3 class="p5 gradient border-top border-bottom text-shadow mt10">[{isys type='lang' ident='LC__MODULE__JDISC__CONFIGURATION__COMMON_SETTINGS'}]</h3>
	<table class="contentTable" style="border-top: none;">
        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CONFIGURATION__DEFAULT_SERVER' ident='LC__MODULE__JDISC__CONFIGURATION__DEFAULT_SERVER'}]</td>
            <td class="value">[{isys type='f_dialog' name='C__MODULE__JDISC__CONFIGURATION__DEFAULT_SERVER' p_bDbFieldNN="1"}]</td>
        </tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CONFIGURATION__TITLE' ident='LC__MODULE__JDISC__CONFIGURATION__TITLE'}]</td>
			<td class="value">[{isys type='f_text' name='C__MODULE__JDISC__CONFIGURATION__TITLE'}]</td>
		</tr>

		<tr>
			<td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CONFIGURATION__HOST' ident='LC__MODULE__JDISC__CONFIGURATION__HOST'}]</td>
			<td class="value">[{isys type='f_text' name='C__MODULE__JDISC__CONFIGURATION__HOST'}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CONFIGURATION__PORT' ident='LC__MODULE__JDISC__CONFIGURATION__PORT'}]</td>
			<td class="value">[{isys type='f_text' name='C__MODULE__JDISC__CONFIGURATION__PORT'}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CONFIGURATION__DATABASE' ident='LC__MODULE__JDISC__CONFIGURATION__DATABASE'}]</td>
			<td class="value">[{isys type='f_text' name='C__MODULE__JDISC__CONFIGURATION__DATABASE'}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CONFIGURATION__USERNAME' ident='LC__MODULE__JDISC__CONFIGURATION__USERNAME'}]</td>
			<td class="value">[{isys type='f_text' name='C__MODULE__JDISC__CONFIGURATION__USERNAME'}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CONFIGURATION__PASSWORD' ident='LC__MODULE__JDISC__CONFIGURATION__PASSWORD'}]</td>
			<td class="value">[{isys type='f_password' name='C__MODULE__JDISC__CONFIGURATION__PASSWORD'}]</td>
		</tr>

        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CONFIGURATION__VERSION_CHECK' ident='LC__MODULE__JDISC__CONFIGURATION__ALLOW_IMPORT_OLDER_VERSION'}]</td>
            <td class="value">[{isys type='f_dialog' name='C__MODULE__JDISC__CONFIGURATION__VERSION_CHECK' p_bDbFieldNN="1"}]</td>
        </tr>

        [{if !isys_glob_is_edit_mode()}]
        <tr>
            <td class="key">
                <button type="button" class="btn fr" id="check_button">
                    <img src="[{$dir_images}]icons/silk/database_connect.png" class="mr5" /><span>[{isys type="lang" ident="LC__MODULE__JDISC__CONNECTION_CHECK"}]</span>
                </button>
            </td>
            <td>
                <p id="connection_result" class="p5 ml20 mr5 hide"></p>
            </td>
        </tr>
        [{/if}]
	</table>

    <h3 class="p5 gradient border-top border-bottom text-shadow mt10">[{isys type='lang' ident='LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_SETTINGS'}]</h3>
    <table class="contentTable" style="border-top: none;">
        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CONFIGURATION__DISCOVERY_USERNAME' ident='LC__MODULE__JDISC__CONFIGURATION__USERNAME'}]</td>
            <td class="value">[{isys type='f_text' name='C__MODULE__JDISC__CONFIGURATION__DISCOVERY_USERNAME'}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CONFIGURATION__DISCOVERY_PASSWORD' ident='LC__MODULE__JDISC__CONFIGURATION__PASSWORD'}]</td>
            <td class="value">[{isys type='f_password' name='C__MODULE__JDISC__CONFIGURATION__DISCOVERY_PASSWORD'}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CONFIGURATION__DISCOVERY_PORT' ident='LC__MODULE__JDISC__CONFIGURATION__PORT'}]</td>
            <td class="value">[{isys type='f_text' name='C__MODULE__JDISC__CONFIGURATION__DISCOVERY_PORT'}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CONFIGURATION__DISCOVERY_PROTOCOL' ident='LC__CATD__PROTOCOL'}]</td>
            <td class="value">[{isys type='f_text' name='C__MODULE__JDISC__CONFIGURATION__DISCOVERY_PROTOCOL'}]</td>
        </tr>

        [{if !isys_glob_is_edit_mode()}]
        <tr>
            <td class="key">
                <button type="button" class="btn fr" id="check_button_discovery">
                    <img src="[{$dir_images}]icons/silk/database_connect.png" class="mr5" /><span>[{isys type="lang" ident="LC__MODULE__JDISC__CONNECTION_CHECK"}]</span>
                </button>
            </td>
            <td>
                <p id="connection_result_discovery" class="p5 ml20 mr5 hide"></p>
            </td>
        </tr>
        [{/if}]

    </table>

    <h3 class="p5 gradient border-top border-bottom text-shadow mt10">[{isys type='lang' ident='LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_CATEGORY_SETTINGS'}]</h3>
    <table class="contentTable" style="border-top: none;">
        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CONFIGURATION__DISCOVERY_TIMEOUT' description='LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_TIMEOUT_DESCRIPTION' ident='LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_TIMEOUT'}]</td>
            <td class="value">[{isys type='f_text' name='C__MODULE__JDISC__CONFIGURATION__DISCOVERY_TIMEOUT'}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__CONFIGURATION__DISCOVERY_IMPORT_RETRIES' description='LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_IMPORT_RETRIES_DESCRIPTION' ident='LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_IMPORT_RETRIES'}]</td>
            <td class="value">[{isys type='f_text' name='C__MODULE__JDISC__CONFIGURATION__DISCOVERY_IMPORT_RETRIES'}]</td>
        </tr>
    </table>

	[{if !isys_glob_is_edit_mode()}]
	<script type="text/javascript">

        [{if $jdisc_id}]
        document.isys_form.id.value = '[{$jdisc_id}]';
        [{/if}]

        var $checkButton = $('check_button'),
	        $checkButtonDiscovery = $('check_button_discovery');

        $checkButton.on('click', function () {
	        $checkButton
		        .disable()
		        .update(new Element('img', {src:'[{$dir_images}]ajax-loading.gif', className:'mr5'}))
		        .insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'));

	        new Ajax.Request('?call=jdisc&ajax=1&func=check_connection', {
		        method: "post",
		        parameters: {
			        'jdisc_server': '[{$jdisc_id}]'
		        },
		        onComplete: function (transport) {
			        var json = transport.responseJSON,
				        $result = $('connection_result')
					        .removeClassName('box-green')
					        .removeClassName('box-red')
					        .removeClassName('hide')
					        .update(json.message);

			        $checkButton
				        .enable()
				        .update(new Element('img', {src:'[{$dir_images}]icons/silk/database_connect.png', className:'mr5'}))
				        .insert(new Element('span').update('[{isys type="lang" ident="LC__MODULE__JDISC__CONNECTION_CHECK"}]'));

			        if (json.connection) {
				        $result.addClassName('box-green');
			        } else {
				        $result.addClassName('box-red');
			        }
		        }.bind(this)
	        });
        });

        $checkButtonDiscovery.on('click', function () {
	        $checkButtonDiscovery
		        .disable()
		        .update(new Element('img', {src:'[{$dir_images}]ajax-loading.gif', className:'mr5'}))
		        .insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'));

	        new Ajax.Request('?call=jdisc&ajax=1&func=check_connection_discovery', {
		        method: "post",
		        parameters: {
			        'jdisc_server': '[{$jdisc_id}]'
		        },
		        onSuccess: function (transport) {
			        var json = transport.responseJSON,
				        $result = $('connection_result_discovery')
					        .removeClassName('box-green')
					        .removeClassName('box-red')
					        .removeClassName('hide')
					        .update(json.message);

			        if (json.success) {
				        $result.addClassName('box-green');
			        } else {
				        $result.addClassName('box-red');
			        }

			        $checkButtonDiscovery
				        .enable()
				        .update(new Element('img', {src:'[{$dir_images}]icons/silk/database_connect.png', className:'mr5'}))
				        .insert(new Element('span').update('[{isys type="lang" ident="LC__MODULE__JDISC__CONNECTION_CHECK"}]'));
		        }.bind(this)
	        });
        });

	</script>
	[{/if}]
</div>
[{/if}]
