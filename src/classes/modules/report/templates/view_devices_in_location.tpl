<style type="text/css">
    #report_view__devices_in_location div.box {
        width: 100%;
    }
</style>

<div class="p10" id="report_view__devices_in_location">
    <table class="contentTable">
        <tr>
            <td></td>
            <td class="bold">
                <p class="mb5">[{isys type="lang" ident="LC__REPORT__VIEW__DEVICES_IN_LOCATION_TEXT"}]</p>
            </td>
        </tr>
        <tr>
            <td class="key">[{isys type="f_label" name="C__CONTAINER_OBJECT" ident="LC__CMDB__CATG__LOGICAL_UNIT__PARENT"}]</td>
            <td class="value">
            [{isys
                type="f_popup"
                name="C__CONTAINER_OBJECT"
                p_strPopupType="browser_object_ng"
                edit_mode="1"
            }]</td>
        </tr>
        <tr>
            <td class="key">[{isys type="f_label" name="C__OBJECT_TYPES" ident="LC__CMDB__OBJTYPE"}]</td>
            <td class="value">[{isys type="f_dialog" name="C__OBJECT_TYPES" p_bEditMode=1}]</td>
        </tr>
        <tr>
            <td class="key"></td>
            <td>
                <a id="data-loader" class="btn ml20"><img src="[{$dir_images}]icons/silk/database_table.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__LOAD"}]</span></a>
            </td>
        </tr>
    </table>

    <div class="box mt10">
        <h3 class="gradient text-shadow p5">[{isys type="lang" ident="LC__UNIVERSAL__RESULT"}]</h3>
        <div id="devices_in_location" class="p5"></div>
    </div>
</div>

<script type="text/javascript">

    var $devices_in_location = $('devices_in_location');

    $('data-loader').on('click', function () {
        var obj_id = $('C__CONTAINER_OBJECT__HIDDEN').value;

        if (obj_id > 0) {
            new Ajax.Request('[{$ajax_url}]', {
                parameters: {
                    obj_id: obj_id
                },
                method: "post",
                onSuccess: function (transport) {
                    var json = transport.responseJSON,
                        deviceTree = new dTree('devices_in_location', '[{$dir_images}]dtree/'),
                        objtype_icon = '[{$dir_images}]tree/',
                        icon,
                        obj,
                        parent;

                    for (var i in json) {
                        if (json.hasOwnProperty(i)) {
                            obj = json[i];
                            parent = obj.parent;

                            if (obj.isys_obj__id == obj_id) {
                                parent = -1;
                            }

                            // Handling for tree and silk icons
                            if (obj.isys_obj_type__icon && !obj.isys_obj_type__icon.startsWith('images/icons/silk')) {
                                obj.isys_obj_type__icon = objtype_icon + obj.isys_obj_type__icon;
                            }

                            icon = obj.isys_obj_type__icon;

                            if (obj.isys_obj_type__icon == '') {
                                icon = '[{$dir_images}]dtree/page.gif'
                            }

                            deviceTree.add(
                                obj.isys_obj__id,
                                parent,
                                new Element('span', {'data-obj-type-id': obj.isys_obj_type__id}).update(obj.isys_obj__title + ' (' + obj.isys_obj_type__title + ')').outerHTML,
                                '?[{$smarty.const.C__CMDB__GET__OBJECT}]=' + obj.isys_obj__id,
                                '',
                                '',
                                icon,
                                icon,
                                1,
                                '',
                                0,
                                '');
                        }
                    }

                    $devices_in_location.update(deviceTree);

                    // We assign the tree to the global scope (Necessary for toggling the subtrees).
                    window.devices_in_location = deviceTree;

                    // The first entry will always be displayed as "rootNode" - We don't want this.
                    $devices_in_location.select('a.rootNode').invoke('removeClassName', 'rootNode').invoke('addClassName', 'node');

                    $('C__OBJECT_TYPES').simulate('change');
                }
            });
        }

    });

    // If the category dialog changes, we highlight only the selected object-type.
    $('C__OBJECT_TYPES').on('change', function () {
        if (this.value == -1) {
            $devices_in_location.select('div.node').each(function (el) {
                new Effect.Opacity(el, {to: 1, duration: 0.5});
            });
        } else {
            $devices_in_location.select('div.node a.node span:not([data-obj-type-id=' + this.value + '])').each(function (el) {
                new Effect.Opacity(el.up('div.node'), {to: 0.25, duration: 0.5});
            });
            $devices_in_location.select('div.node a.node span[data-obj-type-id=' + this.value + ']').each(function (el) {
                new Effect.Opacity(el.up('div.node'), {to: 1, duration: 0.5});
            });
        }
    });
</script>