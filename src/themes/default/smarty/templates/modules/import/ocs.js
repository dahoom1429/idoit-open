"use strict";

window.ocs_list = function () {
    $('ocs_object').hide();
    $('ocs_list').show();
    $('ocs_db_list').show();
    $('ocs_list_import_button').show();
};

window.ocs_object = function ocs_object(hwID, p_snmp) {
    aj_submit('?[{$smarty.const.C__GET__MODULE_ID}]=[{$smarty.const.C__MODULE__IMPORT}]&request=showOCSObject&hwID=' + hwID + '&snmp=' + p_snmp + '&selected_ocsdb=' + $('selected_ocsdb').value, 'get', 'ocs_object');
    $('ocs_list').hide();
    $('ocs_db_list').hide();
    $('ocs_object').show();
    $('ocs_list_import_button').hide();
};


window.ocs_import = function (hwID, objTypeID, p_snmp)
{
    $('ocs_import_done').update($('ocs_import_message').innerHTML).appear();
    $('ocs_list_import_button').hide();
    new Effect.SlideUp($('ocs_object'), {duration:0.8});

    var category_arr = [];

    $$('#ocs_object input.categories').each(function(ele){
        if(ele.name != '' && ele.checked)
        {
            category_arr.push(ele.value);
        }
    });

    new Ajax.Updater('ocs_import_done', 'controller.php?load=ocs',
    {
        parameters:
        {
            hardwareID:hwID,
            snmp:p_snmp,
            objTypeID:objTypeID,
            selected_ocsdb:$F('selected_ocsdb'),
            category:Object.toJSON(category_arr),
            overwrite_ip_port:$F('ocs_overwrite_hostaddress_port_single'),
            overwrite_all_cats:$F('ocs_overwrite_all_cats_single'),
            ocs_logging:$F('ocs_logging_single')
        },
        method:'post',
        onComplete: function() {
            if($('ocs_multi_button_back')) $('ocs_multi_button_back').show();
        }
    });
};

window.getOCSImportPopup = function()
{
    if($$('#ocs_object_list input[type="checkbox"]:checked').length > 0)
    {
        $('ocs_no_selection_error').hide();
        get_popup('ocs_category_selection', '', 320, 320);
    }
    else {
        $('ocs_no_selection_error').show();
    }
};

window.ocs_multi_import = function() {
	var category_arr = [],
		id_arr = [],
		id_snmp_arr = [],
		objtype_arr = [],
		objtype_snmp_arr = [];

    $('ocs_db_list').hide();

    if($$('#ocs_object_list input[type="checkbox"]:checked').length == 0) {
        return null;
    }

    popup_close();

    $('ocs_import_done').update($('ocs_import_message').innerHTML).appear();
    $('ocs_list_import_button').hide();

    new Effect.SlideUp($('ocs_list'), {duration:0.8});

	$$('#popup input.categories').each(function (ele) {
		if (ele.name != '' && ele.checked) {
			category_arr.push(ele.value);
		}
	});

	$$('.checkbox').each(function (ele) {
		if (ele.checked) {
			if (ele.name == 'id[]') {
				id_arr.push(ele.value);
				objtype_arr.push(ele.up('tr').down('select').value);
			} else if (ele.name == 'id_snmp[]') {
				id_snmp_arr.push(ele.value);
				objtype_snmp_arr.push(ele.up('tr').down('select').value);
			}
		}
	});

	new Ajax.Updater('ocs_import_done', 'controller.php?load=ocs', {
		parameters: {
			id: Object.toJSON(id_arr),
			id_snmp: Object.toJSON(id_snmp_arr),
			objtypes: Object.toJSON(objtype_arr),
			objtypes_snmp: Object.toJSON(objtype_snmp_arr),
			selected_ocsdb: $F('selected_ocsdb'),
			category: Object.toJSON(category_arr),
			overwrite_ip_port: $F('ocs_overwrite_hostaddress_port_multi'),
            overwrite_all_cats: $F('ocs_overwrite_all_categories'),
            ocs_logging: $F('ocs_logging_multi')
		},
		method: 'post',
		onComplete: function () {
			if ($('ocs_multi_button_back')) {
				$('ocs_multi_button_back').show();
			}
		}
	});
};

window.show_ocs_objects = function (p_id) {
	$('ocs_submitter').hide();
	$('ocs_import_done').hide();
	$('ocs_error').hide();

	if (p_id > 0) {
		$('ocs_db_ajax_loader').show();
		new Ajax.Request('?ajax=1&call=ocs_import&func=object_list', {
			parameters: {
				ocs_id: p_id
			},
			method: 'post',
			onComplete: function (xhr) {
				var json = xhr.responseJSON;

                $('ocs_db_ajax_loader').hide();

				if (json == null) {
					$('ocs_object_list').update('');
					new Effect.Appear('ocs_error', {duration: 1.5});
				} else {

					var odd_even = 'CMDBListElementsOdd';
					$('ocs_object_list').update('');

                    var objtype_cloned = null;

                    json.each(function(ele){
                        objtype_cloned = $('templaet_objtype_arr').clone(true);
                        objtype_cloned.setAttribute('id', '');
                        objtype_cloned.setAttribute('style', '');
    
                        if (ele.imported != null) {
                            var optionGroup = (new Element('optgroup', { label: '[{isys type="lang" ident="LC__MODULE__OCS_IMPORT__SPECIAL"}]' }))
                                .insert((new Element('option', { value: 'recalculate' }))
                                .update('[{isys type="lang" ident="LC__MODULE__OCS_IMPORT__RECALCULATE_OBJECT_TYPE_BASED_ON_TAGS"}]'))
                            objtype_cloned.insert({top: optionGroup});
                        }
                        
                        objtype_cloned.on('focusin', function(e, elem) {
                            setOcsPreviousValueObjectType(elem, elem.getValue());
                        });
                        
                        if(ele.objtype !== null)
                        {
                            objtype_cloned.value = ele.objtype;
                        }

                        if(ele.snmp > 0){
                            objtype_cloned.setAttribute('name', 'objtype_snmp_arr[]');
                        }
                        else{
                            objtype_cloned.setAttribute('name', 'objtype_arr[]');
                        }

                        $('ocs_object_list').insert(
                            new Element('tr', {className: 'listRow ' + odd_even}).update(
                                new Element('td').update(
                                    new Element('input', {type:'checkbox', className:'checkbox', name:((ele.snmp > 0)? 'id_snmp[]' :'id[]'), value:ele.ID})
                                )
                            ).insert(
                                new Element('td').update(ele.TAG).observe('click', function(){
                                    ocs_object(ele.ID, ele.snmp);
                                })
                            ).insert(
                                new Element('td').insert(objtype_cloned)
                            ).insert(
                                    new Element('td').update((ele.snmp > 0 ? '[{isys type="lang" ident="LC__UNIVERSAL__YES"}]': '[{isys type="lang" ident="LC__UNIVERSAL__NO"}]')).observe('click', function(){
                                        ocs_object(ele.ID, ele.snmp);
                                    })
                            ).insert(
                                new Element('td',{className:'bold'}).update(ele.NAME).observe('click', function(){
                                    ocs_object(ele.ID, ele.snmp);
                                })
                            ).insert(
                                new Element('td').update(ele.OSNAME).observe('click', function(){
                                    ocs_object(ele.ID, ele.snmp);
                                })
                            ).insert(
                                new Element('td').update(ele.IPADDR).observe('click', function(){
                                    ocs_object(ele.ID, ele.snmp);
                                })
                            ).insert(
                                new Element('td').update(
                                    ((ele.imported != null)? '<span class="bold green ocs_object_imported">' + ele.imported + '</span>': '<span class="grey">[{isys type="lang" ident="LC__MODULE__IMPORT__NOT_IMPORTED"}]</span>')
                                ).observe('click', function(){
                                    ocs_object(ele.ID, ele.snmp);
                                })
                            )

                        );
                        odd_even = (odd_even == 'CMDBListElementsOdd')? 'CMDBListElementsEven': 'CMDBListElementsOdd';
                    });

                    $('ocs_submitter').show();
                }
            }
        });
    } else if(p_id == undefined)
    {
        $('ocs_object_list').update('');
        new Effect.Appear('ocs_error', {duration:1.5});
    }
};

window.change_all_objtypes = function (p_selected_objtype){
    if($('ocs_object_list')) {
        $$('#ocs_object_list .ocs_objtype_dialog').each(function (ele) {
            ele.value = p_selected_objtype;
        }.bind(p_selected_objtype));
    }
};

window.recalculateTypesOfExistingObjects = function(status) {
    var isRecalculateObjectType = $('ocs_object_list') && status === '1';
    
    $$('tr span.ocs_object_imported').each(function(e) {
        var selectBox = e.up('tr').down('select');
        
        if (!selectBox) {
            return;
        }
        
        if (isRecalculateObjectType) {
            setOcsPreviousValueObjectType(selectBox, selectBox.getValue());
            selectBox.setValue('recalculate')
        } else {
            var previousValue = getOcsPreviousValueObjectType(selectBox);

            if (previousValue !== undefined) {
                selectBox.setValue(previousValue);
            }
        }
    });
}

window.setOcsPreviousValueObjectType = function(element, value) {
    element.setAttribute('data-previous-value', value);
}

window.getOcsPreviousValueObjectType = function(element) {
    return element.getAttribute('data-previous-value');
}