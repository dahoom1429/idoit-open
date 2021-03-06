<input type="hidden" name="request" id="request" value="saveList" />
<input type="hidden" name="changes_in_entry" id="changes_in_entry" value="" />
<input type="hidden" name="changes_in_object" id="changes_in_object" value="" />

<div id="C__MODULE__MULTIEDIT" class="display-container multiedit-display-container">
    <h3 class="gradient border-bottom p10 text-shadow">[{isys type="lang" ident="LC__MULTIEDIT__MULTIEDIT"}] <img class="fr mr10 inactive" id="multiEditLoader" style="margin-top: -1px;" alt="" src="./images/ajax-loading.gif"></h3>

    <div id="multiedit-config" class="display-block">
        <table width="100%" class="contentTable">
            <tr id="multiedit-config-object-category">
                <td class="key">[{isys type="lang" ident="LC__MODULE__MULTIEDIT__OBJECTS_AND_CATEGORY"}]</td>
                <td class="value">
                    [{isys
                    name="C__MULTIEDIT__OBJECTS"
                    type="f_popup"
                    p_strPopupType="browser_object_ng"
                        p_strClass="input-small"
                    p_bEnableMetaMap=0}]
                    [{isys name="C__MULTIEDIT__CATEGORY" p_bEnableMetaMap=0 chosen=1 type="f_dialog" p_strClass="input-small"}]
                    [{isys name="C__MULTIEDIT__FILTER_OBJECT_INFO" type="f_dialog" p_strClass="input-mini"}]
                </td>
            </tr>
            <tr id="multiedit-config-filter">
                <td class="key">[{isys type="f_label" ident="LC_UNIVERSAL__FILTERS" name="C__MULTIEDIT__FILTER"}]</td>
                <td class="value" style="vertical-align: top;" colspan="2">
                    <div id="multiEditFilter" >
                        [{isys name="C__MULTIEDIT__FILTER_PROPERTY" type="f_dialog" p_strClass="input-small"}]

                        <div class="input-group input-size-small ml20">
                            [{isys name="C__MULTIEDIT__FILTER_VALUE" type="f_text" disableInputGroup=true p_bInfoIconSpacer=0}]

                            <div class="attach input-group-addon input-group-addon-clickable" style="border-left: 0px;">
                                <img id="filter" class="execute-filter" src="[{$dir_images}]icons/silk/zoom.png" />
                            </div>
                            <div class="attach input-group-addon input-group-addon-clickable">
                                <img id="resetFilter" class="reset-filter" src="[{$dir_images}]icons/silk/cross.png" />
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div id="multiEditContainer" class="multiedit-output border-top border-grey mt10 display-block">
        <div id="multiEditHeader" class="multiedit-table-header multiedit-table">
        </div>

        <div id="multiEditList" class="multiedit-table-body multiedit-table">
        </div>
    </div>

    <div id="multiEditFooter" class="multiedit-footer">
        <div class="multiedit-footer-changes box-green p5" id="changesNote">
            [{isys type="lang" ident="LC__MULTIEDIT__REGISTERED_CHANGES"}]: <span class="multiedit-footer-changes-counter">0</span>
            <br />
            [{isys type="lang" ident="LC__MODULE__MULTIEDIT__REGISTERED_DISABLED_ROWS"}]: <span class="multiedit-footer-changes-disablerows">0</span>
        </div>

        <div class="m5">
            <p><img src="[{$dir_images}]icons/silk/information.png" class="vam"> [{isys type="lang" ident="LC__MULTIEDIT__PLACEHOLDER_INFO"}]</p>

            <ul>
                <li>[{isys type="lang" ident="LC__MULTIEDIT__PLACEHOLDER_INFO_EXAMPLE_1" p_bHtmlEncode=false}]</li>
                <li>[{isys type="lang" ident="LC__MULTIEDIT__PLACEHOLDER_INFO_EXAMPLE_2" p_bHtmlEncode=false}]</li>
                <li>[{isys type="lang" ident="LC__MULTIEDIT__PLACEHOLDER_INFO_EXAMPLE_3" p_bHtmlEncode=false}]</li>
            </ul>
        </div>
    </div>
</div>

<style type="text/css">
[{include file="`$assetsDir`css/multiedit.css"}]
</style>

<script type="text/javascript">
    document.observe('dom:loaded', function () {
        "use strict";
        var objTypeGroup = '[{$objTypeGroup}]';
        get_tree_object_type(objTypeGroup, false);

        var $menuItems = $('mainMenu').select('li');

        $('mainMenu').select('li').each(function (ele) {
            if (ele.getAttribute('id') == 'menuItem_' + objTypeGroup + '0' || (ele.hasClassName('extras') && objTypeGroup === ''))
            {
                ele.addClassName('active');
            }
            else
            {
                ele.removeClassName('active');
            }
        });

        idoit.Require.addModule('multiEditSort', '[{$wwwAssetsDir}]js/multieditSort.js');
        idoit.Require.addModule('multiEdit', '[{$wwwAssetsDir}]js/multiedit.js');

        idoit.Require.require(['multiEdit', 'multiEditSort', 'fileUploader'], function () {

            if (typeof module !== 'undefined' && module.exports) {
                module.exports = Tablesort;
            } else {
                window.Tablesort = Tablesort;
            }

            var translator = idoit.Translate || new Hash;

            // Adding some translations.
            translator.set('LC__UNIVERSAL__ALL', '[{isys type="lang" ident="LC__UNIVERSAL__ALL" p_bHtmlEncode=false}]');
            translator.set('LC__MULTIEDIT__SUCCESSFUL', '[{isys type="lang" ident="LC__MULTIEDIT__SUCCESSFUL" p_bHtmlEncode=false}]');
            translator.set('LC__VALIDATION_ERROR', '[{isys type="lang" ident="LC__VALIDATION_ERROR" p_bHtmlEncode=false}]');
            translator.set('LC__UNIVERSAL__OBJECT_TITLE', '[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TITLE" p_bHtmlEncode=false}]');
            translator.set('LC__UNIVERSAL__ID', '[{isys type="lang" ident="LC__UNIVERSAL__ID" p_bHtmlEncode=false}]');
            translator.set('LC__MODULE__MULTIEDIT__MULTIVALUE_INFO_TEXT', '[{isys type="lang" ident="LC__MODULE__MULTIEDIT__MULTIVALUE_INFO_TEXT" p_bHtmlEncode=false}]');
            translator.set('LC__UNIVERSAL__CHOOSEN_PLACEHOLDER', '[{isys type="lang" ident="LC__UNIVERSAL__CHOOSEN_PLACEHOLDER" p_bHtmlEncode=false}]');
            translator.set('LC__UNIVERSAL__CHOOSEN_PLACEHOLDER', '[{isys type="lang" ident="LC__UNIVERSAL__CHOOSEN_PLACEHOLDER" p_bHtmlEncode=false}]');
            translator.set('LC__UNIVERSAL__CHOOSEN_EMPTY', '[{isys type="lang" ident="LC__UNIVERSAL__CHOOSEN_EMPTY" p_bHtmlEncode=false}]');

            window.multiEdit = new Multiedit({
                url: window.www_dir + 'multiedit/ajax',
                objectsElement: $('C__MULTIEDIT__OBJECTS__HIDDEN'),
                categoriesElement: $('C__MULTIEDIT__CATEGORY'),
                objectInfoElement: $('C__MULTIEDIT__FILTER_OBJECT_INFO'),
                // btnLoadList: $('startEditing'),
                // btnAddValue: $('addValues'),
                loaderElement: $('multiEditLoader'),
                editBtnsContainer: $('editButtons'),
                filterElement: $('multiEditFilter'),
                multiEditConfig: $('multiedit-config'),
                multivalueCategories: [{$multivalueCategories}],
                multiEditList: $('multiEditList'),
                multiEditHeader: $('multiEditHeader'),
                multiEditContainer: $('multiEditContainer'),
                multiEditFooter: $('multiEditFooter'),
                translation: translator,
                overlay: $('overlay')
            });
        });
    });
</script>
