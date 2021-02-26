<style>
    /* Styles for sexy progress-bar */
    #discovery #discovery-table .progress {
        border: 1px solid #aaa;
        height: 10px;
        border-radius: 2px;
        background: #666;
    }

    #discovery #discovery-table .progress-bar {
        background: #D4ECFF url('[{$dir_images}]gradient.png') repeat-x scroll 0 -5px;
        height: 10px;
    }
</style>
<h3 class="border-top border-bottom gradient p5 text-shadow mt10">
    [{isys type='lang' ident='LC__MODULE__JDISC__IMPORT__OPTIONS'}]
</h3>
<table id="discovery-table" class="contentTable" style="border-top: none;">
    <tr>
        <td class="key">[{isys type='f_label' name='C__MODULE__JDISC__DISCOVERY__JDISC_SERVERS' ident='LC__MODULE__JDISC__DISCOVERY__HOST'}]</td>
        <td class="value">
            [{isys type="f_dialog" name="C__MODULE__JDISC__DISCOVERY__JDISC_SERVERS" p_strClass="normal" p_bDbFieldNN=0 p_bSort=false}]
        </td>
    </tr>
    <tr>
        <td class="key">
            [{isys type='f_label' name='C__MODULE__JDISC__DISCOVERY__DISCOVERY_JOBS' ident='Discovery Jobs'}]
        </td>
        <td class="value">
            [{isys type="f_dialog" name="C__MODULE__JDISC__DISCOVERY__DISCOVERY_JOBS" p_strClass="normal" p_bDbFieldNN=0 p_bSort=false}]
        </td>
    </tr>
    <tr>
        <td class="key">
            [{isys type='f_label' name='C__MODULE__JDISC__DISCOVERY__DISCOVERY_JOBS__DESCRIPTION' ident='LC__MODULE__JDISC__DISCOVERY__JOBS__DISCOVERY_JOB_DESCRIPTION'}]
        </td>
        <td class="value">
            [{isys type='f_textarea' name='C__MODULE__JDISC__DISCOVERY__DISCOVERY_JOBS__DESCRIPTION' p_nRows=5}]
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <button type="button" name="discovery" id="C__MODULE__JDISC__DISCOVERY__BUTTON" class="ml20 btn">[{isys type='lang' ident='LC__MODULE__JDISC__BUTTON__START_DISCOVERY'}]</button>
            <span class="ml10 hide">[{isys type="lang" ident="LC__UNIVERSAL__IMPORT_IN_PROGRESS"}]</span>
        </td>
    </tr>
</table>
<div id="jdisc-discovery-message" class="m10 p5 hide">
</div>

<script type="text/javascript">
    (function () {
        "use strict";

        $('C__MODULE__JDISC__DISCOVERY__BUTTON').on('click', function() {

            this.update(new Element('img', {src:'[{$dir_images}]ajax-loading.gif', className:'mr5 vam'}))
                    .insert(new Element('span', {className:'vam'}).update('[{isys type='lang' ident='LC__UNIVERSAL__LOADING'}]'))
                    .next('span.hide').removeClassName('hide');

            new Ajax.Request('?call=jdisc&ajax=1&func=discover_devices',
                    {
                        parameters: {
                            host: $('C__MODULE__JDISC__DISCOVERY__JDISC_SERVERS').value,
                            job: $('C__MODULE__JDISC__DISCOVERY__DISCOVERY_JOBS').value
                        },
                        method: "post",
                        onSuccess: function (transport) {
                            var json = transport.responseJSON,
                                    i;
                            if(json['success']) {
                                idoit.Notify.success(json['message']);
                            } else {
                                idoit.Notify.error(json['message']);
                            }
                            this.update('[{isys type='lang' ident='LC__MODULE__JDISC__BUTTON__START_DISCOVERY'}]').next('span').addClassName('hide');
                        }.bind(this)
                    });
        });

        var retrieve_discovery_jobs = function() {
            $('C__MODULE__JDISC__DISCOVERY__BUTTON').addClassName('disabled');
            $('C__MODULE__JDISC__DISCOVERY__BUTTON').setAttribute('disabled', 'disabled');

            $('jdisc-discovery-message').update('');
            $('jdisc-discovery-message').addClassName('hide');
            $('jdisc-discovery-message').removeClassName('box-red');

            $('C__MODULE__JDISC__DISCOVERY__DISCOVERY_JOBS').update(new Option('-', '-1'));
            $('C__MODULE__JDISC__DISCOVERY__DISCOVERY_JOBS__DESCRIPTION').update('');

            if($('C__MODULE__JDISC__DISCOVERY__JDISC_SERVERS').value > 0)
            {
                new Ajax.Request('?call=jdisc&ajax=1&func=get_discovery_jobs', {
                    parameters: {
                        host: $('C__MODULE__JDISC__DISCOVERY__JDISC_SERVERS').value
                    },
                    onSuccess: function (transport) {
                        var json = transport.responseJSON;

                        if (json['success'] === true) {
                            $('C__MODULE__JDISC__DISCOVERY__BUTTON').removeClassName('disabled');
                            $('C__MODULE__JDISC__DISCOVERY__BUTTON').removeAttribute('disabled');
                            $('C__MODULE__JDISC__DISCOVERY__DISCOVERY_JOBS').update('');

                            var counter = 0;
                            var selected = '';
                            json['data'].each(function (item) {
                                if(counter == 0){
                                    $('C__MODULE__JDISC__DISCOVERY__DISCOVERY_JOBS__DESCRIPTION').update(item.description);
                                }
                                $('C__MODULE__JDISC__DISCOVERY__DISCOVERY_JOBS').insert(new Option(item.name, Object.toJSON(item)));
                                counter++;
                            });
                        } else {
                            // No jobs found disable discovery
                            if (!$('jdisc-discovery-message').hasClassName('box-red')) {
                                $('jdisc-discovery-message').removeClassName('hide');
                                $('jdisc-discovery-message').addClassName('box-red');
                                $('jdisc-discovery-message').insert(json['message']);
                                $('jdisc-discovery-message').show()
                            } // if
                        }
                    }
                });
            }
        };

        $('C__MODULE__JDISC__DISCOVERY__JDISC_SERVERS').on('change', function() {
            retrieve_discovery_jobs();
        });

        $('C__MODULE__JDISC__DISCOVERY__DISCOVERY_JOBS').on('change', function(){
            if(this.value.isJSON())
            {
                var json = JSON.parse(this.value);
                $('C__MODULE__JDISC__DISCOVERY__DISCOVERY_JOBS__DESCRIPTION').update(json.description);
            }
        });

    }());
</script>