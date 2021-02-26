var SimpleAuthConfiguration = Class.create({
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

		this.options = Object.extend({
			rights: {},
			modules: {},
			paths: {},
			inherited_paths: {},
			edit_mode: 0
		}, options || {});

		var thead = new Element('thead', {className:'gradient'});

		// Insert all "right"-checkboxes.
		for (var i in this.options.rights) {
			if (! this.options.rights.hasOwnProperty(i)) {
                continue;
			}

            thead.insert(new Element('th', {style:'width:25px;'}).update(
                new Element('img', {src:window.dir_images + this.options.rights[i].icon, alt:this.options.rights[i].title, title:this.options.rights[i].title})
            ));

            this.rights[this.options.rights[i].const] = this.options.rights[i].value;
		}

		// Insert the "condition", "parameter" and "action" fields.
		thead.insert(
				new Element('th', {className:'border-left'}).update(idoit.Translate.get('LC__AUTH_GUI__AUTH_MODULES'))
			).insert(
				new Element('th', {className:'border-left', style:'width:50px;'}).update(idoit.Translate.get('LC__AUTH_GUI__ACTION'))
			);

		this.element.update(
			new Element('table', {cellspacing:0, style:'width:100%;'}).update(
				thead
			).insert(
				new Element('tbody')
			)
		);

		 for (i in this.options.inherited_paths) {
			 if (this.options.inherited_paths.hasOwnProperty(i)) {
			    this.display_path(i, this.options.inherited_paths[i], true);
			 }
		 }

		for (i in this.options.paths) {
			if (this.options.paths.hasOwnProperty(i)) {
				this.display_path(i, this.options.paths[i]);
			}
		}

		// This has to be called only once.
		this.set_observer();

        this.element.select('input.right-checkbox:checked').invoke('simulate', 'change');
	},

	/**
	 * Method for displaying / adding a new path to the GUI.
	 * @param   module
	 * @param   rights
	 * @param   inherited
	 * @return  AuthConfiguration
	 */
	display_path: function (module, rights, inherited) {
		var i,
			right,
			tr = new Element('tr', {id: this.element_id + '-row-' + this.path_counter, 'data-counter': this.path_counter}),
			options = new Element('select', {id: this.element_id + '_module_' + this.path_counter, name: 'module_' + this.path_counter, className: 'input input-small module-select', disabled: !this.options.edit_mode});

		if (Object.isUndefined(inherited)) {
			inherited = false;
		}

		if (Object.isUndefined(rights)) {
			rights = [];
		}

		// @see ID-4792
		if (! Object.isArray(rights)) {
		    try {
                rights = Object.values(rights);
            } catch (e) {
		        rights = [];
            }
        }
		
		for (i in this.options.rights) {
			if (this.options.rights.hasOwnProperty(i)) {
				right = this.options.rights[i];

                tr.insert(new Element('td', {className: 'center'}).update(new Element('input', {type: 'checkbox', disabled: (!this.options.edit_mode || inherited), className: 'right-checkbox', name: 'right_' + this.path_counter + '[]', value: right.value, checked: rights.in_array(parseInt(right.value))})));
			}
		}

		for (i in this.options.modules) {
			if (this.options.modules.hasOwnProperty(i)) {
				options.insert(new Element('option', {value:i}).update(this.options.modules[i]));
			}
		}

		if (options.down('option[value="' + module + '"]')) {
			options.down('option[value="' + module + '"]').writeAttribute('selected', 'selected');
		}

		if (inherited) {
			tr.addClassName(inherited ? 'inactive' : '');
			options.writeAttribute('disabled', 'disabled');
		}

		tr
			.writeAttribute('data-inherited', inherited ? 1 : 0)
			.insert(new Element('td', {className:'border-left'}).update(new Element('span', {className: 'mr5'}).update(idoit.Translate.get('LC__AUTH_GUI__REFERS_TO'))).insert(options))
			.insert(new Element('td', {className:'border-left'})
				.update((this.options.edit_mode && ! inherited) ? new Element('button', {className: 'btn btn-small remove-path-button', type: 'button', title: idoit.Translate.get('LC__UNIVERSAL__REMOVE')}).update(new Element('img', {src: window.dir_images + 'icons/silk/cross.png'})) : ''));

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
			right,
			tr = new Element('tr', {id: this.element_id + '-row-' + this.path_counter, 'data-counter': this.path_counter}),
			options = new Element('select', {id: this.element_id + '_module_' + this.path_counter, name: 'module_' + this.path_counter, className: 'input input-small module-select', disabled: !this.options.edit_mode});

		for (i in this.options.rights) {
			if (this.options.rights.hasOwnProperty(i)) {
				right = this.options.rights[i];

                tr.insert(new Element('td', {className: 'center'}).update(new Element('input', {
                    type:      'checkbox',
                    disabled:  !this.options.edit_mode,
                    className: 'right-checkbox',
                    name:      'right_' + this.path_counter + '[]',
                    value:     right.value,
                    checked:   false
                })));
			}
		}

		for (i in this.options.modules) {
			if (this.options.modules.hasOwnProperty(i)) {
				options.insert(new Element('option', {value:i}).update(this.options.modules[i]));
			}
		}

		tr
			.insert(new Element('td', {className:'border-left'}).update(new Element('span', {className: 'mr5'}).update(idoit.Translate.get('LC__AUTH_GUI__REFERS_TO'))).insert(options))
			.insert(new Element('td', {className:'border-left'})
				.update((this.options.edit_mode) ? new Element('button', {className: 'btn btn-small remove-path-button', type: 'button', title: idoit.Translate.get('LC__UNIVERSAL__REMOVE')}).update(new Element('img', {src: window.dir_images + 'icons/silk/cross.png'})) : ''));

		this.element.down('tbody').insert(tr);
		this.path_counter++;

		return this;
	},

	/**
	 * Method for resetting all observers.
	 * @return  AuthConfiguration
	 */
	set_observer: function () {
		this.element.stopObserving();
		
        this.element.on('change', 'input.right-checkbox', this.update_rights.bindAsEventListener(this));

		this.element.on('click', 'button.remove-path-button', this.remove_path);

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