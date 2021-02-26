[{if $js_init}]
<script type="text/javascript">
    // Add some translations.
    idoit.Translate.set('C__CMDB__GET__OBJECT', '[{$smarty.const.C__CMDB__GET__OBJECT}]');
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT__ADD', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__ADD"}]');
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT__NO_PRESELECTION', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__NO_PRESELECTION"}]');
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT__REMOVE', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__REMOVE"}]');
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__OBJECT_HAS_BEEN_ADDED', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__OBJECT_HAS_BEEN_ADDED"}]');
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT__0_FROM_TYPE_1_HAS_BEEN_REMOVED', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__0_FROM_TYPE_1_HAS_BEEN_REMOVED"}]');
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__OBJECT_SELECTED', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__OBJECT_SELECTED"}]');
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT__0_IS_SELECTED', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__0_IS_SELECTED"}]');
    idoit.Translate.set('LC__UNIVERSAL__ALL', ('[{isys type="lang" ident="LC__UNIVERSAL__ALL"}]'));
    idoit.Translate.set('LC__UNIVERSAL__PAGE', ('[{isys type="lang" ident="LC__UNIVERSAL__PAGE"}]'));
    idoit.Translate.set('LC__UNIVERSAL__OBJECT_TITLE', '[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TITLE"}]');
    idoit.Translate.set('LC__UNIVERSAL__OBJECT_TYPE', '[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TYPE"}]');
    idoit.Translate.set('LC__CMDB__CATG__RELATION', ('[{isys type="lang" ident="LC__CMDB__CATG__RELATION"}]').slice(0, 3) + '.');
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__ADD_ALL_ON_PAGE', ('[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__ADD_ALL_ON_PAGE"}]'));
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__ADD_ALL_BY_FILTER', ('[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__ADD_ALL_BY_FILTER"}]'));
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__EMPTY_RESULTS', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__EMPTY_RESULTS"}]');
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_DATA', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_DATA"}]');
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_URL', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_URL"}]');
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__FILTER_LABEL', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__FILTER_LABEL"}]');
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__LOADING', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__LOADING"}]');
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__SEARCH_LABEL', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__SEARCH_LABEL"}]');
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_OF', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_OF"}]');
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_PAGES', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_PAGES"}]');
    idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT__PRESELECTED_OBJECTS', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__PRESELECTED_OBJECTS"}]');

    // @see  ID-6220  Delete the global variable before initialization to prevent side effects.
    delete window.browserParentPreselection;

    var returnElement           = '[{$return_element}]',
        multiselection          = !!parseInt('[{$multiselection}]'),
        firstColWidth           = (multiselection ? '105px' : '40px'),
        customFilter            = null,
        browserFilter           = {
            'specificCategory':  '[{$specificCategoryFilter}]',
            'globalCategory':    '[{$globalCategoryFilter}]',
            'objectType':        '[{$typeFilter}]',
            'objectTypeExclude': '[{$typeBlacklist}]',
            'cmdbStatus':        '[{$cmdb_filter}]'
        },
        defaultSortingField     = null,
        defaultSortingDirection = 'asc',
        $browserContent         = $('browser-content'),
        $browserContentOverlay  = $browserContent.down('.overlay'),
        $rightsError            = $('rightsError');

    try {
        defaultSortingField = JSON.parse('[{$defaultSortingField|default:"null"}]');
        defaultSortingDirection = '[{$defaultSortingDirection|default:"asc"}]';
    } catch (e) {
        defaultSortingField = null;
        defaultSortingDirection = 'asc';
    }

    try {
        customFilter = JSON.parse('[{$customFilters|json_encode|escape:"javascript"}]');
    } catch (e) {

    }

    // Initialize list compnents.
    window.browserList = new Browser.objectList('objectList', {
        jsonClient:              idoitJSON,
        listOptions:             {
            colgroup:                '<colgroup><col style="width:' + firstColWidth + ';" /></colgroup>',
            multiselection:          multiselection,
            firstSelection:          true,
            secondSelection:         false,
            secondSelectionExists:   [{$secondSelection|default:"false"}],
            objectSelectionCallback: '[{if $secondSelection}]browserPreselection.secondSelectionCall[{else}]browserPreselection.select[{/if}]',
            instanceName:            'browserList',
            useAuth:                 !!parseInt('[{$useAuth}]'),
            quickinfo:               {
                active: [{isys_usersettings::get('gui.quickinfo.active', 1)}],
                delay:  [{isys_usersettings::get('gui.quickinfo.delay', 0.5)}]
            }
        },
        cmdb_filter:             '[{$cmdb_filter}]',
        browserFilter:           browserFilter,
        customFilter:            customFilter,
        urlBase:                 '[{$config.www_dir}]',
        returnElement:           returnElement,
        objectBrowserName:       '[{$objectBrowserName}]',
        defaultSortingField:     defaultSortingField,
        defaultSortingDirection: defaultSortingDirection
    });

    window['acceptCallback[{$return_element}]'] = function() {
        var callback = function() {
            moveToParent('[{$return_element}]', '[{$return_view}]');
            [{if $formsubmit}]
            $('navMode').setValue(C__NAVMODE__JS_ACTION);
            $('isys_form').submit();
            if ($('preselectionLoader')) {
                $('preselectionLoader').removeClassName('hide');
            }

            if ($browserContent) {
                $browserContent.hide();
            }

            if ($('bottombar')) {
                $('bottombar').hide();
            }
            [{else}]
            popup_close();
            [{/if}]
            // clear the scope
            delete window['acceptCallback[{$return_element}]'];
        };
        // register to callback
        idoit.callbackManager.registerCallback('idoit.popup.[{$return_element}].accept', callback);
        // unregister callback
        idoit.callbackManager.registerCallback('idoit.popup.[{$return_element}].accept', null);
        callback();
    };

    [{include file=$js_init}]

    Ajax.Responders.register({
        onCreate: function (oXHR, oJson) {
            $('ajaxLoader').show();
        },
        onComplete: function (oXHR, oJson) {
            $('ajaxLoader').hide();
        }
    });

    /**
      * Show and close the object creation wizard
      */
    window.showWindow = function (element, focusElement) {
        var $element = $(element),
            centerCoordinate = parseInt(($browserContent.getWidth() / 2) - 150);

        if ($rightsError && $rightsError.visible()) {
            $rightsError.addClassName('hide');
        }

        // Prepare the elements for animation.
        $element.setStyle({left: centerCoordinate + 'px', top: '33px', opacity: 0}).removeClassName('hide');
        $browserContentOverlay.setStyle({opacity: 0}).removeClassName('hide');

        // Animate the elements.
        new Effect.Morph($element, {
            style:       'top: 43px; opacity: 1',
            duration:    0.3,
            afterFinish: function () {
                if (focusElement && $(focusElement) && $(focusElement).focus) {
                    $(focusElement).focus();
                }
            }
        });

        new Effect.Morph($browserContentOverlay, {
            style:       'opacity: 1',
            duration:    0.3
        });
    };

    window.closeWindow = function (element) {
        var $element = $(element);

        if ($rightsError && $rightsError.visible()) {
            $rightsError.addClassName('hide');
        }

        // Prepare the elements for animation.
        $element.setStyle({opacity: 1}).removeClassName('hide')
        $browserContentOverlay.setStyle({opacity: 1}).removeClassName('hide')

        // Animate the elements.
        new Effect.Morph($element, {
            style:       'top: 53px; opacity: 0',
            duration:    0.3,
            afterFinish: function () {
                $element.addClassName('hide');
            }
        });

        new Effect.Morph($browserContentOverlay, {
            style:       'opacity: 0',
            duration:    0.3,
            afterFinish: function () {
                $browserContentOverlay.addClassName('hide');
            }
        });
    };

    /**
      * Place the call and really create the object group
      */
    window.createObjectGroup = function(objectGroupTitle, forceOverwrite) {
        var objects_selected = [], i;

        $('ajaxLoader').show();

        if ($('reportFilter').visible()) {
            // Creating a group from a report will always contain all objects.
            for (i in window.browserList.cache) {
                if (!window.browserList.cache.hasOwnProperty(i)) {
                    continue;
                }

                objects_selected.push(parseInt(window.browserList.cache[i].__checkbox__));
            }
        } else {
            objects_selected = browserPreselection.getSelection();
        }

        // Lets call the object group creation.
        idoitJSON.createObjectGroup(objectGroupTitle, JSON.stringify(objects_selected), forceOverwrite, function(xhr) {
            if (xhr.responseJSON) {
                if (!xhr.responseJSON.exists) {
                    closeWindow('new-objectgroup');

                    $('ajaxLoader').hide();

                    if (xhr.responseJSON > 0) {
                        $('object_group').insert(new Element('option', {value: xhr.responseJSON}).update(objectGroupTitle + ' (' + objects_selected.length + ')'));

                        $('object_group').setValue(xhr.responseJSON);

                        var $dateCondition = $('leftPane').down('li[data-condition="idoit.Component.Browser.Condition.DateCondition"]'),
                            $dateConditionView = $('rightPane').down('[data-condition-view="idoit.Component.Browser.Condition.DateCondition"]');

                        if ($dateCondition && $dateConditionView) {
                            $dateCondition.simulate('click');

                            // Show latest objects, where the new one should be on first place.
                            $dateConditionView.down('select').setValue('latest-created').simulate('change');
                        }
                    }
                } else {
                    if (confirm('[{isys type="lang" ident="LC__CMDB__CATG__GLOBAL__OBJECT_GROUP_EXISTS"}]'.replace('%s', objectGroupTitle))) {
                        idoitJSON.createObjectGroup(objectGroupTitle, JSON.stringify(objects_selected), true, function(xhr) {
                            closeWindow('new-objectgroup');

                            $('ajaxLoader').hide();
                        });
                    }
                }
            }
        });
    };

    /**
      * Place the call and really create the object
      */
    window.createObject = function(objectTitle, objectType) {
        $('ajaxLoader').show();

        // Lets call the object creation.
        idoitJSON.createObject(objectTitle, objectType, function (xhr) {
            if (xhr.responseJSON > 0) {
                // Close dialog and hide ajax loader.
                closeWindow('new-object');

                $('ajaxLoader').hide();

                var $dateCondition = $('leftPane').down('li[data-condition="idoit.Component.Browser.Condition.DateCondition"]'),
                    $dateConditionView = $('rightPane').down('[data-condition-view="idoit.Component.Browser.Condition.DateCondition"]');

                if ($dateCondition && $dateConditionView) {
                    $dateCondition.simulate('click');

                    // Show latest objects, where the new one should be on first place.
                    $dateConditionView.down('select').setValue('latest-created').simulate('change');
                }


                // Reset the "new object" window.
                $('createObjectTitle').setValue('');
                $('createObjectType').selectedIndex = 1;
            } else {
                idoit.Notify.info('Error while creating the object. Please try again. Error: ' + transport.responseText + ', HTTP-Status: ' + transport.statusText, {life:5});
            }
        }, 1);
    };

    var $reportDropdown = $('report_dropdown');

    // Check whether report dropdown exists or not
    if ($reportDropdown) {
        $reportDropdown.on('change', function() {
            var value = parseInt($reportDropdown.getValue());

            if (isNaN(value) || value <= 0) {
                browserList.showError(browserList.msgs.emptyResults);
                return;
            }

            browserList.performRequest('cmdb/browse/report/' + value);
        });
    }

    /**
     * Register closing on keydown when popup is unused
     */
    document.on('keydown', function(ev) {
        if (ev.keyCode == Event.KEY_ESC || ev.keyCode == Event.KEY_RETURN) {
            popup_close();
        }
    });

    /**
     * Register escape key for closing the popup
     */
    $('popup-object-ng-[{$return_element}]').observe('keydown', function(ev) {
        ev = ev || window.event;
        if (ev.keyCode == Event.KEY_ESC) {
            [{if $callback_abort}][{$callback_abort}][{/if}]
            popup_close();
        }
        else if (ev.keyCode == Event.KEY_RETURN) {
            window['acceptCallback[{$return_element}]']();
        }
    });
</script>
[{else}]

    [{assign var=error value='js_init not set.'}]

[{/if}]

<div id="popup-object-ng-[{$return_element}]">
    <h3 class="popup-header">
        <img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]icons/close-circle.png">
        <img class="fr mr10" id="ajaxLoader" style="display:none; margin-top:-1px;" alt="" src="[{$dir_images}]ajax-loading.gif">
        <span>[{$browser_title|default:"Browser"}]</span>
    </h3>

    [{if !$error}]
    <div class="m10" id="preselectionLoader"><img src="[{$dir_images}]ajax-loading-big.gif" class="vam" /> [{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__LOADING"}]</div>

    <div id="new-object" class="blurred-shadow-small hide">
        <h3 class="popup-header">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CREATE_NEW_OBJECT"}]</h3>

        <table class="m10 padding spacing vat">
            <colgroup>
                <col width="30%"/>
            </colgroup>
            <tr>
                <td>[{isys type="f_label" name="createObjectTitle" ident="LC__UNIVERSAL__OBJECT_TITLE"}]</td>
                <td><input type="text" class="input" id="createObjectTitle" name="createObjectTitle" onkeypress="if(event.keyCode == Event.KEY_RETURN) { event.preventDefault(); return false; }"/></td>
            </tr>
            <tr>
                <td>[{isys type="f_label" ident="LC__UNIVERSAL__OBJECT_TYPE" name="createObjectType"}]</td>
                <td>[{isys type="f_dialog" p_arData=$allObjectTypes p_strSelectedID=$defaultObjectTypeFilter p_bInfoIconSpacer="0" p_bDbFieldNN=1 p_bEditMode=1 p_bEnableMetaMap=0 id="createObjectType" sort=true name="createObjectType" disableInputGroup=true}]</td>
            </tr>
        </table>

        <div class="popup-footer" style="position: relative">
            <button type="button" id="btn_createObject" class="btn" onclick="createObject($F('createObjectTitle'), $F('createObjectType'));">
                <img src="[{$dir_images}]icons/silk/tick.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__CREATE_OBJECT"}]</span>
            </button>
            <button type="button" class="btn" onclick="closeWindow('new-object');">
                <img src="[{$dir_images}]icons/silk/cross.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
            </button>
        </div>
    </div>

    <div id="new-objectgroup" class="blurred-shadow-small hide">
        <h3 class="popup-header">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CREATE_NEW_OBJECT_GROUP_FROM_SELECTION"}]</h3>

        [{if $allowedToCreateGroups}]
            <table class="m10 padding spacing vat">
                <colgroup>
                    <col width="50%"/>
                </colgroup>
                <tr>
                    <td>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SELECTED_ELEMENTS"}]</td>
                    <td id="selectedObjectsForGroup"></td>
                </tr>
                <tr>
                    <td>[{isys type="f_label" ident="LC__CMDB__OBJECT_BROWSER__NAME_OF_THE_GROUP" name="createObjectGroupTitle"}]</td>
                    <td><input type="text" class="input input-block" id="createObjectGroupTitle" name="createObjectGroupTitle"/></td>
                </tr>
            </table>
        [{else}]
            <p class="box-red m5 p5">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__NOT_ALLOWED_TO_CREATE_OBJECT_GROUP"}]</p>
        [{/if}]

        <div class="popup-footer" style="position: relative">
            [{if $allowedToCreateGroups}]
            <button type="button" id="btn_createObjectGroup" class="btn" onclick="createObjectGroup($F('createObjectGroupTitle'));">
                <img src="[{$dir_images}]icons/silk/tick.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__CREATE_OBJECT"}]</span>
            </button>
            [{/if}]

            <button type="button" class="btn" onclick="closeWindow('new-objectgroup');">
                <img src="[{$dir_images}]icons/silk/cross.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
            </button>
        </div>
    </div>

    <div id="rightsError" class="box-red hide">
        <h3 class="p5">[{isys type="lang" ident="LC__AUTH__EXCEPTION"}] <span onclick="$('rightsError').addClassName('hide');" class="bold fr mouse-pointer">&times;</span></h3>
        <p class="p5"></p>
    </div>

    <div class="browser-content" id="browser-content" style="display:none;">
        <div id="objectview" class="object-browser-content-area">
            <div class="overlay hide"></div>

            <div id="leftPane" class="fl browserContent">
                <div>
                    <h3 class="p10">[{isys type="lang" ident="LC__UNIVERSAL__VIEW"}]</h3>

                    [{if is_array($primaryConditions) && count($primaryConditions)}]
                    <ul>
                        [{foreach $primaryConditions as $conditionClass => $condition}]
                            [{if !empty($condition->retrieveOverview())}]
                                <li data-condition="[{$conditionClass|escape}]">[{isys type="lang" ident=$condition->getName()}]</li>
                            [{/if}]
                        [{/foreach}]
                    </ul>
                    [{/if}]

                    [{if is_array($customConditions) && count($customConditions)}]
                    [{if is_array($primaryConditions) && count($primaryConditions)}]
                    <hr class="m10 mr0" style="border-style: dotted;" />
                    [{/if}]

                    <ul>
                        [{foreach $customConditions as $conditionClass => $condition}]
                            [{if !empty($condition->retrieveOverview())}]
                                <li data-condition="[{$conditionClass|escape}]">[{isys type="lang" ident=$condition->getName()}]</li>
                            [{/if}]
                        [{/foreach}]
                    </ul>
                    [{/if}]

                    [{if is_array($secondaryConditions) && count($secondaryConditions)}]
                    [{if (is_array($primaryConditions) && count($primaryConditions)) || (is_array($customConditions) && count($customConditions))}]
                    <hr class="m10 mr0" style="border-style: dotted;" />
                    [{/if}]

                    <ul>
                        [{foreach $secondaryConditions as $conditionClass => $condition}]
                            [{if $conditionClass === 'locationView'}]
                                <li data-condition="[{$conditionClass}]">[{isys type="lang" ident=$condition}]</li>
                            [{elseif !empty($condition->retrieveOverview()) || $conditionClass === 'idoit.Component.Browser.Condition.SearchCondition'}]
                                <li data-condition="[{$conditionClass|escape}]">[{isys type="lang" ident=$condition->getName()}]</li>
                            [{/if}]
                        [{/foreach}]
                    </ul>
                    [{/if}]
                </div>

                <ul>
                    <li class="selected-objects" data-condition="selectedObjects">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SELECTED_OBJECTS"}] (<span id="numObjects">0</span>)</li>
                </ul>
            </div>

            <div id="rightPane" class="browserContent">
                <div>
                    <div id="new-object-button" class="fr m5">
                        <button type="button" class="btn" onclick="showWindow('new-object', 'createObjectTitle');">
                            <img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CREATE_NEW_OBJECT"}]</span>
                        </button>
                    </div>

                    [{if isset($primaryConditions['idoit.Component.Browser.Condition.ObjectTypeCondition'])}]
                    <div class="groups" data-condition-view="idoit.Component.Browser.Condition.ObjectTypeCondition">
                        [{isys type="f_dialog" p_onChange="browserList.searchByType(\$F(this));" p_strSelectedID=$defaultObjectTypeFilter p_bInfoIconSpacer=0 p_bDbFieldNN=0 status=0 exclude="C__OBJTYPE__CONTAINER;C__OBJTYPE__LOCATION_GENERIC" p_bEditMode=1 name="object_type" id="object_type" p_strClass="input-small" p_arData=$primaryConditions['idoit.Component.Browser.Condition.ObjectTypeCondition']->retrieveOverview() p_bEnableMetaMap=0 chosen=true sort=true disableInputGroup=true}]
                    </div>
                    [{/if}]

                    [{if isset($primaryConditions['idoit.Component.Browser.Condition.ObjectGroupCondition'])}]
                    <div class="groups" data-condition-view="idoit.Component.Browser.Condition.ObjectGroupCondition">
                        [{isys type="f_dialog" p_onChange="browserList.searchByGroup(\$F(this));" p_bInfoIconSpacer=0 p_bDbFieldNN=0 status=0 p_bEditMode=1 p_arData=$primaryConditions['idoit.Component.Browser.Condition.ObjectGroupCondition']->retrieveOverview() p_bEnableMetaMap=0 id="object_group" sort=true chosen=true name="object_group" p_strClass="input-small" disableInputGroup=true}]
                    </div>
                    [{/if}]

                    [{if isset($primaryConditions['idoit.Component.Browser.Condition.PersonGroupCondition'])}]
                    <div class="groups" data-condition-view="idoit.Component.Browser.Condition.PersonGroupCondition">
                        [{isys type="f_dialog" p_onChange="browserList.searchByPersonGroup(\$F(this));" p_bInfoIconSpacer=0 p_bDbFieldNN=0 status=0 p_bEditMode=1 p_arData=$primaryConditions['idoit.Component.Browser.Condition.PersonGroupCondition']->retrieveOverview() p_bEnableMetaMap=0 id="person_group" sort=true chosen=true name="person_group" p_strClass="input-small" disableInputGroup=true}]
                    </div>
                    [{/if}]

                    [{if isset($primaryConditions['idoit.Component.Browser.Condition.ReportCondition'])}]
                    <div id="reportFilter" class="groups" data-condition-view="idoit.Component.Browser.Condition.ReportCondition">
                        <div class="fr hide" id="filterReports_btn" style="margin-top:-2px;">
                            <button type="button" class="btn" onclick="showWindow('new-objectgroup');$('selectedObjectsForGroup').update(window.browserList.cache.length);" title="[{isys type='lang' ident='LC__CMDB__OBJECT_BROWSER__CREATE_NEW_OBJECT_GROUP_FROM_REPORT_NOTICE'}]">
                                <img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CREATE_NEW_OBJECT_GROUP_FROM_REPORT"}]</span>
                            </button>
                        </div>

                        <div id="filterReports">
                            <label for="report_dropdown">[{isys type="lang" ident="LC__REPORT__BROWSE_REPORTS"}]</label>

                            <select id="report_dropdown" name="report_dropdown" onchange="if($F(this) == '-')$('filterReports_btn').addClassName('hide'); else $('filterReports_btn').removeClassName('hide');" class="input input-small chosen-select ml20">
                                <option value="-">-</option>
                                [{foreach $primaryConditions['idoit.Component.Browser.Condition.ReportCondition']->retrieveOverview() as $reportCategory => $categoryReports}]
                                <optgroup label="[{$reportCategory}]">
                                    [{foreach $categoryReports as $reportId => $reportTitle}]
                                    <option value="[{$reportId}]">[{$reportTitle}]</option>
                                    [{/foreach}]
                                </optgroup>
                                [{/foreach}]
                            </select>
                        </div>
                    </div>
                    [{/if}]

                    [{if isset($secondaryConditions['idoit.Component.Browser.Condition.RelationTypeCondition'])}]
                    <div class="groups" data-condition-view="idoit.Component.Browser.Condition.RelationTypeCondition">
                        <select class="input input-small chosen-select" onchange="browserList.searchByRelationType($F(this));">
                            <option value="-1">-</option>
                            [{foreach $secondaryConditions['idoit.Component.Browser.Condition.RelationTypeCondition']->retrieveOverview() as $relationTypeId => $relationTypeName}]
                            <option value="[{$relationTypeId}]">[{isys type="lang" ident=$relationTypeName}]</option>
                            [{/foreach}]
                        </select>
                    </div>
                    [{/if}]

                    [{if isset($secondaryConditions['idoit.Component.Browser.Condition.DateCondition'])}]
                    <div id="groupLatest" class="groups" data-condition-view="idoit.Component.Browser.Condition.DateCondition">
                        <select class="input input-small chosen-select" onchange="browserList.searchByTimeCondition($F(this));">
                            <option value="-1">-</option>
                            [{foreach $secondaryConditions['idoit.Component.Browser.Condition.DateCondition']->retrieveOverview() as $dateId => $dateName}]
                            <option value="[{$dateId}]">[{isys type="lang" ident=$dateName}]</option>
                            [{/foreach}]
                        </select>
                    </div>
                    [{/if}]

                    [{if isset($secondaryConditions['idoit.Component.Browser.Condition.SearchCondition'])}]
                    <div id="searchFilter" class="groups" data-condition-view="idoit.Component.Browser.Condition.SearchCondition">
                        <label for="obj-filter">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__ENTER_SEARCH_PHRASE"}]</label>

                        <input type="search" placeholder="[{isys type='lang' ident='LC__CMDB__OBJECT_BROWSER__ENTER_SEARCH_PHRASE'}]" class="input input-small ml20" id="obj-filter" name="obj-filter" />
                    </div>
                    [{/if}]

                    [{foreach $customConditions as $conditionClassName => $condition}]
                    <div class="groups" data-condition-view="[{$conditionClassName}]">
                        <select name="object_catfilter" class="input input-small chosen-select m10" id="object_catfilter" onchange="browserList.searchByCustomCondition($F(this), {name: '[{$conditionClassName|escape:"javascript"}]', objID: glob(C__CMDB__GET__OBJECT)});">
                            <option value="-1">-</option>
                            [{foreach $condition->retrieveOverview() as $parameter => $name}]
                            <option value="[{$parameter}]">[{isys type="lang" ident=$name}]</option>
                            [{/foreach}]
                        </select>
                    </div>
                    [{/foreach}]

                    <div class="groups" data-condition-view="locationView">
                        <div class="browserContent">
                            <div class="p10" id="locationBrowser"></div>
                        </div>
                        <script type="text/javascript">[{$locationBrowser}]</script>
                    </div>

                    <div class="groups" data-condition-view="selectedObjects">
                        <div class="browserContent">

                            [{if !$secondSelection}]
                                <div class="mr5 mb5">
                                    <button type="button" class="btn fr" onclick="showWindow('new-objectgroup');$('selectedObjectsForGroup').update(browserPreselection.getSelection().length);">
                                        <img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CREATE_NEW_OBJECT_GROUP_FROM_SELECTION"}]</span>
                                    </button>

                                    <br class="cb" />
                                </div>
                            [{/if}]

                            <div id="objectPreselection"></div>
                        </div>
                    </div>
                </div>

                <div id="objectList"[{if $secondSelection}] class="left fl"[{/if}]>
                    <p class="p5 m10 box-green"><strong>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__PLEASE_MAKE_A_SELECTION"}]</strong></p>
                </div>

                [{if $secondSelection}]
                <div id="portList" class="right fl">
                    <p class="p5 m10 box-green"><strong>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__PLEASE_MAKE_A_SELECTION"}]</strong></p>
                </div>
                [{/if}]
            </div>
        </div>
    </div>

    <div class="popup-footer">
        <p id="latestLog" class="fr"></p>

        <button type="button" class="btn mr5" onclick="window['acceptCallback[{$return_element}]']();">
            <img src="[{$dir_images}]icons/silk/tick.png" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__BUTTON_SAVE"}]</span>
        </button>
        <button type="button" class="btn popup-closer" onclick="[{if $callback_abort}][{$callback_abort}][{/if}]">
            <img src="[{$dir_images}]icons/silk/cross.png" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__BUTTON_CANCEL"}]</span>
        </button>
    </div>

    [{else}]

    <div>
        <p class="emptyPageMessage text-lightgrey"><img src="[{$dir_images}]outlet.png" class="" /> [{$error}]</p>

        [{if $errorDetail}]
        <pre class="p10" style="margin-top:100px;height:300px;overflow:auto;border:1px solid #ccc;background-color:#eee;">[{$errorDetail}]</pre>
        [{/if}]
    </div>
    [{/if}]
</div>
<script>
    (function () {
        'use strict';

        var $popup     = $('popup-object-ng-[{$return_element}]'),
            $leftPane  = $('leftPane'),
            $firstLi   = $leftPane.down('li'),
            $rightPane = $('rightPane'),
            $searchBar = $('obj-filter');

        $popup.select('.popup-closer').invoke('on', 'click', function () {
            popup_close();
        });

        $leftPane.on('click', 'li', function (ev) {
            var $li           = ev.findElement('li'),
                $objectList   = $('objectList'),
                $secondList   = $('portList'),
                $newObject    = $('new-object-button'),
                conditionName = $li.readAttribute('data-condition'),
                $group        = $rightPane.down('[data-condition-view="' + conditionName + '"]'),
                $parameter;

            $leftPane.select('li.selected').invoke('removeClassName', 'selected');

            $$('.browserContent .groups').invoke('addClassName', 'hide');

            $li.addClassName('selected');

            if ($group) {
                $parameter = $group.removeClassName('hide').down('select,input');

                $objectList.removeClassName('hide');

                if ($secondList) {
                    $secondList.removeClassName('hide');
                }

                $newObject.removeClassName('hide');

                if (conditionName === 'idoit.Component.Browser.Condition.SearchCondition') {
                    // Resetting the "internal" search string is necessary to trigger a new search by simulating a change event.
                    window.browserList.searchString = '';
                    window.browserList.showError('[{isys type='lang' ident='LC__CMDB__OBJECT_BROWSER__ENTER_SEARCH_PHRASE'}]');
                } else if (conditionName === 'locationView') {
                    // Special treatment for location view
                    $parameter = false;
                    $objectList.addClassName('hide');

                    if ($secondList) {
                        $secondList.addClassName('hide');
                    }
                } else if (conditionName === 'selectedObjects') {
                    // Special treatment for selected objects.
                    $parameter = false;
                    $objectList.addClassName('hide');

                    $newObject.addClassName('hide');

                    if ($secondList) {
                        $secondList.addClassName('hide');
                    }
                }

                $group.removeClassName('hide');

                if ($parameter) {
                    $parameter.focus();
                    $parameter.simulate('change');
                }
            }
        });

        // Select the first object filter.
        if ($firstLi) {
            $firstLi.simulate('click');

            (function(){
                if (!'[{$defaultObjectTypeFilter}]'.blank()) {
                    return;
                }

                var $openContainer = $rightPane.down('[data-condition-view="' + $firstLi.readAttribute('data-condition') + '"]');

                if (!$openContainer) {
                    return;
                }

                var $condition = $openContainer.down('select');

                if (!$condition) {
                    return;
                }

                var $firstValidOption = $condition.down('option[value!="-1"]');

                if ($firstValidOption) {
                    $condition.setValue($firstValidOption.readAttribute('value')).simulate('change');
                }
            })();
        }

        // Check whether search bar exists or not
        if ($searchBar) {
            $searchBar.on('keydown', function (ev) {
                if (ev.keyCode === Event.KEY_RETURN) {
                    ev.preventDefault();
                    ev.stopPropagation();
                    return false;
                }
            });

            $searchBar.on('keyup', function () {
                delay(function () {
                    window.browserList.search($searchBar);
                }, 500, 'searchbar.keypress');
            });

            $searchBar.on('change', function () {
                window.browserList.search($searchBar);
            });
        }

        $popup.select('select.chosen-select').each(function ($element) {
            var width = null;

            if ($element.getWidth() === 0) {
                width = '290px';
            }

            new Chosen($element, {
                default_multiple_text:     '[{isys type="lang" ident="LC__UNIVERSAL__CHOOSEN_PLACEHOLDER" p_bHtmlEncode=false}]',
                placeholder_text_multiple: '[{isys type="lang" ident="LC__UNIVERSAL__CHOOSEN_PLACEHOLDER" p_bHtmlEncode=false}]',
                no_results_text:           '[{isys type="lang" ident="LC__UNIVERSAL__CHOOSEN_EMPTY" p_bHtmlEncode=false}]',
                disable_search_threshold:  10,
                search_contains:           true,
                width:                     width
            });
        });
    })();
</script>
