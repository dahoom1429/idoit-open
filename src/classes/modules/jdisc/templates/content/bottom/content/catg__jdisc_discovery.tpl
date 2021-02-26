<div>
    <table class="contentTable">
        <tr>
            <td class="key">[{isys type='f_label' name='C__CMDB__CATG__JDISC_DISCOVERY__SERVER' ident='LC__CMDB__CATG__JDISC_DISCOVERY__JDISC_SERVER'}]</td>
            <td class="value">
                [{isys type='f_dialog' p_bDbFieldNN=1 name='C__CMDB__CATG__JDISC_DISCOVERY__SERVER' p_bEditMode=1}]
            </td>
        </tr>

        <tr>
            <td class="key">[{isys type='f_label' name='C__CMDB__CATG__JDISC_DISCOVERY__PROFILE' ident='LC__CMDB__CATG__JDISC_DISCOVERY__JDISC_PROFILE'}]</td>
            <td class="value">
                [{isys type='f_dialog' name='C__CMDB__CATG__JDISC_DISCOVERY__PROFILE' p_bEditMode=1}]
            </td>
        </tr>

        [{if !$ip_unique_check}]
        <tr>
            <td class="key">[{isys type="f_label" name="C__CMDB__CATG__JDISC_DISCOVERY__IP_CONFLICTS" ident="LC__MODULE__JDISC__IMPORT__OVERWRITE_IP_CONFLICTS"}]</td>
            <td class="value">
                [{isys type="f_dialog" name="C__CMDB__CATG__JDISC_DISCOVERY__IP_CONFLICTS" p_bEditMode=1 p_bDisabled=$ip_unique_check p_bDbFieldNN=true}]
            </td>
        </tr>
        [{/if}]

        <tr>
            <td class="key">[{isys type='f_label' name='C__CMDB__CATG__JDISC_DISCOVERY__MODE' ident='LC__CMDB__CATG__JDISC_DISCOVERY__UPDATE_MODE'}]</td>
            <td>
                [{isys type="f_dialog" name="C__CMDB__CATG__JDISC_DISCOVERY__MODE" p_bEditMode=1 p_bDbFieldNN=true}]
            </td>
        </tr>

        <tr>
            <td class="key">[{isys type='f_label' name='C__CMDB__CATG__JDISC_DISCOVERY__TARGET_TYPE' description="LC__CMDB__CATG__JDISC_DISCOVERY__TARGET_TYPE__DESCRIPTION" ident='LC__CMDB__CATG__JDISC_DISCOVERY__TARGET_TYPE'}]</td>
            <td>
                [{isys type="f_dialog" name="C__CMDB__CATG__JDISC_DISCOVERY__TARGET_TYPE" p_bEditMode=1 p_bDbFieldNN=true}]
            </td>
        </tr>

        <tr>
            <td class="key"><label for="module-jdisc-import-detailed-logging">[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__LOGGING"}]</label></td>
            <td class="value pl20">
                <select name="" id="module-jdisc-import-detailed-logging" class="input input-small">
                    <option value="0" selected="selected">[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__LOGGING_LESS"}]</option>
                    <option value="1">[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__LOGGING_DETAIL"}]</option>
                    <option value="2">[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__LOGGING_DEBUG"}]</option>
                </select>
            </td>
        </tr>

        <tr>
            <td class="key">

            </td>
            <td class="value jdisc-discovery pl20">
                <button type="button" class="btn" id="jdisc-discovery-scan">[{isys type="lang" ident="LC__CMDB__CATG__JDISC_DISCOVERY__SCAN"}]</button>
                <button type="button" class="btn" id="jdisc-discovery-update">[{isys type="lang" ident="LC__CMDB__CATG__JDISC_DISCOVERY__UPDATE_OBJECT"}]</button>
                <button type="button" class="btn" id="jdisc-discovery-scan-update">[{isys type="lang" ident="LC__CMDB__CATG__JDISC_DISCOVERY__SCAN_UPDATE_OBJECT"}]</button>
                <span class="ml10 hide" id="jdisc-discovery-progress-text">[{isys type="lang" ident="LC__UNIVERSAL__IMPORT_IN_PROGRESS"}]</span>
            </td>
        </tr>
    </table>
    <div id="module-jdisc-import-message" class="m10 p5 hide"></div>
    <fieldset class="overview hide" id="jdisc-discovery-import-container">
        <legend>
            <span>
                [{isys type="lang" ident="LC__CMDB__CATG__JDISC_DISCOVERY__IMPORT_RESULT"}]
                <img src="[{$dir_images}]ajax-loading.gif" id="jdisc-discovery-import-container-loader" class="ml5 vam hide">
            </span>
        </legend>
        <div id="jdisc-discovery-import-container-log" class="mt5 p10" style="border-top: none; font-family: 'Lucida Console','Monaco',Courier New, monospace;">
            [{isys type="lang" ident="LC__UNIVERSAL__WAITING"}]
        </div>
    </fieldset>
    <fieldset class="overview hide" id="jdisc-discovery-scan-container">
        <legend>
            <span>
                [{isys type="lang" ident="LC__CMDB__CATG__JDISC_DISCOVERY__DISCOVERY_LOG"}]
                <img src="[{$dir_images}]ajax-loading.gif" id="jdisc-discovery-scan-container-loader" class="ml5 vam hide">
                <button type="button" class="btn hide" id="jdisc-discovery-scan-container-cancel">Cancel Scan</button>
            </span>
        </legend>
        <div id="jdisc-discovery-scan-container-log" class="mt5 p10" style="border-top: none">
            [{isys type="lang" ident="LC__UNIVERSAL__WAITING"}]
        </div>
    </fieldset>

    <input type="hidden" id="C__MODULE__JDISC__IMPORT__FILTER__ADDRESS" value="[{$primary_ip}]">
    <input type="hidden" id="C__MODULE__JDISC__IMPORT__FILTER__HOSTNAME" value="[{$primary_hostname}]">
    <input type="hidden" id="C__MODULE__JDISC__IMPORT__OBJECT_ID" value="[{$object_id}]">
    <input type="hidden" id="C__MODULE__JDISC__IMPORT__OBJECTTYPE_ID" value="[{$objectTypeID}]">

</div>
<script>
    (function () {
        "use strict";

        if($('C__CMDB__CATG__JDISC_DISCOVERY__SERVER')) {
            var scan_update = false;
	        var web_service_active = false;
	        // Ajax request for jdisc scan
            var req = null;

            /*
             * Hide or show the buttons
             */
            var handle_jdisc_discovery_buttons = function(p_import_active, p_webservice_active) {

	            if(p_import_active)
	            {
		            if($('jdisc-discovery-update').hasClassName('disabled')) {
			            $('jdisc-discovery-update').removeAttribute('disabled');
			            $('jdisc-discovery-update').removeClassName('disabled');
		            }
	            }
	            else
	            {
		            if(!$('jdisc-discovery-update').hasClassName('disabled')) {
			            $('jdisc-discovery-update').setAttribute('disabled', 'disabled');
			            $('jdisc-discovery-update').addClassName('disabled');
		            }
	            }

	            if(p_webservice_active)
	            {
		            if($('jdisc-discovery-scan').hasClassName('disabled')) {
			            $('jdisc-discovery-scan').removeAttribute('disabled');
			            $('jdisc-discovery-scan').removeClassName('disabled');
		            }

		            if($('jdisc-discovery-scan-update').hasClassName('disabled')) {
			            $('jdisc-discovery-scan-update').removeAttribute('disabled');
			            $('jdisc-discovery-scan-update').removeClassName('disabled');
		            }
	            }
	            else
	            {
		            if(!$('jdisc-discovery-scan').hasClassName('disabled')) {
			            $('jdisc-discovery-scan').setAttribute('disabled', 'disabled');
			            $('jdisc-discovery-scan').addClassName('disabled');
		            }

		            if(!$('jdisc-discovery-scan-update').hasClassName('disabled')) {
			            $('jdisc-discovery-scan-update').setAttribute('disabled', 'disabled');
			            $('jdisc-discovery-scan-update').addClassName('disabled');
		            }
	            }
            };

            /*
             * Switch groups and profiles for the selected jdisc server
             */
            $('C__CMDB__CATG__JDISC_DISCOVERY__SERVER').on('change', function (ele) {

                if (!$('jdisc-discovery-update').hasClassName('disabled')) {
                    $('C__CMDB__CATG__JDISC_DISCOVERY__PROFILE').setAttribute('disabled', 'disabled');
                    handle_jdisc_discovery_buttons(false, false);
                }

                if($('C__MODULE__JDISC__IMPORT__FILTER__ADDRESS').value != '') {
                    new Ajax.Request('?call=jdisc&ajax=1&func=get_groups_and_profiles',
                        {
                            parameters: {
                                'jdisc_server': ele.findElement().value,
                                'object_type': $('C__MODULE__JDISC__IMPORT__OBJECTTYPE_ID').value,
                                'check_web_service': 1
                            },
                            method: "post",
                            onSuccess: function (transport) {
                                var json = transport.responseJSON;

                                if (json.success) {
                                    var jdisc_profiles = $H(json.data.profiles);
                                    var default_profile = json.data.default_profile;
                                    var profiles_ele = $('C__CMDB__CATG__JDISC_DISCOVERY__PROFILE');
	                                web_service_active = json.data.web_service_active;

	                                profiles_ele.update('');
                                    profiles_ele.insert(new Element('option', {value:'-1'}).insert('-'));

                                    jdisc_profiles.each(function (ele) {
                                        if(ele.key === default_profile)
                                        {
                                            profiles_ele.insert(new Element('option', {'value':ele.key, 'selected':true, 'class':'bold'}).insert(ele.value + " ([{isys type='lang' ident='LC__CMDB__CATG__JDISC_DISCOVERY__DEFAULT_PROFILE'}])"));
                                        }
                                        else
                                        {
                                            profiles_ele.insert(new Element('option', {value:ele.key}).insert(ele.value));
                                        }
                                    });

	                                handle_jdisc_discovery_buttons(true, web_service_active);
                                    if(!web_service_active)
                                    {
                                        window.idoit.Notify.error('[{isys type="lang" ident="LC__MODULE__JDISC__DISCOVERY__CONNECTION_FAILED"}]')
                                    }
                                    $('C__CMDB__CATG__JDISC_DISCOVERY__PROFILE').removeAttribute('disabled');
                                }
                                else {
                                    window.idoit.Notify.error(json.message);
                                }
                            }
                        }
                    );
                }
                else
                {
                    $('module-jdisc-import-message').addClassName('box-red');
                    $('module-jdisc-import-message').removeClassName('hide');
                    $('module-jdisc-import-message').update('[{isys type="lang" ident="LC__CMDB__CATG__JDISC_DISCOVERY__NO_PRIMARY_IP_ADDRESS_DEFINED"}]')
                }
            });

            /*
             * Import device to i-doit
             */
            $('jdisc-discovery-update').on('click', function(){

                if(this.hasClassName('disabled')) return;

                $('jdisc-discovery-progress-text').removeClassName('hide');

                handle_jdisc_discovery_buttons(false, false);
                start_jdisc_import();
            });

            var start_jdisc_import = function() {
                $('jdisc-discovery-import-container-loader').removeClassName('hide');
                $('jdisc-discovery-import-container').removeClassName('hide');

                var message_container = $('module-jdisc-import-message').update().removeClassName('box-red').removeClassName('box-green'),
                        log_container = $('jdisc-discovery-import-container-log').update('[{isys type='lang' ident='LC__UNIVERSAL__LOADING'}]');

                new Ajax.Request('?call=jdisc&ajax=1&func=import',
                {
                    parameters: {
                        'jdisc_server': $('C__CMDB__CATG__JDISC_DISCOVERY__SERVER').value,
                        'group': '-1',
                        'profile': $('C__CMDB__CATG__JDISC_DISCOVERY__PROFILE').value,
                        'mode': $('C__CMDB__CATG__JDISC_DISCOVERY__MODE').value,
	                    'objID': '[{$smarty.get.objID|strip_tags|escape}]',
                        'detailed-logging': $('module-jdisc-import-detailed-logging').value,
                        'overwrite_hostaddress': (($('C__CMDB__CATG__JDISC_DISCOVERY__IP_CONFLICTS'))? $('C__CMDB__CATG__JDISC_DISCOVERY__IP_CONFLICTS').value: 0)
                    },
                    method: "post",
                    onSuccess: function (transport) {
                        var json = transport.responseJSON,
                                log_message = [],
                                log,
                                i;

                        $('jdisc-discovery-import-container-loader').addClassName('hide');

                        if (typeof transport.responseJSON == 'undefined') {
                            message_container
                                    .addClassName('box-red')
                                    .removeClassName('hide')
                                    .update('[{isys type="lang" ident="LC__MODULE__JDISC__REQUEST_ERROR"}]');

                            log_container.update();
                        } else {
                            if (json.success) {
                                message_container
                                        .addClassName('box-green')
                                        .removeClassName('hide')
                                        .update("[{isys type='lang' ident='LC__MODULE__JDISC__IMPORT__SUCCEEDED'}]");

                                log_container.update('');
                                if (json.data.stats)
                                {
                                    log_container.update(json.data.stats.replace(/(\#[0-9]+)$/gim, '<a href="?objID=$1">$1</a>') + '<br /><br />');
                                }
                                log_container.insert(log_message.join(''));

                            } else {
                                $('jdisc-discovery-import-container-log').update('[{isys type="lang" ident="LC__UNIVERSAL__WAITING"}]');
                                idoit.Notify.error(json.message);
                            }
                        }

                        // Enable all buttons and hide the progress text
                        if(!scan_update){
                            handle_jdisc_discovery_buttons(true, web_service_active);
                            $('jdisc-discovery-progress-text').addClassName('hide');
                        }
                    }
                });
            };

            /*
             * @todo
             * Does not work at the moment because there is no soap call for cancelling a jdisc scan of a device
             */
            $('jdisc-discovery-scan-container-cancel').on('click', function(){
                if(req)
                {
                    req.transport.abort();

                    handle_jdisc_discovery_buttons(true, web_service_active);
                    //$('jdisc-discovery-scan-container-cancel').addClassName('hide');
                    $('jdisc-discovery-scan-container-loader').addClassName('hide');
                    req = null;
                }
            });

            /*
             * Scan device
             */
            $('jdisc-discovery-scan').on('click', function(){

                if(this.hasClassName('disabled')) return;

                $('jdisc-discovery-progress-text').removeClassName('hide');

                // Disable buttons while scanning
                handle_jdisc_discovery_buttons(false, false);
                // Start jdisc scan
                start_jdisc_scan();
            });

            var intervalTimer;

            var intervalFunc = function (req_params, timeout, retries){
                var timeoutValue = parseInt($('configuredTimeout').innerHTML);

                if (timeoutValue == 0) {
                    checkScanQueue(req_params, timeout, retries);
                } else {
                    timeoutValue = timeoutValue - 1;
                    $('configuredTimeout').innerHTML = timeoutValue;
                }
            }

            var startIntervalFunc = function (req_params, timeout, retries) {
                intervalTimer = setInterval(function (){
                    intervalFunc(req_params, timeout, retries);
                }, 1000);
            }

            var checkScanQueue = function (req_params, timeout, retries) {
                req_params[0].innerHTML += 'Check if device is still in the queue.<br />';
                clearInterval(intervalTimer);

                new Ajax.Request('?call=jdisc&ajax=1&func=checkQueue', {
                    method: "post",
                    parameters: {
                        'host': $('C__CMDB__CATG__JDISC_DISCOVERY__SERVER').value
                    },
                    onSuccess: function (transport){
                        var json = transport.responseJSON;
                        var timeoutRetries = parseInt($('configuredRetries').innerHTML);

                        // Check if device is still in queue
                        if (json.success) {
                            req_params[0].innerHTML += 'Device is not in the queue anymore starting import.<br />';
                            start_jdisc_import();
                            return;
                        }

                        if (retries >= 1)
                        {
                            retries = parseInt(retries) - 1;

                            req_params[0].innerHTML += 'Device is in queue retries left ' + retries + '.<br />';
                            $('configuredTimeout').innerHTML = timeout;
                            $('configuredRetries').innerHTML = retries;

                            startIntervalFunc(req_params, timeout, retries);
                        } else {
                            req_params[0].innerHTML += 'Device is still in queue please try the jdisc import later.<br />';
                        }
                    }.bind(req_params)
                });
            }

            /**
             * Function to scan the device
             */
            var start_jdisc_scan = function() {
                $('jdisc-discovery-scan-container-loader').removeClassName('hide');
                $('jdisc-discovery-scan-container').removeClassName('hide');
                //$('jdisc-discovery-scan-container-cancel').removeClassName('hide');

                var d = $('jdisc-discovery-scan-container-log');
                var old_text = '';
                var req_params = [d, old_text, '', false];
                d.innerHTML = '';

                var req_url = '?call=jdisc&ajax=1&func=discover&type=discover_device';

                req = new Ajax.Request(req_url,
                {
                    method: "post",
                    parameters:{
                        'objID': $('C__MODULE__JDISC__IMPORT__OBJECT_ID').value,
                        'objTypeID': $('C__MODULE__JDISC__IMPORT__OBJECTTYPE_ID').value,
                        'host': $('C__CMDB__CATG__JDISC_DISCOVERY__SERVER').value,
                        'hostaddress': $('C__MODULE__JDISC__IMPORT__FILTER__ADDRESS').value,
                        'hostname': $('C__MODULE__JDISC__IMPORT__FILTER__HOSTNAME').value,
                        'targetType': $('C__CMDB__CATG__JDISC_DISCOVERY__TARGET_TYPE').value
                    },
                    onComplete: function(transport){

                        if (req_params[2].blank()) {
                            req_params[2] = transport.responseText.substring(req_params[1].length);
                        }

                        var json = JSON.parse(req_params[2]);

                        $('jdisc-discovery-scan-container-loader').addClassName('hide');
                        //$('jdisc-discovery-log-scan-cancel').addClassName('hide');

                        if(!scan_update) {
                            handle_jdisc_discovery_buttons(true, web_service_active);
                            $('jdisc-discovery-progress-text').addClassName('hide');
                        }

                        if(json.success)
                        {
                            idoit.Notify.success(json.message, {life:5});
                            // Trigger import if the scan update button has been clicked
                            if(scan_update)
                            {
                                scan_update = false;

                                if (json.data) {
                                    var timeout = json.data['timeout'];
                                    var retries = json.data['retries'];

                                    req_params[0].innerHTML += 'Waiting for configured timeout <span id="configuredTimeout" class="bold">' +
                                                               timeout + '</span>/' + timeout +
                                                               '. Retries <span id="configuredRetries" class="bold">' + retries +'</span>/ ' + retries + '.<br />';

                                    startIntervalFunc(req_params, timeout, retries);
                                } else{
                                    start_jdisc_import();
                                }
                            }
                        }
                        else
                        {
                            idoit.Notify.error(json.message, {life:5});
                            handle_jdisc_discovery_buttons(true, web_service_active);
                            $('jdisc-discovery-scan-container').addClassName('hide');
                            $('jdisc-discovery-progress-text').addClassName('hide');
                        }
                    },
                    onInteractive: function (transport) {
                        var new_text = transport.responseText.substring(req_params[1].length);

                        if(new_text != null && new_text != 'null' && new_text != 'false' && !new_text.isJSON())
                        {
                            req_params[0].innerHTML += new_text + "<br />";
                            req_params[1] = transport.responseText;
                        }
                        if(new_text.isJSON())
                        {
                            req_params[2] = new_text;
                        }
                        req_params[3] = true;
                    }
                });
            };

            /*
             * Scan device and immediately import device from jdisc to i-doit
             */
            $('jdisc-discovery-scan-update').on('click', function(){
                if(this.hasClassName('disabled')) return;

                handle_jdisc_discovery_buttons(false, false);
                scan_update = true;
                $('jdisc-discovery-progress-text').removeClassName('hide');

                if(!$('module-jdisc-import-message').hasClassName('hide'))
                {
                    $('module-jdisc-import-message').addClassName('hide');
                    $('jdisc-discovery-import-container-log').update('[{isys type="lang" ident="LC__UNIVERSAL__WAITING"}]');
                }
                // Start JDisc scan
                start_jdisc_scan();
            });

            $('C__CMDB__CATG__JDISC_DISCOVERY__SERVER').simulate('change');
        }
    }());
</script>