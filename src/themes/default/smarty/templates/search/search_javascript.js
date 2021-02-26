function mysearch_addCriterias(view) {
	var location = $('isys_form').action || document.location;

	if (view == 1) {
		aj_submit(location + '&request=mysearch_viewCriterias', 'post', 'mydoitArea', 'isys_form', null, null);
	} else {
		aj_submit(location + '&request=mysearch_addCriterion', 'post', 'mydoitArea', 'isys_form', null, null);
	}
}


// *** ADD A INPUT ELEMENT, CHECKBOX AND SELECT BOX ***
function mysearch_addElement() {
	$('secondtd').insert({
		before:'<td width="75px" id="first" class="first">#{name}</td>'.interpolate({
			name:'<label><input type="checkbox" name="C__SEARCH_OPTION_0" value="1" id="checkbox0"> <span style="vertical-align:top;">[{$checkboxnot}]</span></label>'
		})

	});

	var ni = $('addedDiv');

	var newdiv = new Element('div', {id:'addedDiv1Row'});

	var html = "<table cellpadding=\"3\" cellspacing=\"1\" width=\"470\" border=\"0\"><tr>";
	html += "<td width=\"75px\"><label><input type=\"checkbox\" name=\"C__SEARCH_OPTION_1\" value=\"1\" id=\"checkbox1\"> <span style=\"vertical-align:top;\"> [{$checkboxnot}]</span></label></td>";
	html += "<td width=\"200px\">";
	html += "<input type=\"search\" name=\"C__SEARCH_TEXT[]\" class=\"input input-small\" autosave=\"idoit.search\" placeholder=\"[{isys type='lang' ident='LC__UNIVERSAL__SEARCH'}]\" id=\"searchText1\" size=\"25\" style=\"padding:3px;\" onkeypress=\"if((event.which&&event.which==13)||(event.keyCode&&event.keyCode==13)) { mysearch_change('index.php', 'ResponseContainer'); return false;}else return true;\" /></td>";
	html += "<td>&nbsp;</td>";
	html += "</tr></table>";

	newdiv.innerHTML = html;
	ni.insert(newdiv);

	$('idAddElement').hide();
	$('selectop').show();
	$('idRemoveElement').show();
}

// *** REMOVE ELEMENTS  ***
function mysearch_rem (divNum) {
	$('addedDiv' + divNum + 'Row').remove();
	$('selectop').hide();
	$('idAddElement').show();
	$('idRemoveElement').hide();
	$$('td.first').invoke('remove');
}

//  *** AJAX SEARCH ***
function mysearch_change (script, element, p_elemAdvSearch) {
	// Search with saved criterias check the optional parameter.
	if (p_elemAdvSearch !== undefined ) {

		/*structue of p_elemAdvSearch, for more details see @/src/classes/modules/isys_module_search_criterias.php
		  e.g. 'aaa,bbbbbb#1,2,3#3,5,8#0,0#AND#1#1' where
		  		"search text#objects#categories#NOT option#operator#checkbox only words#checkbox case sensitiv" */

		var l_searchFields = p_elemAdvSearch.split("#");

		// search fields are comma separated e.g. "aaa,bbbb"
		var l_searchText = l_searchFields[0].split(",");

		// default fields
		$('searchText').value 		= l_searchText[0];
		$('obj_inputHiden').value 	= l_searchFields[1];
		$('catgs_inputHiden').value = l_searchFields[2];
		// for l_searchFields[3] and l_searchFields[4] see bellow
		$('worts').checked 			= l_searchFields[5];
		$('casesensitiv').checked 	= l_searchFields[6];

		// if form fields are hidden, e.g. for search with two fields
		if(l_searchText[1] ) {
			// second field is not visibel => show it
			if($('searchText1') === null) mysearch_addElement();

			// set all other hidden fields
			$('searchText1').value 		= l_searchText[1];
			// search options are comma separated e.g. "0,1"
			var l_searchOptions 		= l_searchFields[3].split(",");
			$('checkbox0').checked 		= l_searchOptions[0];
			$('checkbox1').checked 		= l_searchOptions[1];
			$('search_option_op').value	= l_searchFields[4];

		// second field is visibel but search only with 1 field => remove 2 field
		} else if($('searchText1')) {
			 mysearch_rem(1);
		}
	}

    if($('current_page'))
    {
        $('page_counter').innerHTML = $('current_page').options[$('current_page').selectedIndex].innerHTML;
    }

    if($('searchText'))
    {
        var $object_type_selection = $('obj_input').removeClassName('box-red'),
            $category_selection = $('catgs_input').removeClassName('box-red'),
            $searchfield = $('searchText').removeClassName('box-red'),
            $last_searchfield;

        for (var i = 1, $search_field_x; ($search_field_x = $("searchText" + i)); i++) {
            if ($search_field_x.getValue().length < 3) {
                $last_searchfield = $search_field_x.addClassName('box-red');
            } else {
                $search_field_x.removeClassName('box-red');
            }
        }

        // Validate.
        if ($searchfield) {
            if ($searchfield.getValue().length < 3) {
                $searchfield.addClassName('box-red').focus();
                idoit.Notify.error('[{isys type="lang" ident="LC__MODULE__SEARCH__NOTIFY__SEARCHPHRASE_TO_SHORT"}]', {life: 10});
                return;
            } else if ($last_searchfield) {
                $last_searchfield.focus();
                idoit.Notify.error('[{isys type="lang" ident="LC__MODULE__SEARCH__NOTIFY__SEARCHPHRASE_TO_SHORT"}]', {life: 10});
                return;
            } else if ($object_type_selection.getValue().empty()) {
                $object_type_selection.addClassName('box-red').focus();
                idoit.Notify.error('[{isys type="lang" ident="LC__MODULE__SEARCH__NOTIFY__NO_OBJECT_TYPES_SELECTED"}]', {life: 10});
                return;
            } else if ($category_selection.getValue().empty()) {
                $category_selection.addClassName('box-red').focus();
                idoit.Notify.error('[{isys type="lang" ident="LC__MODULE__SEARCH__NOTIFY__NO_CATEGORIES_SELECTED"}]', {life: 10});
                return;
            }
        }
    }
	$(element).update('<img src="images/ajax-loading.gif" class="m5" style="vertical-align:middle;" /> <span>[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span>');

	$('isys_form').action = script + '?call=search_table';

	new Ajax.Updater(
					element,
					script + '?ajax=1&call=search_table',
					{
						parameters: $('isys_form').serialize(false),
						history:false,
						onComplete: function() {
							change_action_parameter('call', 'search_table');
						}
					});
}

// *** UPDATE INPUT FIELDS AFTER CLICK ON CHECKBOXES **
function mysearch_updateTypeInput (p_name, p_value, p_clasname) {
	var allChecked= true;

	// init fields
	$(p_clasname + '_input').value = '';
	$(p_clasname + '_inputHiden').value = '';

	$$('.' + p_clasname).each(function (check) {
		if (check.checked) {
			if ($(p_clasname + '_input').value == "") {
				$(p_clasname + '_input').value += check.name;
				$(p_clasname + '_inputHiden').value += check.value;
			} else {
				$(p_clasname + '_input').value += ',' + check.name;
				$(p_clasname + '_inputHiden').value += ',' + check.value;
			}
		} else {
			allChecked = false;
		}
	});

	if (Object.isElement(p_name) && p_name.value == "") {
		$(p_clasname + '_input').value = '[{isys type="lang" ident="LC__UNIVERSAL__ALL"}]';
	}

	$('select_all_' + p_clasname).checked = allChecked;
}