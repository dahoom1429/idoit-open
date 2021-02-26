[{extends './base_list_config.tpl'}]
[{block 'properties'}]
	[{isys
		type="f_property_selector"
		obj_type_id=$list_obj_type_id
		p_bInfoIconSpacer=0
		name="list"
		p_strStyle="margin-left:5px;"
		custom_fields=true
		grouping=false
		sortable=true
		preselection=$selected_properties
		dynamic_properties=true
		provide=$provides
		allow_sorting=true
		default_sorting=$default_sorting
		check_sorting=true
		p_consider_rights=true
		max_items=isys_tenantsettings::get('cmdb.limits.object-table-columns', 0)
		callback_add="window.updateDefaultFilterAttributes"
		callback_remove="window.updateDefaultFilterAttributes"}]
[{/block}]

[{block 'extra_js'}]
var $selectDefaultFilter = $('default_filter_field'),
	constantToDaoMatcher = {};
window.updateDefaultFilterAttributes = function () {
    var $selectedProperties = $('list_selection_field').select('.property'),
        label,
        attributeLabel,
        categoryLabel,
        categoryConst,
        propertyKey,
        defaultFilterList   = [],
        constantsToLoad     = [],
        i;

    $selectDefaultFilter.store('previousValue', $selectDefaultFilter.getValue()).disable();

    for (i in $selectedProperties) {
        if (!$selectedProperties.hasOwnProperty(i)) {
            continue;
        }

        propertyKey = $selectedProperties[i].readAttribute('data-propkey');
        categoryConst = $selectedProperties[i].readAttribute('data-catconst');

        label = $selectedProperties[i].down('span').innerText;
        categoryLabel = '';

        try {
            label = label.match(/(.*)\((.*?)\)$/);
            categoryLabel = label[2] + ' > ';
            attributeLabel = label[1]
        } catch (e) {
            attributeLabel = $selectedProperties[i].down('span').innerText;
        }

        if (!constantToDaoMatcher.hasOwnProperty(categoryConst)) {
            constantsToLoad.push(categoryConst);
        }

        defaultFilterList.push({
            categoryConst: categoryConst,
            label:         categoryLabel + attributeLabel,
            propertyKey:   propertyKey,
            daoClass:      constantToDaoMatcher[categoryConst] || null,
            value:         (constantToDaoMatcher[categoryConst] || '') + '__' + propertyKey
        });
    }

    if (defaultFilterList.length) {
        window.processDefaultFilterValues(defaultFilterList, constantsToLoad);
    }
};

window.processDefaultFilterValues = function (defaultFilterList, constantsToLoad) {
    var i;

    if (constantsToLoad.length) {
        new Ajax.Request('?call=get_category_data&ajax=1&func=get_dao_classes_by_constants', {
            parameters: {
                constants: constantsToLoad.join()
            },
            onComplete: function (xhr) {
                var json = xhr.responseJSON;

                if (json.success) {
                    for (i in defaultFilterList) {
                        if (!defaultFilterList.hasOwnProperty(i) || !json.data.hasOwnProperty(defaultFilterList[i].categoryConst)) {
                            continue;
                        }

                        // Fill up the matcher.
                        constantToDaoMatcher[defaultFilterList[i].categoryConst] = json.data[defaultFilterList[i].categoryConst];

                        // Set daoClass and value
                        defaultFilterList[i].daoClass = json.data[defaultFilterList[i].categoryConst];
                        defaultFilterList[i].value = defaultFilterList[i].daoClass + '__' + defaultFilterList[i].propertyKey;
                    }

                    window.renderDefaultFilterSelect(defaultFilterList);
                } else {
                    idoit.Notify.error(json.message, {sticky: true});
                }
            }
        });
    } else {
        window.renderDefaultFilterSelect(defaultFilterList);
    }
};

window.renderDefaultFilterSelect = function (defaultFilterList) {
    var i;

    $selectDefaultFilter.update();
    $selectDefaultFilter.enable();

    defaultFilterList.sort(function (a, b) {
        return a.label.localeCompare(b.label);
    });

    for (i in defaultFilterList) {
        if (!defaultFilterList.hasOwnProperty(i)) {
            continue;
        }

        if (defaultFilterList[i].daoClass === 'isys_cmdb_dao_category_g_custom_fields') {
            continue;
        }

        // isys_cmdb_dao_category_g_global__title
        $selectDefaultFilter.insert(new Element('option', {value: defaultFilterList[i].value}).update(defaultFilterList[i].label))
    }

    $selectDefaultFilter.setValue($selectDefaultFilter.retrieve('previousValue'));
    $selectDefaultFilter.simulate('change');
};
[{/block}]