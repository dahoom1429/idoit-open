var ReportCondition = Class.create({
    initialize:function (options) {

        this.options = Object.extend({
            conditions: '[]',
            catg_optgroup: null,
            cats_optgroup: null,
            catg_custom_optgroup: null,
            block_count: 0,
            parent_div: 'dyn',
            reload_finished: false,
            overlay: 'condition_overlay',
            user_input_fields: []
        }, options || {});

        new Ajax.Request('?ajax=1&call=report&func=add_division',
            {
                method: "post",
                onSuccess: function(transport) {

                    var json = transport.responseJSON;

                    if(json.error == true)
                    {
                        idoit.Notify.error(json.message);
                    }
                    else
                    {
                        var catg = $H(json.data.catg),
                            cats = $H(json.data.cats),
                            catg_custom = $H(json.data.catg_custom),
                            cnd_tpl = $('ReportConditionTemplate').down('select.conditionCategory'),
                            cnd_sub_tpl = $('ReportConditionSubTemplate').down('select.conditionSubCategory');

                        this.options.catg_optgroup = new Element('optgroup', {'label': '[{isys type="lang" ident="LC__CMDB__OBJTYPE__CATG"}]'});
                        this.options.cats_optgroup = new Element('optgroup', {'label': '[{isys type="lang" ident="LC__CMDB__OBJTYPE__CATS"}]'});
                        this.options.catg_custom_optgroup = new Element('optgroup', {'label': '[{isys type="lang" ident="LC__CMDB__CUSTOM_CATEGORIES"}]'});

                        catg.each(function(pair) {
                            this.options.catg_optgroup.insert(new Element('option', {'value': pair.value}).update(pair.key));
                        }.bind(this));

                        cats.each(function(pair) {
                            this.options.cats_optgroup.insert(new Element('option', {'value': pair.value}).update(pair.key));
                        }.bind(this));

                        catg_custom.each(function(pair) {
                            this.options.catg_custom_optgroup.insert(new Element('option', {'value': pair.value}).update(pair.key));
                        }.bind(this));

                        cnd_tpl.insert(this.options.catg_optgroup).insert(this.options.cats_optgroup).insert(this.options.catg_custom_optgroup);
                        cnd_sub_tpl.insert(this.options.catg_optgroup.clone(true)).insert(this.options.cats_optgroup.clone(true)).insert(this.options.catg_custom_optgroup.clone(true));

                        // We call this function to fill the second select-field.
                        this.addPropertiesToCondition(cnd_tpl);
                        this.addPropertiesToCondition(cnd_sub_tpl, null, true);
                    }
                }.bind(this)
            });
        
        this.loadPlaceholderUserInputFields();
    },
    addPropertiesToCondition: function(categoryElement, p_data, p_subCondition){

        var selectedCategory = categoryElement.options[categoryElement.selectedIndex].value;

        new Ajax.Request('?ajax=1&call=report&func=add_property_selection_to_division',
            {
                parameters: {
                    'cat_id': selectedCategory
                },
                method: "post",
                onSuccess: function(transport) {
                    var json = transport.responseJSON;
                    var select = null;

                    if(json.error == true)
                    {
                        idoit.Notify.error(json.message);
                    }
                    else
                    {
                        var jsonData = $H(json.data);

                        if(p_subCondition)
                        {
                            select = categoryElement.up('table').down('select.conditionSubProperty').update('');
                        }
                        else
                        {
                            select = categoryElement.up('table').down('select.conditionProperty').update('');
                        }

                        if (transport.responseText == '[]') {
                            select.insert(new Element('option').update(idoit.Translate.get('LC__REPORT__NO_ATTRIBUTES_FOUND')));
                            return;
                        }

                        jsonData.each(function(pair) {
                            select.insert(new Element('option', {'value': pair.key}).update(pair.value));
                        });

                        if(p_data)
                        {
                            select.value = p_data.property;
                        }
                        if(categoryElement.up('div').id != 'ReportCondtionBlockTemplate' && categoryElement.up('table').id != 'ReportConditionSubTemplate')
                        {
                            this.modifyConditionValue(select, p_data, p_subCondition);
                        }
                    }
                }.bind(this)
            });
    },
    modifyConditionValue: function(propertyElement, p_data, p_subCondition) {
        var prop_id = propertyElement.value;
        var condition_info = propertyElement.id.split('_');
        var cndTemplateValue = '';
        var cndTemplateID = '';
        var cndValueClass = '';
        var cndOperatorClass = '';
        var cndUnitClass = '';
        var cndUserInputClass = '';
        var cndComparisionClass = '';
        var valueId = null;
        var assignments = null;
        var valueName = '';
        var property_id = '';

        if(p_subCondition)
        {
            cndTemplateValue = 'ReportConditionSubTemplateValue';
            cndTemplateID = 'querycondition_#{queryConditionBlock}_#{queryConditionLvl}_#{queryConditionSubLvl}_operator';
            cndValueClass = 'conditionSubValue';
            cndOperatorClass = 'conditionSubOperator';
            cndUnitClass = 'conditionSubUnit';
            cndComparisionClass = 'conditionSubComparison';
            valueId = 'querycondition_'+condition_info[1]+'_'+condition_info[2]+'_'+condition_info[3]+'_value';
            propertyID = propertyElement.id.substring(propertyElement.id.indexOf('_C_')+1, propertyElement.id.indexOf('property')-1);

            assignments = {
                queryConditionBlock: condition_info[1],
                queryConditionLvl: condition_info[2],
                queryConditionSubLvl: condition_info[3],
                queryConditionSubLvlProp: propertyID
            };
        }
        else
        {
            cndTemplateValue = 'ReportConditionTemplateValue';
            cndTemplateID = 'querycondition_#{queryConditionBlock}_#{queryConditionLvl}_operator';
            cndValueClass = 'conditionValue';
            cndOperatorClass = 'conditionOperator';
            cndUnitClass = 'conditionUnit';
            cndUserInputClass = 'conditionUserInput';
            cndComparisionClass = 'conditionComparison';
            valueId = 'querycondition_'+condition_info[1]+'_'+condition_info[2]+'_value';

            assignments = {
                queryConditionBlock: condition_info[1],
                queryConditionLvl: condition_info[2]
            };
        }

        if(propertyElement.up().down('span').down('.'+cndValueClass).id.indexOf('VIEW') > 0)
        {
            valueName = propertyElement.up().down('span').down('.'+cndValueClass).next('input').readAttribute('name');
        }
        else
        {
            valueName = propertyElement.up().down('span').down('.'+cndValueClass).readAttribute('name');
        }

        property_id = '';

        if(p_data){
            property_id = p_data.value;
        }

        if (prop_id != '') {

            new Ajax.Request('?ajax=1&call=report&func=add_contraint_to_property',
                {
                    parameters: {
                        'division': valueName,
                        'prop_id': prop_id,
                        'prop_class': cndValueClass,
                        'value': property_id
                    },
                    method: "post",
                    onSuccess: function(transport) {

                        var operatorDisabled = propertyElement.next('span').down('.'+cndOperatorClass).disabled;
                        Element.remove(propertyElement.next('span'));

                        var span = $(cndTemplateValue).clone(true);
                        span.removeAttribute('id');
                        span.down('.'+cndOperatorClass).id = cndTemplateID;

                        // Template for the condition equation and value
                        var tpl = new Template(span.innerHTML);
                        span.innerHTML = tpl.evaluate(assignments);

                        var json = transport.responseJSON,
                            equation = json.equation,
                            value_field = span.down('.'+cndValueClass),
                            unit_field = span.down('.'+cndUnitClass),
                            operator_field = span.down('.'+cndOperatorClass),
                            special_field = json.special_field,
                            field = json.field,
                            unit = json.unit,
                            user_input = null,
                            tmp, i,
                            comparison_field = span.down('select.'+cndComparisionClass),
                            initialField = comparison_field.next();

                        comparison_field.stopObserving();

                        operator_field.disabled = true;
                        unit_field.disabled = true;

                        comparison_field.update('');

                        for (i in equation)
                        {
                            if (!equation.hasOwnProperty(i)) continue;

                            var val = equation[i];
                            if (equation[i] == '&lt;') val = '<';
                            if (equation[i] == '&gt;') val = '>';
                            if (equation[i] == '&lt;=') val = '<=';
                            if (equation[i] == '&gt;=') val = '>=';

                            if (equation[i] == 'under_location')
                            {
                                comparison_field.insert(new Element('option', {value: val}).update('[{isys type="lang" ident="LC__REPORT__FORM__CONDITIONS__BELOW"}]'));
                            }
                            else if (equation[i] == 'subcnd')
                            {
                                comparison_field.insert(new Element('option', {value: val}).update('[{isys type="lang" ident="LC__REPORT__FORM__CONDITIONS__ASSIGNED_ATTRIBUTE"}]'));
                            }
                            else if (equation[i] == 'PLACEHOLDER')
                            {
                                comparison_field.insert(new Element('option', {value: val}).update('[{isys type="lang" ident="LC__REPORT__FORM__CONDITIONS__PLACEHOLDER"}]'));
                            }
                            else
                            {
                                comparison_field.insert(new Element('option', {value: val}).update(equation[i]));
                            }
                        }

                        if(p_data){
                            comparison_field.value = p_data.comparison;
                            
                            var level = p_subCondition === true ? 'subLevel' : 'firstLevel';
                            
                            if (this.options.user_input_fields[p_data.value] !== undefined) {
                                if (this.options.user_input_fields[p_data.value][level] !== undefined) {
                                    user_input = this.options.user_input_fields[p_data.value][level];
                                }
                            }
                        }

                        if(($('queryconditionblock_'+condition_info[1]).children.length-2) > 1 && !operatorDisabled && !p_subCondition) {
                            operator_field.show();
                            operator_field.disabled = false;
                            if(p_data){
                                operator_field.value = p_data.operator;
                            }
                        }
                        else if(p_subCondition){
                            if((propertyElement.up('div.constraintSubDiv').children.length-2) > 1 && !operatorDisabled) {
                                operator_field.show();
                                operator_field.disabled = false;
                                if(p_data){
                                    operator_field.value = p_data.operator;
                                }
                            }
                        }

                        if (special_field != null && comparison_field.value != 'PLACEHOLDER')
                        {
                            Element.remove(initialField);
                            comparison_field.insert({after: special_field});
                        } else {
                            if (field != null)
                            {
                                if (Object.toJSON(field) == '[]')
                                {
                                    value_field.disabled = true;
                                    value_field.value = idoit.Translate.get('LC__REPORT__EMPTY_RESULT');
                                }
                                else
                                {
                                    tmp = $H(field);
                                    Element.remove(value_field);
                                    value_field = new Element('select', {
                                        'id':        valueId,
                                        'name':      valueName,
                                        'className': 'input reportDialog ' + cndValueClass,
                                        style:       'width:140px;'
                                    });

                                    tmp.each(function (pair) {
                                        value_field.insert(new Element('option', {'value': pair.key.replace(' ', '')}).update(pair.value));
                                    });

                                    if (p_data)
                                    {
                                        value_field.value = p_data.value;
                                    }

                                    span.down('select.' + cndComparisionClass).insert({after: value_field});
                                }
                            }
                            else if (p_data)
                            {
                                if(
                                    p_data.comparison != 'IS NULL' &&
                                    p_data.comparison != 'IS NOT NULL' &&
                                    p_data.comparison != 'PLACEHOLDER'
                                ) {
                                    value_field.value = p_data.value;
                                } else if (p_data.comparison == 'PLACEHOLDER') {
                                    Element.remove(value_field);
                                    value_field = new Element('select', {
                                        'id':        valueId,
                                        'name':      valueName,
                                        'className': 'input reportDialog ' + cndValueClass,
                                        style:       'width:140px;'
                                    });
                                    
                                    this.appendPlaceholderOptions(value_field);
    
                                    if (p_data)
                                    {
                                        value_field.value = p_data.value;
                                    }
    
                                    span.down('select.' + cndComparisionClass).insert({after: value_field});
                                } else {
                                    value_field.disabled = true;
                                    value_field.hide();
                                }
                            }

                            if (unit != null) {
                                unit_field.update('');

                                tmp = $H(unit);

                                tmp.each(function(pair) {
                                    unit_field.insert(new Element('option', {'value': pair.key}).update(pair.value));
                                });

                                if(p_data)
                                {
                                    unit_field.value = p_data.unit;
                                }

                                unit_field.show();
                                unit_field.disabled = false;
                            }
                           
                            if (user_input != null) {
                                var placeholderTemplate = new Template(user_input);
    
                                value_field.insert({after: placeholderTemplate.evaluate(assignments)});
                                value_field.next().value = p_data['user_input'] ? p_data['user_input'] : '';
                            }
                        }

                        comparison_field.on('change', function(ele){
                            if (ele.findElement().value !== 'PLACEHOLDER' &&
                                ele.findElement().next().hasClassName('conditionValue') &&
                                ele.findElement().next().next().hasClassName('reportInput'))
                            {
                                if (comparison_field.next().next()) {
                                    comparison_field.next().next().remove();
                                }
        
                                if (special_field != null) {
                                    initialField = special_field;
                                }
                                
                                comparison_field.next().remove();
                                comparison_field.insert({after: initialField});
                            }
                            
                            if(ele.findElement().value == 'subcnd') {
                                var prop_assignment = '';

                                if(typeof propertyID != 'undefined'){
                                    var parent_info = [];
                                    if(condition_info.length > 4)
                                    {
                                        for(a in condition_info)
                                        {
                                            if(a > 3 && a < condition_info.length - 1)
                                            {
                                                parent_info.push(condition_info[a]);
                                            }
                                        }
                                        prop_assignment = parent_info.join('_') + '--' + propertyElement.value;
                                    }
                                    else
                                    {
                                        prop_assignment = propertyElement.value;
                                    }
                                } else{
                                    prop_assignment = propertyElement.value;
                                }

                                var subConditionID = condition_info[0] + '_' + condition_info[1] + '_' + condition_info[2] + '_0_' + prop_assignment;
                                if(comparison_field.next('a', 1)) {
                                    comparison_field.next('a', 1).hide();
                                }

                                if(propertyElement.up('tr').next('tr')) {
                                    this.addSubConditionBlock(propertyElement.up('table'), condition_info);
                                    propertyElement.up('tr').next('tr').show();
                                } else {
                                    this.addSubConditionBlock(propertyElement.up('table'), condition_info);
                                    this.addSubCondition(propertyElement.up('table'), subConditionID);
                                }

                                if(comparison_field.next('div.input-group'))
                                {
                                    comparison_field.next('div.input-group').down('input').disabled = true;
                                    comparison_field.next('div.input-group').hide();
                                }
                                if(comparison_field.next('a', 1)){
                                    comparison_field.next('a', 1).hide();
                                }
                            }
                            else if(ele.findElement().value == 'IS NULL' || ele.findElement().value == 'IS NOT NULL') {
                                if(comparison_field.next('input'))
                                {
                                    comparison_field.next('input').disabled = true;
                                    comparison_field.next('input').hide();
                                }
                                if(comparison_field.next('div.input-group')) {
                                    comparison_field.next('div.input-group').show();
                                    comparison_field.next('div.input-group').down('input').disabled = false;
                                }
                                if(comparison_field.next('a', 1)){
                                    comparison_field.next('a', 1).hide();
                                }
                                if(propertyElement.up('table').lastChild.readAttribute('class') == 'reportConditionsSubBlock') {
                                    this.removeSubConditions(propertyElement.up('table'));
                                }
                            } else if(ele.findElement().value == 'PLACEHOLDER') {
                                if (special_field) {
                                    comparison_field.next().next().remove();
                                }
                                
                                $$('input[name="category_report"]').forEach(function(field) {
                                    field.setValue('on');
                                });
    
                                var placeholderField = new Element('select', {
                                    'id':        valueId,
                                    'name':      valueName,
                                    'className': 'input reportDialog ' + cndValueClass,
                                    style:       'width:140px;'
                                });
    
                                this.appendPlaceholderOptions(placeholderField);
    
                                comparison_field.next().remove();
                                
                                comparison_field.insert({after: placeholderField});
    
                                var level = p_subCondition === true ? 'subLevel' : 'firstLevel';
    
                                if (this.options.user_input_fields[placeholderField.value] !== undefined) {
                                    if (this.options.user_input_fields[placeholderField.value][level] !== undefined) {
                                        var placeholderTemplate = new Template(this.options.user_input_fields[placeholderField.value][level]);
    
                                        placeholderField.insert({after: placeholderTemplate.evaluate(assignments)});
                                    }
                                }

                                if(comparison_field.next('div.input-group')) {
                                    comparison_field.next('div.input-group').show();
                                    comparison_field.next('div.input-group').down('input').disabled = false;
                                }
                                if(comparison_field.next('a', 1)){
                                    comparison_field.next('a', 1).show();
                                }

                                if(propertyElement.up('table').lastChild.readAttribute('class') == 'reportConditionsSubBlock') {
                                    this.removeSubConditions(propertyElement.up('table'));
                                }
                            }else {

                                if(comparison_field.next('input'))
                                {
                                    comparison_field.next('input').disabled = false;
                                    comparison_field.next('input').show();
                                }

                                if(comparison_field.next('div.input-group')) {
                                    comparison_field.next('div.input-group').show();
                                    comparison_field.next('div.input-group').down('input').disabled = false;
                                }
                                if(comparison_field.next('a', 1)){
                                    comparison_field.next('a', 1).show();
                                }

                                if(propertyElement.up('table').lastChild.readAttribute('class') == 'reportConditionsSubBlock') {
                                    this.removeSubConditions(propertyElement.up('table'));
                                }
                            }
    
                            if ($$('option[value="PLACEHOLDER"]:selected').length === 0) {
                                $$('input[name="category_report"]').forEach(function(field) {
                                    field.setValue(0);
                                });
                            }
                        }.bind(this));

                        propertyElement.up().insert(span);
                        if(p_data.comparison == 'subcnd') {
                            var browser_field = span.down('.input-group');

                            browser_field.hide();
                            browser_field.down('input').disabled = true;

                            //comparison_field.next('a').hide();
                            if(comparison_field.next('a', 1)){
                                comparison_field.next('a', 1).hide();
                            }
                            if(propertyElement.up('tr').next('tr')) {
                                propertyElement.up('tr').next('tr').show();
                            } else {
                                this.addSubConditionBlock(propertyElement.up('table'), condition_info, p_data);
                                var prop_assignment = '';
                                if(typeof propertyID != 'undefined'){
                                    prop_assignment = propertyID + '--' + $(condition_info.join('_')).value;
                                } else{
                                    prop_assignment = $(condition_info.join('_')).value;
                                }
                                var blockID = condition_info[1];
                                var conditionLvl = condition_info[2];
                                var subConditionLvl = 0;
                                var subConditionID = '';

                                for(a in p_data.subcnd[prop_assignment]) {
                                    if(p_data.subcnd[prop_assignment].hasOwnProperty(a)) {
                                        subConditionID = condition_info[0] + '_' + blockID + '_' + conditionLvl + '_' + subConditionLvl + '_' + prop_assignment;
                                        var arr = p_data.subcnd[prop_assignment][a];
                                        arr.subcnd = p_data.subcnd;
                                        this.addSubCondition(propertyElement.up('table'), subConditionID, arr);
                                        subConditionLvl = subConditionLvl+1;
                                    }
                                }
                            }

                        }
                    }.bind(this)
                });
        }
    },
    addSubConditionBlock: function(parentTable, condition_info, p_data)
               {
                   var selected_property_id = condition_info.join('_');
                   var assigned_property = $(selected_property_id).value;
                   var tr_tmpl = $('ReportConditionSubTemplateBlock').clone(true);
                   var block = parseInt(condition_info[1]);
                   var lvl = parseInt(condition_info[2]);
                   var assignment = '';
                   var subLvl = (isNumeric(condition_info[3]))? parseInt(condition_info[3]): 0;

                   if(selected_property_id.indexOf('_C_') != -1)
                   {
                       prop_assignment = selected_property_id.substring(selected_property_id.indexOf('_C_')+1, selected_property_id.indexOf('property')-1)+'--'+assigned_property;
                   } else {
                       prop_assignment = assigned_property;
                   }

                   var block_id = condition_info[0]+'_'+block+'_'+lvl+'_'+prop_assignment+'_block';
                   var child_id = condition_info[0]+'_'+block+'_'+lvl+'_'+subLvl+'_'+prop_assignment;
                   var assignments = {
                       queryConditionBlock: block,
                       queryConditionLvl: lvl,
                       queryConditionSubLvl: subLvl,
                       queryConditionSubLvlProp: prop_assignment
                   };

                   var tpl = new Template(tr_tmpl.innerHTML);
                   tr_tmpl.innerHTML = tpl.evaluate(assignments);

                   tr_tmpl.id = block_id;
                   Element.remove(tr_tmpl.down('table#ReportConditionSubTemplate'));
                   tr_tmpl.show();
                   parentTable.insert(tr_tmpl);
               },
    addConditionBlock: function() {
        var div, block_remover, add_condtion;
        $('dvsn-remover').setStyle({display: 'none'});

        div = $('ReportCondtionBlockTemplate').clone(true);
        Element.remove(div.down('#ReportConditionSubTemplateBlock'));

        // We insert our new division to the GUI.
        if($(this.options.parent_div).childElements().length >= 2)
        {
            var operatorSelection = $('ReportConditionTemplateOperator').clone(true);
            operatorSelection.setAttribute('id', 'querycondition_' + this.options.block_count);
            operatorSelection.setAttribute('name', 'querycondition[' + this.options.block_count + ']');
            operatorSelection.addClassName('reportBlockOperator');
            operatorSelection.removeClassName('fl');
            operatorSelection.show();
            $(this.options.parent_div).insertBefore(operatorSelection, $('dvsn-remover'));
            this.options.block_count++;
        }

        var QueryConditionTemplate = new Template(div.innerHTML);

        var templateAssignment = {
            queryConditionBlock: this.options.block_count,
            queryConditionLvl: 0,
            queryConditionSubLvl: 0
        };

        div.innerHTML = QueryConditionTemplate.evaluate(templateAssignment);

        div.id = 'queryconditionblock_'+this.options.block_count;
        div.down('table').id = 'querycondition_'+this.options.block_count+'_0';
        div.down('input.conditionValue').id = 'querycondition_'+this.options.block_count+'_0_value';
        div.down('select.conditionOperator').id = 'querycondition_'+this.options.block_count+'_0_operator';
        div.down('select.conditionOperator').hide();
        div.down('select.conditionOperator').disabled = true;
        div.down('#ReportConditionTemplateValue').removeAttribute('id');

        this.modifyConditionValue(div.querySelector('.conditionProperty'));
        this.addCondition(div, true);

        return div;
    },
    addCondition: function(div, isBlock){
        var nextCondition = null, tableId = div.id;

        if(isBlock)
        {
            $(this.options.parent_div).insertBefore(div, $('dvsn-remover'));
            Effect.Appear('queryconditionblock_' + this.options.block_count, {duration: 0.5});
            this.options.block_count++;
            nextCondition = div.down('table');
        }
        else
        {
            var tableIdArr = div.down('table').id.split('_');
            var tableCount = div.childElementCount - 2;
            var conditionId = (parseInt(tableIdArr[2])+tableCount);
            var blockId = parseInt(tableIdArr[1]);
            var tableId = tableIdArr[0]+'_'+tableIdArr[1]+'_'+conditionId;
            nextCondition = $('ReportConditionTemplate').clone(true);

            var QueryConditionTemplate = new Template(nextCondition.innerHTML);

            var templateAssignment = {
                queryConditionBlock: blockId,
                queryConditionLvl: conditionId,
                queryConditionSubLvl: 0
            };

            nextCondition.innerHTML = '';
            nextCondition.update(QueryConditionTemplate.evaluate(templateAssignment));

            nextCondition.id = tableId;
            nextCondition.down('input.conditionValue').id = tableId+'_value';
            nextCondition.down('select.conditionOperator').id = tableId+'_operator';
            nextCondition.down('select.conditionOperator').hide();
            nextCondition.down('select.conditionOperator').disabled = true;
            nextCondition.down('select.conditionUnit').id = tableId+'_unit';
            nextCondition.down('select.conditionUnit').hide();
            nextCondition.down('select.conditionUnit').disabled = true;
            nextCondition.down('table.reportCondtionsSubTable').id = tableId + '_' + 0;

            $(tableIdArr[0]+'_'+tableIdArr[1]+'_'+(conditionId-1)).down('select.conditionOperator').show();
            $(tableIdArr[0]+'_'+tableIdArr[1]+'_'+(conditionId-1)).down('select.conditionOperator').disabled = false;

            // Add the "remove condition" button
            nextCondition.down('tr').insert(new Element('td', {style:'position:absolute;'}).update(new Element('span', {className:'subremove-condition'})));

            div.insert(nextCondition);
        }

        if(nextCondition.down('tr#ReportConditionSubTemplateBlock')){
            Element.remove(nextCondition.down('tr#ReportConditionSubTemplateBlock'));
        }

        this.add_observer(div, isBlock);
    
        delay(function () {
            var conditionCategory = $$('#' + tableId + ' .conditionCategory')[0];
            if (conditionCategory)
            {
                new Chosen(conditionCategory, {search_contains: true});
            }
        }, 150, 'report.condition');

        return nextCondition;
    },
    add_observer: function(div, isBlock){

        if(isBlock)
        {
            div.down('span.remove').on('click', function(el){
                this.removeConditionBlock(el.findElement().up().id);
            }.bind(this));

            div.down('span.add').on('click', function(el){
                this.addCondition(el.findElement().up(), false);
            }.bind(this));
        }

        $$('span.subremove').invoke('stopObserving');
        $$('span.subremove-condition').invoke('stopObserving');
        $$('span.subsubremove-condition').invoke('stopObserving');
        $$('span.subadd').invoke('stopObserving');

        $$('select.conditionCategory').invoke('stopObserving');
        $$('select.conditionProperty').invoke('stopObserving');

        $$('select.conditionSubCategory').invoke('stopObserving');
        $$('select.conditionSubProperty').invoke('stopObserving');

        $$('span.subadd').invoke('on', 'click', function(el){
            this.addSubCondition(el.findElement().up('table'));
        }.bind(this));

        $$('span.subremove').invoke('on', 'click', function(el){
            this.removeSubConditions(el.findElement().up('table'));
        }.bind(this));

        $$('span.subremove-condition').invoke('on', 'click', function(el){
            var $table = el.findElement().up('table'),
                $prev_table = $table.previous('table');

            if (! $table.next('table')) {
                $prev_table.down('.conditionOperator').disable().hide();
            }

            $table.remove();
        });

        $$('span.subsubremove-condition').invoke('on', 'click', function(el){
            var $table = el.findElement().up('table'),
                $prev_table = $table.previous('table');

            if (! $table.next('table')) {
                $prev_table.down('.conditionSubOperator').disable().hide();
            }

            $table.remove();
        });

        $$('select.conditionCategory').invoke('on', 'change', function(el){
            this.addPropertiesToCondition(el.findElement());
        }.bind(this));

        $$('select.conditionProperty').invoke('on', 'change', function(el){
            this.modifyConditionValue(el.findElement());
        }.bind(this));

        $$('select.conditionSubCategory').invoke('on', 'change', function(el){
            this.addPropertiesToCondition(el.findElement(), null, true);
        }.bind(this));

        $$('select.conditionSubProperty').invoke('on', 'change', function(el){
            this.modifyConditionValue(el.findElement(), null, true);
        }.bind(this));
    },
    removeConditionBlock: function(id){
        var nextElement = $(id).next().id;
        new Effect.Fade(id, {
            'duration': 0.5,
            'afterFinish': function() {
                if(typeof $(id).previous() != 'undefined')
                {
                    Element.remove($(id).previous());
                } else if($(nextElement) != 'undefined' && $(nextElement).id != 'dvsn-remover' && typeof $(id).previous() == 'undefined')
                {
                    Element.remove($(nextElement));
                }
                Element.remove($(id));
                if ($(this.options.parent_div).down('div').id == 'dvsn-remover') {
                    $('dvsn-remover').setStyle({display: 'block'});
                }
    
                if ($$('option[value="PLACEHOLDER"]:selected').length === 0) {
                    $$('input[name="category_report"]').forEach(function(field) {
                        field.setValue(0);
                    });
                }
            }.bind(this)
        });
    },
    addSubCondition: function(parentElement, conditionID, p_data){
        var l_conditionID = '';

        if(!conditionID)
        {
            l_conditionID = parentElement.lastChild.down('div').lastElementChild.id;
        }
        else
        {
            l_conditionID = conditionID;
        }

        var rawID = l_conditionID.substring(0, l_conditionID.indexOf('_C_'));
        var condition_info = rawID.split('_');
        var blockId = condition_info[1];
        var conditionLvl = condition_info[2];
        var subLvl = condition_info[3];
        var conditionSubLvl = parseInt(subLvl) + ((conditionID)? 0: 1);
        var assignmentID = l_conditionID.substring((parseInt(l_conditionID.indexOf('_C_'))+1), l_conditionID.length);
        var newTableID = condition_info[0] + '_' + condition_info[1] + '_' + condition_info[2] + '_' +
                         conditionSubLvl + '_' + assignmentID;
        var nextCondition = $('ReportConditionSubTemplate').clone(true);
        var QueryConditionTemplate = new Template(nextCondition.innerHTML);

        var templateAssignment = {
            queryConditionBlock: blockId,
            queryConditionLvl: conditionLvl,
            queryConditionSubLvl: conditionSubLvl,
            queryConditionSubLvlProp: assignmentID
        };

        nextCondition.innerHTML = '';
        nextCondition.update(QueryConditionTemplate.evaluate(templateAssignment));

        nextCondition.id = newTableID;
        nextCondition.down('input.conditionSubValue').id = assignmentID+'_value';
        nextCondition.down('select.conditionSubOperator').id = assignmentID+'_operator';
        nextCondition.down('select.conditionSubOperator').hide();
        nextCondition.down('select.conditionSubOperator').disabled = true;
        nextCondition.down('select.conditionSubUnit').id = assignmentID+'_unit';
        nextCondition.down('select.conditionSubUnit').hide();
        nextCondition.down('select.conditionSubUnit').disabled = true;
        nextCondition.down('span#ReportConditionSubTemplateValue').removeAttribute('id');

        if(parentElement.lastChild.down('div.constraintSubDiv').childElementCount > 2) {
            parentElement.lastChild.down('div.constraintSubDiv').lastElementChild.down('select.conditionSubOperator').show();
            parentElement.lastChild.down('div.constraintSubDiv').lastElementChild.down('select.conditionSubOperator').disabled = false;
        }


        parentElement.lastChild.down('div').insert(nextCondition);

        // Only add the "subsub remover" if this table is not the first one!
        if (nextCondition.previous('table')) {
            nextCondition.down('tr').insert(new Element('td', {style:'position:absolute;'}).update(new Element('span', {className:'subsubremove-condition'})))
        }

        if(p_data)
        {
            $(newTableID).down('select.conditionSubCategory').value = p_data.category;
            this.addPropertiesToCondition($(newTableID).down('select.conditionSubCategory'), p_data, true);
        }

        this.add_observer();
    },
    removeSubConditions: function(parentElement){
        $$('#'+parentElement.id+' .reportConditionsSubBlock').each(function(ele){
            Element.remove(ele);
        });

        // Show Object browser again
        if(parentElement.down('span .conditionComparison')){
            if(parentElement.down('span .conditionComparison').value == 'subcnd')
            {
                parentElement.down('span .conditionComparison').selectedIndex = 0;
            }
            parentElement.down('span .conditionComparison').simulate('change');
        } else{
            if(parentElement.down('span .conditionSubComparison').value == 'subcnd')
            {
                parentElement.down('span .conditionSubComparison').selectedIndex = 0;
            }
            parentElement.down('span .conditionSubComparison').simulate('change');
        }

        // Fixing TypeError: Cannot read property 'disabled' of undefined
        var parentInput = parentElement.down('span').down('input');
        if(parentInput && parentInput.disabled === true)
        {
            if(parentElement.down('span .conditionSubComparison').selectedIndex == 0)
            {
                parentElement.down('span').down('input').disabled = false;
                parentElement.down('span').down('input').next('input').disabled = false;
                parentElement.down('span').down('input').show();
                parentElement.down('span').down('a').show();

                if(parentElement.down('span').down('a').next('a')){
                    parentElement.down('span').down('a').next('a').show();
                }
            }
        }
    },
    handle_preselection: function(p_data){
        var saveOnClick = $('navbar_item_C__NAVMODE__SAVE').getAttribute('onclick');
        // ID-4953: Disable save button while loading preselection
        $('navbar_item_C__NAVMODE__SAVE').setAttribute('onclick', '');
        $('navbar_item_C__NAVMODE__SAVE').className = 'navbar_item_inactive';
        Ajax.Responders.register({
            onCreate: function() {
                if(Ajax.activeRequestCount > 0 && !this.options.reload_finished && !$(this.options.overlay).visible())
                {
                    $(this.options.overlay).show();
                }
            }.bind(this),
            onComplete: function () {
                if(Ajax.activeRequestCount == 0 && !this.options.reload_finished)
                {
                    this.options.reload_finished = true;
                    if(p_data)
                    {
                        var cnds_blocks = $H(p_data.conditions);
                        var currentConditionBlock = null;
                        var currentCondition = null;
                        var counter = 0;

                        if(cnds_blocks.size() > 0){
                            // first conditionblock
                            currentConditionBlock = this.addConditionBlock();
                            currentCondition = currentConditionBlock.down('table');
                            cnds_blocks.each(function(pair) {
                                if(typeof pair.value == 'string')
                                {
                                    currentConditionBlock = this.addConditionBlock();
                                    currentConditionBlock.previous('select').value = pair.value;
                                    currentCondition = currentConditionBlock.down('table');
                                }
                                else
                                {
                                    counter = 0;
                                    for(var i in pair.value)
                                    {
                                        if(!pair.value.hasOwnProperty(i)) continue;

                                        if(counter > 0)
                                        {
                                            currentCondition = this.addCondition(currentConditionBlock, false);
                                        }
                                        else
                                        {
                                            counter++;
                                        }
                                        currentCondition.down('.conditionCategory').value = pair.value[i]['category'];
                                        this.addPropertiesToCondition(currentCondition.down('.conditionCategory'), pair.value[i]);
                                    }
                                }
                            }.bind(this));
                        }
                    }
                }
                else if(Ajax.activeRequestCount == 0 && this.options.reload_finished === true && $(this.options.overlay).visible())
                {
                    new Effect.SlideUp(this.options.overlay, {duration:0.4});
                    // ID-4953: Enable save button after loading preselection
                    $('navbar_item_C__NAVMODE__SAVE').setAttribute('onclick', saveOnClick);
                    $('navbar_item_C__NAVMODE__SAVE').className = 'navbar_item';
                }
            }.bind(this)
        });
    },
    appendPlaceholderOptions: function (select) {
        select.insert(new Element('option', {'value': 'current-user-id'}).update(idoit.Translate.get('LC__REPORT__PLACEHOLDER__CURRENT_USER')));
        select.insert(new Element('option', {'value': 'object-id'}).update(idoit.Translate.get('LC__REPORT__PLACEHOLDER__OBJECT')));
        select.insert(new Element('option', {'value': 'unequal-object-id'}).update(idoit.Translate.get('LC__REPORT__PLACEHOLDER__UNEQUAL_OBJECT')));
        select.insert(new Element('option', {'value': 'greater-than-current-date-time'}).update(idoit.Translate.get('LC__REPORT__PLACEHOLDER__GREATER_DATETIME')));
        select.insert(new Element('option', {'value': 'lower-than-current-date-time'}).update(idoit.Translate.get('LC__REPORT__PLACEHOLDER__LOWER_DATETIME')));
        select.insert(new Element('option', {'value': 'regular-expression'}).update(idoit.Translate.get('LC__REPORT__PLACEHOLDER__REGEX')));
    },
    loadPlaceholderUserInputFields: function () {
        new Ajax.Request('?ajax=1&call=report&func=get_user_input_field_for_placeholders', {
            method:     "post",
            parameters: {
                'placeholders[]': [
                    'current-user-id',
                    'object-id',
                    'unequal-object-id',
                    'greater-than-current-date-time',
                    'lower-than-current-date-time',
                    'regular-expression'
                ]
            },
            onSuccess:  function (transport) {
                this.options.user_input_fields = transport.responseJSON;
            }.bind(this)
        });
    }
});
