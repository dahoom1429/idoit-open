/**
 * i-doit Base tree class.
 * This class should be used as an "abstract" parent. Extending classes need to provide an own data handler ("loadChildrenNodes").
 *
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
window.BaseTree = Class.create({
    options:   {},
    openNodes: [],
    cache:     {},
    
    /**
     * Constructor method.
     *
     * @param $container
     * @param options
     * @returns {Window.BaseTree}
     */
    initialize: function ($container, options) {
        this.$container = $container;
        this.options = {
            rootNodeId:  1,
            nodeSorting: Prototype.emptyFunction
        };
        
        // Empty the cache for each instantiation.
        this.cache = {};
        this.openNodes = [];
        
        Object.extend(this.options, options || {});
        
        this.addObserver();
        
        return this;
    },
    
    /**
     * Method for adding all necessary observers.
     */
    addObserver: function () {
        this.$container.on('click', '.child-toggle', function (ev) {
            var nodeId = ev.findElement('li').readAttribute('data-id');
            
            this.toggleOpenNode(nodeId);
        }.bind(this));
    },
    
    /**
     * Method for setting the currently opened nodes.
     *
     * @param nodes
     * @returns {Window.BaseTree}
     */
    setOpenNodes: function (nodes) {
        // We prefix the keys so we ALWAYS work with strings.
        this.openNodes = nodes.map(function (d) {
            return 'n' + d;
        });
        
        return this;
    },
    
    /**
     * Method for toggling the node state (open/closed).
     *
     * @param nodeId
     * @returns {Window.BaseTree}
     */
    toggleOpenNode: function(nodeId) {
        if (this.isOpenNode(nodeId)) {
            this.openNodes = this.openNodes.filter(function(id) {
                return 'n' + nodeId !== id;
            });
        } else {
            this.openNodes.push('n' + nodeId);
        }
        
        this.process();
        
        return this;
    },
    
    /**
     * Method for finding out if a node is open.
     *
     * @param nodeId
     * @returns {boolean}
     */
    isOpenNode: function (nodeId) {
        return this.openNodes.indexOf('n' + nodeId) !== -1;
    },
    
    /**
     * Process method for rendering the complete tree.
     */
    process: function () {
        this.openNodes.push('n' + this.options.rootNodeId);
        
        this.openNodes = this.openNodes.uniq();
        
        this.displayRoot();
    },
    
    /**
     * Method for rendering a node.
     *
     * @param data
     * @returns {*}
     */
    renderNode: function (data) {
        var open  = this.isOpenNode(data.nodeId);
        
        return new Element('li')
            .writeAttribute('data-id', data.nodeId)
            .update(new Element('img')
                .writeAttribute('src', window.dir_images + 'icons/silk/bullet_toggle_' + (open ? 'minus' : 'plus') + '.png')
                .writeAttribute('class', 'child-toggle ' + (data.hasChildren ? '' : 'hide')))
            .insert(new Element('div')
                .writeAttribute('class', 'tree-inner')
                .insert(new Element('img')
                    .writeAttribute('src', data.nodeTypeIcon)
                    .writeAttribute('class', 'mr5')
                    .writeAttribute('title', data.nodeTypeTitle))
                .insert(new Element('span').update(data.nodeTitle)))
            .insert(new Element('ul')
                .writeAttribute('class', 'css-tree ' + (open ? '' : 'hide')));
    },
    
    /**
     * Method for displaying the tree root. Will re-load data if necessary.
     */
    displayRoot: function () {
        var $root, data, i;
        
        if (this.cache.hasOwnProperty('n' + this.options.rootNodeId)) {
            data = this.cache['n' + this.options.rootNodeId];
        
            $root = new Element('ul')
                .writeAttribute('class', 'css-tree');
            
            $root.update(this.renderNode(data).addClassName('root'));
            
            this.$container.update($root);
            
            data.children.sort(this.options.nodeSorting);
            
            for (i in data.children) {
                if (!data.children.hasOwnProperty(i)) {
                    continue;
                }
                
                $root.insert(this.renderNode(data.children[i]));
                
                if (this.isOpenNode(data.children[i].nodeId)) {
                    this.displayChildrenNodes(data.children[i].nodeId);
                }
            }
            
            return;
        }
        
        this.loadChildrenNodes(this.options.rootNodeId, this.displayRoot.bind(this));
    },
    
    /**
     * Method for displaying children nodes. Will re-load data if necessary.
     *
     * @param nodeId
     */
    displayChildrenNodes: function (nodeId) {
        var $childrenList = this.$container.down('[data-id="' + nodeId + '"] ul'),
            data, i;
        
        if (this.cache.hasOwnProperty('n' + nodeId) && (!this.cache['n' + nodeId].hasChildren || (this.cache['n' + nodeId].hasChildren && this.cache['n' + nodeId].children.length))) {
            data = this.cache['n' + nodeId];
            $childrenList.update();
    
            data.children.sort(this.options.nodeSorting);
            
            for (i in data.children) {
                if (!data.children.hasOwnProperty(i)) {
                    continue;
                }
                
                $childrenList.insert(this.renderNode(data.children[i]));
                
                if (this.isOpenNode(data.children[i].nodeId)) {
                    this.displayChildrenNodes(data.children[i].nodeId);
                }
            }
            
            return;
        }
        
        $childrenList
            .insert(new Element('li')
                .update(new Element('img')
                    .writeAttribute('src', window.dir_images + 'ajax-loading.gif')));
        
        this.loadChildrenNodes(nodeId, this.displayChildrenNodes.bind(this));
    },
    
    /**
     * Method for loading children nodes via ajax.
     *
     * @param nodeId
     * @param callback
     */
    loadChildrenNodes: function (nodeId, callback) {
        throw "There is no data for element " + nodeId + "! Please extend this class and implement 'loadChildrenNodes'.";
    }
});
