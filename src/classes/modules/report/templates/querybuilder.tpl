<style type="text/css">
[{include file="./style.css"}]
</style>

<div class="bg-white report-query-builder">
    <input type="hidden" name="report_id" id="report_id" value="[{$report_id}]">
    <input type="hidden" name="queryBuilder" value="1">

    <fieldset class="overview border-top-none">
        <legend><span>[{isys type="lang" ident="LC__REPORT__INFO__NAME_AND_DESCRIPTION"}]</span></legend>

        <div class="p10">
            <table class="contentTable">
                <tr>
                    <td class="key">[{isys type="f_label" name="title" ident="LC__REPORT__FORM__TITLE"}]</td>
                    <td class="value">[{isys type="f_text" name="title" id="title" p_strClass="reportInput" p_bEditMode=1 p_strValue=$report_title}]</td>
                </tr>
                <tr>
                    <td class="key">[{isys type="f_label" name="report_category" ident="LC_UNIVERSAL__CATEGORY"}]</td>
                    <td class="value">[{isys type="f_dialog" p_bDbFieldNN=1 p_arData=$category_data p_strSelectedID=$category_selected name="report_category" id="report_category" p_strClass="reportInput" p_bEditMode=1}]</td>
                </tr>
                <tr>
                    <td class="key">[{isys type="f_label" name="title" ident="LC__REPORT__FORM__CATEGORY_REPORT"}]</td>
                    <td class="value">[{isys type="checkbox" name="category_report" id="category_report" p_strClass="reportInput" p_bEditMode=1 p_bChecked=$isCategoryReport p_bDisabled=1}]</td>
                    <input type="hidden" name="category_report" value="[{if $isCategoryReport}]on[{else}]0[{/if}]"/>
                </tr>
                <tr>
                    <td class="key">[{isys type="f_label" name="description" ident="LC__REPORT__FORM__DESCRIPTION"}]</td>
                    <td class="value">[{isys type="f_textarea" name="description" p_nRows="5" p_bEditMode=1 p_strValue=$report_description}]</td>
                </tr>
            </table>
        </div>
    </fieldset>

    <fieldset class="overview" style="margin-top: 20px;">
        <legend>
            <span class="no-arrow">[{isys type="lang" ident="LC__REPORT__FORM__ADVANCED_OPTIONS"}]<button type="button" class="ml10 vam btn btn-small" id="report_advanced_options_button">[{isys type="lang" ident="LC__REPORT__FORM__ADVANCED_OPTIONS_SHOW"}]</button></span>
        </legend>
        <div class="p10 pt5 pb5">
            <div class="hide" id="report_advanced_options">

                <table class="mt10 mb5">
                    <tr>
                        <td class="key">[{isys type="f_label" ident="LC__REPORT__FORM__SHOW_HTML"}]</td>
                        <td class="value">[{isys type="f_dialog" p_bDbFieldNN=1 p_arData=$yes_or_no p_strSelectedID=$show_html p_bSort=false name="show_html" p_strClass="input-mini" p_bEditMode=1}]</td>
                    </tr>
                    <tr>
                        <td class="key">[{isys type="f_label" ident="LC__REPORT__FORM__COMPRESSED_MULTIVALUE_RESULTS"}]</td>
                        <td class="value">[{isys type="f_dialog" p_bDbFieldNN=1 p_arData=$yes_or_no p_strSelectedID=$compressed_multivalue_results p_bSort=false name="compressed_multivalue_results" p_strClass="input-mini" p_bEditMode=1}]</td>
                    </tr>
                    <tr>
                        <td><label for="empty_values">[{isys type="lang" ident="LC__REPORT__FORM__SHOW_EMPTY_VALUES"}]</label></td>
                        <td>[{isys type="f_dialog" p_bDbFieldNN=1 p_arData=$yes_or_no p_strSelectedID=$empty_values_selected p_bSort=false name="empty_values" p_strClass="input-mini" p_bEditMode=1}]</td>
                    </tr>
                    <tr>
                        <td><label for="keep_description_format">[{isys type="lang" ident="LC__REPORT__FORM__KEEP_DESCRIPTION_FORMAT"}]</label></td>
                        <td>[{isys type="f_dialog" p_bDbFieldNN=1 p_arData=$yes_or_no p_strSelectedID=$keep_description_format p_bSort=false name="keep_description_format" p_strClass="input-mini" p_bEditMode=1}]</td>
                    </tr>
                    <tr>
                        <td><label for="display_relations">[{isys type="lang" ident="LC__REPORT__FORM__DISPLAY_RELATION_OBJECTS"}]</label></td>
                        <td>[{isys type="f_dialog" p_bDbFieldNN=1 p_arData=$yes_or_no p_strSelectedID=$display_relations_selected p_bSort=false name="display_relations" p_strClass="input-mini" p_bEditMode=1}]</td>
                    </tr>
                    <tr>
                        <td class="key">[{isys type="f_label" name="sorting_direction" ident="LC__REPORT__INFO__SORTING"}]</td>
                        <td class="value">[{isys type="f_dialog" name="sorting_direction" p_bDbFieldNN='1' p_arData=$sorting_data p_strSelectedID=$sorting_direction p_bEditMode=1 p_strClass="input-mini" disableInputGroup=true}]</td>
                    </tr>
                    <tr>
                        <td class="key">[{isys type="f_label" name="statusFilter" ident="LC__REPORT__INFO__STATUS_FILTER_FOR_MULTIVALUE_CATEGORIES"}]</td>
                        <td class="value">[{isys type="f_dialog" name="statusFilter" p_bDbFieldNN='1' p_arData=$statusFilter p_strSelectedID=$statusFilterValue p_bEditMode=1 p_strClass="input-mini" disableInputGroup=true}]</td>
                    </tr>
                </table>
            </div>
        </div>
    </fieldset>

    <fieldset class="overview">
        <legend><span>[{isys type="lang" ident="LC__REPORT__FORM__OUTPUT"}]<button type="button" class="ml10 vam btn btn-small checkup">[{isys type="lang" ident="LC__REPORT__FORM__CHECK"}]</button></span></legend>

        <div class="p10">
            <p class="mt10 mb10">[{isys type="lang" ident="LC__REPORT__INFO__ATTRIBUTE_CHOOSER_TEXT"}]</p>
            [{isys type="f_property_selector"
                grouping=false
                sortable=true
                p_bInfoIconSpacer=0
                p_bEditMode=true
                name="report"
                p_bInfoIcon=false
                provide=$smarty.const.C__PROPERTY__PROVIDES__REPORT
                p_consider_rights=true
                custom_fields=true
                report=true
                allow_sorting=true
                check_sorting=true
                default_sorting=$default_sorting
                preselection=$preselection_data
                preselection_lvls=$preselection_lvls
                replace_dynamic_properties=true}]
        </div>
    </fieldset>


    <fieldset class="overview">
        <legend><span>[{isys type="lang" ident="LC__REPORT__FORM__CONDITIONS"}]<button type="button" class="ml10 vam btn btn-small checkup">[{isys type="lang" ident="LC__REPORT__FORM__CHECK"}]</button></span></legend>

        <div id="condition_overlay" style="position:absolute; display:[{if $report_id == '' || $querybuilder_conditions == ''}]none[{/if}]; top:0; left:0; height:100%; width: 100%;">
            <div style="position:absolute; z-index: 1001; opacity: 0.4; background: #FFF; height:100%; width: 100%;">
            </div>
            <div class="mt10" style="position:absolute; left:50%; margin-left: -108px; text-align: center; z-index: 1100;">
                <span class="vam"><b>[{isys type="lang" ident="LC__REPORT__FORM__LOADING_CONDITIONS"}] </b><img src="[{$dir_images}]ajax-loading.gif" class="vam"/></span>
            </div>
        </div>

        <div id="ReportCondtionBlockTemplate" class="constraintDiv p10" style="display:none;min-width:805px;max-width: 58%">
            <span class="remove"></span>
            <span class="add"></span>
            <table id="ReportConditionTemplate" class="reportCondtionsTable">
                <tr>
                    <td>
                        <label class="reportLabel">[{isys type="lang" ident="LC__REPORT__FORM__CATEGORY"}]</label>
                    </td>
                    <td>
                        <select id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_category" class="input reportDialog conditionCategory fl" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][category]" size="1" style="width:275px;">
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label class="reportLabel">[{isys type="lang" ident="LC__REPORT__FORM__PROPERTY"}]</label>
                    </td>
                    <td>
                        <select id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_property" class="input reportDialog2 conditionProperty fl" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][property]" size="1" style="width:275px;">
                        </select>
                        <span id="ReportConditionTemplateValue">
                            <select id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_comparison" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][comparison]" class="input reportDialog2 conditionComparison fl" style="width:80px;">
                                <option value="=">=</option>
                                <option value="&lt;">&lt;</option>
                                <option value="&gt;">&gt;</option>
                                <option value="!=">!=</option>
                                <option value="&lt;=">&lt;=</option>
                                <option value="&gt;=">&gt;=</option>
                                <option value="LIKE">LIKE</option>
                                <option value="LIKE %...%">LIKE %...%</option>
                                <option value="NOT LIKE">NOT LIKE</option>
                                <option value="NOT LIKE %...%">NOT LIKE %...%</option>
                            </select>
                            <input id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_value" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][value]" class="input reportInput conditionValue fl" style="width:140px;">
                            <select id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_unit" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][unit]" class="input reportDialog2 conditionUnit fl" style="width:60px;display:none;" disabled></select>
                            <select id="ReportConditionTemplateOperator" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][operator]" class="input reportDialog2 conditionOperator ml20" style="width:60px;display:none;">
                                <option value="AND">[{isys type="lang" ident="LC__UNIVERSAL__AND" p_func="strtoupper"}]</option>
                                <option value="OR">[{isys type="lang" ident="LC_UNIVERSAL__OR" p_func="strtoupper"}]</option>
                            </select>
                        </span>
                    </td>
                </tr>
                <tr style="display: none;" id="ReportConditionSubTemplateBlock" class="reportConditionsSubBlock">
                    <td colspan="2">
                        <div class="constraintSubDiv">
                            <span class="subremove"></span>
                            <span class="subadd"></span>
                            <table id="ReportConditionSubTemplate" class="reportCondtionsSubTable ml10 p10">
                                <tr>
                                    <td>
                                        <label class="reportLabel">[{isys type="lang" ident="LC__REPORT__FORM__CATEGORY"}]</label>
                                    </td>
                                    <td>
                                        <select id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_#{queryConditionSubLvl}_#{queryConditionSubLvlProp}_category" class="input reportDialog conditionSubCategory fl" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][subcnd][#{queryConditionSubLvlProp}][#{queryConditionSubLvl}][category]" size="1" style="width:275px;">
                                        </select>
                                    </td>
                                </tr>
                               <tr>
                                    <td>
                                        <label class="reportLabel">[{isys type="lang" ident="LC__REPORT__FORM__PROPERTY"}]</label>
                                    </td>
                                    <td>
                                        <select id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_#{queryConditionSubLvl}_#{queryConditionSubLvlProp}_property" class="input reportDialog2 conditionSubProperty fl" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][subcnd][#{queryConditionSubLvlProp}][#{queryConditionSubLvl}][property]" size="1" style="width:275px;">
                                        </select>
                                        <span id="ReportConditionSubTemplateValue">
                                            <select id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_#{queryConditionSubLvl}_#{queryConditionSubLvlProp}_comparison" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][subcnd][#{queryConditionSubLvlProp}][#{queryConditionSubLvl}][comparison]" class="input reportDialog2 conditionSubComparison fl" style="width:80px;">
                                                <option value="=">=</option>
                                                <option value="&lt;">&lt;</option>
                                                <option value="&gt;">&gt;</option>
                                                <option value="!=">!=</option>
                                                <option value="&lt;=">&lt;=</option>
                                                <option value="&gt;=">&gt;=</option>
                                                <option value="LIKE">LIKE</option>
                                                <option value="LIKE %...%">LIKE %...%</option>
                                                <option value="NOT LIKE">NOT LIKE</option>
                                                <option value="NOT LIKE %...%">NOT LIKE %...%</option>
                                            </select>
                                            <input id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_#{queryConditionSubLvl}_#{queryConditionSubLvlProp}_value" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][subcnd][#{queryConditionSubLvlProp}][#{queryConditionSubLvl}][value]" class="input reportInput conditionSubValue fl" style="width:140px;">
                                            <select id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_#{queryConditionSubLvl}_#{queryConditionSubLvlProp}_unit" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][subcnd][#{queryConditionSubLvlProp}][#{queryConditionSubLvl}][unit]" class="input reportDialog2 conditionSubUnit fl" style="width:60px;display:none;" disabled></select>
                                            <select id="ReportConditionSubTemplateOperator" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][subcnd][#{queryConditionSubLvlProp}][#{queryConditionSubLvl}][operator]" class="input reportDialog2 conditionSubOperator ml20" style="width:60px;display:none;">
                                                <option value="AND">[{isys type="lang" ident="LC__UNIVERSAL__AND" p_func="strtoupper"}]</option>
                                                <option value="OR">[{isys type="lang" ident="LC_UNIVERSAL__OR" p_func="strtoupper"}]</option>
                                            </select>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="p10 mt10">
            <p>[{isys type="lang" ident="LC__REPORT__INFO__DIVISION_TEXT"}]</p>
            <ul style="margin-top: 5px;">
                <li>[{isys type="lang" ident="LC__REPORT__INFO__DIVISION_POINT1"}]</li>
                <li>[{isys type="lang" ident="LC__REPORT__INFO__DIVISION_POINT2"}]</li>
            </ul>

            <div id="dyn" style="min-height:50px">
                <div id="dvsn-remover" class="constraintDiv p10" style="width: 805px;">[{isys type="lang" ident="LC__REPORT__FORM__NO_CONSTRAINTS_ADDED"}]</div>
            </div>

            <button type="button" class="btn btn-small"><img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__REPORT__FORM__BUTTON__ADD_CONDITION_BLOCK"}]</span></button>

            <div id="errors"></div>
        </div>
    </fieldset>

<script type="text/javascript">
idoit.Translate.set('LC__REPORT__NO_ATTRIBUTES_FOUND', '[{isys type="lang" ident="LC__REPORT__NO_ATTRIBUTES_FOUND"}]');
idoit.Translate.set('LC__REPORT__EMPTY_RESULT', '[{isys type="lang" ident="LC__REPORT__EMPTY_RESULT"}]');
idoit.Translate.set('LC__REPORT__PLACEHOLDER__OBJECT', '[{isys type="lang" ident="LC__REPORT__PLACEHOLDER__OBJECT"}]');
idoit.Translate.set('LC__REPORT__PLACEHOLDER__UNEQUAL_OBJECT', '[{isys type="lang" ident="LC__REPORT__PLACEHOLDER__UNEQUAL_OBJECT"}]');
idoit.Translate.set('LC__REPORT__PLACEHOLDER__CURRENT_USER', '[{isys type="lang" ident="LC__REPORT__PLACEHOLDER__CURRENT_USER"}]');
idoit.Translate.set('LC__REPORT__PLACEHOLDER__GREATER_DATETIME', '[{isys type="lang" ident="LC__REPORT__PLACEHOLDER__GREATER_DATETIME"}]');
idoit.Translate.set('LC__REPORT__PLACEHOLDER__LOWER_DATETIME', '[{isys type="lang" ident="LC__REPORT__PLACEHOLDER__LOWER_DATETIME"}]');
idoit.Translate.set('LC__REPORT__PLACEHOLDER__REGEX', '[{isys type="lang" ident="LC__REPORT__PLACEHOLDER__REGEX"}]');

[{include file="./report.js"}]
[{include file="./report_condition.js"}]

var $advancedOptionsButton = $('report_advanced_options_button'),
    $advancedOptionsContainer = $('report_advanced_options');

$advancedOptionsButton.on('click', function(){
    $advancedOptionsContainer.toggleClassName('hide');

    if ($advancedOptionsContainer.hasClassName('hide')) {
        $advancedOptionsButton.update('[{isys type="lang" ident="LC__REPORT__FORM__ADVANCED_OPTIONS_SHOW"}]');

        $advancedOptionsButton.up('span').addClassName('no-arrow')
    } else {
        $advancedOptionsButton.update('[{isys type="lang" ident="LC__REPORT__FORM__ADVANCED_OPTIONS_HIDE"}]');

        $advancedOptionsButton.up('span').removeClassName('no-arrow')
    }
});

$$('.checkup').invoke('on', 'click', function(e){
    var lvls_content = new Hash,
        lvl = 1;

    $$('tr.selector-spacer').each(function (ele) {
        var child_elements = ele.getElementsByTagName('input'),
            lvls = new Hash,
            a;

        for (a in child_elements)
        {
            if (child_elements.hasOwnProperty(a) && !isNaN(a))
            {
                if ((a % 2) != 0)
                {
                    lvls.set(child_elements[a].id.split('_' + lvl)[0], child_elements[a].value);
                }
            }
        }
        lvls_content.set(lvl, lvls);

        lvl++;
    });

    var l_parameters = {
                    'report__HIDDEN_IDS': $F('report__HIDDEN_IDS'),
                    'lvls': Object.toJSON(lvls_content),
                    'func':'report_preview'
                };

    get_popup('report', '', 800, 508, l_parameters);

    /*if ($$('option[value="PLACEHOLDER"]:selected').length > 0) {
        idoit.Notify.info('[{isys type="lang" ident="LC__REPORT__REPORT_PREVIEW__NO"}]')
    } else {
        get_popup('report', '', 800, 508, l_parameters);
    }*/
});


var report_condition = new ReportCondition();

$('dyn').up().down('button').on('click', function(){
    report_condition.addConditionBlock();
}.bind(report_condition));

[{if $querybuilder_conditions}]
    report_condition.handle_preselection([{$querybuilder_conditions}]);
[{/if}]

/**
 * Retrieve report id after saving to prevent saving duplicate reports
 */
document.on('form:saved', function(ev) {
    if ($('report_id')) {
        if (ev.memo.response.responseJSON) {
            var response = ev.memo.response.responseJSON;

            if (response.id) {
                $('report_id').value = response.id;
            }
        }
    }
});

</script>
