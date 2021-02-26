(function () {
	"use strict";

	// Initialize preselection component.
    window.browserPreselection = new Browser.preselection('objectPreselection', {
        $selectionCounter: 'numObjects',
        secondElement:      false,
        multiselection:     JSON.parse('[{if $multiselection}]true[{else}]false[{/if}]'),
        latestLogElement:   'latestLog',
        instanceName:       'browserPreselection',
        urlBase:            '[{$config.www_dir}]',
        returnElement:      '[{$return_element}]',
        objectBrowserName:  '[{$objectBrowserName}]',
        afterFinish:        function () {
            $('preselectionLoader').addClassName('hide');
            $('browser-content').show();
        },
        afterRemove:        function () {
            try {
                window.browserSearch.updateTable();
            } catch (e) {
            
            }
            
            try {
                window.browserReport.updateTable();
            } catch (e) {
            
            }
            
            try {
                window.browserList.updateTable();
            } catch (e) {
            
            }
        }
    });
    
    window.browserParentPreselection = JSON.parse('[{$parentPreselection|json_encode|escape:"javascript"}]') || [];
    window.browserPreselection.setSelection(JSON.parse('[{$preselection|json_encode|escape:"javascript"}]'));
    
    if (Object.isArray(window.browserParentPreselection)) {
        window.browserParentPreselection = [];
    }

	// Moves object browser data to a parent field.
	window.moveToParent = function (hiddenElement, viewElement) {
        var $view = $(viewElement),
            $hidden = $(hiddenElement),
            selection = window.browserPreselection.getSelection();

		if (window.browserPreselection.isMultiselection()) {
            $hidden.setValue(JSON.stringify(selection));

			if ($view) {
				$view.setValue('[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__SELECTED_OBJECTS" p_bHtmlEncode=0}]'.replace('{0}', selection.length));
			}
            
            [{if $callback_accept}][{$callback_accept}][{/if}]
		} else {
			if (selection.length > 0) {
				if ($view && selection[0]) {
                    $view.setValue(idoit.Translate.get('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__LOADING'));
                    $view.setAttribute('data-last-value', $view.getValue());
                    
                    window.browserPreselection.getObjectMetaData(selection[0], function (data) {
                        $view.setValue(data.isys_obj_type__title + ' >> ' + data.isys_obj__title);
                        [{if $callback_accept}][{$callback_accept}][{/if}]
                    });
				}

				if ($hidden && selection[0]) {
                    $hidden.setValue(selection[0]);
				}
			} else {
				if ($view) {
					$view.setValue('[{isys type="lang" ident="LC__UNIVERSAL__CONNECTION_DETACHED" p_bHtmlEncode=0}]');

                    $view.setAttribute('data-last-value', $view.getValue());
				}

				if ($hidden) {
                    $hidden.setValue('');
				}
			}
		}
	}

	// Pre-load the current list view.
	if ($('object_type')) {
		$('object_type').simulate('change');
	} else if ($('object_catfilter')) {
		$('object_catfilter').simulate('change');
	}
}());
