var AuthConfiguration = Class.create({
    /**
     * Constructor method, initializes everything necessary.
     * @param  element
     * @param  options
     */
    initialize: function (element, options) {
        this.element_id = element;
        this.element = $(element);
        
        this.path_counter = 1;
        this.rights = {};
        this.newAuthPathClick = false;
 
        this.options = Object.extend({
            ajax_url:        '',
            methods:         {},
            rights:          {},
            paths:           {},
            inherited_paths: {},
            wildchar:        '*',
            empty_id:        'empty-id',
            
            edit_mode: 0
        }, options || {});
        
        var calculated_size = parseInt((this.element.getWidth() - 50 - (Object.keys(this.options.rights).length * 25)) / 2),
            thead,
            thead_row,
            i,
            i2;
        
        thead = new Element('thead', {className: 'gradient'});
        
        thead_row = new Element('tr');
        
        // Insert all "right"-checkboxes.
        for (i in this.options.rights) {
            if (!this.options.rights.hasOwnProperty(i)) {
                continue;
            }
        
            thead_row.insert(new Element('th', {style: 'width:25px;'})
                .update(new Element('img', {
                    src:   '[{$dir_images}]' + this.options.rights[i].icon,
                    alt:   this.options.rights[i].title,
                    title: this.options.rights[i].title
                })));
        
            this.rights[this.options.rights[i].const] = this.options.rights[i].value;
        }
        
        // Insert the "condition", "parameter" and "action" fields.
        thead_row.insert(
            new Element('th', {
                style:     'width:' + calculated_size + 'px;',
                className: 'border-left'
            }).update(idoit.Translate.get('LC__AUTH_GUI__CONDITION'))
        ).insert(
            new Element('th', {
                style:     'width:' + calculated_size + 'px;',
                className: 'border-left'
            }).update(idoit.Translate.get('LC__AUTH_GUI__PARAMETER'))
        ).insert(
            new Element('th', {
                style:     'width:50px;',
                className: 'border-left'
            }).update(idoit.Translate.get('LC__AUTH_GUI__ACTION'))
        );
        
        this.element.update(
            new Element('table', {
                cellspacing: 0,
                style:       'width:100%;'
            }).update(
                thead.update(thead_row)
            ).insert(
                new Element('tbody')
            )
        );
        
        for (i in this.options.inherited_paths) {
            if (this.options.inherited_paths.hasOwnProperty(i)) {
                // Now check the next right-level.
                for (i2 in this.options.inherited_paths[i]) {
                    if (this.options.inherited_paths[i].hasOwnProperty(i2) && (i2 != this.options.empty_id || (this.options.methods.hasOwnProperty(i) && this.options.methods[i].type == 'boolean'))) {
                        this.display_path(i, i2, this.options.inherited_paths[i][i2], true);
                    }
                }
            }
        }
        
        for (i in this.options.paths) {
            if (this.options.paths.hasOwnProperty(i)) {
                // Now check the next right-level.
                for (i2 in this.options.paths[i]) {
                    if (this.options.paths[i].hasOwnProperty(i2) && (i2 != this.options.empty_id || (this.options.methods.hasOwnProperty(i) && this.options.methods[i].type == 'boolean'))) {
                        this.display_path(i, i2, this.options.paths[i][i2]);
                    }
                }
            }
        }
        
        // This has to be called only once.
        this.set_observer();
    },
    
    /**
     * Method for displaying / adding a new path to the GUI.
     * @param   method
     * @param   param
     * @param   rights
     * @param   inherited
     * @return  AuthConfiguration
     */
    display_path: function (method, param, rights, inherited) {
        var i,
            tr      = new Element('tr', {
                id:             this.element_id + '-row-' + this.path_counter,
                'data-counter': this.path_counter
            }),
            options = new Element('select', {
                id:        this.element_id + '_method_' + this.path_counter,
                name:      'method_' + this.path_counter,
                className: 'input input-small method-select',
                disabled:  !this.options.edit_mode
            }),
            innerTD = '';
        
        if (Object.isUndefined(inherited)) {
            inherited = false;
        }
        
        if (typeof method !== 'string') {
            method = '';
        }
        
        if (typeof param !== 'string') {
            param = '';
        }
        
        if (Object.isUndefined(rights) || !Object.isArray(rights)) {
            rights = [];
        }
        
        for (i in this.options.rights) {
            if (this.options.rights.hasOwnProperty(i)) {
                // The first checkbox shall not be editable, since we always will have at least "view" rights, when adding a new path.
                tr.insert(new Element('td', {className: 'center'}).update(new Element('input', {
                    type:      'checkbox',
                    disabled:  !this.options.edit_mode,
                    className: 'right-checkbox',
                    name:      'right_' + this.path_counter + '[]',
                    value:     this.options.rights[i].value,
                    checked:   rights.in_array(parseInt(this.options.rights[i].value))
                })));
            }
        }
        
        for (i in this.options.methods) {
            if (this.options.methods.hasOwnProperty(i)) {
                options.insert(new Element('option', {value: i}).update(this.options.methods[i].title));
            }
        }
        
        if (options.down('option[value="' + method + '"]')) {
            options.down('option[value="' + method + '"]').writeAttribute('selected', 'selected');
        }
        
        if (!param.blank()) {
            if ((this.options.edit_mode == 0 || inherited) && param == '*') {
                // This will save a lot of time ;)
                innerTD = new Element('td', {
                    id:        this.element_id + '_param_' + this.path_counter,
                    className: 'border-left'
                }).update(new Element('span', {className: 'fl mr5'}).update(idoit.Translate.get('LC__UNIVERSAL__ALL')));
                // Also we disable the checkboxes.
                tr.select('.right-checkbox').invoke('disable');
            } else {
                this.get_parameter(this.element_id + '_param_' + this.path_counter, method, param, inherited);
                
                innerTD = new Element('td', {
                    id:        this.element_id + '_param_' + this.path_counter,
                    className: 'border-left'
                });
                innerTD
                    .insert(new Element('img', {
                        className: 'vam mr5',
                        src:       '[{$dir_images}]ajax-loading.gif'
                    }))
                    .insert(new Element('span', {className: 'vam'}).update(idoit.Translate.get('LC__UNIVERSAL__LOADING')));
            }
        }
        
        if (inherited) {
            tr.addClassName(inherited ? 'inactive' : '').select('checkbox').invoke('disable');
            options.disable();
        }
        
        tr
            .writeAttribute('data-inherited', inherited ? 1 : 0)
            .insert(new Element('td', {className: 'border-left'}).update(new Element('span', {className: 'mr5'}).update(idoit.Translate.get('LC__AUTH_GUI__REFERS_TO')))
                                                                 .insert(options))
            .insert(innerTD)
            .insert(new Element('td', {className: 'border-left'})
                .update((this.options.edit_mode && !inherited) ? new Element('button', {
                    className: 'btn btn-small remove-path-button',
                    type:      'button',
                    title:     idoit.Translate.get('LC__UNIVERSAL__REMOVE')
                }).update(new Element('img', {src: '[{$dir_images}]icons/silk/cross.png'})) : ''));
        
        this.element.down('tbody').insert(tr);
        this.path_counter++;
        
        return this;
    },
    
    /**
     * Basically does the same as display_path, but will be called by the GUI (not internally).
     * Maybe we can combine the two methods without to much stress.
     * @return  AuthConfiguration
     */
    create_new_path: function () {
        var i,
            tr      = new Element('tr', {
                id:             this.element_id + '-row-' + this.path_counter,
                'data-counter': this.path_counter
            }),
            options = new Element('select', {
                id:        this.element_id + '_method_' + this.path_counter,
                name:      'method_' + this.path_counter,
                className: 'input input-small method-select'
            });
    
        // ID-4817  Default rights will only be applied by user interaction.
        this.newAuthPathClick = true;
        
        for (i in this.options.rights) {
            if (this.options.rights.hasOwnProperty(i)) {
                tr
                    .insert(new Element('td', {className: 'center'})
                        .update(new Element('input', {
                            type:      'checkbox',
                            className: 'right-checkbox',
                            name:      'right_' + this.path_counter + '[]',
                            value:     this.options.rights[i].value,
                            checked:   false
                        })));
            }
        }
        
        for (i in this.options.methods) {
            if (this.options.methods.hasOwnProperty(i)) {
                options.insert(new Element('option', {value: i}).update(this.options.methods[i].title));
            }
        }
        
        tr
            .insert(new Element('td', {className: 'border-left'})
                .update(new Element('span', {className: 'mr5'}).update(idoit.Translate.get('LC__AUTH_GUI__REFERS_TO')))
                .insert(options))
            .insert(new Element('td', {id: this.element_id + '_param_' + this.path_counter, className: 'border-left'})
                .update(new Element('img', {className: 'vam', src: '[{$dir_images}]ajax-loading.gif'}))
                .insert(new Element('span', {className: 'vam'}).update(idoit.Translate.get('LC__UNIVERSAL__LOADING'))))
            .insert(new Element('td', {className: 'border-left'})
                .update(new Element('button', {className: 'btn btn-small remove-path-button', type: 'button', title: idoit.Translate.get('LC__UNIVERSAL__REMOVE')})
                    .update(new Element('img', {src: '[{$dir_images}]icons/silk/cross.png'}))));
        
        this.element.down('tbody').insert(tr);
        
        this.get_parameter(this.element_id + '_param_' + this.path_counter, $F(this.element_id + '_method_' + this.path_counter), false);
        
        this.path_counter++;
        
        return this;
    },
    
    /**
     * Method for retrieving the parameter element, by a given method and parameter.
     * @param   element
     * @param   method
     * @param   param
     * @param   inherited
     * @return  AuthConfiguration
     */
    get_parameter: function (element, method, param, inherited) {
        var counter = element.substr(this.element_id.length + 7);
        
        if (!this.options.methods.hasOwnProperty(method)) {
            idoit.Notify.warning('The path for method "' + method + '" could not be found...', {life: 5});
            return this;
        }
        
        new Ajax.Request(this.options.ajax_url + '&func=retrieve_parameter', {
                parameters: {
                    method:    this.options.methods[method].type,
                    param:     param,
                    edit_mode: inherited ? 0 : this.options.edit_mode,
                    counter:   counter
                },
                method:     'post',
                onComplete: function (r) {
                    var json = r.responseJSON;
                    
                    if (json && json.success) {
                        this.display_parameter(element, json, inherited);
                    } else {
                        $(element).update(new Element('p', {className: 'p5 box-red'}).update(json.message || r.responseText));
                    }
                }.bind(this)
            });
        
        return this;
    },
    
    /**
     * Method for actually displaying the loaded parameter, gets called by "get_parameter()".
     * @param   element
     * @param   json
     * @param   inherited
     * @return  AuthConfiguration
     */
    display_parameter: function (element, json, inherited) {
        var el            = $(element),
            counter       = el.up('tr').readAttribute('data-counter'),
            button_active = (json.data.param == this.options.wildchar || json.data.param.substr(0, 1) == this.options.wildchar),
            $lastCheckbox = el.up('tr').down('input.right-checkbox:not([data-not-available],.hide):checked:last'),
            button, temp_obj, i;
        
        if (this.options.edit_mode == 0 || inherited) {
            // Just show the text "all" instead the button when we are in view-mode.
            button = button_active ? new Element('span', {className: 'fl mr5'}).update(idoit.Translate.get('LC__UNIVERSAL__ALL')) : '';
        } else {
            // Workaround because some browsers have problems with outerHTML
            temp_obj = new Element('div')
                .insert(new Element('button', {
                    id:        'auth_param_button_' + json.data.counter,
                    type:      'button',
                    className: 'fl mr5 btn param-button' + (button_active ? ' btn-green' : '')
                })
                    .update(new Element('span').update(idoit.Translate.get('LC__UNIVERSAL__ALL'))))
                .insert(new Element('input', {
                    id:    'auth_param_button_val_' + json.data.counter,
                    name:  'auth_param_button_val_' + json.data.counter,
                    type:  'hidden',
                    value: (button_active ? '1' : '0')
                }));
            button = temp_obj.innerHTML;
            temp_obj = null;
        }
        
        if (json.data.method == 'boolean') {
            // Don't display the "All" button here.
            button = '';
        }
        
        // ATTENTION: We don't use "button_active" here, because the '.param-hider' element shall only be used/displayed when the complete param only contains the wildchar!!
        el.update((json.data.param == this.options.empty_id) ? '' : button)
          .insert(new Element('div', {
              className: 'fl',
              style:     'position:relative;'
          })
              .update(new Element('div', {
                  className: 'param-hider',
                  style:     'position:absolute;width:100%;height:100%;background:#fff;opacity:0.5;z-index:100;' +
                             ((json.data.param == this.options.wildchar) ? 'display:block;' : 'display:none;')
              }))
              .insert(((json.data.param == this.options.wildchar && this.options.edit_mode == 0) ? '' : json.data.html)))
          .up('tr').writeAttribute('data-method', json.data.method);
        
        this.change_rights($F(this.element_id + '_method_' + json.data.counter), json.data.counter);
        
        if (this.options.edit_mode == 1) {
            if ($lastCheckbox) {
                // Simulate the "change" event to trigger this.update_rights()...
                $lastCheckbox.simulate('change');
            }
            
            // Also disable the first field, if the "all" button was clicked and we have a multi-parameter.
            if (button_active && ['category_in_obj_type', 'category_in_object', 'category_in_location'].in_array(el.up('tr').readAttribute('data-method'))) {
                if ($('auth_param_form_' + counter)) {
                    $('auth_param_form_' + counter).disable();
                } else if ($('auth_param_form_' + counter + '[]')) {
                    $('auth_param_form_' + counter + '[]').disable();
                }
            }
        }
        
        el.select('.chosen-select').each(function ($dialog) {
            new Chosen($dialog, {
                default_multiple_text:     '[{isys type="lang" ident="LC__AUTH_GUI__CATEGORY_CHOOSE" p_bHtmlEncode=false}]',
                placeholder_text_multiple: '[{isys type="lang" ident="LC__AUTH_GUI__CATEGORY_CHOOSE" p_bHtmlEncode=false}]',
                no_results_text:           '[{isys type="lang" ident="LC__SMARTY__PLUGIN__DIALOGLIST__NO_RESULTS"}]',
                search_contains:           true
            });
            
            new ChosenExtension($dialog, {
                'chosen-btn-all':      '[{isys type="lang" ident="LC__UNIVERSAL__CHOOSE_ALL_SHORT"}]',
                'chosen-btn-inverted': '[{isys type="lang" ident="LC__UNIVERSAL__CHOOSE_INVERTED_SHORT"}]',
                'chosen-btn-none':     '[{isys type="lang" ident="LC__UNIVERSAL__CHOOSE_NONE_SHORT"}]',
                chosenMaxHeight:       '100px'
            });
        });
        
        return this;
    },
    
    /**
     * Method for reloading a parameter, when the method is changed.
     * @param   ev
     * @return  AuthConfiguration
     */
    change_method: function (ev) {
        var $tr         = ev.findElement().up('tr'),
            $checkboxes = $tr.select('input.right-checkbox:not(.hide)'),
            counter     = $tr.readAttribute('data-counter'),
            method      = $F(this.element_id + '_method_' + counter),
            i;
        
        // ID-4817  Default rights will only be applied by user interaction.
        this.newAuthPathClick = true;
        
        // Reset all checkboxes before triggering any change.
        for (i in $checkboxes) {
            if ($checkboxes.hasOwnProperty(i)) {
                // Activate and un-check.
                $checkboxes[i].enable().writeAttribute('disabled', null).setValue(null);
                $checkboxes[i].checked = false;
                $checkboxes[i].disabled = false;
            }
        }
        
        this.get_parameter(this.element_id + '_param_' + counter, method, false);
        
        return this;
    },
    
    /**
     * This method is used to disable rights, which are not available for the given method.
     * @param  method
     * @param  cnt
     * @return  AuthConfiguration
     */
    change_rights: function (method, cnt) {
        var $checkboxes      = $(this.element_id + '-row-' + cnt).select('input.right-checkbox'),
            available_rights = (this.options.methods[method].hasOwnProperty('rights')) ? this.options.methods[method].rights : [],
            default_rights   = (this.options.methods[method].hasOwnProperty('defaults')) ? this.options.methods[method].defaults : [];
        
        // We want to disable all "inherited" checkboxes.
        if ($(this.element_id + '-row-' + cnt).readAttribute('data-inherited') == 1) {
            $checkboxes.invoke('disable');
            return this;
        }
        
        // If the current method does not inherit any special rights, we continue...
        if (this.options.edit_mode == 0) {
            return this;
        }
        
        $checkboxes.invoke('enable').invoke('writeAttribute', 'data-not-available', null);
        
        if (available_rights.length === 0) {
            return this;
        }
        
        $checkboxes.each(function (el) {
            if (!available_rights.in_array(parseInt(el.value))) {
                // Disable and un-check the checkbox.
                el.writeAttribute('data-not-available', 1).disable().setValue(0);
            }
            
            if (this.newAuthPathClick && default_rights.in_array(parseInt(el.value)) && !el.match('[data-not-available="1"]')) {
                el.setValue(1);
            }
        }.bind(this));
    
        this.newAuthPathClick = false;
        
        return this;
    },
    
    /**
     * This method will be called, if the "all" button is clicked.
     * @param   ev
     */
    update_parameter_state: function (ev) {
        var el        = ev.findElement('button'),
            div       = el.next('div').down('.param-hider'),
            use_hider = !['category_in_obj_type', 'category_in_object', 'category_in_location'].in_array(el.up('tr').readAttribute('data-method'));
        
        if (el.hasClassName('btn-green')) {
            el.removeClassName('btn-green');
            
            if (!Object.isUndefined(el.next('input'))) {
                el.next('input').setValue(0);
            }
            
            if (use_hider) {
                div.setStyle({display: 'none'});
            } else {
                div.next('div').down('input,select').enable().fire('chosen:updated');
            }
        } else {
            el.addClassName('btn-green');
            
            if (!Object.isUndefined(el.next('input'))) {
                el.next('input').setValue(1);
            }
            
            if (use_hider) {
                div.setStyle({display: 'block'});
            } else {
                div.next('div').down('input,select').disable().fire('chosen:updated');
            }
        }
    },
    
    /**
     * Method for resetting all observers.
     * @return  AuthConfiguration
     */
    set_observer: function () {
        this.element.stopObserving();
        this.element.on('click', 'button.param-button', this.update_parameter_state);
        this.element.on('click', 'button.remove-path-button', this.remove_path);
        
        // The internet explorer has massive problems handling "onChange" events...
        this.element.on('change', 'input.right-checkbox', this.update_rights.bindAsEventListener(this));
        this.element.on('change', 'select.method-select', this.change_method.bindAsEventListener(this));
        
        return this;
    },
    
    /**
     * Method for removing a path.
     * @param   ev
     */
    remove_path: function (ev) {
        ev.findElement().up('tr').remove();
    },
    
    /**
     * Checking and/or disabling other checkboxes, depending on the inheritance.
     * @param   ev
     */
    update_rights: function (ev) {
        var $checkbox = ev.findElement('input'),
            checked   = $checkbox.checked,
            value     = $checkbox.readAttribute('value'),
            $row      = $checkbox.up('tr'),
            $tmp,
            mapping   = {};
        
        mapping[this.rights.CREATE] = [];
        mapping[this.rights.VIEW] = [];
        mapping[this.rights.EDIT] = [this.rights.CREATE]; // @see ID-3878
        mapping[this.rights.ARCHIVE] = []; // @see ID-3878
        mapping[this.rights.DELETE] = []; // @see ID-3878
        mapping[this.rights.EXECUTE] = [this.rights.VIEW];
        mapping[this.rights.SUPERVISOR] = Object.values(this.rights);

        $row.select('input.right-checkbox:not([data-not-available],.hide)').each(function ($checkbox) {
            // Don't use "getValue()" because that will only work, if the checkbox is checked.
            if (mapping[value].in_array(parseInt($checkbox.readAttribute('value')))) {
                if (checked) {
                    $checkbox.setValue(1).disable();
                    
                    // This is necessary to send disabled values to the backend!
                    if (!$checkbox.next('.right-checkbox.hide')) {
                        $checkbox.insert({after: $checkbox.clone().enable().addClassName('hide')})
                    }
                } else {
                    $checkbox.setValue(0).enable();
                    
                    // Remove previously created helper-checkboxes (if they exist).
                    if ($checkbox.next('.right-checkbox.hide')) {
                        $checkbox.next('.right-checkbox.hide').remove();
                    }
                }
            }
        });
        
        if (!checked) {
            $tmp = $row.down('input.right-checkbox:not([data-not-available],.hide):checked:last');
            
            if ($tmp) {
                $tmp.simulate('change');
            }
        }
        
        $checkbox.enable();
    }
});