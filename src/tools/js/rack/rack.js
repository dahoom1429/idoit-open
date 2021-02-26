/**
 * Rack class for displaying racks and their containing objects.
 * Required variables:
 *   idoit.Translate.get("LC__CMDB__CATS__RACK__REASSIGN_OBJECT");
 *   idoit.Translate.get("LC__CMDB__CATS__RACK__REMOVE_OBJECT");
 *   idoit.Translate.get("LC__UNIVERSAL__TITLE_LINK");
 *
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
window.Rack = Class.create({
    /**
     * Initialize method, gets called when object is created.
     *
     * @param $element
     * @param options
     */
    initialize: function ($element, options) {
        this.$element = $element;
        this.options = options;
        
        this.options = Object.extend({
            edit_right:             true,                    // Will be used to add event handlers and stuff.
            objects:                [],                      // Objects in this rack.
            room_view:              false,                   // The room view will deactivate some options and display the rack a lot smaller.
            slots:                  24,                      // Available slots in this rack.
            verticalSlots:          0,                       // Available slots in this rack.
            verticalSlotsMirrored:  0,                       // Define if the vertical slots shall be mirrored (default for rear view).
            verticalSlotSorting:    1,                       // Define the vertical slot sorting order.
            view:                   'front',                 // The view-point of this rack instance.
            slot_sort:              'asc',                   // Defines the slot-number sorting.
            object_link:            false,                   // Define if the object-titles shall link to the object in i-doit.
            objectReassign:         false,                   // Defines if the objects shall be re-assignable (use a callback!!).
            objectReassignCallback: Prototype.emptyFunction, // Defines the callback method for reassigning an object.
            object_remove:          false,                   // Defines if the objects shall be removable (use a callback!!).
            objectRemoveCallback:   Prototype.emptyFunction, // Defines the callback method for removing an object.
            slot_segment:           false,
            slotSegmentCallback:    Prototype.emptyFunction,
            slotDetach:             false,
            slotDetachCallback:     Prototype.emptyFunction,
            detailView:             false,
            quickinfoCallback:      Prototype.emptyFunction,
            OPTION_VERTICAL:        4,
            OPTION_HORIZONTAL:      3,
            INSERTION_FRONT:        1,
            INSERTION_REAR:         0,
            INSERTION_BOTH:         2
        }, options || {});
        
        this.create_rack();
        this.setObserver();
    },
    
    select: function (horizontal, from, to) {
        var i;
        
        if (horizontal) {
            if (this.options.slot_sort === 'asc') {
                // @See ID-4676
                if (from > to) {
                    i = to;
                    to = from;
                    from = i;
                }
                
                for (i = from; i <= to; i++) {
                    this.$element.down('.slot-' + i).addClassName('selected');
                }
            } else {
                // @See ID-4676
                if (from < to) {
                    i = to;
                    to = from;
                    from = i;
                }
                
                for (i = from; i >= to; i--) {
                    this.$element.down('.slot-' + i).addClassName('selected');
                }
            }
        } else {
            this.$element.select('.left-slots .slot,.right-slots .slot').each(function ($slot) {
                if ($slot.readAttribute('data-slot') == from) {
                    $slot.addClassName('selected');
                }
            });
        }
    },
    
    unselect: function () {
        this.$element.select('.selected').invoke('removeClassName', 'selected');
    },
    
    setObserver: function () {
        this.$element.on('click', '.slot-options', this.slot_option.bindAsEventListener(this));
    },
    
    /**
     * Method for creating the main HTML, rack slots, assigned objects and vertical slots alltogether.
     *
     * @return  Rack
     */
    create_rack: function () {
        var left_slots  = 'left-slots',
            right_slots = 'right-slots',
            table_class = '';
        
        if (this.options.view == 'rear' && this.options.verticalSlotsMirrored) {
            // We turn this around, because it's the back view.
            left_slots = 'right-slots';
            right_slots = 'left-slots';
        }
        
        if (this.options.room_view) {
            table_class = 'room-view';
        }
        
        this.$element.update(
            new Element('table', {cellPadding: 0, cellSpacing: 0, width: '100%', className: table_class})
                .update(new Element('tbody')
                    .update(new Element('tr')
                        .update(new Element('td', {className: left_slots}))
                        .insert(new Element('td', {className: 'main-slots'})
                            .update(new Element('table', {cellPadding: 0, cellSpacing: 0})))
                        .insert(new Element('td', {className: right_slots}))))
        );
        
        this.createVerticalSlots().create_slots().createQuickinfos();
        
        this.updateChassisSizes();
        
        return this;
    },
    
    createQuickinfos: function () {
        var $rackObjects    = this.$element.select('.object-title'),
            $segmentObjects = this.$element.select('.chassis .device[data-object-id]'),
            $link, i;
        
        for (i in $rackObjects) {
            if (!$rackObjects.hasOwnProperty(i)) {
                continue;
            }
            
            this.options.quickinfoCallback($rackObjects[i], $rackObjects[i].up('[data-object-id]').readAttribute('data-object-id'));
        }
        
        for (i in $segmentObjects) {
            if (!$segmentObjects.hasOwnProperty(i)) {
                continue;
            }
            
            $link = new Element('a', {target: '_blank', href: '?objID=' + $segmentObjects[i].readAttribute('data-object-id')})
                .update($segmentObjects[i].textContent || $segmentObjects[i].innerText || $segmentObjects[i].innerHTML);
            
            this.options.quickinfoCallback($segmentObjects[i].update($link), $segmentObjects[i].readAttribute('data-object-id'));
        }
    },
    
    /**
     * Method for only creating the vertical slots.
     *
     * @return  Rack
     */
    createVerticalSlots: function (num) {
        var fl = 'fl',
            fr = 'fr',
            sorting;
        
        switch (this.options.verticalSlotSorting) {
            default:
            case 1:
                /*
                 *  [ 1] [ 7] ======== [ 8] [ 2]
                 *  [ 3] [ 9] ======== [10] [ 4]
                 *  [ 5] [11] ======== [12] [ 6]
                 */
                sorting = (this.options.slot_sort == 'asc' ? [1, 7, 3, 9, 5, 11, 2, 8, 4, 10, 6, 12] : [5, 11, 3, 9, 1, 7, 6, 12, 4, 10, 2, 8]);
                break;
            
            case 2:
                /*
                 *  [ 1] [ 2] ======== [ 3] [ 4]
                 *  [ 5] [ 6] ======== [ 7] [ 8]
                 *  [ 9] [10] ======== [11] [12]
                 */
                sorting = (this.options.slot_sort == 'asc' ? [1, 2, 5, 6, 9, 10, 4, 3, 8, 7, 12, 11] : [9, 10, 5, 6, 1, 2, 12, 11, 8, 7, 4, 3]);
                break;
            
            case 3:
                /*
                 *  [ 1] [ 2] ======== [ 7] [ 8]
                 *  [ 3] [ 4] ======== [ 9] [10]
                 *  [ 5] [ 6] ======== [11] [12]
                 */
                sorting = (this.options.slot_sort == 'asc' ? [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12] : [5, 6, 3, 4, 1, 2, 11, 12, 9, 10, 7, 8]);
                break;
        }
        
        if (this.options.view === 'rear' && this.options.verticalSlotsMirrored) {
            fl = 'fr';
            fr = 'fl';
        }
        
        this.$element.down('.left-slots')
            .update(new Element('div', {className: 'slot ' + fl, 'data-slot': sorting[0], 'data-vertical': 1}))
            .insert(new Element('div', {className: 'slot ' + fl, 'data-slot': sorting[1], 'data-vertical': 1}))
            .insert(new Element('br', {className: 'clear'}))
            .insert(new Element('div', {className: 'slot ' + fl, 'data-slot': sorting[2], 'data-vertical': 1}))
            .insert(new Element('div', {className: 'slot ' + fl, 'data-slot': sorting[3], 'data-vertical': 1}))
            .insert(new Element('br', {className: 'clear'}))
            .insert(new Element('div', {className: 'slot last ' + fl, 'data-slot': sorting[4], 'data-vertical': 1}))
            .insert(new Element('div', {className: 'slot last ' + fl, 'data-slot': sorting[5], 'data-vertical': 1}));
        
        this.$element.down('.right-slots')
            .update(new Element('div', {className: 'slot ' + fr, 'data-slot': sorting[6], 'data-vertical': 1}))
            .insert(new Element('div', {className: 'slot ' + fr, 'data-slot': sorting[7], 'data-vertical': 1}))
            .insert(new Element('br', {className: 'clear'}))
            .insert(new Element('div', {className: 'slot ' + fr, 'data-slot': sorting[8], 'data-vertical': 1}))
            .insert(new Element('div', {className: 'slot ' + fr, 'data-slot': sorting[9], 'data-vertical': 1}))
            .insert(new Element('br', {className: 'clear'}))
            .insert(new Element('div', {className: 'slot last ' + fr, 'data-slot': sorting[10], 'data-vertical': 1}))
            .insert(new Element('div', {className: 'slot last ' + fr, 'data-slot': sorting[11], 'data-vertical': 1}));
        
        return this.updateVerticalSlots(num);
    },
    
    /**
     * Method for creating the rack slots and the containing objects.
     *
     * @return  Rack
     */
    create_slots: function () {
        var i,
            cnt,
            num,
            $tbody  = new Element('tbody'),
            object,
            rowspan = 0,
            display_obj,
            className,
            last,
            $rackContent;
        
        for (i = 1; i <= this.options.slots; i++) {
            num = i;
            
            // We check for ascending or descanding sorting to label the slots right.
            if (this.options.slot_sort !== 'asc')
            {
                num = (this.options.slots - num) + 1;
            }
            
            className = 'row slot-' + parseInt(num);
            num = ((num < 10) ? '0' + num : num);
            
            object = null;
            display_obj = false;
            
            for (cnt in this.options.objects) {
                // Fix for iterating through member methods.
                if (this.options.objects.hasOwnProperty(cnt)) {
                    // Option "4" = Vertical assignment.
                    if (this.options.objects[cnt].option != 4 && this.options.objects[cnt].pos == i) {
                        if (this.options.objects[cnt].insertion == 2 ||
                            (this.options.view == 'front' && this.options.objects[cnt].insertion == 1) ||
                            (this.options.view == 'rear' && this.options.objects[cnt].insertion == 0)) {
                            object = this.options.objects[cnt];
                        }
                    }
                }
            }
            
            $rackContent = new Element('td', {className: 'slot'}).update(this.renderObjectOptionsButton());
            
            $tbody.insert(
                new Element('tr', {
                    className:             className,
                    'data-slotnum':        i,
                    'data-object-id':      (object !== null ? object.id || null : null),
                    'data-object-title':   (object !== null ? object.title || null : null),
                    'data-object-height':  (object !== null ? object.height || null : null),
                    'data-object-chassis': ((object !== null && object.isChassis) ? 1 : null)
                })
                    .update(new Element('td').update(num))
                    .insert($rackContent)
                    .insert(new Element('td').update(num))
            );
            
            if (rowspan > 0) {
                $tbody.down('tr:last').down('td', 1).remove();
                rowspan--;
            }
            
            if (object !== null) {
                if (this.options.view == 'front' && object.insertion == this.options.INSERTION_FRONT) {
                    display_obj = true;
                } else if (this.options.view == 'rear' && object.insertion == this.options.INSERTION_REAR) {
                    display_obj = true;
                } else if (object.insertion == this.options.INSERTION_BOTH) {
                    display_obj = true;
                }
                
                if (display_obj) {
                    rowspan = (object.height - 1);
                    
                    try {
                        // @see  ID-5201  Used in `renderObject` to skip "chassis" rendering for empty chassis.
                        object.renderable = true;
                        
                        $rackContent
                            .insert(this.renderObject(object))
                            .writeAttribute({rowspan: (object.height > 1 ? object.height : null)})
                            .setStyle({backgroundColor: object.color, backgroundImage: 'none'});
                    } catch (e) {
                        $tbody.down('tr:last').previous('tr').down('td', 1).removeAttribute('rowspan');
                        $rackContent
                            .update(new Element('div', {className: 'box-red'}).update('Positioning error in ')
                            .insert(new Element('a', {href: '?objID=' + object.id}).update(object.title)));
                    }
                }
            }
        }
        
        // Add the "last" class to the last TD in the table.
        last = $tbody.down('td.slot:last');
        
        this.$element.down('.main-slots table').update($tbody);
        
        // For preventing JS errors...
        if (last) {
            last.addClassName('last');
        }
        
        return this;
    },
    
    /**
     * Method for updating the viewable vertical slots (may be called from a onChange-event).
     *
     * @param   slotNumber
     * @return  Rack
     */
    updateVerticalSlots: function (slotNumber) {
        var verticalSlots = this.options.verticalSlots;
        
        if (typeof slotNumber != 'undefined') {
            verticalSlots = slotNumber;
        }
        
        // We need to append the ID of this instance to not update all found left and right slots on a page.
        this.$element.select('.left-slots .slot,.right-slots .slot').each(function ($slot) {
            var displayObject = false, i, object,
                slotNumber    = $slot.readAttribute('data-slot'),
                attributes;
            
            $slot.update(this.renderObjectOptionsButton());
            
            if (isNaN(verticalSlots)) {
                $slot.hide();
            } else {
                for (i in this.options.objects) {
                    // Fix for iterating through member methods.
                    if (this.options.objects.hasOwnProperty(i)) {
                        if (this.options.objects[i].option == this.options.OPTION_VERTICAL && this.options.objects[i].pos == slotNumber) {
                            if ((this.options.view == 'front' && this.options.objects[i].insertion == this.options.INSERTION_FRONT) ||
                                (this.options.view == 'rear' && this.options.objects[i].insertion == this.options.INSERTION_REAR)) {
                                object = this.options.objects[i];
                                displayObject = true;
                            }
                        }
                    }
                }
                
                if (displayObject) {
                    attributes = {
                        'data-slotnum':       slotNumber,
                        'data-object-id':     (object !== null ? object.id || null : null),
                        'data-object-title':  (object !== null ? object.title || null : null),
                        'data-object-height': (object !== null ? object.height || null : null)
                    };
    
                    // @see  ID-5201  Used in `renderObject` to skip "chassis" rendering for empty chassis.
                    object.renderable = true;
                    
                    $slot.insert(this.renderObject(object).addClassName('rotated'))
                        .setStyle({backgroundColor: object.color, backgroundImage: 'none'})
                        .writeAttribute(attributes);
                    
                    $slot.down('.cmdb-marker').removeClassName('m5');
                } else {
                    $slot.insert(new Element('span', {className: 'rotated'}).update('Slot ' + slotNumber))
                        .writeAttribute('data-slotnum', slotNumber);
                }
                
                if (parseInt($slot.readAttribute('data-slot')) > verticalSlots) {
                    $slot.hide();
                } else {
                    $slot.show();
                }
            }
        }.bind(this));
        
        return this;
    },
    
    /**
     * Method for setting the slot sorting.
     *
     * @param   string  sorting
     * @return  Rack
     */
    set_slot_sorting: function (sorting) {
        sorting = sorting.toLowerCase();
        
        if (sorting === 'asc' || sorting === 'desc') {
            this.options.slot_sort = sorting;
        }
        
        return this;
    },
    
    /**
     * Method for setting new objects.
     *
     * @param   array  objects
     * @return  Rack
     */
    setObjects: function (objects) {
        this.options.objects = objects;
        
        return this;
    },
    
    /**
     * Method for rendering a DIV with object information inside:
     * Object, Object type and controls for re-arranging and removing.
     *
     * @param   object
     * @return  Element
     */
    renderObject: function (object) {
        var object_title     = object.title + ' (' + object.type + ', FF: ' + object.formfactor + ')',
            $objectContainer = new Element('div', {className: 'slot-object'}),
            chassisFrontEmpty, chassisRearEmpty;
        
        // @see ID-4678 In case of a big chassis we want to use all the space we have to display it.
        if (object.isChassis && object.height > 1) {
            var rowHeight = (this.options.room_view ? 12 : 27);
            
            $objectContainer.setStyle({maxHeight: (object.height * rowHeight) + 'px'});
        }
        
        if (this.options.room_view) {
            object_title = new Element('a', {href: '?objID=' + object.id}).update(object_title);
        }
        
        // @see  ID-5201  A lot of new code to better render the front- and rear view of chassis.
        if (object.isChassis && object.renderable) {
            chassisFrontEmpty = object.chassis.matrix === undefined || object.chassis.matrix.length === 0;
            chassisRearEmpty = object.chassis.matrixRear === undefined || object.chassis.matrixRear.length === 0;
            
            // Check if we render the rack front and if the object should only be rendered here.
            if (object.insertion == this.options.INSERTION_FRONT) {
                // If the chassis has no defined matrix: render as "normal" object.
                if (chassisFrontEmpty) {
                    // Call itself to jump in the other branch.
                    object.renderable = false;
                    return this.renderObject(object);
                }
    
                new RackChassis($objectContainer, {
                    x:        object.chassis.x,
                    y:        object.chassis.y,
                    matrix:   object.chassis.matrix,
                    devices:  object.chassis.devices,
                    mirrored: false
                });
            }
    
            // Check if we render the rack rear and if the object should only be rendered here.
            if (object.insertion == this.options.INSERTION_REAR) {
                // If the chassis has no defined matrix: render as "normal" object.
                if (chassisRearEmpty) {
                    // Call itself to jump in the other branch.
                    object.renderable = false;
                    return this.renderObject(object);
                }
                
                new RackChassis($objectContainer, {
                    x:        object.chassis.xRear,
                    y:        object.chassis.yRear,
                    matrix:   object.chassis.matrixRear,
                    devices:  object.chassis.devicesRear,
                    mirrored: false
                });
            }
            
            if (object.insertion == this.options.INSERTION_BOTH) {
                if (this.options.view === 'front') {
                    if (chassisFrontEmpty) {
                        // If we render the rack front, but the chassis front is empty, we mirror the chassis rear view.
                        new RackChassis($objectContainer, {
                            x:        object.chassis.xRear,
                            y:        object.chassis.yRear,
                            matrix:   object.chassis.matrixRear,
                            devices:  object.chassis.devicesRear,
                            mirrored: true
                        });
                    } else {
                        new RackChassis($objectContainer, {
                            x:        object.chassis.x,
                            y:        object.chassis.y,
                            matrix:   object.chassis.matrix,
                            devices:  object.chassis.devices,
                            mirrored: false
                        });
                    }
                } else {
                    if (chassisRearEmpty) {
                        // If we render the rack rear, but the chassis rear is empty, we mirror the chassis front view.
                        new RackChassis($objectContainer, {
                            x:        object.chassis.x,
                            y:        object.chassis.y,
                            matrix:   object.chassis.matrix,
                            devices:  object.chassis.devices,
                            mirrored: true
                        });
                    } else {
                        new RackChassis($objectContainer, {
                            x:        object.chassis.xRear,
                            y:        object.chassis.yRear,
                            matrix:   object.chassis.matrixRear,
                            devices:  object.chassis.devicesRear,
                            mirrored: false
                        });
                    }
                }
            }
            
            return $objectContainer.setStyle({margin: '-1px 0 0 -1px'});
        } else {
            return $objectContainer
                .update(new Element('img', {
                    src:       ((object.icon != '') ? object.icon : window.dir_images + 'empty.gif'),
                    alt:       object.type,
                    className: 'object-icon'
                }))
                .insert(new Element('p', {class: 'object-title'}).update(object_title))
                .insert(new Element('div', {
                    className: 'cmdb-marker m5 mouse-help',
                    style:     'background-color:' + object.cmdb_color + ';',
                    title:     object.cmdb_status
                }));
        }
    },
    
    // @see ID-4678
    updateRowSizes: function (expanded) {
        var $rowspanRows = this.$element.select('tr.row>td.slot[rowspan]'),
            $objectContainer,
            rowHeight, rowSpan, i;
        
        if (expanded) {
            rowHeight = (this.options.room_view ? 41 : 84);
        } else {
            rowHeight = (this.options.room_view ? 12 : 27);
        }
        
        for (i in $rowspanRows) {
            if (!$rowspanRows.hasOwnProperty(i)) {
                continue;
            }
            
            $objectContainer = $rowspanRows[i].down('.slot-object');
            
            if (!$objectContainer) {
                continue;
            }
            
            rowSpan = parseInt($rowspanRows[i].readAttribute('rowspan'));
            
            $objectContainer.setStyle({maxHeight: (rowSpan * rowHeight) + 'px'});
        }
        
        this.updateChassisSizes();
    },
    
    updateChassisSizes: function () {
        setTimeout(function () {
            this.$element.select('.slot-object').invoke('fire', 'update:fitToContainer');
        }.bind(this), 500);
    },
    
    renderObjectOptionsButton: function () {
        if (this.options.edit_right && !this.options.room_view && (this.options.objectReassign || this.options.object_remove || this.options.link_objects)) {
            return new Element('img', {className: 'slot-options', src: window.dir_images + 'icons/silk/cog.png'});
        }
        
        return null;
    },
    
    slot_option: function (ev) {
        var $contextWrapper  = $('rackOptionsPopup'),
            x                = Event.pointerX(ev) - 10,
            y                = Event.pointerY(ev) - 10,
            $element         = ev.findElement(),
            $objectElement   = $element.up('[data-slotnum]'),
            $options         = new Element('ul', {className: 'list-style-none m0'}),
            objectId         = $objectElement.readAttribute('data-object-id'),
            objectTitle      = $objectElement.readAttribute('data-object-title'),
            objectSlotHeight = $objectElement.readAttribute('data-object-height'),
            objectIsChassis  = $objectElement.readAttribute('data-object-chassis'),
            slotNumber       = $objectElement.readAttribute('data-slotnum'),
            verticalSlots    = !!$element.up('[data-vertical]');
        
        if ($contextWrapper) {
            $contextWrapper.remove();
        }
        
        if (objectId && this.options.object_link) {
            $options.insert(
                new Element('li').update(
                    new Element('a', {href: '?objID=' + objectId, target: '_blank'})
                        .update(new Element('img', {src: window.dir_images + 'icons/silk/link.png', className: 'mr5'}))
                        .insert(idoit.Translate.get('LC__UNIVERSAL__TITLE_LINK'))
                )
            );
        }
        
        if (objectId && this.options.objectReassign) {
            $options.insert(
                new Element('li', {'data-object-id': objectId, 'data-object-title': objectTitle, 'data-object-height': objectSlotHeight}).update(
                    new Element('a', {href: '#'})
                        .update(new Element('img', {src: window.dir_images + 'icons/silk/arrow_switch.png', className: 'mr5'}))
                        .insert(idoit.Translate.get('LC__CMDB__CATS__RACK__REASSIGN_OBJECT'))
                )
            );
            
            $options.down('li:last').on('click', this.options.objectReassignCallback);
        }
        
        if (objectId && !objectIsChassis && this.options.object_remove) {
            $options.insert(
                new Element('li', {'data-object-id': objectId, 'data-object-title': objectTitle, 'data-object-height': objectSlotHeight}).update(
                    new Element('a', {href: '#'})
                        .update(new Element('img', { src: window.dir_images + 'icons/silk/cross.png', className: 'mr5'}))
                        .insert(idoit.Translate.get('LC__CMDB__CATS__RACK__REMOVE_OBJECT'))
                )
            );
            
            $options.down('li:last').on('click', this.options.objectRemoveCallback);
        }
        
        if (this.options.slot_segment && !objectIsChassis && !verticalSlots) {
            $options.insert(
                new Element('li', {'data-slot-number': slotNumber}).update(
                    new Element('a', {href: '#'})
                        .update(new Element('img', {src: window.dir_images + 'icons/silk/application_split.png', className: 'mr5'}))
                        .insert(idoit.Translate.get('LC__CMDB__CATS__RACK__SEGMENT_SLOT'))
                )
            );
            
            $options.down('li:last').on('click', this.options.slotSegmentCallback.bindAsEventListener(this));
        }
        
        if (this.options.slotDetach && objectIsChassis && !verticalSlots) {
            $options.insert(
                new Element('li', {'data-object-id': objectId, 'data-object-title': objectTitle, 'data-slot-number': slotNumber}).update(
                    new Element('a', {href: '#'})
                        .update(new Element('img', {src: window.dir_images + 'icons/silk/application_delete.png', className: 'mr5'}))
                        .insert(idoit.Translate.get('LC__CMDB__CATS__RACK__RESET_SLOT'))
                )
            );
            
            $options.down('li:last').on('click', this.options.slotDetachCallback);
        }
        
        if ($options.select('li').length === 0) {
            idoit.Notify.info(idoit.Translate.get('LC__CMDB__CATS__RACK__SLOT_HAS_NO_OPTIONS'), {life: 5});
            return;
        }
        
        $contextWrapper = new Element('div', {id: 'rackOptionsPopup', className: 'rackOptionsPopup box-white', style: 'top:' + (y + 2) + 'px; left:' + (x + 2) + 'px; opacity:0', 'data-slot-number': slotNumber})
            .update(new Element('h4', {className: 'p5 gradient'}).update(objectTitle || idoit.Translate.get('LC__CMDB__CATS__RACK__CONFIGURE_SLOT')))
            .insert($options);
        
        $contextWrapper.on('mouseleave', function () {
            if ($contextWrapper) {
                new Effect.Morph($contextWrapper, {
                    style:       'top:' + (y + 2) + 'px; left:' + (x + 2) + 'px; opacity: 0;',
                    duration:    0.25,
                    afterFinish: function () {
                        $contextWrapper.remove();
                    }
                });
            }
        });
        
        $('rackview').insert($contextWrapper);
        
        new Effect.Morph($contextWrapper, {
            style:    'top:' + y + 'px; left:' + x + 'px; opacity: 1;',
            duration: 0.25
        });
    },
    
    getChassisData: function (position) {
        var $slots = this.$element.down('.slot-' + position).select('[data-slot-id]'),
            result = [],
            i;
        
        for (i in $slots) {
            if ($slots.hasOwnProperty(i)) {
                result.push({
                    id:    $slots[i].readAttribute('data-slot-id'),
                    title: $slots[i].readAttribute('data-slot-title')
                });
            }
        }
        
        return result.sort(function (a, b) {
            return a.title.localeCompare(b.title);
        });
    },
    
    selectChassis: function (chassisId) {
        this.$element.select('.selected').invoke('removeClassName', 'selected');
        this.$element.down('[data-slot-id="' + chassisId + '"]').addClassName('selected');
    }
});
