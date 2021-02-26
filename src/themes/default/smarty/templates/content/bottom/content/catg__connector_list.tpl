<table width="100%" class="gradient text-shadow border-bottom">
    <colgroup>
        <col width="51%" />
    </colgroup>
    <tr>
        <td><h3 class="m5">[{isys type="lang" ident="LC__CMDB__CATG__CONNECTOR__FRONT"}]</h3></td>
        <td><h3 class="m5">[{isys type="lang" ident="LC__CMDB__CATG__CONNECTOR__BACK"}]</h3></td>
    </tr>
</table>

[{if $inputs->num_rows() > 0}]

    [{while $l_row = $inputs->get_row()}]
    [{assign var="row" value=$list_dao->modify_row($l_row)}]
    [{assign var="l_sibling_id" value=$l_row.isys_catg_connector_list__id}]
    [{assign var="siblings" value=$dao_connector->get_data_by_sibling($l_sibling_id, $smarty.session.cRecStatusListView, isys_glob_get_param("sort"), isys_glob_get_param("dir"))}]

    <div class="connector p10">
        <table id="inputs" cellpadding="0" cellspacing="0" width="100%">
            <colgroup>
                <col width="48%" />
                <col width="3%" />
            </colgroup>
            <tr>
                <td class="border" style="vertical-align: top;">
                    <table class="mainTable">
                        <thead>
                        <tr>
                            [{counter assign="counter"}]
                            <th><input class="check_input" type="checkbox" onClick="CheckAllBoxes(this, 'check_input');" value="X" /></th>

                            [{foreach from=$list_dao->get_fields() item="header" key="header_key"}]
                            <th title="[{isys type="lang" ident="LC__UNIVERSAL__SORT"}]">
                                <a href="javascript:" onclick="$('dir').setValue('[{$list_dao->get_order()}]'); $('sort').setValue('[{$header_key}]'); form_submit();">
                                    [{$header}]
                                </a>
                            </th>
                            [{/foreach}]
                        </tr>
                        </thead>
                        <tbody>
                        <tr data-connector-id="[{$row.isys_catg_connector_list__id}]">
                            <td><input type="checkbox" class="checkbox check_input" name="id[]" value="[{$row.isys_catg_connector_list__id}]" /></td>

                            [{foreach from=$list_dao->get_fields() item="header" key="header_key"}]
                            <td data-link="[{$conn_link}]&cateID=[{$row.isys_catg_connector_list__id}]">[{$row.$header_key}]</td>
                            [{/foreach}]
                        </tr>
                        </tbody>
                    </table>
                </td>

                <td>
                    <div class="dash"></div>
                </td>

                <td class="border" style="background-color: #ccc; vertical-align: top;">

                    [{if $siblings->num_rows() > 0}]

                    <table class="mainTable" cellpadding="0" cellspacing="0">
                        <thead>
                        <tr>
                            [{counter assign="counter"}]
                            <th><input class="check_output" type="checkbox" onClick="CheckAllBoxes(this, 'check_output');" value="X" /></th>

                            [{foreach from=$list_dao->get_fields() item="header" key="header_key"}]
                            <th title="[{isys type="lang" ident="LC__UNIVERSAL__SORT"}]">
                                <a href="javascript:" onclick="    $('dir').setValue('[{$list_dao->get_order()}]'); $('sort').setValue('[{$header_key}]'); form_submit();">
                                    [{$header}]
                                </a>
                            </th>
                            [{/foreach}]
                        </tr>
                        </thead>
                        <tbody>
                        [{while $row = $siblings->get_row()}]
                            [{assign var="row" value=$list_dao->modify_row($row)}]

                            <tr data-connector-id="[{$row.isys_catg_connector_list__id}]">
                                <td><input type="checkbox" class="checkbox check_output" name="id[]" value="[{$row.isys_catg_connector_list__id}]" /></td>

                                [{foreach from=$list_dao->get_fields() item="header" key="header_key"}]
                                <td data-link="[{$conn_link}]&cateID=[{$row.isys_catg_connector_list__id}]">[{$row.$header_key}]</td>
                                [{/foreach}]
                            </tr>
                            [{/while}]
                        </tbody>
                    </table>
                    [{else}]

                    <h3 class="p10 text-shadow">[{isys type="lang" ident="LC__UNIVERSAL__UNASSIGNED"}]</h3>

                    [{/if}]
                </td>
            </tr>
        </table>
    </div>
    [{/while}]
    [{/if}]

[{if $outputs->num_rows() > 0}]
    <div class="connector m10">
        <table id="outputs" cellpadding="0" cellspacing="0" width="100%">
            <colgroup>
                <col width="48%" />
                <col width="3%" />
            </colgroup>
            <tr>
                <td class="border" style="background-color: #ccc; vertical-align: top;">
                    <h3 class="p10 text-shadow">[{isys type="lang" ident="LC__UNIVERSAL__UNASSIGNED"}]</h3>
                </td>
                <td>
                    <div class="dash"></div>
                </td>
                <td class="border" style="vertical-align: top;">

                    <table class="mainTable" cellpadding="0" cellspacing="0">
                        <thead>
                        <tr>
                            [{counter assign="counter"}]
                            <th><input class="check_output" type="checkbox" onClick="CheckAllBoxes(this, 'check_output');" value="X" /></th>

                            [{foreach from=$list_dao->get_fields() item="header" key="header_key"}]
                            <th title="[{isys type="lang" ident="LC__UNIVERSAL__SORT"}]">
                                <a href="javascript:"
                                   onclick="document.isys_form.dir.value='[{$list_dao->get_order()}]'; document.isys_form.sort.value='[{$header_key}]'; form_submit();">
                                    [{$header}]
                                    [{if $smarty.post.sort eq $header_key}]<img src="images/[{$smarty.post.dir|lower|default:"desc"}].png" height="10" border="0" />[{/if}]
                                </a>
                            </th>
                            [{/foreach}]

                        </tr>
                        </thead>
                        <tbody>
                        [{while $row = $outputs->get_row()}]
                            [{assign var="row" value=$list_dao->modify_row($row)}]

                            <tr data-connector-id="[{$row.isys_catg_connector_list__id}]">

                                <td><input type="checkbox" class="checkbox check_output" name="id[]" value="[{$row.isys_catg_connector_list__id}]" /></td>

                                [{foreach from=$list_dao->get_fields() item="header" key="header_key"}]
                                <td data-link="[{$conn_link}]&cateID=[{$row.isys_catg_connector_list__id}]">[{$row.$header_key}]</td>
                                [{/foreach}]

                            </tr>
                            [{/while}]
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    [{/if}]


[{if $inputs->num_rows() <=0 && $outputs->num_rows() <= 0}]

    <h3 class="p10">[{isys type="lang" ident="LC__CATG__CONNECTOR__NO_CONNECTORS"}]</h3>

    [{/if}]

<div class="hide">
    [{isys
        title='LC__POPUP__BROWSER__UI_CON_SELECTION'
        name='C__CATG__CONNECTOR__ASSIGNED_CONNECTOR'
        type='f_popup'
        p_strPopupType='browser_cable_connection_ng'
        secondSelection=true
        secondList='isys_cmdb_dao_category_g_connector::object_browser'
        multiselection=true
        edit_mode=true
        callback_accept="window.createNewConnection();"
        usageWarning="LC__BROWSER_CABLE_CONNECTION__ERROR"}]
</div>

<style>
    .mainTable tr .input-group {
        opacity: 0;
    }

    .mainTable tr:hover .input-group {
        opacity: 1;
    }

    .mainTable .input-group .input-group-addon {
        padding: 1px;
        height: 18px;
        min-width: 18px;
    }

    .mainTable .input-group .input-group-addon img {
        width: 14px;
        height: 14px;
    }

    #scroller {
        overflow-x: auto;
    }
</style>

<script>
    (function () {
        'use strict';

        var currentConnector = 0;

        $('scroller').on('click', 'td[data-link]', function (ev) {
            var $target = ev.findElement('.input-group-addon-clickable'),
                $tr, $connector, connId;

            //  Only follow links, if we didn't click an any function-buttons.
            if (!$target)
            {
                document.location = ev.findElement('td').readAttribute('data-link');
                return;
            }

            $tr = $target.up('tr');
            connId = $tr.readAttribute('data-connector-id');
            $connector = $tr.down('.connected-connector');

            if ($target.hasClassName('detach'))
            {
                if ($target.up('.input-group').readAttribute('data-connector-id') == '0')
                {
                    idoit.Notify.info('[{isys type="lang" ident="LC__CABLE_CONNECTION__NO_CONNECTOR_AVAILABLE"}]', {life: 5});
                    return;
                }

                if (confirm('[{isys type="lang" ident="LC__CABLE_CONNECTION__POPUP_CONNECTION_DISCONNECT_SELECTED_CONNECTOR" p_bHtmlEncode=false}]'))
                {
                    $connector
                            .update(new Element('img', {
                                src:       window.dir_images + 'ajax-loading.gif',
                                className: 'mr5',
                                style:     'width:12px; height:12px;'
                            }))
                            .insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'));

                    new Ajax.Request('?ajax=1&call=connector&method=detachConnector', {
                        parameters: {
                            connector: connId
                        },
                        method:     'post',
                        onSuccess:  function (xhr) {
                            var json = xhr.responseJSON;

                            if (is_json_response(xhr, true))
                            {
                                if (json.success)
                                {
                                    $connector.update('[{isys_tenantsettings::get('gui.empty_value', '-')}]');
                                    $tr.down('.cable-name').update();
                                    $target.up('.input-group').writeAttribute('data-connector-id', 0);
                                }
                                else
                                {
                                    idoit.Notify.error(json.message, {sticky: true});
                                }
                            }
                        }
                    });
                }
            }
            else
            {
                currentConnector = connId;
                $('C__CATG__CONNECTOR__ASSIGNED_CONNECTOR__HIDDEN').setValue($target.up('.input-group').readAttribute('data-connector-id'));
                $('C__CATG__CONNECTOR__ASSIGNED_CONNECTOR__VIEW').next('.input-group-addon-clickable').simulate('click');
            }
        });

        // @see ID-4592 and ID-4647
        window.createNewConnection = function () {
            var connector  = $F('C__CATG__CONNECTOR__ASSIGNED_CONNECTOR__HIDDEN'),
                $tr        = $('scroller').down('tr[data-connector-id="' + currentConnector + '"]'),
                $connector = $tr.down('span.connected-connector');

            if (connector > 0)
            {
                $connector
                    .update(new Element('img', {
                        src:       window.dir_images + 'ajax-loading.gif',
                        className: 'mr5',
                        style:     'width:12px; height:12px;'
                    }))
                    .insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'));

                new Ajax.Request('?ajax=1&call=connector&method=connectConnectors', {
                    parameters: {
                        a: currentConnector,
                        b: connector
                    },
                    method:     'post',
                    onSuccess:  function (xhr) {
                        var json = xhr.responseJSON;

                        if (is_json_response(xhr, true))
                        {
                            if (json.success)
                            {
                                $connector
                                    .update(new Element('a', {
                                        id:   'reloaded_' + json.data[0].connId,
                                        href: '?objID=' + json.data[0].objId
                                    }).update(json.data[0].objTitle + ' &raquo; ' + json.data[0].connTitle))
                                    .previous('.input-group').writeAttribute('data-connector-id', json.data[0].connId);

                                new Tip('reloaded_' + json.data[0].connId, '', {
                                    ajax:      {url: '?ajax=1&call=quick_info&objID=' + json.data[0].objId},
                                    delay:     [{isys_usersettings::get('gui.quickinfo.delay', 0.5)}],
                                    className: 'objectinfo'
                                });

                                // Display the connected cable.
                                $tr.down('.cable-name')
                                   .update(new Element('a', {
                                       id:   'reloaded_c_' + json.data[0].cableId,
                                       href: '?objID=' + json.data[0].cableId
                                   }).update(json.data[0].cableTitle));

                                new Tip('reloaded_c_' + json.data[0].cableId, '', {
                                    ajax:      {url: '?ajax=1&call=quick_info&objID=' + json.data[0].cableId},
                                    delay:     [{isys_usersettings::get('gui.quickinfo.delay', 0.5)}],
                                    className: 'objectinfo'
                                });
                            }
                            else
                            {
                                idoit.Notify.error(json.message, {sticky: true});
                            }
                        }
                    }
                });
            }
        };
    })();
</script>
