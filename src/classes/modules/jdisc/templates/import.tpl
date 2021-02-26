[{* Smarty template for JDisc import
    @ author: Benjamin Heisig <bheisig@i-doit.org>
    @ author: Leonard Fischer <lfischer@i-doit.org>
    @ copyright: synetics GmbH
    @ license: <http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3>
*}]

<div id="module-jdisc-import">
    <ul id="objectTabs" class="m0 gradient browser-tabs">
        <li><a href="#import" data-tab="#import">[{isys type="lang" ident=LC__MODULE__JDISC__IMPORT}]</a></li>
        <li><a href="#discovery" data-tab="#discovery">[{isys type="lang" ident=LC__MODULE__JDISC__DISCOVERY}]</a></li>
    </ul>

    <div id="import">
        <h3 class="border-top border-bottom gradient p5 text-shadow mt10">
            [{isys type='lang' ident='LC__MODULE__JDISC__IMPORT__OPTIONS'}]

            <span class="fr">
                <a href="[{$link_to_jdisc_configuration}]" title="[{isys type='lang' ident='LC__MODULE__JDISC__CONFIGURATION'}]">[{isys type='lang' ident='LC__MODULE__JDISC__LINK_TO_CONFIGURATION'}]</a> -
                <a href="[{$link_to_jdisc_profiles}]" title="[{isys type='lang' ident='LC__MODULE__JDISC__PROFILES'}]">[{isys type='lang' ident='LC__MODULE__JDISC__LINK_TO_PROFILES'}]</a>
            </span>
        </h3>
        <table class="contentTable" style="border-top: none;">
            <tr>
                <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__IMPORT__JDISC_SERVERS' ident='LC__MODULE__JDISC__IMPORT__JDISC_SERVERS'}]</td>
                <td class="value">
                    [{isys type="f_dialog" name="C__MODULE__JDISC__IMPORT__JDISC_SERVERS" p_bDbFieldNN=1 p_bSort=false p_strClass="normal"}]
                    <img src="[{$dir_images}]ajax-loading.gif" id="switch-server-loader" class="vam hide">
                </td>
            </tr>

            <tr id="jdisc_groups" style="[{if $jedi_version}]display:none;[{/if}]">
                <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__IMPORT__GROUP' ident='LC__MODULE__JDISC__IMPORT__GROUP'}]</td>
                <td class="value">
                    [{isys type="f_dialog" name="C__MODULE__JDISC__IMPORT__GROUP" p_strClass="normal" p_bSort=false}]
                </td>
            </tr>

            [{if count($filter_files) > 0}]
                <tr>
                    <td class="key">
                        [{isys type='f_label' name='C__MODULE__JDISC__IMPORT__FILTER' ident='LC_UNIVERSAL__FILTERS'}]
                    </td>
                    <td class="value">
                        [{isys type="f_dialog" name="C__MODULE__JDISC__IMPORT__FILTER" p_strClass="normal" p_bSort=false}]
                    </td>
                </tr>
                [{foreach from=$filter_files item='filter_file'}]
                    [{include file=$filter_file}]
                [{/foreach}]
            [{/if}]
            <tr>
                <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__IMPORT__PROFILE' ident='LC__MODULE__JDISC__IMPORT__PROFILE' mandatory=true}]</td>
                <td class="value">
                    [{isys type="f_dialog" name="C__MODULE__JDISC__IMPORT__PROFILE" p_strClass="normal" p_bDbFieldNN=true}]
                </td>
            </tr>
            <tr>
                <td class="key" style="vertical-align: top;">[{isys type='f_label' name='C__MODULE__JDISC__IMPORT__MODE' ident='LC__MODULE__JDISC__IMPORT__MODE' mandatory=true}]</td>
                <td>
                    [{isys type="f_dialog" name="C__MODULE__JDISC__IMPORT__MODE" p_strClass="normal" p_bDbFieldNN=true}]
	                <br class="cb" />
                    <dl class="ml20 mt10">
                        <dt class="text-bold">[{isys type='lang' ident='LC__MODULE__JDISC__IMPORT__MODE'}]:</dt>
                        <dd style="margin-left: 2em;">[{isys type='lang' p_bHtmlEncode="0" ident='LC__MODULE__JDISC__IMPORT__MODE__DESCRIPTION'}]</dd>
                    </dl>
                </td>
            </tr>
            [{if !$ip_unique_check}]
            <tr>
                <td class="key">[{isys type="f_label" name="C__MODULE__JDISC__IMPORT__IP_CONFLICTS" ident="LC__MODULE__JDISC__IMPORT__OVERWRITE_IP_CONFLICTS"}]</td>
                <td class="value">
                    [{isys type="f_dialog" name="C__MODULE__JDISC__IMPORT__IP_CONFLICTS" p_strClass="normal" p_bDisabled=$ip_unique_check p_bDbFieldNN=true}]
                </td>
            </tr>
            [{/if}]
            <tr>
                <td class="key"><label for="module-jdisc-import-detailed-logging">[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__LOGGING"}]</label></td>
                <td class="value pl20">
                    <select name="" id="module-jdisc-import-detailed-logging" class="normal">
                        <option value="0" selected="selected">[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__LOGGING_LESS"}]</option>
                        <option value="1">[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__LOGGING_DETAIL"}]</option>
                        <option value="2">[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__LOGGING_DEBUG"}]</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="key"><label for="module-jdisc-import-detailed-logging">[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__CREATE_SEARCH_INDEX"}]</label></td>
                <td class="value pl20"><input type="checkbox" checked="checked" id="C__MODULE__JDISC__IMPORT__CREATE_SEARCH_INDEX" name="C__MODULE__JDISC__IMPORT__CREATE_SEARCH_INDEX"></td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr class="mt5 mb5">
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <button type="button" name="import" id="C__MODULE__JDISC__IMPORT__BUTTON" class="ml20 btn">[{isys type='lang' ident='LC__MODULE__JDISC__START_IMPORT'}]</button>
                    <span class="ml10 hide">[{isys type="lang" ident="LC__UNIVERSAL__IMPORT_IN_PROGRESS"}]</span>
                </td>
            </tr>
        </table>

	    <div id="module-jdisc-import-message" class="m10 p5 hide"></div>

        <input type="hidden" id="module-jdisc-import-filter-type" value="">
        <input type="hidden" id="module-jdisc-import-filter-data" value="">

        <fieldset class="overview">
            <legend>
                <span>[{isys type='lang' ident='LC__MODULE__JDISC__IMPORT__RESULT'}]</span>
            </legend>
            <div id="module-jdisc-import-log" class="mt5 p10" style="border-top: none; font-family: 'Lucida Console','Monaco',Courier New, monospace;">[{isys type="lang" ident="LC__UNIVERSAL__WAITING"}]</div>
        </fieldset>
    </div>
    <div id="discovery">
        [{include file=$discovery_tpl}]
    </div>


	<script type="text/javascript">

        [{if !$is_connected}]
            $('C__MODULE__JDISC__IMPORT__BUTTON').addClassName('disabled');
            document.observe('dom:loaded', function(){
               idoit.Notify.error('[{$error}]');
            });
        [{/if}]

        (function () {
            "use strict";

            $('C__MODULE__JDISC__IMPORT__BUTTON').on('click', function () {

                if(this.hasClassName('disabled')) return;

                var message_container = $('module-jdisc-import-message').update().removeClassName('box-red').removeClassName('box-green'),
                        log_container = $('module-jdisc-import-log').update('[{isys type='lang' ident='LC__UNIVERSAL__LOADING'}]');

                this.update(new Element('img', {src:'[{$dir_images}]ajax-loading.gif', className:'mr5 vam'}))
                        .insert(new Element('span', {className:'vam'}).update('[{isys type='lang' ident='LC__UNIVERSAL__LOADING'}]'))
                        .next('span.hide').removeClassName('hide');

                new Ajax.Request('?call=jdisc&ajax=1&func=import',
                {
                    parameters: {
                        jdisc_server: $('C__MODULE__JDISC__IMPORT__JDISC_SERVERS').value,
                        group: (($('C__MODULE__JDISC__IMPORT__GROUP'))? $('C__MODULE__JDISC__IMPORT__GROUP').value: '-1'),
                        profile: $('C__MODULE__JDISC__IMPORT__PROFILE').value,
                        mode: $('C__MODULE__JDISC__IMPORT__MODE').value,
                        filter_type: $('module-jdisc-import-filter-type').value,
                        filter_data: $('module-jdisc-import-filter-data').value,
                        'detailed-logging': $('module-jdisc-import-detailed-logging').value,
                        'regenerate-index': ($('C__MODULE__JDISC__IMPORT__CREATE_SEARCH_INDEX').checked? 1: 0),
                        overwrite_hostaddress: (($('C__MODULE__JDISC__IMPORT__IP_CONFLICTS'))? $('C__MODULE__JDISC__IMPORT__IP_CONFLICTS').value: 0)
                    },
                    method: "post",
                    onComplete: function (transport) {
                        var json = transport.responseJSON,
                                log_message = [],
                                log,
                                i,
                                error_msg = '';

                        this.update('[{isys type='lang' ident='LC__MODULE__JDISC__START_IMPORT'}]').next('span').addClassName('hide');

                        [{if isset($dirs.log)}]
                            error_msg = '[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__REQUEST_ERROR" values=addslashes($dirs.log)}]';
                        [{else}]
                            error_msg = '[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__REQUEST_ERROR" values="/log"}]';
                        [{/if}]

                        if (typeof transport.responseJSON == 'undefined')
                        {
                            message_container
                                    .addClassName('box-red')
                                    .removeClassName('hide')
                                    .update('[{isys type="lang" ident="LC__MODULE__JDISC__REQUEST_ERROR"}]');

                            log_container.update(error_msg + '<br />' + transport.responseText).addClassName('box-red mt15 m10');
                        }
                        else
                        {
	                        if (json) {
		                        if (json.success) {
			                        message_container
					                        .addClassName('box-green')
					                        .removeClassName('hide')
					                        .update("[{isys type='lang' ident='LC__MODULE__JDISC__IMPORT__SUCCEEDED'}]");

			                        log_container.update('');
			                        if (json.data.stats) {
				                        log_container.update(json.data.stats.replace(/(\#[0-9]+)$/gim,
						                        '<a href="?objID=$1">$1</a>') + '<br /><br />');
			                        }
			                        log_container.insert(log_message.join(''));

		                        }
		                        else {
			                        idoit.Notify.error(json.message);
			                        log_container.update(json.message);
		                        }
	                        }
	                        else
	                        {
		                        log_container.update(error_msg + '<br />' + transport.responseText).addClassName('box-red mt15 m10');
	                        }
                        }
                    }.bind(this)
                });
            });

            $('C__MODULE__JDISC__IMPORT__JDISC_SERVERS').on('change', function(ele) {

                $('switch-server-loader').removeClassName('hide');
                $('C__MODULE__JDISC__IMPORT__BUTTON').addClassName('disabled');

                new Ajax.Request('?call=jdisc&ajax=1&func=get_groups_and_profiles',
                    {
                        parameters: {
                            jdisc_server: ele.findElement().value
                        },
                        method: "post",
                        onComplete: function (transport) {
                            var json = transport.responseJSON;
                            $('switch-server-loader').addClassName('hide');

                            if(json && json.success)
                            {
                                $('C__MODULE__JDISC__IMPORT__BUTTON').removeClassName('disabled');
                                var jdisc_profiles = $H(json.data.profiles);
                                var groups_ele = $('C__MODULE__JDISC__IMPORT__GROUP');
                                var profiles_ele = $('C__MODULE__JDISC__IMPORT__PROFILE');
                                var show_jdisc_groups = false;

                                if(json.data.groups === false)
                                {
                                    if($('jdisc_groups').visible())
                                    {
                                        $('jdisc_groups').hide();
                                    }
                                }
                                else
                                {
                                    if(!$('jdisc_groups').visible())
                                    {
                                        $('jdisc_groups').show();
                                    }
                                    show_jdisc_groups = true;
                                    var jdisc_groups = $H(json.data.groups);
                                }

                                if(show_jdisc_groups)
                                {
                                    if(groups_ele)
                                    {
                                        var cloned_first_element = groups_ele.options[0].cloneNode();
                                        groups_ele.update(cloned_first_element);

                                        jdisc_groups.each(function(ele){
                                            groups_ele.insert(new Element('option', {value:ele.key}).insert(ele.value));
                                        });
                                    }
                                    groups_ele.removeAttribute('disabled');
                                }
                                else
                                {
                                    groups_ele.setAttribute('disabled', 'disabled');
                                }

                                profiles_ele.update('');

                                jdisc_profiles.each(function(ele){
                                    profiles_ele.insert(new Element('option', {value:ele.key}).insert(ele.value));
                                });
                                if($('C__MODULE__JDISC__IMPORT__BUTTON').hasClassName('disabled'))
                                {
                                    $('C__MODULE__JDISC__IMPORT__BUTTON').removeClassName('disabled');
                                }
                            } else {
                                idoit.Notify.error(json.message);

                                if(!$('C__MODULE__JDISC__IMPORT__BUTTON').hasClassName('disabled'))
                                {
                                    $('C__MODULE__JDISC__IMPORT__BUTTON').addClassName('disabled');
                                }
                            }
                        }
                    }
                );
            });

	        if ($('C__MODULE__JDISC__IMPORT__FILTER')) {
		        $('C__MODULE__JDISC__IMPORT__FILTER').on('change', function (ele) {
			        var l_id = ele.findElement().value;

			        $$('.import_filter').each(function (ele) {
				        ele.hide();
			        });
			        $$(' .' + l_id).each(function (ele) {
				        ele.show();
			        });

			        $('module-jdisc-import-filter-type').value = l_id;
		        });
	        }
        }());
	</script>
</div>
<script type="text/javascript">
    new Tabs('objectTabs', {
        wrapperClass: 'browser-tabs',
        contentClass: 'browser-tab-content',
        tabClass:     'text-shadow mouse-pointer'
    });
</script>


