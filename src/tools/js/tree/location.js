/**
 * i-doit Location tree class.
 * This will display the location tree by a given root ID and provide checkboxes or radio-buttons, according to the "multiselect" setting.
 *
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
window.LocationTree = Class.create(window.BaseTree, {
    selectedNodes: [],
    
    /**
     * Constructor method.
     *
     * @param   $super
     * @param   $container
     * @param   options
     * @returns {Window.LocationTree}
     */
    initialize: function ($super, $container, options) {
        this.options = {
            mode:           'combined',
            onlyContainer:  false,
            considerRights: false,
            onSelect:       Prototype.emptyFunction,
            onUnselect:     Prototype.emptyFunction,
            multiselect:    false
        };
    
        // Empty the cache for each instantiation.
        this.selectedNodes = [];
        
        options = Object.extend(this.options, options || {});
        
        $super($container, options);
        
        return this;
    },
    
    /**
     * Method for setting currently selected nodes.
     *
     * @param   nodes
     * @returns {Window.LocationTree}
     */
    setSelectedNodes: function (nodes) {
        this.selectedNodes = nodes.map(function (nodeId) {
            return 'n' + nodeId;
        });
    
        return this;
    },
    
    /**
     * Method adding a node as "selected".
     *
     * @param   nodeId
     * @returns {Window.LocationTree}
     */
    selectNode:   function (nodeId) {
        if (this.options.multiselect) {
            this.selectedNodes.push('n' + nodeId);
        } else {
            this.selectedNodes = ['n' + nodeId];
        }
        
        this.options.onSelect(nodeId, this.cache['n' + nodeId]);
        
        return this;
    },
    
    /**
     * Method removing a node as "selected".
     *
     * @param   nodeId
     * @returns {Window.LocationTree}
     */
    unselectNode: function (nodeId) {
        this.selectedNodes = this.selectedNodes.filter(function(id) {
            return nodeId !== id;
        });
        
        this.options.onUnselect(nodeId, this.cache['n' + nodeId]);
        
        return this;
    },
    
    /**
     *
     * @param   nodeId
     * @returns {boolean}
     */
    isSelected: function (nodeId) {
        return this.selectedNodes.indexOf('n' + nodeId) !== -1;
    },
    
    /**
     * Method for adding all necessary observers.
     */
    addObserver: function ($super) {
        $super();
    
        this.$container.on('change', 'input', function (ev) {
            var $input = ev.findElement('input'),
                $li = $input.up('li'),
                nodeId = $li.readAttribute('data-id');
            
            if ($input.getValue()) {
                this.selectNode(nodeId);
            } else {
                this.unselectNode(nodeId);
            }
            
            this.process();
        }.bind(this));
    },
    
    /**
     * Method for setting the tree "mode" (will only affect the data-loading).
     *
     * @param   mode
     * @returns {Window.LocationTree}
     */
    setMode: function(mode) {
        this.options.mode = mode;
        
        return this;
    },
    
    /**
     * Method for rendering a node.
     *
     * @param   data
     * @returns {*}
     */
    renderNode: function(data) {
        var open = this.isOpenNode(data.nodeId),
            selected = this.isSelected(data.nodeId);
        
        return new Element('li')
            .writeAttribute('data-id', data.nodeId)
            .update(new Element('img')
                .writeAttribute('src', window.dir_images + 'icons/silk/bullet_toggle_' + (open ? 'minus' : 'plus') + '.png')
                .writeAttribute('class', 'child-toggle ' + (data.hasChildren ? '' : 'hide')))
            .insert(new Element('label')
                .writeAttribute('class', 'tree-inner mouse-pointer ' + (selected ? 'text-bold' : ''))
                .update(new Element('input')
                    .writeAttribute('type', this.options.multiselect ? 'checkbox' : 'radio')
                    .writeAttribute('value', data.nodeId)
                    .writeAttribute('name', 'location-tree-selection' + (this.options.multiselect ? '[]' : ''))
                    .writeAttribute('disabled', data.nodeId === 0 ? 'disabled' : null)
                    .setValue(selected ? 1 : null))
                .insert(new Element('img')
                    .writeAttribute('src', data.nodeTypeIcon)
                    .writeAttribute('class', 'ml5 mr5')
                    .writeAttribute('title', data.nodeTypeTitle))
                .insert(new Element('span').update(data.nodeTitle)))
            .insert(new Element('ul')
                .writeAttribute('class', 'css-tree ' + (open ? '' : 'hide')));
    },
    
    /**
     * Method for loading children nodes via ajax.
     *
     * @param nodeId
     * @param callback
     */
    loadChildrenNodes: function (nodeId, callback) {
        new Ajax.Request(window.www_dir + 'cmdb/browse-location/' + nodeId, {
            parameters: {
                mode: this.options.mode,
                onlyContainer: this.options.onlyContainer ? 1 : 0,
                considerRights: this.options.considerRights ? 1 : 0
            },
            onComplete: function (xhr) {
                var json = xhr.responseJSON, i;
                
                if (!json.success) {
                    idoit.Notify.error(json.message || xhr.responseText, {sticky: true});
                }
                
                this.cache['n' + nodeId] = json.data;
                
                for (i in json.data.children) {
                    if (!json.data.children.hasOwnProperty(i)) {
                        continue;
                    }
                    
                    this.cache['n' + json.data.children[i].nodeId] = json.data.children[i];
                }
                
                if (Object.isFunction(callback)) {
                    callback(nodeId, this.cache['n' + nodeId]);
                }
            }.bind(this)
        });
    }
});
