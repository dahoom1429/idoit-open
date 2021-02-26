/**
 * Chassis class for displaying chassis and its containing objects.
 *
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
var Chassis = Class.create({
	/**
	 * Initialize method, gets called when object is created.
	 *
	 * @param   element  string
	 * @param   options  object
	 * @return  Chassis
	 */
	initialize:function (element, options) {
		this.options = options;
        
        this.options = Object.extend({
            matrix:             [],                      // The chassis matrix.
            devices:            [],                      // The slots assigned devices.
            view:               'front',                 // The view-point of this chassis instance.
            x:                  0,                       // The grid width.
            y:                  0,                       // The grid height.
            size:               2,                       // The grid size - From 0-5. 0 is the smallest.
            editmode:           false,                   // Defines, if checkboxes shall be displayed.
            collision_callback: Prototype.emptyFunction, // Callback in case of collision-detection.
            options_active:     false,                   // Defines if options shall be displayed.
            option_callback:    Prototype.emptyFunction, // Callback for option-click.
            info_active:        false,                   // Defines if options shall be displayed.
            info_callback:      Prototype.emptyFunction, // Callback for option-click.
            sizesInPercent:     false,
            mirrored:           false
        }, options || {});

		// This is handled separately, because of the IE.
		var tbody = new Element('tbody', {id: 'matrix-' + $(element).id});

		// Prepare the table-size, so that the matrix won't get mashed if it reaches the border.
		this.root_element = $(element).update(new Element('table').update(tbody));
		this.element = tbody;

		this.create_chassis();
	},

	/**
	 * Method for adding observers to the corresponding elements.
	 *
	 * @return  Chassis
	 */
	add_observer:function () {
		$(this.element.id).select('input').invoke('on', 'click', this.checkbox_click.bindAsEventListener(this));
		$(this.element.id).select('img.option').invoke('on', 'click', this.options.option_callback);
		$(this.element.id).select('div.title img.info').invoke('on', 'click', this.options.info_callback);

		return this;
	},

	/**
	 * Method for creating the HTML matrix.
	 *
	 * @return  Chassis
	 */
	create_chassis:function () {
		var x,
			y,
			tr,
			td,
			devices,
			device,
			height,
			width,
			size = this.getGridSize(false),
            $objectDiv,
            $containerDiv;

		this.element.update();

		for (y = -1; y < this.options.y; y++) {
		 
			tr = new Element('tr');
   
			// This is necessary to display a chassis mirrored (for rack view).
			for ((this.options.mirrored ? x = (this.options.x - 1) : x = -1); (this.options.mirrored ? x >= -1 : x < this.options.x); (this.options.mirrored ? x-- : x++)) {
			 
				if (y >= 0 && x >= 0) {
					// This might happen on "real-time-resizing".
					if (typeof this.options.matrix[y] === 'undefined') {
						this.options.matrix[y] = [];
					}

					if (typeof this.options.matrix[y][x] === 'undefined') {
						this.options.matrix[y][x] = null;
					}

					if (this.options.matrix[y][x] !== false) {
						td = new Element('td', this.options.matrix[y][x]);

						if (this.options.matrix[y][x] !== null) {
							td.update(new Element('div', {className: 'title', title: this.options.matrix[y][x]['data-slot-title']}).update(this.options.matrix[y][x]['data-slot-title']));
						}

						if (this.options.editmode === true && this.options.matrix[y][x] === null) {
							td.insert(new Element('input', {type: 'checkbox', id: x + '-' + y})).addClassName('edit');
						} else {
							if (devices = this.options.devices[x + '-' + y]) {
                                $containerDiv = new Element('div', {className: 'device-container'});
							    
                                // We can set some properties here.
                                td.addClassName('device')
                                    .writeAttribute('data-slot-id', devices[0].slotid)
                                    .insert($containerDiv);
                                
								// This is necessary because we can have more than one device per slot.
								for (var i in devices) {


									// Important check, so that we don't iterate through member-functions.
									if (devices.hasOwnProperty(i)) {
										device = devices[i];

										$objectDiv = new Element('div', {className: 'device', style: 'background:' + device.object_color}).update(device.title);

										if (device.object_color === null) {
											device.object_color = '#FFFFFF';
										}

										if (device.object_id !== null && device.object_id > 0) {
                                            $objectDiv.writeAttribute('data-object-id', device.object_id).writeAttribute('data-object-type', device.object_type);
										}
                                        
                                        $containerDiv.insert($objectDiv).setStyle({background: device.object_color});
									}
								}
							}
						}

						if (this.options.options_active === true && td.hasClassName('slot')) {
							td.down('div.title').insert(new Element('img', {src: window.dir_images + 'icons/silk/bullet_arrow_down.png', className: 'option fr'}));
						}

						if (this.options.info_active === true && td.hasClassName('slot')) {
							td.down('div.title').insert(new Element('img', {src: window.dir_images + 'icons/silk/information.png', className: 'info fr'}));
						}

						tr.insert(td);
					}
				} else {
					// Add "edge" TDs so that col- and rowspans won't shrink the view.
					if (y === -1 && x === -1) {
						if (this.element.up('table').down('colgroup')) {
							this.element.up('table').down('colgroup').remove();
						}

						var colgroup = new Element('colgroup'),
							col_width = this.getGridSize(true),
							col_x;

						if (! this.options.mirrored) {
						    colgroup.insert(new Element('col', {width: 0, style: 'width:0;'}));
                        }
						
						for (col_x = 0; col_x < this.options.x; col_x ++) {
							colgroup.insert(new Element('col', {width: col_width, style: 'width:' + col_width + ';'}))
						}
                        
                        if (this.options.mirrored) {
                            colgroup.insert(new Element('col', {width: 0, style: 'width:0;'}));
                        }

						this.element.up('table').insert({top: colgroup});
					}

					var className = 'edge-' + ((y === -1) ? ((x === -1) ? 'first' : 'top') : 'side'),
                        style = '';
                    
                    if (this.options.sizesInPercent && this.options.x > 0 && this.options.y > 0) {
                        style = ((y === -1) ? ((x === -1) ? '' : 'width:' + (100 / this.options.x) + '%') : 'height:' + (100 / this.options.y) + '%');
                    } else {
                        style = ((y === -1) ? ((x === -1) ? '' : 'width:' + size + 'px') : 'height:' + size + 'px');
                    }
					
					tr.insert(new Element('td', {className: className, style: style}));
				}
			}

			this.element.insert(tr);
		}

		return this.add_observer();
	},

	/**
	 * Method which get called, when clicking on a checkbox inside the matrix.
	 */
	checkbox_click:function () {
		var checkboxes = $$('#' + this.element.id + ' input:checked');

		$(this.element.id).select('td').invoke('removeClassName', 'selected');
		$(this.element.id).select('input').invoke('enable');
		this.options.collision_callback(false);

		switch (checkboxes.length) {
			case 0:
				// Nothing to do here.
				break;
			case 1:
				checkboxes[0].up().addClassName('selected');
				break;
			case 2:
				[checkboxes[0].up(), checkboxes[1].up()].invoke('addClassName', 'selected');

				var check = [checkboxes[0].id.split('-'), checkboxes[1].id.split('-')];

				this.mark_rectangle(parseInt(check[0][0]), parseInt(check[1][0]), parseInt(check[0][1]), parseInt(check[1][1]));

				$$('#' + this.element.id + ' input:not(:checked)').invoke('disable');
				break;
		}
	},

	/**
	 * Method for marking the selected rectangle.
	 *
	 * @param   x1  integer
	 * @param   x2  integer
	 * @param   y1  integer
	 * @param   y2  integer
	 * @return  Chassis
	 */
	mark_rectangle:function (x1, x2, y1, y2) {
		var from_x = ((x1 > x2) ? x2 : x1),
			to_x = ((x1 > x2) ? x1 : x2),
			from_y = ((y1 > y2) ? y2 : y1),
			to_y = ((y1 > y2) ? y1 : y2),
			x,
			y,
			tmp,
			collision = false;

		for (y = from_y; y <= to_y; y++) {
			for (x = from_x; x <= to_x; x++) {
				tmp = $$('#' + this.element.id + ' #' + x + '-' + y)[0];
				// Just to prevent some Javascript errors.
				if (tmp) {
					tmp.up().addClassName('selected');
				} else {
					collision = true;
				}
			}
		}

		// This callback gets called, if the assignment crosses any other slot(s).
		this.options.collision_callback(collision);

		return this;
	},

	/**
	 * Method for reseting the whole table and building it up new.
	 *
	 * @return  Chassis
	 * @todo    At some point "this.element" is beeing lost, so we assign it new. We should check this some time...
	 */
	reset_html:function () {
		this.element = $(this.element.id);

		return this.create_chassis();
	},

	/**
	 * Method for setting the edit mode.
	 *
	 * @param   editmode  boolean
	 * @return  Chassis
	 */
	set_editmode:function (editmode) {
		this.options.editmode = !! editmode;

		return this;
	},

	/**
	 * Method for setting new devices.
	 *
	 * @param   devices  array
	 * @return  Chassis
	 */
	set_devices:function (devices) {
		this.options.devices = devices;

		return this;
	},

	/**
	 * Method for setting new objects.
	 *
	 * @param   matrix  array
	 * @return  Chassis
	 */
	set_matrix:function (matrix) {
		this.options.matrix = matrix;

		return this;
	},

	/**
	 * Set the horizontal size of the matrix.
	 *
	 * @param   x  integer
	 * @return  Chassis
	 */
	set_x:function (x) {
		this.options.x = parseInt(x);

		return this;
	},

	/**
	 * Set the vertical size of the matrix.
	 *
	 * @param   y  integer
	 * @return  Chassis
	 */
	set_y:function (y) {
		this.options.y = parseInt(y);

		return this;
	},

	/**
	 * Sets the size of each matrix-field (from 0-5).
	 *
	 * @param   size  integer
	 * @return  Chassis
	 */
	set_size:function (size) {
		if (size >= 0 && size <= 5) {
			this.options.size = size;
		}

		return this;
	},

	/**
	 * Method for retrieving the current size-option.
	 *
	 * @return  integer
	 */
	get_size:function () {
		return this.options.size;
	},

	/**
	 * Method for retrieving the pixel-size of the currently set size-option.
	 *
     * @param unit
     * @returns {*}
     */
    getGridSize:function (unit) {
		switch (parseInt(this.options.size)) {
			case 0: return 25 + (unit ? 'px' : 0); // The fields will not get smaller than this.
			case 1: return 35 + (unit ? 'px' : 0);
			default:
			case 2: return 45 + (unit ? 'px' : 0); // Standard size.
			case 3: return 55 + (unit ? 'px' : 0);
			case 4: return 70 + (unit ? 'px' : 0);
			case 5: return 85 + (unit ? 'px' : 0); // Damn huge!
		}
	}
});



/**
 * RackChassis class for displaying chassis (and its containing objects) inside racks.
 * The class is located here, so that the JS compiler won't mess up.
 *
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
var RackChassis = Class.create(Chassis, {
    /**
     * Initialize method, gets called when object is created.
     *
     * @param   element  string
     * @param   options  object
     * @return  Chassis
     */
    initialize: function (element, options) {
        this.options = options;
        
        this.options = Object.extend({
            matrix:   [],      // The chassis matrix.
            devices:  [],      // The slots assigned devices.
            view:     'front', // The view-point of this chassis instance.
            x:        0,       // The grid width.
            y:        0,       // The grid height.
            mirrored: false    // Display the chassis mirrored - will be necessary when it's inserted front and back.
        }, options || {});
        
        this.options.editmode = false;
        this.options.collision_callback = Prototype.emptyFunction;
        this.options.options_active = false;
        this.options.option_callback = Prototype.emptyFunction;
        this.options.info_active = false;
        this.options.info_callback = Prototype.emptyFunction;
        this.options.sizesInPercent = true;
        
        // Prepare the table-size, so that the matrix won't get mashed if it reaches the border.
        this.element = new Element('tbody');
        this.root_element = $(element).update(new Element('table', {className:'chassis'}).update(this.element));
        
        this.create_chassis();
    
        this.root_element.on('update:fitToContainer', function () {
            this.fitToContainer();
        }.bind(this));
    },
    
    add_observer: function () {
        return this;
    },
    
    /**
     * Method for retrieving the pixel-size of the currently set size-option.
     *
     * @param unit
     * @returns {*}
     */
    getGridSize:function (unit) {
        return (100 / this.options.x) + (unit ? '%' : 0);
    },
    
    /**
     * Method for setting the chassis height = the container height.
     * @see  ID-5501
     */
    fitToContainer: function () {
        var $container = this.root_element.up('td'),
            $chassisElements = [
                this.root_element,
                this.root_element.down('table')
            ];
    
        $chassisElements.invoke('setStyle', {height: '0px'});
        
        // Because of some animations we need to wait a bit to assign the new heights.
       setTimeout(function(){
           $chassisElements.invoke('setStyle', {height: $container.getHeight() + 'px'});
       }.bind(this), 250);
    }
});