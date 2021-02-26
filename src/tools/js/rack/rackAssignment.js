/**
 * Rack assignment class for assigning objects to racks.
 *
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
window.RackAssignment = Class.create({
	/**
	 * Initialize method, gets called when object is created.
	 *
     * @param objects
     * @param options
     */
    initialize: function (objects, options) {
        this.selectedObjectId = 0;
        this.options = options;
        this.objects = objects;
        
        this.options = Object.extend({
            rackFront:                 null,
            rackRear:                  null,
            $unassignedObjects:        null,
            $unassignedObjectsCounter: null,
            $assignmentBox:            null, // Select: Horizontal or vertical
            $assignmentOption:         null, // Select: Horizontal or vertical
            $assignmentInsertion:      null, // Select: Front, rear or both
            $assignmentPosition:       null, // Select: Position in the rack
            $assignmentSegmentSlot:    null, // Select: Position in the chassis
            $assignmentSubmitButton:   null, // Button: Submit position in rack
            $heightChangeBox:          null,
            $heightChangeSelect:       null,
            $heightChangeButton:       null,
            $segmentBox:               null,
            $segmentObjectSelect:      null,
            $segmentPreview:           null,
            $segmentButton:            null,
            segmentTemplates:          {},
            rackObjectId:              0,
            allowedToEdit:             false,
            quickinfoCallback:         Prototype.emptyFunction,
            OPTION_VERTICAL:           4,
            OPTION_HORIZONTAL:         3,
            INSERTION_FRONT:           1,
            INSERTION_REAR:            0,
            INSERTION_BOTH:            2,
            lastSlotNumber:            0
        }, options || {});
        
        this.setObserver();
        this.resetAssignment();
    },
	
	setObserver: function () {
        this.options.$unassignedObjects.on('render:list', this.updateUnassignedObjectsList.bindAsEventListener(this));
        this.options.$unassignedObjectsCounter.on('update:list', this.updateUnassignedObjectsCounter.bindAsEventListener(this));
        
        if (this.options.allowedToEdit) {
            this.options.$unassignedObjects.on('click', '.object-item', this.selectObjectForAssignment.bindAsEventListener(this));
            this.options.$unassignedObjects.on('click', '.he-edit', this.editHeightUnits.bindAsEventListener(this));
            this.options.$unassignedObjects.on('click', '.location-remove', this.detachObjectLocation.bindAsEventListener(this));
            
            this.options.$heightChangeButton.on('click', this.heightChangeSubmit.bindAsEventListener(this));
            
            this.options.$assignmentOption.on('change', this.assignmentOptionChanged.bindAsEventListener(this));
            this.options.$assignmentInsertion.on('change', this.assignmentInsertionChanged.bindAsEventListener(this));
            this.options.$assignmentPosition.on('change', this.assignmentPositionChanged.bindAsEventListener(this));
            this.options.$assignmentSegmentSlot.on('change', this.assignmentSegmentSlotChanged.bindAsEventListener(this));
            this.options.$assignmentSubmitButton.on('click', this.assignmentSubmit.bindAsEventListener(this));
    
            this.options.$segmentObjectSelect.on('change', this.segmentLoadPreview.bindAsEventListener(this));
            this.options.$segmentButton.on('click', this.segmentCreate.bindAsEventListener(this));
        }
    },
    
    setLastSlotNumber: function(slotNumber) {
        this.options.lastSlotNumber = slotNumber;
        
        return this;
    },
    
    setObjects: function(objects) {
	    this.objects = objects;
    },
    
    getObjects: function () {
	    return this.objects;
    },
    
    resetAssignment: function () {
        this.selectedObjectId = 0;
        
        $('object-positioning').down('span').update();
    
        $('rackview').select('.active').invoke('removeClassName', 'active');
        
        this.options.$heightChangeBox.addClassName('hide');
        this.options.$heightChangeSelect.addClassName('hide');
        this.options.$heightChangeButton.addClassName('hide');
        
        this.options.$assignmentBox.addClassName('hide');
        this.options.$assignmentOption.addClassName('hide');
        this.options.$assignmentInsertion.addClassName('hide');
        this.options.$assignmentPosition.addClassName('hide');
        this.options.$assignmentSegmentSlot.addClassName('hide');
        
        this.options.$segmentBox.addClassName('hide').writeAttribute({'data-slot-insertion': null, 'data-slot-position': null});
        this.options.$segmentButton.addClassName('hide');
        
        this.options.rackFront.unselect();
        this.options.rackRear.unselect();
    },
    
    selectObjectForAssignment: function (ev) {
        var $objectItem = ev.findElement('[data-object-id]'),
            objectTitle = $objectItem.readAttribute('data-object-title');
    
        this.resetAssignment();
    
        this.selectedObjectId = $objectItem.readAttribute('data-object-id');
    
        if ($objectItem.up('.list')) {
            $objectItem.down('.object-item').addClassName('active');
        }
    
        $('object-positioning').down('span').update(objectTitle);
        
        this.options.$assignmentOption.removeClassName('hide').setValue(null).simulate('change');
        
        this.options.$assignmentBox.removeClassName('hide');
    },
    
    assignmentOptionChanged: function () {
        new Ajax.Request('?ajax=1&call=rack&func=get_rack_insertions', {
            parameters: {
                'obj_id': this.options.rackObjectId,
                'option': this.options.$assignmentOption.getValue()
            },
            method:     "post",
            onSuccess:  function (xhr) {
                var i, json = xhr.responseJSON;
                
                this.options.$assignmentInsertion.update();
                
                for (i in json)
                {
                    if (!json.hasOwnProperty(i))
                    {
                        continue;
                    }
                    
                    this.options.$assignmentInsertion.insert(new Element('option', {value: json[i].id}).update(json[i].title));
                }
                
                this.options.$assignmentInsertion.removeClassName('hide').setValue(null).simulate('change');
            }.bind(this)
        });
    },
    
    assignmentInsertionChanged: function () {
        // At first we load the free slots, so we don't have to mess around with various calculation later on.
        new Ajax.Request('?ajax=1&call=rack&func=get_free_slots', {
            parameters: {
                'rack_obj_id': this.options.rackObjectId,
                'assign_obj_id': this.selectedObjectId,
                'option': this.options.$assignmentOption.getValue(),
                'insertion': this.options.$assignmentInsertion.getValue(),
                'rackSlotSort': this.options.rackFront.options.slot_sort || this.options.rackRear.options.slot_sort
            },
            method:     "post",
            onSuccess:  function (xhr) {
                var i, fromto, json = xhr.responseJSON, offset = 0;
                
                this.options.$assignmentPosition.update();
    
                if (Object.keys(json).length) {
                    for (i in json) {
                        if (!json.hasOwnProperty(i)) {
                            continue;
                        }
            
                        fromto = i.split(';');
            
                        this.options.$assignmentPosition.insert(new Element('option', {
                            value:       fromto[0],
                            'data-from': fromto[1],
                            'data-to':   fromto[2]
                        }).update(json[i]));
                    }
        
                    if (this.options.lastSlotNumber > 0) {
                        this.options.lastSlotNumber = parseInt(this.options.lastSlotNumber);
                        this.options.$assignmentPosition.setValue(this.options.lastSlotNumber);
                        
                        // Check if the value has been set correctly (could be, that the value is not available because it's already taken).
                        while ((this.options.lastSlotNumber + offset) != this.options.$assignmentPosition.getValue()) {
                            // The logic here is: check if the next position is free (first -1, then +1, then -2, then +2, ...)
                            if (offset > 0) {
                                offset *= -1;
                            } else {
                                offset *= -1;
                                offset ++;
                            }
    
                            this.options.$assignmentPosition.setValue(this.options.lastSlotNumber + offset);
                            
                            if (offset < -50 || offset > 50) {
                                break;
                            }
                        }
                    }
                    
                    this.options.$assignmentPosition.removeClassName('hide').simulate('change');
                    this.options.$assignmentSubmitButton.removeClassName('hide');
                } else {
                    this.options.$assignmentPosition.addClassName('hide');
                    this.options.$assignmentSubmitButton.addClassName('hide');
                }
            }.bind(this)
        });
    },
    
    assignmentPositionChanged: function () {
        var $positionOption = this.options.$assignmentPosition.down(':selected'),
            positionFrom = parseInt($positionOption.readAttribute('data-from')),
            positionTo = parseInt($positionOption.readAttribute('data-to')),
            horizontal = (this.options.$assignmentOption.getValue() == this.options.OPTION_HORIZONTAL),
            chassisData,
            i;
        
        this.setLastSlotNumber($positionOption.readAttribute('value'));
        this.options.rackFront.unselect();
        this.options.rackRear.unselect();
        
        // The strings "(" and ")" will only appear if a chassis is selected.
        if ($positionOption.innerHTML.indexOf('(') > 0 && $positionOption.innerHTML.indexOf(')') > 0)
        {
            this.options.$assignmentSegmentSlot.update();
    
            if (this.options.$assignmentInsertion.getValue() == this.options.INSERTION_FRONT || this.options.$assignmentInsertion.getValue() == this.options.INSERTION_BOTH) {
                chassisData = this.options.rackFront.getChassisData(positionFrom);
            }
    
            if (this.options.$assignmentInsertion.getValue() == this.options.INSERTION_REAR) {
                chassisData = this.options.rackRear.getChassisData(positionFrom);
            }
            
            for (i in chassisData)
            {
                if (chassisData.hasOwnProperty(i))
                {
                    this.options.$assignmentSegmentSlot.insert(new Element('option', {value:chassisData[i].id}).update(chassisData[i].title));
                }
            }
            
            this.options.$assignmentSegmentSlot.removeClassName('hide').simulate('change');
        }
        else
        {
            this.options.$assignmentSegmentSlot.addClassName('hide');
            
            if (this.options.$assignmentInsertion.getValue() == this.options.INSERTION_FRONT || this.options.$assignmentInsertion.getValue() == this.options.INSERTION_BOTH) {
                this.options.rackFront.select(horizontal, positionFrom, positionTo);
            }
    
            if (this.options.$assignmentInsertion.getValue() == this.options.INSERTION_REAR || this.options.$assignmentInsertion.getValue() == this.options.INSERTION_BOTH) {
                this.options.rackRear.select(horizontal, positionFrom, positionTo);
            }
            
            this.options.$assignmentSubmitButton.removeClassName('hide');
        }
    },
    
    assignmentSegmentSlotChanged: function () {
        var chassisSlotId = this.options.$assignmentSegmentSlot.getValue();
    
        if (this.options.$assignmentInsertion.getValue() == this.options.INSERTION_FRONT || this.options.$assignmentInsertion.getValue() == this.options.INSERTION_BOTH) {
            this.options.rackFront.selectChassis(chassisSlotId);
        }
    
        if (this.options.$assignmentInsertion.getValue() == this.options.INSERTION_REAR || this.options.$assignmentInsertion.getValue() == this.options.INSERTION_BOTH) {
            this.options.rackRear.selectChassis(chassisSlotId);
        }
    },
    
    assignmentSubmit: function () {
        this.setLastSlotNumber(0);
        
        new Ajax.Request('?ajax=1&call=rack&func=assign_object_to_rack', {
            parameters: {
                'rack_obj_id': this.options.rackObjectId,
                'obj_id':      this.selectedObjectId,
                'option':      this.options.$assignmentOption.getValue(),
                'insertion':   this.options.$assignmentInsertion.getValue(),
                'pos':         this.options.$assignmentPosition.getValue(),
                'chassisSlot': (this.options.$assignmentSegmentSlot.hasClassName('hide') ? null : this.options.$assignmentSegmentSlot.getValue())
            },
            method:     "post",
            onSuccess:  function (xhr) {
                var json = xhr.responseJSON,
                    $listEntry = this.options.$unassignedObjects.down('[data-object-id="' + this.selectedObjectId + '"]');
                
                if ($listEntry)
                {
                    $listEntry.remove();
                }
                
                this.setObjects(json);
                this.options.rackFront.setObjects(json).create_rack();
                this.options.rackRear.setObjects(json).create_rack();
                this.updateUnassignedObjectsList();
                this.resetAssignment();
            }.bind(this)
        });
    },
    
    detachObjectFromRack: function (objectId, callback) {
        new Ajax.Request('?ajax=1&call=rack&func=remove_object_assignment',
            {
                parameters: {
                    'rack_obj_id': this.options.rackObjectId,
                    'obj_id':      objectId
                },
                method:     "post",
                onSuccess:  function (xhr) {
                    var json = xhr.responseJSON;
    
                    this.setObjects(json);
                    this.options.rackFront.setObjects(json).create_rack();
                    this.options.rackRear.setObjects(json).create_rack();
                    this.updateUnassignedObjectsList();
                    this.resetAssignment();
                    
                    if (Object.isFunction(callback))
                    {
                        callback();
                    }
                }.bind(this)
            });
    },
    
    updateUnassignedObjectsList: function () {
        var i, object, $list = this.options.$unassignedObjects.down('.list').update();
    
        for (i in this.objects) {
            if (! this.objects.hasOwnProperty(i) || this.objects[i].insertion || this.objects[i].pos) {
                continue;
            }
        
            object = this.objects[i];
            
            $list.insert(new Element('div')
                .setStyle({background: object.color})
                .writeAttribute({
                    'data-background': object.color,
                    'data-object-id': object.id,
                    'data-object-title': object.title,
                    'data-object-height': object.height
                })
                .update(new Element('div', {className: 'object-item fl'})
                    .update(new Element('strong').update(object.rawHeight + idoit.Translate.get('LC__CMDB__CATG__RACKUNITS_ABBR')))
                    .insert(new Element('p', {title: object.type + ' &raquo; ' + object.title}).update(object.type + ' &raquo; ' + object.title))
                    .insert(new Element('em').update(idoit.Translate.get('LC__CMDB__CATG__FORMFACTOR_TYPE') + ': ' + (object.formfactor || '-'))))
                .insert(new Element('span', {
                    className: 'mouse-pointer location-remove',
                    title: idoit.Translate.get('LC_UNIVERSAL__REMOVE_LOCATION')})
                    .update(new Element('img', {src: window.dir_images + 'icons/silk/cross.png'})))
                .insert(new Element('span', {
                    className: 'mouse-pointer he-edit',
                    title: idoit.Translate.get('LC__CMDB__CATS__RACK__CHANGE_HEIGHT_UNITS')})
                    .update(new Element('img', {src: window.dir_images + 'icons/silk/cog.png'})))
                .insert(new Element('a', {
                    className: 'mouse-pointer objectlink',
                    href:'?objID=' + object.id,
                    target:'_blank',
                    title: idoit.Translate.get('LC__UNIVERSAL__TITLE_LINK')})
                    .update(new Element('img', {src: window.dir_images + 'icons/silk/link.png'})))
                .insert(new Element('br', {className: 'cb'})));
        }
    
        $list.select('.object-item').each(function($item) {
            this.options.quickinfoCallback($item, $item.up('div').readAttribute('data-object-id'));
        }.bind(this));
        
        this.options.$unassignedObjectsCounter.fire('update:list');
    },
    
    updateUnassignedObjectsCounter: function () {
        this.options.$unassignedObjectsCounter.update('(' + this.options.$unassignedObjects.select('.object-item').length + ')');
    },
    
    editHeightUnits: function (ev) {
        var $objectItem = ev.findElement('[data-object-id]'),
            descriptionText = idoit.Translate.get('LC__CMDB__CATS__RACK__CHANGE_HEIGHT_UNITS__DESCRIPTION');
    
        this.resetAssignment();
        
        this.selectedObjectId = $objectItem.readAttribute('data-object-id');
        
        this.options.$heightChangeBox.removeClassName('hide');
        this.options.$heightChangeBox.down('p').update(descriptionText.replace('%s', new Element('strong').update($objectItem.readAttribute('data-object-title')).outerHTML));
        this.options.$heightChangeSelect.removeClassName('hide').setValue($objectItem.readAttribute('data-object-height'));
        this.options.$heightChangeButton.removeClassName('hide');
    },
    
    heightChangeSubmit: function () {
        new Ajax.Request('?ajax=1&call=rack&func=save_object_ru', {
            parameters: {
                'obj_id': this.selectedObjectId,
                'height': this.options.$heightChangeSelect.getValue()
            },
            method:     "post",
            onSuccess:  function (xhr) {
                var json    = xhr.responseJSON,
                    $object = this.options.$unassignedObjects.down('[data-object-id="' + this.selectedObjectId + '"]');
                
                if (json.success)
                {
                    $object.writeAttribute('data-object-height', json.data.height)
                       .down('strong')
                       .update(json.data.height + idoit.Translate.get('LC__CMDB__CATG__RACKUNITS_ABBR'));
                    
                    new Effect.Highlight($object, {
                        startcolor: '#88ff88',
                        endcolor:   $object.readAttribute('data-background')
                    });
                    
                    this.resetAssignment();
                }
                else
                {
                    idoit.Notify.error(json.message || 'An error occured!', {sticky:true});
                    
                    new Effect.Highlight($object, {
                        startcolor: '#ffB7B7',
                        endcolor:   $object.readAttribute('data-background')
                    });
                }
            }.bind(this)
        });
    },
    
    detachObjectLocation: function (ev) {
        var $objectItem = ev.findElement('[data-object-id]'),
            remove      = confirm(idoit.Translate.get('LC__CMDB__CATS__RACK__REMOVE_OBJECT_LOCATION'));
        
        if (remove)
        {
            new Ajax.Request('?ajax=1&call=rack&func=detach_object_from_rack', {
                parameters: {
                    'obj_id': $objectItem.readAttribute('data-object-id')
                },
                method:     'post',
                onSuccess:  function (xhr) {
                    var json = xhr.responseJSON;
                    
                    if (json.success)
                    {
                        $objectItem.remove();
                    }
                    else
                    {
                        new Effect.Highlight($objectItem, {
                            startcolor: '#ffB7B7',
                            endcolor:   $objectItem.readAttribute('data-background')
                        });
                    }
                }
            });
        }
    },
    
    prepareSlotSegmentation: function (position, insertion) {
        this.resetAssignment();
        
        this.options.$segmentObjectSelect.removeClassName('hide').setValue(null).simulate('change');
        this.options.$segmentBox.removeClassName('hide').writeAttribute({
            'data-slot-position':  position,
            'data-slot-insertion': insertion
        });
    },
    
    detachSlotSegmentation: function (chassisObjectId) {
        new Ajax.Request('?ajax=1&call=rack&func=detach_slot_segmentation', {
                parameters: {
                    rackObjectId: this.options.rackObjectId,
                    segmentationObjectId: chassisObjectId
                },
                method:     'post',
                onSuccess:  function (xhr) {
                    var json = xhr.responseJSON;
                    
                    if (json.success)
                    {
                        this.setObjects(json.data);
                        this.options.rackFront.setObjects(json.data).create_rack();
                        this.options.rackRear.setObjects(json.data).create_rack();
                        this.updateUnassignedObjectsList();
                        this.resetAssignment();
                    }
                    else
                    {
                        idoit.Notify.error(json.message, {sticky: true});
                    }
                }.bind(this)
        });
    },
    
    segmentLoadPreview: function () {
        var segmentTemplateValue = this.options.$segmentObjectSelect.getValue(),
            callback = function () {
                new RackChassis(this.options.$segmentPreview, this.options.segmentTemplates[segmentTemplateValue]);
    
                this.options.$segmentButton.removeClassName('hide');
            }.bind(this);

        if (segmentTemplateValue > 0) {
            if (! this.options.segmentTemplates.hasOwnProperty(segmentTemplateValue)) {
                new Ajax.Request('?ajax=1&call=rack&func=get_chassis_layout', {
                    parameters: {
                        'templateObjectId': segmentTemplateValue
                    },
                    method:     'post',
                    onSuccess:  function (xhr) {
                        var json = xhr.responseJSON;
                    
                        if (json.success)
                        {
                            this.options.segmentTemplates[segmentTemplateValue] = json.data;
    
                            callback();
                        }
                        else
                        {
                            idoit.Notify.error(json.message, {sticky: true});
                            
                            new Effect.Highlight(this.options.$segmentBox, {
                                startcolor: '#ffB7B7',
                                endcolor:   '#ffffff'
                            });
                        }
                    }.bind(this)
                });
            } else {
                callback();
            }
        } else {
            this.options.$segmentPreview.update();
            this.options.$segmentButton.addClassName('hide');
        }
    },
    
    segmentCreate: function () {
        var segmentTemplateValue = this.options.$segmentObjectSelect.getValue();
        
        if (segmentTemplateValue > 0) {
            this.options.$segmentButton.addClassName('hide').down('img').writeAttribute('src', window.dir_images + 'ajax-loading.gif');
            
            new Ajax.Request('?ajax=1&call=rack&func=create_segment_from_template', {
                parameters: {
                    rackObjectId: this.options.rackObjectId,
                    templateObjectId: segmentTemplateValue,
                    option: this.options.OPTION_HORIZONTAL,
                    insertion: this.options.$segmentBox.readAttribute('data-slot-insertion'),
                    position: this.options.$segmentBox.readAttribute('data-slot-position')
                },
                method:     'post',
                onSuccess:  function (xhr) {
                    var json = xhr.responseJSON;
    
                    this.options.$segmentButton.down('img').writeAttribute('src', window.dir_images + 'icons/silk/application_split.png');
                    
                    if (json.success)
                    {
                        this.setObjects(json.data);
                        this.options.rackFront.setObjects(json.data).create_rack();
                        this.options.rackRear.setObjects(json.data).create_rack();
                        this.updateUnassignedObjectsList();
                        this.resetAssignment();
                    }
                    else
                    {
                        idoit.Notify.error(json.message, {sticky: true});
                    
                        new Effect.Highlight(this.options.$segmentBox, {
                            startcolor: '#ffB7B7',
                            endcolor:   '#ffffff'
                        });
                    }
                }.bind(this)
            });
        } else {
            this.options.$segmentPreview.update();
            this.options.$segmentButton.addClassName('hide');
        }
    }
});
