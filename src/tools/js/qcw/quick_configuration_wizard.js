var QuickConfWizard = Class.create({
    options:                {},
    currentObjectTypeGroup: null,
    currentObjectType:      null,

    // Constructor method, sets all initial observer.
    initialize: function (options) {
        this.options = Object.extend({
            ajax_url:                        '',
            message_obj_type_sorting_notice: '',
            popupButtonLabel:                'Adjust',
            object_type_sort:                'manual',
            $objTypeHider:                   false,
            $categoryHider:                  false
        }, options || {});

        this.$objTypeGroupSearch = $('obj_type_group_search');
        this.$objTypeGroupList = $('obj_type_group_list');
        this.$objTypeGroupPopupButton = $('obj_type_group_popup_button');
        this.$objTypeSearch = $('obj_type_search');
        this.$objTypeList = $('obj_type_list');
        this.$objTypePopupButton = $('obj_type_popup_button');
        this.$categorySearch = $('category_search');
        this.$categoryList = $('category_list');
        this.$categoryPopupButton = $('category_popup_button');

        this.$loadingObjectTypeGroup = $('loading-object-type-group');
        this.$loadingObjectType = $('loading-object-type');
        this.$loadingCategory = $('loading-category');

        // Set some observer.
        this.$objTypeGroupSearch.on('keyup', this.filterList.bind(this, this.$objTypeGroupSearch, this.$objTypeGroupList));
        this.$objTypeGroupList.on('click', 'button', this.hideObjectTypeGroup.bind(this));
        this.$objTypeGroupList.on('click', 'li', this.load_obj_type_by_group.bind(this));
        this.$objTypeGroupList.on('list:updated', this.loadObjectTypeGroups.bind(this));
        this.$objTypeGroupList.on('list:updated', this.loadObjectTypes.bind(this));
        this.$objTypeSearch.on('keyup', this.filterList.bind(this, this.$objTypeSearch, this.$objTypeList));
        this.$objTypeList.on('click', 'button', this.detachObjectTypeFromGroup.bind(this));
        this.$objTypeList.on('click', 'li', this.load_categories_by_obj_type.bind(this));
        this.$objTypeList.on('list:updated', this.loadObjectTypes.bind(this));
        this.$categorySearch.on('keyup', this.filterList.bind(this, this.$categorySearch, this.$categoryList));
        this.$categoryList.on('click', 'button', this.detachCategoryFromObjectType.bind(this));
        this.$categoryList.on('list:updated', this.loadCategories.bind(this));

        // Set the focus to the object type group search.
        this.$objTypeGroupSearch.focus();

        this.loadObjectTypeGroups()
            .loadObjectTypes()
            .loadCategories()
            .buildPopupButtons();

        Position.includeScrollOffsets = true;
    },

    loadObjectTypeGroups: function (ev) {
        this.$loadingObjectTypeGroup.show();

        new Ajax.Request(this.options.ajax_url + '&func=loadObjectTypeGroups', {
            method:     'post',
            onComplete: function (xhr) {
                var json = xhr.responseJSON, i;

                // Hide the "loading" icon.
                this.$loadingObjectTypeGroup.hide();

                if (json.success && json.data)
                {
                    this.$objTypeGroupList.update();

                    for (i in json.data)
                    {
                        if (!json.data.hasOwnProperty(i))
                        {
                            continue;
                        }

                        this.$objTypeGroupList.insert(this.renderListItem(json.data[i], true));
                    }

                    Sortable.create(this.$objTypeGroupList, {
                        tag:      'li',
                        handle:   'handle',
                        onUpdate: this.sortObjectTypeGroup.bind(this)
                    });

                    if (Object.isFunction(ev.memo.callback))
                    {
                        ev.memo.callback.call(this);
                    }
                }
                else
                {
                    idoit.Notify.error(json.message || xhr.responseText, {sticky:true});
                }
            }.bind(this)
        });

        return this;
    },

    loadObjectTypes: function (ev) {
        this.$loadingObjectType.show();

        new Ajax.Request(this.options.ajax_url + '&func=loadObjectTypes', {
            method:     'post',
            onComplete: function (xhr) {
                var json = xhr.responseJSON, i;

                // Hide the "loading" icon.
                this.$loadingObjectType.hide();

                if (json.success && json.data)
                {
                    this.$objTypeList.update();

                    for (i in json.data)
                    {
                        if (!json.data.hasOwnProperty(i))
                        {
                            continue;
                        }

                        this.$objTypeList.insert(this.renderListItem(json.data[i], true, true));
                    }

                    Sortable.create(this.$objTypeList, {
                        tag:      'li',
                        handle:   'handle',
                        onUpdate: this.sortObjectType.bind(this)
                    });

                    if (Object.isFunction(ev.memo.callback))
                    {
                        ev.memo.callback.call(this);
                    }
                }
                else
                {
                    idoit.Notify.error(json.message || xhr.responseText, {sticky:true});
                }
            }.bind(this)
        });

        return this;
    },

    loadCategories: function (ev) {
        this.$loadingCategory.show();

        new Ajax.Request(this.options.ajax_url + '&func=loadCategories', {
            method:     'post',
            onComplete: function (xhr) {
                var json = xhr.responseJSON, i;

                // Hide the "loading" icon.
                this.$loadingCategory.hide();

                if (json.success && json.data)
                {
                    this.$categoryList.update();

                    for (i in json.data)
                    {
                        if (!json.data.hasOwnProperty(i))
                        {
                            continue;
                        }

                        this.$categoryList.insert(this.renderListItem(json.data[i]));
                    }

                    if (Object.isFunction(ev.memo.callback))
                    {
                        ev.memo.callback.call(this);
                    }
                }
                else
                {
                    idoit.Notify.error(json.message || xhr.responseText, {sticky:true});
                }
            }.bind(this)
        });

        return this;
    },

    renderListItem: function (data, sortable, color) {
        var $li = new Element('li', {className:(data.active ? '' : 'hide'), 'data-const':data.constant});

        if (data.selfdefined) {
            $li.addClassName('selfdefined');
        }

        if (sortable) {
            $li.writeAttribute('id', 'sortable-id-' + data.constant)
               .insert(new Element('span', {className:'handle'}));
        }

        if (color) {
            $li.insert(new Element('div', {className:'cmdb-marker', style:'background:' + data.color}));
        }

        $li
            .insert(new Element('span', {className:'title', title:data.title}).update(data.title))
            .insert((data.hasOwnProperty('count') ? new Element('span', {className:'counter text-grey'}).update('(' + data.count + ')') : ''));

        if (!data.orphaned) {
            $li.insert(new Element('button', {className:'fr btn btn-small', title:idoit.Translate.get('LC__UNIVERSAL__HIDE')})
                .update(new Element('img', {src:window.dir_images + 'icons/silk/cross.png', alt:'x'})));
        }

        return $li;
    },

    buildPopupButtons: function () {
        var that    = this,
            $button = new Element('button', {type: 'button', className: 'btn btn-block'})
            .update(new Element('img', {src: window.dir_images + 'icons/silk/pencil.png', className: 'mr5'}))
            .insert(new Element('span').update(that.options.popupButtonLabel));

        // Append the object type group button.
        this.$objTypeGroupPopupButton.update($button.outerHTML).down('button').on('click', function () {
            get_popup('qcw_adjust_object_type_group', '', '800', '600', {params: ''}, '');
        });

        // Append the object type button.
        this.$objTypePopupButton.update($button.outerHTML).down('button').on('click', function () {
            get_popup('qcw_adjust_object_type', '', '800', '600', {
                params:                 '',
                currentObjectTypeGroup: that.currentObjectTypeGroup
            }, '');
        });

        // Append the category button.
        this.$categoryPopupButton.update($button.outerHTML).down('button').on('click', function () {
            get_popup('qcw_adjust_category', '', '800', '600', {
                params:            '',
                currentObjectType: that.currentObjectType
            }, '');
        });
    },

    sortObjectTypeGroup: function () {
        var sorting = this.$objTypeGroupList.select('li').invoke('readAttribute', 'data-const');

        // Display the "loading" icon.
        this.$loadingObjectTypeGroup.show();

        new Ajax.Request(this.options.ajax_url + '&func=sortObjectTypeGroups', {
            parameters: {sorting: sorting.join(',')},
            method:     'post',
            onComplete: function (r) {
                var json = r.responseJSON;

                // Hide the "loading" icon.
                this.$loadingObjectTypeGroup.hide();

                if (json.success && json.data)
                {
                    // @todo Do something.
                }
                else
                {
                    // @todo Do something.
                }
            }.bind(this)
        });
    },

    sortObjectType: function () {
        var sorting = this.$objTypeList.select('li').invoke('readAttribute', 'data-const');

        if (this.options.object_type_sort != 'manual')
        {
            idoit.Notify.info(this.options.message_obj_type_sorting_notice, {sticky: true});
            return;
        }

        // Display the loading icon.
        this.$loadingObjectType.show();

        new Ajax.Request(this.options.ajax_url + '&func=sortObjectTypes', {
            parameters: {sorting: sorting.join(',')},
            method:     'post',
            onComplete: function () {
                this.$loadingObjectType.hide();

                if (json.success && json.data)
                {
                    // @todo Do something.
                }
                else
                {
                    // @todo Do something.
                }
            }.bind(this)
        });
    },

    filterList: function ($searchField, $filterList) {
        delay(function () {
            var search = $searchField.getValue().toLowerCase();

            $filterList.select('li').invoke('removeClassName', 'filtered');

            if (!search.blank())
            {
                $filterList.select('li').filter(function ($li) {
                    return $li.down('.title').innerHTML.toLowerCase().indexOf(search) == -1;
                }).invoke('addClassName', 'filtered');
            }
        }, 250, 'qcw.filter');
    },

    load_obj_type_by_group: function (ev) {
        var $li = ev.findElement('li'),
            title,
            constant;

        if (this.options.$categoryHider)
        {
            this.options.$categoryHider.removeClassName('hide');
        }

        this.$categoryList.select('li').invoke('addClassName', 'hide');
        title = $li.down('span.title').innerHTML;
        constant = $li.readAttribute('data-const');

        if (this.currentObjectTypeGroup != constant && !ev.findElement().match('.handle'))
        {
            this.currentObjectTypeGroup = constant;

            this.$loadingObjectType.show();
            this.$objTypeSearch.setValue('');

            new Ajax.Request(this.options.ajax_url + '&func=loadAssignedObjectTypesByGroup', {
                parameters: {objectTypeGroupConst: constant},
                method:     'post',
                onSuccess:  function (transport) {
                    var json = transport.responseJSON;

                    if (this.options.$objTypeHider)
                    {
                        this.options.$objTypeHider.addClassName('hide');
                    }

                    if (json.success && Object.isArray(json.data))
                    {
                        this.$objTypeList.select('li')
                            .invoke('addClassName', 'hide')
                            .filter(function ($li_object_type) {
                                return json.data.in_array($li_object_type.readAttribute('data-const'));
                            })
                            .invoke('removeClassName', 'hide');
                    }

                    this.$loadingObjectType.hide();
                    this.$objTypeSearch.focus();
                    this.buildPopupButtons();
                }.bind(this)
            });

            // Update the headline and highlight the next container.
            new Effect.Highlight($('objtypegroup_name').update(title).setStyle({color: '#589C8D'}).morph('color:#000;').up('.container'), {
                startcolor:   '#d4ffde',
                restorecolor: '#fff'
            });

            // Remove all previous "active" states.
            this.$objTypeGroupList.select('.active').invoke('removeClassName', 'active');

            // Add the "active" state to the current LI.
            $li.addClassName('active');
        }
    },

    load_categories_by_obj_type: function (ev) {
        var $li = ev.findElement('li'),
            title,
            constant;

        title = $li.down('span.title').innerHTML;
        constant = $li.readAttribute('data-const');

        // Do not load the object types, because of dragging.
        if (this.currentObjectType != constant && !ev.findElement().match('.handle'))
        {
            this.currentObjectType = constant;

            this.$loadingCategory.show();
            this.$categorySearch.setValue('');

            new Ajax.Request(this.options.ajax_url + '&func=loadAssignedCategoriesByObjectTypes', {
                parameters: {objectTypeConst: constant},
                method:     'post',
                onSuccess:  function (transport) {
                    var json = transport.responseJSON;

                    if (this.options.$categoryHider)
                    {
                        this.options.$categoryHider.addClassName('hide');
                    }

                    if (json.success && Object.isArray(json.data))
                    {
                        this.$categoryList.select('li')
                            .invoke('addClassName', 'hide')
                            .filter(function ($li_category) {
                                return json.data.in_array($li_category.readAttribute('data-const'))
                            })
                            .invoke('removeClassName', 'hide');
                    }

                    this.$loadingCategory.hide();
                    this.$categorySearch.focus();
                    this.buildPopupButtons();
                }.bind(this)
            });

            new Effect.Highlight($('objtype_name').update(title).setStyle({color: '#589C8D'}).morph('color:#000;').up('.container'), {
                startcolor:   '#d4ffde',
                restorecolor: '#fff'
            });

            this.$objTypeList.select('li.active').invoke('removeClassName', 'active');

            $li.addClassName('active');
        }
    },

    hideObjectTypeGroup: function (ev) {
        var $li      = ev.findElement('li'),
            constant = $li.readAttribute('data-const');

        // Stop the propagation, so no further event gets triggered.
        ev.stopImmediatePropagation();

        this.$loadingObjectTypeGroup.show();
        $li.down('button').disable()
           .down('img').writeAttribute('src', window.dir_images + 'ajax-loading.gif');

        new Ajax.Request(this.options.ajax_url + '&func=hideObjectTypeGroup', {
            parameters: {objectTypeGroupConst: constant},
            method:     'post',
            onComplete: function (r) {
                var json = r.responseJSON;

                this.$loadingObjectTypeGroup.hide();

                if (json.success && json.data)
                {
                    this.$objTypeGroupList.down('[data-const="' + r.request.parameters.objectTypeGroupConst + '"]').remove();
                }
                else
                {
                    idoit.Notify.warning(idoit.Translate.get('LC__MODULE__QCW__RESET_ERROR__OBJ_TYPE_GROUP_RESET'), {life: 10});

                    this.$objTypeGroupList
                        .down('[data-const="' + r.request.parameters.objectTypeConst + '"] button').enable()
                        .down('img').writeAttribute('src', window.dir_images + 'icons/silk/delete.png');
                }
            }.bind(this)
        });
    },

    detachObjectTypeFromGroup: function (ev) {
        var $li      = ev.findElement('li'),
            constant = $li.readAttribute('data-const');

        // Stop the propagation, so no further event gets triggered.
        ev.stopImmediatePropagation();

        this.$loadingObjectType.show();
        $li.down('button').disable()
           .down('img').writeAttribute('src', window.dir_images + 'ajax-loading.gif');

        new Ajax.Request(this.options.ajax_url + '&func=detachObjectTypeFromGroup', {
            parameters: {objectTypeConst: constant},
            method:     'post',
            onComplete: function (r) {
                var json = r.responseJSON;

                this.$loadingObjectType.hide();

                if (json.success && json.data)
                {
                    this.$objTypeList.down('[data-const="' + r.request.parameters.objectTypeConst + '"] button').remove();
                    $li.writeAttribute('class', 'hide');
                }
                else
                {
                    idoit.Notify.warning(idoit.Translate.get('LC__MODULE__QCW__RESET_ERROR__OBJ_TYPE_ASSIGNMENT'), {life: 10});

                    this.$objTypeList
                        .down('[data-const="' + r.request.parameters.objectTypeConst + '"] button').enable()
                        .down('img').writeAttribute('src', window.dir_images + 'icons/silk/delete.png');
                }
            }.bind(this)
        });
    },

    detachCategoryFromObjectType: function (ev) {
        var $li              = ev.findElement('li'),
            categoryConstant = $li.readAttribute('data-const'),
            customCategory   = $li.hasClassName('selfdefined') ? 1: 0;

        // Stop the propagation, so no further event gets triggered.
        ev.stopImmediatePropagation();

        this.$loadingCategory.show();
        $li.down('button').disable()
           .down('img').writeAttribute('src', window.dir_images + 'ajax-loading.gif');

        new Ajax.Request(this.options.ajax_url + '&func=detachCategoryFromObjectType', {
            parameters: {
                objectTypeConst: this.currentObjectType,
                categoryConst:   categoryConstant,
                customCategory:  customCategory
            },
            method:     'post',
            onComplete: function (r) {
                var json = r.responseJSON;

                this.$loadingCategory.hide();

                if (json.success && json.data)
                {
                    this.$categoryList.down('[data-const="' + r.request.parameters.categoryConst + '"]').addClassName('hide');
                }
                else
                {
                    idoit.Notify.warning(idoit.Translate.get('LC__MODULE__QCW__RESET_ERROR__GLOBAL_CAT_ASSIGNMENT'), {life: 10});

                    this.$categoryList
                        .down('[data-const="' + r.request.parameters.categoryConst + '"] button').enable()
                        .down('img').writeAttribute('src', window.dir_images + 'icons/silk/delete.png');
                }
            }.bind(this)
        });
    }
});