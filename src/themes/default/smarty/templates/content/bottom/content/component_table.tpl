[{assign var="fields" value=[]}]
[{if $options.filterDefaultColumn.field}]
    [{foreach from=$properties item="property"}]
        [{if $options.filterDefaultColumn.field == $property->getPropertyKey()}]
            [{append var="fields" value=$property}]
        [{/if}]
    [{/foreach}]
[{/if}]
[{foreach from=$properties item="property"}]
    [{if $options.filterDefaultColumn.field != $property->getPropertyKey() and isset($options.filterColumns[$property->getPropertyKey()])}]
        [{append var="fields" value=$property}]
    [{/if}]
[{/foreach}]
[{isys_group name="tom.content.table"}]
    <div class="advanced-filters-header">
        [{if $fields|count > 0}]
            <div class="advanced-filters-container fl">
                <div class="advanced-filters collapsed flex-container-row" data-toggle-role="area" data-toggle-id="[{$unique}]" data-toggle-class="collapsed">
                    <div class="advanced-filters-table">
                        <table>
                            [{foreach from=$fields item="property" key="i"}]
                                [{if not $property->isFilterable()}]
                                    [{continue}]
                                [{/if}]
                                [{assign var='extraParams' value=['data-filter-role'=>"value"]}]
                                [{if $i == 0}]
                                    [{append var="extraParams" value=1 index='data-filter-default'}]
                                [{/if}]
                                <tr [{if $i == 0}] class="default-field"[{/if}]>
                                    <td>
                                        <label class="fr" for="tableFilter[[{$property->getPropertyKey()}]]">[{isys type="lang" ident=$property->getName()}]</label>
                                    </td>
                                    <td class="pl20">
                                        [{isys type="f_property"
                                        property=$property
                                        name="tableFilter[[{$property->getPropertyKey()}]]"
                                        p_strClass="input input-block has-addon"
                                        inputGroupMarginClass=""
                                        p_strValue=$options.filterDefaultValues[$property->getPropertyKey()]
                                        p_strSelectedID=$options.filterDefaultValues[$property->getPropertyKey()]
                                        default=null
                                        allow_empty=true
                                        extra-params=$extraParams
                                        p_bEditMode="1"
                                        }]
                                    </td>
                                </tr>
                            [{/foreach}]
                            [{if $fields|count > 1}]
                                <tr>
                                    <td>
                                        <label for="tableFilter[operation]" class="fr">[{isys type="lang" ident='LC__UNIVERSAL__OPERATION'}]</label>
                                    </td>
                                    <td>
                                        [{assign var='value' value="-1"}]
                                        [{if isset($options.filterDefaultValues['operation']) and $options.filterDefaultValues['operation']}]
                                            [{assign var='value' value=$options.filterDefaultValues['operation']}]
                                        [{/if}]
                                        <div class="ml20">
                                            <input
                                                    id="[{$unique}]-operation-and"
                                                    type="radio"
                                                    name="tableFilter[operation]"
                                                    value="-1"
                                                    data-filter-role="value"
                                                    [{if $value == -1}]checked[{/if}]
                                            >
                                            <label for="[{$unique}]-operation-and">
                                                [{isys type="lang" ident='LC__UNIVERSAL__AND'}]
                                            </label>
                                        </div>
                                        <div class="ml20">
                                            <input
                                                    id="[{$unique}]-operation-or"
                                                    type="radio"
                                                    name="tableFilter[operation]"
                                                    value="1"
                                                    data-filter-role="value"
                                                    [{if $value == 1}]checked[{/if}]
                                            >
                                            <label for="[{$unique}]-operation-or">
                                                [{isys type="lang" ident='LC_UNIVERSAL__OR'}]
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            [{/if}]
                            <tr>
                                <td></td>
                                <td>
                                    <button type="button" class="btn" data-filter-role="reset">
                                        <img src="[{$dir_images}]icons/silk/cross.png" />
                                        <span>[{isys type="lang" ident="LC__UNIVERSAL__RESET"}]</span>
                                    </button>

                                    <button type="button" class="btn" data-filter-role="filter">
                                        <img src="[{$dir_images}]icons/silk/zoom.png" />
                                        <span>[{isys type="lang" ident="LC_UNIVERSAL__FILTER"}]</span>
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="advanced-filters-btn-group">
                        <img src="[{$dir_images}]icons/silk/zoom.png" class="mainTableConfigLink search-btn p5" data-filter-role="filter" title="[{isys type="lang" ident="LC__CMDB__OBJECT_LIST__FILTER_TOOLTIP"}]"/>
                        <img src="[{$dir_images}]icons/silk/bullet_arrow_down.png" class="mainTableConfigLink p5" data-toggle-role="toggle" data-toggle-id="[{$unique}]" title="[{isys type="lang" ident="LC__CMDB__OBJECT_LIST__FILTER_EXPAND"}]"/>
                        <!-- <img src="[{$dir_images}]icons/silk/cross.png" class="mainTableConfigLink p5" data-filter-role="reset" title="[{isys type="lang" ident="LC__UNIVERSAL__RESET"}]"/>-->
                    </div>
                </div>
            </div>
            <div class="advanced-filters-search fl" style="display: none" data-filter-role="reset">
                <input data-role="search" class="input input-block" readonly>
            </div>
        [{/if}]
        <div class="m5 fr">
            [{if $options.resizeColumns && $options.resizeColumnAjaxURL}]
                [{if $options.resizeTableAjaxURL}]
                <span class="mainTableConfigLink p5" data-column-role="fit">
                        <img src="[{$dir_images}]icons/silk/table_lightning.png" title="[{isys type="lang" ident="LC__MODULE__CMDB__FIT_LIST_WIDTH_TO_SCREEN"}]" />
                    </span>
                [{/if}]
                <span class="mainTableConfigLink p5" data-column-role="reset">
                        <img src="[{$dir_images}]icons/silk/table.png" title="[{isys type="lang" ident="LC__MODULE__CMDB__RESTORE_DEFAULT_LIST_WIDTH"}]" />
                    </span>
            [{/if}]
            [{if $options.tableConfigURL}]
                <a href="[{$options.tableConfigURL}]" class="mainTableConfigLink p5">
                    <img src="[{$dir_images}]icons/silk/table_edit.png" title="[{isys type="lang" ident="LC__CMDB__LIST_CONFIGURE"}]" />
                </a>
            [{/if}]
            <div class="input-group mainTablePerPage" data-filter-field="rowsPerPage" data-filter-params='{"page": 1}'>
                <div class="input-group-addon input-group-addon-unstyled">[{isys type="lang" ident="LC__CMDB__LIST_SHOW"}]</div>
                [{isys type="f_dialog" name="`$unique`-rows-per-page" p_bEditMode="1"}]
                <div class="input-group-addon input-group-addon-unstyled">/ [{$rows}] [{isys type="lang" ident="LC__UNIVERSAL__ENTRIES"}]</div>
            </div>
        </div>
    </div>
    <div class="table-container">
        <div class="table" id="[{$unique}]-container">
            <div class="table-header">
                <table id="[{$unique}]-header" class="mainTable mainTable-header border-top w100">
                    <colgroup>
                        [{assign var="i" value=0}]
                        [{if $options.enableCheckboxes}]
                    <col style="width: [{$options.columnSizes[$i]|default:'auto'}];"/>
                        [{assign var="i" value=$i + 1}]
                        [{/if}]
                        [{foreach $header as $field}]
                    <col style="width: [{$options.columnSizes[$i]|default:'auto'}];"/>
                        [{assign var="i" value=$i + 1}]
                        [{/foreach}]
                    </colgroup>
                    <thead>
                    <tr>
                        [{if $options.enableCheckboxes}]
                        <th class="no-drag">
                            <span>
                                <label class="mainTableCheckbox"><input type="checkbox" title="[{isys type="lang" ident="LC__MODULE__CMDB__LIST_CHECK_ALL_CURRENT_PAGE"}]" name="table_id_all[]" value="0" /></label>
                            </span>
                        </th>
                        [{/if}]
                        [{foreach $header as $fieldTitle => $field}]
                        <th>
                            [{if $options.order}]<img src="[{$dir_images}]icons/silk/bullet_arrow_up.png" class="sort-arrow opacity-30 hide">[{/if}]
                            <span class="overflowable" title="[{$fieldTitle}]">[{$field}]</span>
                        </th>
                        [{/foreach}]
                    </tr>
                    </thead>
                </table>
            </div>
            <div class="table-body-container">
                <div class="table-body display-container">
                    [{if is_array($data) && count($data)}]
                    <table id="[{$unique}]" class="mainTable border-top is-loading w100">
                        <colgroup>
                            [{assign var="i" value=0}]
                            [{if $options.enableCheckboxes}]
                        <col style="width: [{$options.columnSizes[$i]|default:'auto'}];"/>
                            [{assign var="i" value=$i + 1}]
                            [{/if}]
                            [{foreach $header as $field}]
                        <col style="width: [{$options.columnSizes[$i]|default:'auto'}];"/>
                            [{assign var="i" value=$i + 1}]
                            [{/foreach}]
                        </colgroup>
                        <tbody class="mainTableHover">
                            [{foreach $data as $row}]
                            <tr data-id="[{$row.__id__}]" [{if $options.rowClickAttribute}]data-link="[{$row['__link__']}]"[{/if}]>
                                [{assign var="keys" value=$row|array_keys}]
                                [{for $i=0 to $header|count}]
                                [{assign var="key" value=$keys[$i]}]
                                [{if $key == '__id__'}]
                                    [{if $options.enableCheckboxes}]
                                <td>
                                    [{if $options.dragDrop}]<div class="drag-handle"></div>[{/if}]
                                    <label class="mainTableCheckbox"><input type="checkbox" name="[{$options.tableIdField|default:'table_ids'}][]" value="[{$row[$key]}]" /></label>
                                </td>
                                    [{/if}]
                                [{else}]
                                <td data-property="[{$key}]"><span class="overflowable">[{$row[$key]}]</span></td>
                                [{/if}]
                                [{/for}]
                            </tr>
                            [{/foreach}]
                        </tbody>
                    </table>
                [{else}]
                    <div class="m5 p5 box-blue">
                        <img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" /><span class="vam">[{isys type="lang" ident="LC__CMDB__FILTER__NOTHING_FOUND_STD"}]</span>
                    </div>
                [{/if}]
                </div>
            </div>
            <div class="table-footer">
                <div class="mainTablePager p5">
                    [{$pager}]
                </div>
            </div>
            <script type="application/javascript" data-autoinitialize="1">
                (function () {
                    'use strict';

                    new TableComponent('[{$unique}]', {
                        ajaxMethod:              '[{$options.ajaxMethod}]',
                        [{if $options.ajaxParams}]
                        ajaxParams:             JSON.parse('[{$options.ajaxParams|json_encode:JSON_FORCE_OBJECT|escape:"javascript"}]'),
                        [{/if}]
                        scoped:                  [{if $options.scoped}]true[{else}]false[{/if}],
                        defaultValueField:      '[{$defaultValueField}]',
                        routeParams:            JSON.parse('[{$options.routeParams|json_encode:JSON_FORCE_OBJECT|escape:"javascript"}]'),
                        dragDrop:               [{if $options.dragDrop}]true[{else}]false[{/if}],
                        dragDropBoxContent:     '<img src="' + window.dir_images + 'icons/silk/database_copy.png" class="mr5"><span>%count% [{isys type="lang" ident="LC__CMDB__OBJECT_LIST__DRAG_OBJECTS"}]</span>',
                        checkboxes:             [{if $options.enableCheckboxes}]true[{else}]false[{/if}],
                        resizeColumns:          [{if $options.resizeColumns}]true[{else}]false[{/if}],
                        resizeColumnAjaxURL:    '[{$options.resizeColumnAjaxURL}]',
                        resizeTableAjaxURL:     '[{$options.resizeTableAjaxURL}]',
                        columnSizes:            JSON.parse('[{$options.columnSizes|json_encode|escape:"javascript"}]') || [],
                        tableSize:              JSON.parse('[{$options.tableSize|json_encode|escape:"javascript"}]') || 0,
                        order:                  [{if $options.order}]true[{else}]false[{/if}],
                        orderColumns:           '[{$options.orderColumns|json_encode|escape:"javascript"}]'.evalJSON(),
                        currentOrderColumn:     '[{$smarty.get.orderBy|default:$options.orderDefaultColumn|strip_tags|escape}]',
                        currentOrderDirection:  '[{$smarty.get.orderByDir|default:$options.orderDefaultDirection|strip_tags|escape}]',
                        orderAscendingMessage:  '[{isys type="lang" ident="LC__CMDB__LIST_ORDER_ASCENDING"}]',
                        orderDescendingMessage: '[{isys type="lang" ident="LC__CMDB__LIST_ORDER_DESCENDING"}]',
                        rowClick:               [{if $options.rowClick && ($options.rowClickURL || $options.rowClickAttribute) }]true[{else}]false[{/if}],
                        rowClickUrl:            '[{$options.rowClickURL}]',
                        rowClickAttribute:      '[{$options.rowClickAttribute}]',
                        keyboardCommands:       [{if $options.keyboardCommands}]true[{else}]false[{/if}],
                        loadingMessage:         '[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]',
                        quickInfoActive:        !![{isys_usersettings::get('gui.quickinfo.active', 1)}],
                        quickInfoDelay:         [{isys_usersettings::get('gui.quickinfo.delay', 0.5)}],
                        replacerOptions:        [{$options.replacerOptions}],
                        translation: {
                            pager: {
                                prompt:            '[{isys type="lang" ident="LC__UNIVERSAL__JUMP_TO_PAGE"}]',
                                nanPage:           '[{isys type="lang" ident="LC__CMDB__OBJECT_LIST__INVALID_PAGE_NUMBER"}]',
                                notFound:          '[{isys type="lang" ident="LC__CMDB__OBJECT_LIST__PAGE_NUMBER_DOES_NOT_EXIST"}]',
                                tooMuchPerPage:    '[{isys type="lang" ident="LC__CMDB__OBJECT_LIST__TOO_MUCH_RESULTS_PROMPT"}]',
                            },
                            filter: {
                                and: '[{isys type="lang" ident="LC__UNIVERSAL__AND"}] '
                            }
                        },
                        directEditMode: [{if $options.directEditMode}]1[{else}]0[{/if}]
                        [{if $options.autocomplete !== false}]
                        ,autocomplete: {
                            active: [{isys_usersettings::get('gui.objectlist.autocomplete_search', 1)}],
                            baseUrl: www_dir + '?viewMode=1001&objTypeID=[{$smarty.get.objTypeID|strip_tags|escape}]',
                            placeholder: '[{isys type="lang" ident="LC__MODULE__SEARCH__TITLE"}]',
                            options: {
                                minChars:                 3,
                                choices:                  125,
                                fuzzySuggestion:          ('[{$fuzzySuggestion}]' === '1'),
                                fuzzySuggestionThreshold: parseFloat('[{$fuzzySuggestionThreshold}]'),
                                fuzzySuggestionDistance:  parseInt('[{$fuzzySuggestionDistance}]')
                            }
                        }
                        [{/if}]
                        ,enableMultiselection: [{if $options.enableMultiselection}]true[{else}]false[{/if}]
                        ,objectPathCutLength: parseInt('[{$options.objectPathCutLength}]')
                    });
                    // @see  ID-5079  The problem with the URL seems to be fixe, but when the user archives/deletes/purges/recycles any objects and then immediately navigates - the objects are still inside the cache.
                    $('navBar').on('click', '[data-navmode="[{$smarty.const.C__NAVMODE__ARCHIVE}]"],[data-navmode="[{$smarty.const.C__NAVMODE__DELETE}]"],[data-navmode="[{$smarty.const.C__NAVMODE__PURGE}]"],[data-navmode="[{$smarty.const.C__NAVMODE__QUICK_PURGE}]"],[data-navmode="[{$smarty.const.C__NAVMODE__RECYCLE}]"]', function(){
                        window.tableCache = {};
                    });
                })();
            </script>
        </div>
    </div>
[{/isys_group}]
