var Multiedit = Class.create({
    initialize: function (options) {
        this.options = Object.extend({
            changes: 0,
            disabledRows: [],
            changedFields: [],
            changedEntry: [],
            changedObject: $H(),
            tableSort: null,
            url: '',
            templateCounter: 0,
            objectsElement: null,
            categoriesElement: null,
            objectInfoElement: null,
            filterElement: null,
            filterValue: null,
            btnLoadList: null,
            btnAddValue: null,
            loaderElement: null,
            editBtnsContainer: null,
            selectedCategory: null,
            multiEditConfig: null,
            multivalueCategories: [],
            multiEditHeader: null,
            multiEditList: null,
            multiEditContainer: null,
            multiEditFooter: null,
            callbacks: {},
            translation: {},
            sortByProperty: '',
            sortDirection: '',
            selectedIds: [],
            context: 'module',
            overlay: null,
            disabledKeys: [],
            validationErrors: []
        }, options || {});
        
        this.addObservers();
        
        // @todo CHECK IF THERE ARE NO SELECTED OBJECTS AND CATEGORY IS
        this.deactivateNavbar();
    },
    // updates all dialog plus fields which have the same class
    dialogPlusObserver: function (ev) {
        if (ev.memo.classIterator && ev.memo.selectBox)
        {
            var elements = this.options.multiEditList.select('.' + ev.memo.classIterator),
                current_id;
        
            if (elements.length > 0)
            {
                elements.each(function (ele) {
                    current_id = ele.getValue();
                
                    // ID-2049: Fixed for adding a new entry
                    if (ev.memo.selectBox.name.indexOf('skip') > -1)
                    {
                        current_id = elements[0].getValue();
                    }

                    if (ele.hasAttribute('data-secidentifier') && ev.memo.selectBox.hasAttribute('data-secidentifier'))
                    {
                        if (ele.getAttribute('data-secidentifier') == ev.memo.selectBox.getAttribute('data-secidentifier'))
                        {
                            ele.update(ev.memo.selectBox.innerHTML);
                            ele.setValue(current_id);
                        }
                    }
                    else if (ev.memo.parent == 0)
                    {
                        ele.update(ev.memo.selectBox.innerHTML);
                        ele.setValue(current_id);
                    }
                });
                
                if (ev.findElement().id.indexOf('[-]') > 0) {
                    this.overwriteDialogPlus(this.options.multiEditList.select('.' + ev.memo.classIterator), ev.memo.classIterator, ev.memo.selectBox.value);
                }
            }
        }
    },
    
    addObservers: function () {
    
        // Scroll header
        var scrollOffsetLeft = null;
        this.options.multiEditContainer.on('scroll', function(ele){
            scrollOffsetLeft = ele.findElement().scrollLeft;
            this.options.multiEditHeader.setAttribute('style', 'left: ' + (-1 * scrollOffsetLeft) + "px");
        }.bind(this));
        
        this.options.multiEditConfig.on('objects:updated', this.loadCategories.bindAsEventListener(this));
        this.options.categoriesElement.on('change', this.loadList.bindAsEventListener(this));
        this.options.filterElement.down('img.execute-filter').on('click', this.filterList.bindAsEventListener(this));
        this.options.filterElement.down('img.reset-filter').on('click', this.enableRows.bindAsEventListener(this));
        this.options.objectInfoElement.on('change', this.showObjectInfo.bindAsEventListener(this));
        this.options.multiEditList.on('show:validationErrors', this.showValidationErrors.bindAsEventListener(this));
        
        this.options.filterElement.down('#C__MULTIEDIT__FILTER_VALUE').on('keydown', function (event){
            if (event.keyCode === 13) {
                event.preventDefault();
                
                this.filterList();
            }
        }.bind(this));
        
        // Hook on custom Dialog Plus event to fill all dialog+ DropDown Boxes after adding a new entry
        document.observe('dialog-plus:afterSave', this.dialogPlusObserver.bindAsEventListener(this));
    },
    
    showObjectInfo: function () {
        switch(parseInt(this.options.objectInfoElement.value)) {
            case 1:
                this.options.multiEditList.select('.multiedit-td-object-title-info-sysid').invoke('removeClassName', 'hide');
                this.options.multiEditList.select('.multiedit-td-object-title-info-id').invoke('addClassName', 'hide');
                break;
            case 2:
                this.options.multiEditList.select('.multiedit-td-object-title-info-sysid').invoke('addClassName', 'hide');
                this.options.multiEditList.select('.multiedit-td-object-title-info-id').invoke('removeClassName', 'hide');
                break;
            case 3:
                this.options.multiEditList.select('.multiedit-td-object-title-info-sysid').invoke('removeClassName', 'hide');
                this.options.multiEditList.select('.multiedit-td-object-title-info-id').invoke('removeClassName', 'hide');
                break;
            default:
                this.options.multiEditList.select('.multiedit-td-object-title-info-sysid').invoke('addClassName', 'hide');
                this.options.multiEditList.select('.multiedit-td-object-title-info-id').invoke('addClassName', 'hide');
                break;
                
        }
    },
    
    activateLoader: function () {
        show_overlay();
        if (this.options.loaderElement.hasClassName('inactive'))
        {
            this.options.loaderElement.addClassName('active');
            this.options.loaderElement.removeClassName('inactive');
        }
    },
    
    deactivateLoader: function () {
        hide_overlay();
        if (this.options.loaderElement.hasClassName('active')) {
            this.options.loaderElement.removeClassName('active');
            this.options.loaderElement.addClassName('inactive');
        }
    },
    
    handleLoader: function () {
        if (this.options.loaderElement) {
            if (this.options.loaderElement.hasClassName('active')) {
                this.options.loaderElement.removeClassName('active');
                this.options.loaderElement.addClassName('inactive');
            } else {
                this.options.loaderElement.addClassName('active');
                this.options.loaderElement.removeClassName('inactive');
            }
        }
    },
    
    reloadField: function (url, target, plugin, params, classIdentifier){
        new Ajax.Request(url, {
            parameters: {
                'plugin_name': plugin,
                'parameters':  Object.toJSON(params)
            },
            method:     "post",
            onComplete: function (response) {
                var json = response.responseJSON;
    
                if (Object.isUndefined(json))
                {
                    idoit.Notify.error(response.responseText);
                    return;
                }
                
                if (json.success)
                {
                    var $content = document.createRange().createContextualFragment(json.data).firstChild;
                    
                    if (classIdentifier && target.id.indexOf('[-]') > 0)
                    {
                        // Overwrite all values of this column
                        Array.from(this.options.multiEditList.getElementsByClassName(classIdentifier)).forEach(function (ele) {
    
                            $content.down('.' + classIdentifier).setAttribute('onchange', ele.getAttribute('onchange'));
                            $content.down('.' + classIdentifier).setAttribute('data-secidentifier', ele.getAttribute('data-secidentifier'));
                            
                            ele.innerHTML = $content.innerHTML;
                        }.bind($content));
                    }
                    
                    $content.down('.' + classIdentifier).setAttribute('onchange', target.getAttribute('onchange'));
                    $content.down('.' + classIdentifier).setAttribute('data-secidentifier', params['secTableID']);
                    
                    target.up('td').update($content);
                }
                else
                {
                    idoit.Notify.error(json.message);
                }
            }.bind(this)
        });
    },
    
    loadCategories: function () {
        if (this.options.objectsElement.getValue()) {
            this.activateLoader();
            
            new Ajax.Request(this.options.url, {
                parameters: {
                    request: 'loadCategories',
                    objectIds: this.options.objectsElement.getValue()
                },
                method: "post",
                onSuccess: function(response){
                    var json = response.responseJSON;
                    
                    if (Object.isUndefined(json))
                    {
                        idoit.Notify.error(response.responseText);
                        return;
                    }
                    
                    if (json.success)
                    {
                        var keys = Object.keys(json.data), categories = null, categoryKeys, categoryTitle = null, $option = null, $optGroup;
                        
                        this.options.categoriesElement.update('');
                        this.options.categoriesElement.insert(document.createElement('option').insert('-'));
                        
                        for (var a = 0; a < keys.length; a++) {
                            categories = json.data[keys[a]];
                            categoryKeys = Object.keys(categories);
                            
                            if (categoryKeys.length > 0)
                            {
                                $optGroup = document.createElement('optgroup');
                                $optGroup.setAttribute('label', keys[a]);
                                
                                for (var b = 0; b < categoryKeys.length; b++) {
                                    $option = document.createElement('option');
                                    $option.setAttribute('value', categoryKeys[b]);
                                    $option.insert(categories[categoryKeys[b]]);
    
                                    if (categoryKeys[b] == this.options.selectedCategory) {
                                        $option.selected = true;
                                    }

                                    $optGroup.insert($option);
                                }
                                this.options.categoriesElement.insert($optGroup);
                            }
                        }
                        this.options.categoriesElement.fire('chosen:updated');
                        
                        if (this.options.categoriesElement.getValue() != '-') {
                            this.loadFilter();
                            this.loadList();
                        }
                        
                        this.deactivateLoader();
                    }
                    else
                    {
                        
                        idoit.Notify.error(json.message);
                    }
                }.bind(this)
            });
        } else {
            this.options.multiEditHeader.update('');
            this.options.multiEditList.update('');
        }
    },
    filterList: function () {
        var property = this.options.filterElement.down('#C__MULTIEDIT__FILTER_PROPERTY').getValue(),
            value = this.options.filterElement.down('#C__MULTIEDIT__FILTER_VALUE').getValue();
        
        this.options.disabledRows = [];
        
        if (value !== '' && property !== '') {
            if (property === 'all') {
                var elementList = this.options.multiEditList.select('tr'),
                    elementListLength = elementList.length,
                    tdList = null, tdListLength, disableRow = false,
                    pList = null, pListLength;
                    
    
                for (var i = 0; i < elementListLength; i++)
                {
                    if (elementList[i].id === 'changeAllRow' || !elementList[i].id)
                    {
                        continue;
                    }
        
                    // Iterate through td list
                    tdList = elementList[i].select('td');
                    tdListLength = tdList.length;
                    pList = null;
                    
                    elementLoop:
                    for (var j = 0; j < tdListLength; j++) {
                        
                        if (!tdList[j].hasAttribute('data-sort')) {
                            continue;
                        }
                        
                        if (tdList[j].select('p')) {
                            pList = tdList[j].select('p');
                            pListLength = pList.length;
                            
                            for (var k = 0; k < pListLength; k++) {
                                if (!pList[k].hasAttribute('data-sort')) {
                                    continue;
                                }
                                
                                if (pList[k].readAttribute('data-sort').toLowerCase().indexOf(value.toLowerCase()) >= 0)
                                {
                                    disableRow = false;
                                    break elementLoop;
                                } else {
                                    disableRow = true;
                                }
                            }
                        }
                        
                        if (tdList[j].readAttribute('data-sort').toLowerCase().indexOf(value.toLowerCase()) >= 0) {
                            disableRow = false;
                            break;
                        } else {
                            disableRow = true;
                        }
                    }
                    
                    if (disableRow) {
                        elementList[i].addClassName('hide');
                        elementList[i].select('input,select').each(function (ele){
                            ele.setAttribute('disabled', 'disabled');
                        });
    
                        if (!this.options.disabledRows.includes(elementList[i].id)) {
                            this.options.disabledRows.push(elementList[i].id);
                        }
                    } else{
                        elementList[i].removeClassName('hide');
                        elementList[i].select('input,select').each(function (ele){
                            ele.removeAttribute('disabled');
                        });
                    }
                }
            } else {
                var elementList = this.options.multiEditList.select('.' + property),
                    elementListLength = elementList.length,
                    filterElement;
    
                for (var i = 0; i < elementListLength; i++)
                {
                    filterElement = (elementList[i].tagName.toLowerCase() == 'td' || elementList[i].tagName.toLowerCase() == 'p') ? elementList[i]: elementList[i].up('td');
                    
                    if (!filterElement || filterElement.up('tr').id === 'changeAllRow' || !filterElement.up('tr').id || !filterElement.hasAttribute('data-sort'))
                    {
                        continue;
                    }
                    
                    var elementTr = elementList[i].up('tr');
        
                    if (filterElement.readAttribute('data-sort').toLowerCase().indexOf(value.toLowerCase()) >= 0)
                    {
                        elementTr.removeClassName('hide');
                    }
                    else
                    {
                        elementTr.addClassName('hide');
                        if (!this.options.disabledRows.includes(elementTr.id)) {
                            this.options.disabledRows.push(elementTr.id);
                        }
                    }
                }
            }
        } else {
            this.enableRows();
        }
        
        this.updateChanges();
    },
    
    loadFilter: function () {
        new Ajax.Request(this.options.url, {
            parameters: {
                request: 'loadFilter',
                category: this.options.categoriesElement.getValue()
            },
            method: 'post',
            onSuccess: function (transport){
                var json = transport.responseJSON,
                    $firstOption = document.createElement('option'),
                    $objectTitleOption = document.createElement('option'),
                    $sysIdOption = document.createElement('option'),
                    $idOption = document.createElement('option');
                
                $firstOption.innerHTML = this.options.translation.get('LC__UNIVERSAL__ALL');
                $firstOption.value = 'all';
                $objectTitleOption.innerHTML = this.options.translation.get('LC__UNIVERSAL__OBJECT_TITLE');
                $objectTitleOption.value = 'multiedit-td-object-title';
                $sysIdOption.innerHTML = 'SYSID';
                $sysIdOption.value = 'multiedit-td-object-title-info-sysid';
                $idOption.innerHTML = this.options.translation.get('LC__UNIVERSAL__ID');
                $idOption.value = 'multiedit-td-object-title-info-id';
                
                this.options.filterElement.down('select').update($firstOption).insert($objectTitleOption).insert($sysIdOption).insert($idOption);
                this.options.filterValue = null;
                this.options.filterElement.down('select').disabled = false;
                
                if (json.success) {
                    var categories = json.data,
                        categoryKeys = Object.keys(json.data), properties, propertyKeys, $optGroup, $option;
                    
                    for (var i = 0; i < categoryKeys.length; i++) {
                        $optGroup = document.createElement('optgroup');
                        $optGroup.setAttribute('label', categoryKeys[i]);
                    
                        properties = categories[categoryKeys[i]];
                        propertyKeys = Object.keys(properties);
                        
                        for (var j = 0; j < propertyKeys.length; j++) {
                            $option = document.createElement('option');
                            $option.setAttribute('value', propertyKeys[j]);
                            $option.innerHTML = properties[propertyKeys[j]];
                            $optGroup.insert($option);
                        }
                        this.options.filterElement.down('select').insert($optGroup);
                    }
                } else {
                    this.options.filterElement.down('select').disabled = true;
                    idoit.Notify.error(json.message);
                }
                
            }.bind(this)
        });
    },
    
    loadContent: function (){

        this.activateLoader();
        this.deactivateNavbar();
        this.options.changes = 0;
        this.options.changedEntry = [];
        this.updateChanges();
        
        new Ajax.Request(this.options.url, {
            parameters: {
                'request': 'loadContent',
                'category': this.options.categoriesElement.getValue(),
                'objects': this.options.objectsElement.getValue(),
                'filter': Object.toJSON(this.options.filterValue),
                'editMode': 1
            },
            method: 'post',
            onComplete: function (transport) {
                var json = transport.responseJSON,
                    deactivateEdit = false, showMultivalueInfo = true, i, identifier;
                
                if (json.success == false) {
                    this.deactivateLoader();
                    idoit.Notify.error(json.message);
                }
                
                if (json.success == true)
                {
                    this.options.changes = 0;
                    
                    this.removeContext(this.options.multiEditHeader);
                    this.removeContext(this.options.multiEditList);
    
                    this.options.multiEditHeader.update(json.data.header);
                    this.options.multiEditList.update(json.data.content);
    
                    this.postprocessChosenFields(this.options.multiEditContainer);
                    
                    // Sorting
                    new window.Tablesort(this.options.multiEditList.down('table'), this.options.multiEditHeader.down('table'), {
                        descending: true
                    });

                    if (json.data.type == 'Assignment') {
                        deactivateEdit = true;
                        showMultivalueInfo = false;
                    }
                    
                    if (json.data.multivalued == false) {
                        deactivateEdit = true;
                        showMultivalueInfo = false;
                    }
                    
                    if (this.options.selectedIds.length > 0) {
                        this.disableAllRows();
                        for (i = 0; i < this.options.selectedIds.length; i++) {
                            this.enableRow(this.options.selectedIds[i]);
                        }
                    }
                    
                    if (showMultivalueInfo && this.options.context === 'module') {
                        idoit.Notify.info(this.options.translation.get('LC__MODULE__MULTIEDIT__MULTIVALUE_INFO_TEXT'), {sticky:true});
                    }
    
                    var disabledKeysLength = this.options.disabledKeys.length;
    
                    if (disabledKeysLength > 0) {
                        for (i = 0; i < disabledKeysLength; i++) {
                            this.disableColumn(this.options.disabledKeys[i]);
                        }
                    }
    
                    var disabledRowsLength = this.options.disabledRows.length;
    
                    if (disabledRowsLength > 0) {
                        for (i = 0; i < disabledRowsLength; i++) {
                            this.disableRow(this.options.disabledRows[i]);
                        }
                    }
                    
                    this.deactivateLoader();
                    this.activateNavbar(deactivateEdit);
                    this.showObjectInfo();
    
                    this.options.multiEditList.fire('show:validationErrors');
                }
                
            }.bind(this)
        });
    },
    
    removeContext: function (context){
        while(context.lastChild) {
            context.removeChild(context.lastChild);
        }
    },
    
    loadList: function() {
        var that = this;
        
        if (this.options.categoriesElement.getValue() != "-" && this.options.objectsElement.getValue() != "") {
            this.options.selectedCategory = this.options.categoriesElement.getValue();
            this.loadFilter();
            this.loadContent();
        }
    },
    
    showValidationErrors: function () {
        if (this.options.validationErrors.length > 0) {
            var validationErrors = this.options.validationErrors;
            var validationLength = this.options.validationErrors.length;
            for (var i = 0; i < validationLength; i++) {
                $(validationErrors[i].propertyUiId).addClassName('input-error');
                $(validationErrors[i].propertyUiId).setAttribute(
                    'onmouseover',
                    "if(this.value === '" + validationErrors[i].value + "') { window.multiEdit.showValidationMessage(" + i + "); }"
                );
            }
        }
    },
    
    showValidationMessage: function (id) {
        idoit.Notify.error(this.options.validationErrors[id].message, {duration: 0.5});
    },
    
    save: function() {
        
        if (this.options.changes == 0){
            return;
        }
        
        var i,
            j,
            formDataChanges = {},
            formData = {},
            rows = this.options.multiEditList.select('tr.multiedit-tr-data'),
            rowLength = rows.length,
            cacheValue,
            trData,
            oldValue,
            newValue,
            dataValue,
            dataKey,
            trDataLength,
            entryData,
            chosenSelect,
            cellType,
            skipRegex = new RegExp('_CONFIG|__VIEW|__CABLE_NAME|__action');
        
        for (i = 0; i < rowLength; i++) {
    
            entryData = rows[i].readAttribute('data-entry');
            
            if (rows[i].hasClassName('hide') || this.options.changedEntry.indexOf(entryData) < 0) {
                // Row is disabled so it will not be updated
                rows[i].addClassName('hide');
                continue;
            }
            
            // iterate through each row
            trData = rows[i].select('select,input,textarea');
            
            if (!trDataLength) {
                trDataLength = trData.length;
            }
    
            if (!formData[entryData]) {
                formData[entryData] = {};
                formDataChanges[entryData] = {};
            }
            
            for (j = 0; j < trDataLength; j++) {
                if (!trData[j] || skipRegex.test(trData[j].id) || trData[j].hasAttribute('name') === false)
                {
                    // Skip it we only want the value not the view value
                    continue;
                }
    
                dataValue = trData[j].value;
                oldValue = trData[j].up('td').readAttribute('data-old-value');
                
                if (trData[j].previous() && trData[j].previous().id.indexOf('__VIEW') >= 0) {
                    oldValue = trData[j].previous().hasAttribute('data-last-value') ? trData[j].previous().readAttribute('data-last-value') : null;
                }
                
                if (trData[j].disabled) {
                    continue;
                }
                
                newValue = trData[j].up('td').readAttribute('data-sort');
                
                if (trData[j].hasClassName('chosen-select') && trData[j].next('div')) {
                    chosenSelect = trData[j].next('div').down('ul.chosen-choices').select('li.search-choice');
                    if (chosenSelect.length > 0)
                    {
                        dataValue = '';
    
                        chosenSelect.forEach(function (ele) {
                            dataValue += trData[j].options[ele.down('a').readAttribute('rel')].value + ',';
                        });
                        dataValue = dataValue.substr(0, dataValue.length - 1);
                    }
                }
                
                dataKey = trData[j].up('td').readAttribute('data-key');
                cellType = trData[j].up('td').readAttribute('data-cell-type');

                switch (cellType) {
                    case 'datetime':
                        formData[entryData][dataKey] = dataValue;
                        break;
                    default:
                        if (formData[entryData][dataKey]) {
                            if (Array.isArray(formData[entryData][dataKey])) {
                                formData[entryData][dataKey].push(dataValue);
                            } else {
                                cacheValue = formData[entryData][dataKey];
                                formData[entryData][dataKey] = [
                                    cacheValue,
                                    dataValue
                                ];
                            }
                        } else {
                            formData[entryData][dataKey] = dataValue;
                        }
                        break;
                }
                
                if (newValue != oldValue)
                {
                    formDataChanges[entryData][dataKey.replace('__', '::')] = {
                        'from': oldValue,
                        'to':   newValue
                    };
                }
            }
        }

        if (Object.keys(formData).length > 0) {
            this.options.multiEditList.select('tr.multiedit-tr-data').invoke('highlight');
            
            new Ajax.Request(this.options.url, {
                parameters: {
                    'request': 'saveList',
                    'data': Object.toJSON(formData),
                    'dataChanges': Object.toJSON(formDataChanges),
                    'categoryInfo': $('C__MULTIEDIT__CATEGORY').value
                },
                method: 'post',
                onSuccess: function(transport) {
                    var json = transport.responseJSON;
                    
                    if (json.success) {
                        idoit.Notify.success(this.options.translation.get('LC__MULTIEDIT__SUCCESSFUL'));
                    } else {
                        idoit.Notify[json.messageType](json.message, {sticky:true});
                    }
    
                    this.options.validationErrors = [];
                    if (json.validation) {
                        this.options.validationErrors = json.validation;
                    }
                    
                    this.loadContent();
                }.bind(this)
            });
        }
        
        return;
    },
    
    overwriteText: function (elementList, classNameValue, overwriteValue) {
        var counter = 0;
        
        for (var i = 0; i < elementList.length; i++) {
            if (!elementList[i].id || elementList[i].id == classNameValue + '[-]' || elementList[i].up('tr').hasClassName('hide')) {
                continue;
            }
            elementList[i].setValue(Placeholder.process_counter_string(overwriteValue, counter));
            elementList[i].simulate('change');
            counter++;
        }
    },
    
    overwriteDate: function (elementList, classNameValue, overwriteValue) {
        var counter = 0;
        
        for (var i = 0; i < elementList.length; i++) {
            if (!elementList[i].id || elementList[i].id == classNameValue + '__VIEW[-]' || elementList[i].up('tr').hasClassName('hide')) {
                continue;
            }
            elementList[i].setValue(Placeholder.process_counter_string(overwriteValue, counter));
            elementList[i].simulate('change');
            counter++;
        }
    },
    
    overwriteDateTime: function (elementList, classNameValue, overwriteValue) {
        var counter = 0;
        
        for (var i = 0; i < elementList.length; i++) {
            if (!elementList[i].id || elementList[i].id == classNameValue + '__VIEW[-]' || elementList[i].up('tr').hasClassName('hide')) {
                continue;
            }
            elementList[i].setValue(Placeholder.process_counter_string(overwriteValue.dateValue, counter));
            elementList[i].next().setValue(Placeholder.process_counter_string(overwriteValue.timeValue, counter));
            elementList[i].simulate('change');
            counter++;
        }
    },
    
    overwriteDialogPlus: function (elementList, classNameValue, overwriteValue) {
        var counter = 0;
        
        for (var i = 0; i < elementList.length; i++) {
            if (!elementList[i].id || elementList[i].id == classNameValue + '[-]' || elementList[i].up('tr').hasClassName('hide')) {
                if (!overwriteValue)
                {
                    overwriteValue = elementList[i].getValue();
                }
                continue;
            }
            elementList[i].setValue(Placeholder.process_counter_string(overwriteValue, counter));
            elementList[i].simulate('change');
            counter++;
        }
    },
    
    overwriteMultiselect: function (elementList, classNameValue, overwriteValue) {
        var dataEntry,
            counter = 0;
        
        for (var i = 0; i < elementList.length; i++) {
            if (!elementList[i].id || elementList[i].id == classNameValue + '[-]' ||
                !elementList[i].up('tr').hasAttribute('data-entry') ||
                elementList[i].up('tr').hasClassName('hide'))
            {
                if (!overwriteValue) {
                    overwriteValue = elementList[i].getValue();
                }
                continue;
            }
            elementList[i].setValue(Placeholder.process_counter_string(overwriteValue, counter));
            elementList[i].fire('chosen:updated');
            dataEntry = elementList[i].up('tr').readAttribute('data-entry');
    
            if (this.options.changedEntry.indexOf(dataEntry) < 0)
            {
                this.options.changedEntry.push(dataEntry);
            }
            
            this.options.changes++;
            counter++;
        }
    },
    
    overwriteObjectBrowser: function (elementList, classNameValue, overwriteValue) {
        var hiddenFieldId = null, dataEntry, identifier = '-';
        
        if (!$('C__MULTIEDIT__OBJECTS__HIDDEN') && $('C__MULTIEDIT__OBJECTS')){
            identifier = $('C__MULTIEDIT__OBJECTS').getValue().replace(/\D/g, '') + '-';
        }
        
        var overwriteValueView = this.options.multiEditHeader.select('[id="' + classNameValue + '__VIEW[' + identifier + ']"]')[0].getValue(),
            overwriteValue = this.options.multiEditHeader.select('[id="' + classNameValue + '__HIDDEN[' + identifier + ']"]')[0].getValue();
        
        for (var i = 0; i < elementList.length; i++) {
            if (elementList[i].up('tr').hasClassName('hide')) {
                continue;
            }
            hiddenFieldId = elementList[i].readAttribute('data-hidden-field');
            if ($(hiddenFieldId) && $(hiddenFieldId).up('tr').hasAttribute('data-entry')) {
                $(hiddenFieldId).setValue(overwriteValue);
                dataEntry = $(hiddenFieldId).up('tr').readAttribute('data-entry');
                elementList[i].setValue(overwriteValueView);
                elementList[i].up('td').setAttribute('data-sort', overwriteValueView);
    
                if (this.options.changedEntry.indexOf(dataEntry) < 0)
                {
                    this.options.changedEntry.push(dataEntry);
                }
                
                this.options.changes++;
            }
        }
    },
    overwriteValue: function (currentElement, elementClassName, overwriteAllElement, type) {
        var value = overwriteAllElement ? overwriteAllElement.getValue() : null;
        switch(type) {
            case 'dialog':
            case 'text':
                this.overwriteText(currentElement, elementClassName, value);
                break;
            case 'dialogPlus':
                this.overwriteDialogPlus(currentElement, elementClassName, value);
                break;
            case 'popupDate':
                this.overwriteDate(currentElement, elementClassName, value);
                break;
            case 'popupDateTime':
                    var valueSec = ele.next().getValue();
                    this.overwriteDateTime(currentElement, elementClassName, {'dateValue': value, 'timeValue': valueSec});
                break;
            case 'multiselect':
                this.overwriteMultiselect(currentElement, elementClassName, value);
                break;
            case 'objectBrowser':
                this.overwriteObjectBrowser(currentElement, elementClassName, value);
                break;
        }
    
        this.updateChanges();
    },
    overwriteAll: function(overwriteAllElement, elementClassName, type) {
        
        var hiddenField = null,
            $fieldList = null,
            value = null,
            valueSec = null;
        
        value = overwriteAllElement ? overwriteAllElement.getValue() : null;
        $fieldList = this.options.multiEditList.select('.' + elementClassName);
     
        Placeholder.iteration = 0;
        
        var handler = function (self, itemsCount, ignore, elementClassName, value, type) {
            
            this.self = self;
            this.counter = 0;
            this.ignore = ignore;
            this.itemsCount = itemsCount;
            this.value = value;
            this.elementClassName = elementClassName;
            this.type = type;
            
            this.getLength = function(){
                return this.itemsCount;
            }
            this.getCount = function(){
                return this.counter;
            }
            this.addItem = function(){
                this.counter++;
            }
            this.getIgnore = function(){
                return this.ignore;
            }
            this.execute = function() {
                var self = this.self;
                var value = this.value;
                var elementClassName = this.elementClassName;
                var $fieldList = self.options.multiEditList.select('.' + elementClassName);
                
                switch (this.type) {
                    case 'dialog':
                    case 'text':
                        if ($fieldList.length > 1) {
                            self.overwriteText($fieldList, elementClassName, value);
                        }
                        break;
                    case 'dialogPlus':
                        if ($fieldList.length > 1) {
                            self.overwriteDialogPlus($fieldList, elementClassName, value);
                        }
                        break;
                    case 'popupDate':
                        if ($fieldList.length > 1) {
                            self.overwriteDate($fieldList, elementClassName, value);
                        }
                        break;
                    case 'popupDateTime':
                        if ($fieldList.length > 1) {
                            valueSec = overwriteAllElement.next().getValue();
                            self.overwriteDateTime($fieldList, elementClassName, {
                                'dateValue': value,
                                'timeValue': valueSec
                            });
                        }
                        break;
                    case 'multiselect':
                        if ($fieldList.length > 1) {
                            self.overwriteMultiselect($fieldList, elementClassName, value);
                        }
                        break;
                    case 'objectBrowser':
                        if ($fieldList.length > 0) {
                            self.overwriteObjectBrowser($fieldList, elementClassName, value);
                        }
                        break;
                }
    
                self.updateChanges();
            }
        }
    
        var newEntries = $$('.new-entry');
    
        if (newEntries.length > 0) {
            var entryKeysLength = Object.keys(newEntries).length, i, currentTr, entryId, objectId, objectData;
            var categoryData = this.options.categoriesElement.value.split(':');
            var callbackObject = new handler(this, newEntries.length, false, elementClassName, value, type);
            for (i = 0; i < entryKeysLength; i++) {
                currentTr = newEntries[i].up('tr');
                entryId = currentTr.readAttribute('data-entry');
                objectData = currentTr.id.split('_');
                objectData = objectData[1].split('-');
                objectId = objectData[0];
                this.addNewEntrySynchronous(categoryData[1], objectId, entryId, categoryData[0], overwriteAllElement, elementClassName, type, callbackObject);
            }
            return;
        }
        
        (new handler(this, 0, true, elementClassName, value, type)).execute();
        
        return;
    },
    updateChanges: function() {
        this.options.multiEditFooter.down('.multiedit-footer-changes-counter').innerHTML = this.options.changes;
        this.options.multiEditFooter.down('.multiedit-footer-changes-disablerows').innerHTML = this.options.disabledRows.length;
        
        if (this.options.changes == 1 || this.options.disabledRows.length == 1) {
            this.options.multiEditFooter.down('.multiedit-footer-changes').highlight();
        }
    },
    changed: function(field, elementIdentifier) {
        
        if (!field && !elementIdentifier) {
            return;
        }
        
        var elementObject = ($(elementIdentifier.split('[').join('__HIDDEN[')) ? $(elementIdentifier.split('[').join('__HIDDEN[')) : $(elementIdentifier)),
            viewValue = '', viewElement = null, selection = null, selectionLength = 0;
        
        // Register Callback
        if (elementObject) {
            idoit.callbackManager.triggerCallback(elementIdentifier + '.changed', elementObject.getValue());
        }
        
        // @see  ID-4865  Do not count changes, made in "skip" fields.
        if (Object.isString(elementIdentifier) && elementIdentifier.indexOf('[-]') > -1) {
            return;
        }
        
        switch (elementObject.nodeName.toLowerCase()) {
            case 'select':
                selection = elementObject.select('option:selected');
                selectionLength = selection.length;
                var selectValues = '';
                
                if (selectionLength > 1) {
                    for (var i = 0; i < selectionLength; i++) {
                        viewValue += selection[i].innerHTML + ' ';
                        selectValues += selection[i].value + ',';
                    }
                    // @todo chosen selection
                    
                } else {
                    viewValue = selection[0].innerHTML;
                }
                elementObject.fire('chosen:updated');
                
                break;
            case 'textarea':
            case 'input':
                viewValue = elementObject.value;
                viewElement = $(elementObject.id.replace('HIDDEN', 'VIEW'));
                
                if (viewElement) {
                    viewValue = viewElement.value;
                    
                    if (viewElement.hasAttribute('readonly')) {
                        viewValue = null;
                    }
                }
                
                break;
            default:
                viewValue = elementObject.innerHTML;
                break;
        }
        
        if (viewValue !== null)
        {
            elementObject.up('td').setAttribute('data-sort', viewValue);
        }
        
        var changedEntry = elementObject.up('tr').readAttribute('data-entry');
        
        this.options.changes++;
        
        if (this.options.changedEntry.indexOf(changedEntry) < 0)
        {
            this.options.changedEntry.push(changedEntry);
        }
        
        this.updateChanges();
    },
    changesInEntry: function(p_entry_id) {
        var arr_entries = [];
        
        if(p_entry_id != null) {
            this.options.changedEntry.set(p_entry_id, true);
            this.options.changedEntry.each(function(ele){
                if(!ele[0].match('new') && !ele[0].match('skip')){
                    arr_entries.push(parseInt(ele[0]));
                }
            });
            if(arr_entries.length > 0){
                $('changes_in_entry').setValue(Object.toJSON(arr_entries));
            }
        }
    },
    changesInObject: function(p_object_id) {
        var arr_entries = [];
        
        if(p_object_id != null) {
            this.options.changedObject.set(p_object_id, true);
            this.options.changedObject.each(function(ele){
                if(!ele[0].match('new')){
                    arr_entries.push(parseInt(ele[0]));
                }
            });
            if(arr_entries.length > 0){
                $('changes_in_object').setValue(Object.toJSON(arr_entries));
            }
        }
    },
    addNewEntrySynchronous: function (daoClass, objectId, entryId, categoryIdentifier, overwriteAllElement, elementClassName, type, callback = null){
        this.activateLoader();
    
        new Ajax.Request(this.options.url, {
            parameters: {
                request: 'addNewEntry',
                objectId: objectId,
                entryId: entryId,
                categoryClass: daoClass,
                categoryInfo: categoryIdentifier
            },
            method: 'post',
            onComplete: function (transport) {
                var json = transport.responseJSON;
            
                if (json.success) {
                    var identifier = objectId + '-' + entryId;
                    var $tr = $('object-row_' + identifier);
                    var disabledKeysLength = this.options.disabledKeys.length;
                    var disabledKey;
                
                    if (!$tr) {
                        $tr = document.createElement('tr');
                        $tr.setAttribute('id', 'object-row_' + identifier);
                        this.options.multiEditList.down('tr#changeAllRow')
                            .insert({after: $tr});
                    }
                    $tr.setAttribute('data-entry', identifier)
                    $tr.addClassName('multiedit-tr-data');
                    $tr.removeClassName('emptyValue');
                    $tr.update(json.data);
                
                    if (disabledKeysLength > 0) {
                        for (i = 0; i < disabledKeysLength; i++) {
                            disabledKey = this.options.disabledKeys[i];
                            this.disableColumn(disabledKey);
                        }
                    }
                
                    this.postprocessChosenFields($tr);
                
                    $tr.highlight();
                
                } else {
                    idoit.Notify.error(json.message);
                }
            
                this.deactivateLoader();
            
                if (callback) {
                    if (callback.getIgnore() == false) {
                        callback.addItem();
                        if (callback.getLength() > callback.getCount()) {
                            return;
                        } else {
                            callback.execute();
                        }
                    }
                }
            }.bind(this)
        });
    },
    addNewEntry: function (daoClass, objectId, entryId, categoryIdentifier, overwriteAllElement, elementClassName, type) {
        this.activateLoader();
        
        new Ajax.Request(this.options.url, {
            parameters: {
                request: 'addNewEntry',
                objectId: objectId,
                entryId: entryId,
                categoryClass: daoClass,
                categoryInfo: categoryIdentifier
            },
            method: 'post',
            onComplete: function (transport) {
                var json = transport.responseJSON;
        
                if (json.success) {
                    var identifier = objectId + '-' + entryId;
                    var $tr = $('object-row_' + identifier);
                    var disabledKeysLength = this.options.disabledKeys.length;
                    var disabledKey;
                    
                    if (!$tr) {
                        $tr = document.createElement('tr');
                        $tr.setAttribute('id', 'object-row_' + identifier);
                        this.options.multiEditList.down('tr#changeAllRow')
                            .insert({after: $tr});
                    }
                    $tr.setAttribute('data-entry', identifier)
                    $tr.addClassName('multiedit-tr-data');
                    $tr.removeClassName('emptyValue');
                    $tr.update(json.data);
                    
                    if (disabledKeysLength > 0) {
                        for (i = 0; i < disabledKeysLength; i++) {
                            disabledKey = this.options.disabledKeys[i];
                            this.disableColumn(disabledKey);
                        }
                    }
    
                    if (type !== undefined) {
                        this.overwriteValue($tr.select('input.' + elementClassName + ',select.' + elementClassName), elementClassName, overwriteAllElement, type);
                    }
                    
                    this.postprocessChosenFields($tr);
    
                    $tr.highlight();
                } else {
                    idoit.Notify.error(json.message);
                }
                
                this.deactivateLoader();
        
            }.bind(this)
        });
    },
    
    postprocessChosenFields: function ($context) {
        $context.select('select.re-chosen-select').each(function ($element) {
            new Chosen($element, {
                default_multiple_text:     idoit.Translate.get('LC__UNIVERSAL__CHOOSEN_PLACEHOLDER'),
                placeholder_text_multiple: idoit.Translate.get('LC__UNIVERSAL__CHOOSEN_PLACEHOLDER'),
                no_results_text:           idoit.Translate.get('LC__UNIVERSAL__CHOOSEN_EMPTY'),
                disable_search_threshold:  10,
                search_contains:           true
            });
        });
    },
    
    addNewValuesPopup: function (ele){
        
        if (ele.hasClassName('navbar_item_inactive')) {
            return;
        }
        
        var params = 'ids=' + this.options.objectsElement.value + '&category=' + this.options.categoriesElement.value;
        
        get_popup('multiedit_add_values', params, 500, 150);
    },
    
    disableRow: function (identifier) {
        
        if (!$(identifier)) {
            return;
        }
        
        $(identifier).addClassName('hide');
        $(identifier).select('input,select').each(function (ele){
            ele.setAttribute('disabled', 'disabled');
        });

        if (!this.options.disabledRows.includes(identifier)) {
            this.options.disabledRows.push(identifier);
        }
        this.updateChanges();
    },
    
    disableAllRows: function () {
        if (this.options.multiEditList.down('table'))
        {
            this.options.multiEditList.select('tr.multiedit-tr-data').each(function(row){
                row.addClassName('hide');
                row.select('input[class^="multiedit-disabled"],select[class^="multiedit-disabled"]').each(function (ele){
                    ele.setAttribute('disabled', 'disabled');
                });
                
                var identifier = row.id;
                if (!this.options.disabledRows.includes(identifier)) {
                    this.options.disabledRows.push(identifier);
                }
            }.bind(this));
            this.updateChanges();
        }
    },
    
    enableRow: function (identifier) {
        if (!$(identifier)) {
            return;
        }
        
        $(identifier).removeClassName('hide');
        $(identifier).select('input[class^="multiedit-disabled"],select[class^="multiedit-disabled"]').each(function (ele){
            ele.removeAttribute('disabled');
        });
    
        if (this.options.disabledRows.includes(identifier)) {
            this.options.disabledRows.splice(this.options.disabledRows.indexOf(identifier), 1);
        }
        this.updateChanges();
    },
    
    enableRows: function () {
        if (this.options.multiEditList.down('table'))
        {
            this.options.filterElement.down('#C__MULTIEDIT__FILTER_VALUE').value = '';
            this.options.multiEditList.select('tr.hide').each(function(row){
                row.removeClassName('hide');
                row.select('input,select').each(function (ele){
                    ele.removeAttribute('disabled');
                });
            });
            this.options.disabledRows = [];
            this.options.multiEditFooter.down('.multiedit-footer-changes-disablerows').innerHTML = 0;
            this.enableColumns();
        }
    },
    
    deactivateNavbar: function () {
        $('navBar').select('div').invoke('addClassName', 'navbar_item_inactive');
    },
    
    activateNavbar: function (deactivateEdit) {
        $('navBar').select('div').invoke('removeClassName', 'navbar_item_inactive');
    
        // Deactivate addNewEntry button for assignment categories
        if (deactivateEdit) {
            $('navbar_item_C__NAVMODE__EDIT').addClassName('navbar_item_inactive');
        }
    },
    
    enableColumns: function () {
        var pattern = 'th[data-key],td[data-key]';
        this.options.multiEditHeader.select(pattern).invoke('removeClassName', 'hide');
        this.options.multiEditList.select(pattern).invoke('removeClassName', 'hide');
        this.options.disabledKeys = [];
    },
    
    disableColumn: function (key) {
        var pattern = 'th[data-key="' + key + '"],td[data-key="' + key + '"]';
        if (!this.options.disabledKeys.includes(key)) {
            this.options.disabledKeys.push(key);
        }
        this.options.multiEditHeader.select(pattern).invoke('addClassName', 'hide');
        this.options.multiEditList.select(pattern).invoke('addClassName', 'hide');
    },
    
    disableSort: function (ele) {
        ele.up('th').setAttribute('enable-sort', 0);
    },
    
    enableSort: function (ele) {
        ele.up('th').setAttribute('enable-sort', 1);
    },
    
    sortContent: function (ele, propertyKey) {
        this.options.multiEditHeader.select('img.multiedit-header-sort-img').invoke('addClassName', 'opacity-30');
        var $imageElement = ele.down('img').removeClassName('opacity-30');
        
        if (ele.hasClassName('multiedit-header-sort-asc')) {
            ele.removeClassName('multiedit-header-sort-asc').addClassName('multiedit-header-sort-desc');
            $imageElement.setAttribute('src', window.www_dir + "images/icons/silk/bullet_arrow_down.png");
        } else {
            ele.removeClassName('multiedit-header-sort-desc').addClassName('multiedit-header-sort-asc');
            $imageElement.setAttribute('src', window.www_dir + "images/icons/silk/bullet_arrow_up.png");
        }
    }
});
