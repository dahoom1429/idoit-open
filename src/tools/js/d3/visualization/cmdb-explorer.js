/**
 * i-doit CMDB Explorer javascript base class.
 *
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
window.CMDB_Explorer = Class.create({
    vis:          null,
    svg:          null,
    layout:       null,
    zoom_ev:      null,
    options:      {},
    profile:      {},
    $tooltip_div: null,

    get_option: function (option) {
        return this.options[option];
    },

    set_option: function (option, value) {
        this.options[option] = value;

        return this;
    },

    get_svg: function () {
        return this.svg
    },

    set_svg: function (svg) {
        this.svg = svg;

        return this;
    },
    
    initialize: function ($el, data, options, profile, object_types) {
        this.$element = $el;
        this.data = data || [];
        this.options = {
            name:            '',                      // This name will be used as ID on the SVG element.
            zoom:            true,                    // Shall the canvas be zoomable?
            minZoomLevel:    0.125,                   // Defines the minimal zoom level.
            maxZoomLevel:    2,                       // Defines the maximal zoom level.
            nodeWidth:      50,                      // Is used to position the nodes (horizontally) next to each other.
            nodeHeight:     10,                      // Is used to position the nodes (vertically) next to each other.
            node_row_height: 20,                      // Is used to position the node rows.
            mouseover:       Prototype.emptyFunction, // MouseOver event for any nodes.
            mouseout:        Prototype.emptyFunction, // MouseOut event for any nodes.
            click:           Prototype.emptyFunction, // Click event for any nodes.
            dblclick:        Prototype.emptyFunction, // DoubleClick event for any nodes.
            width:           null,                    // This can be used to change the viewpoints width. The SVG element will always remain 100%x100%.
            height:          null,                    // This can be used to change the viewpoints height. The SVG element will always remain 100%x100%.
            append_styles:   this.append_styles,      // This callback can be used to implement some own stylings via the <def> element.
            tooltips:        false                    // Enable this option to display tooltips (for "micro" or "net" profile).
        };
        
        this.profile = profile || {};
        this.object_types = object_types || {};
        
        // Setting the default width and height.
        this.options.width = $el.getWidth();
        this.options.height = $el.getHeight();
        
        idoit.callbackManager.registerCallback('visualization-zoom', this.zoom, this);
        
        Object.extend(this.options, options || {});
    
        this.svg = d3.select(this.$element).select('svg');
        

        
        this.zoom_ev = d3.zoom()
                         .scaleExtent([
                             this.options.minZoomLevel,
                             this.options.maxZoomLevel
                         ])
                         .on('zoom', this.zooming.bind(this));
    
        if (! this.svg.node()) {
            this.svg = d3.select(this.$element)
                         .append('svg')
                         .attr("xmlns", "http://www.w3.org/2000/svg")
                         .attr('id', this.options.name)
                         .attr('width', '100%')
                         .attr('height', '100%');
    
            this.svg
                .call(this.zoom_ev)
                .on('dblclick.zoom', null);
        }
        
        this.flash_node();
    },

    flash_node: function () {
        setTimeout(function () {
            if (this.vis)
            {
                this.vis.selectAll('.flash')
                    .select('rect')
                    .transition()
                    .style('stroke', this.profile['highlight-color'])
                    .style('stroke-width', '7px')
                    .style('stroke-opacity', 1)
                    .transition()
                    .delay(500)
                    .style('stroke', null)
                    .style('stroke-width', null)
                    .style('stroke-opacity', null)
            }

            this.flash_node();
        }.bind(this), 1000);
    },

    append_styles: function () {
        var defs = this.svg.select('defs');
        if (defs.node() === null)
        {
            defs = this.svg.append('defs');

            defs.append('style').attr('type', 'text/css').text(
                "text { \
                    font-size:11px; \
                    font-family: \"Helvetica Neue\", \"Lucida Grande\", \"Tahoma\", Arial; \
                } \
                .link { \
                    fill: none; \
                    stroke: #aaa; \
                    stroke-linejoin: round; \
                    stroke-width: 1px; \
                }\
                .arrow {\
                    fill: #aaa;\
                }\
                .link.highlight {\
                    stroke-width: 3px;\
                }\
                .node {\
                    cursor: pointer;\
                }\
                .node:hover rect:first-child {\
                    stroke-width: 3px;\
                }\
                .node rect:first-child {\
                    fill: transparent;\
                    stroke: #000;\
                    stroke-opacity: 0.5;\
                }\
                .node.active rect:first-child {\
                    stroke-width: 4px;\
                    stroke-opacity: 1;\
                }\
                .node.highlight rect:first-child,\
                .node rect.root-object {\
                    stroke-width: 4px;\
                    stroke-opacity: 1;\
                }\
                .transparent {\
                    fill-opacity: 0.2;\
                    stroke-opacity: 0.2;\
                }"
            );

            // This section adds in the arrows.
            defs
                .append("svg:marker")
                .attr("id", 'end')
                .attr("viewBox", "0 -5 10 10")
                .attr("refX", 0)
                .attr("refY", 0)
                .attr("markerWidth", 18)
                .attr("markerHeight", 7)
                .attr("orient", "auto")
                .append("svg:path")
                .attr("d", "M0,-5L10,0L0,5")
                .attr("class", "arrow");

            defs
                .append("svg:marker")
                .attr("id", 'end-highlight')
                .attr("viewBox", "0 -5 10 10")
                .attr("refX", 0)
                .attr("refY", 0)
                .attr("markerWidth", 18)
                .attr("markerHeight", 5)
                .attr("orient", "auto")
                .append("svg:path")
                .attr("d", "M0,-5L10,0L0,5")
                .attr("class", "arrow-highlight")
                .style('fill', this.profile['highlight-color']);
        }
    },

    process: Prototype.emptyFunction,

    render_nodes: function (nodes) {
        var i,
            explorer     = this,
            profile      = this.profile,
            object_types = this.object_types,
            profile_rows = Object.keys(profile.rows).length,
            fillcolor, fontcolor, text, text_margin, row,
            pos_y        = (profile_rows * this.options.node_row_height) * -0.5;

        nodes
            .attr('data-relation-obj-id', function (d) { return d.data.data.relation_obj_id; })
            .attr('data-obj-type-id', function (d) { return d.data.data.obj_type_id; })
            .attr('data-object-id', function (d) { return d.data.data.obj_id })
            .attr('data-id', function (d) { return d.data.id });

        if (profile.mikro)
        {
            nodes.append('circle')
                 .attr('r', '5px')
                 .attr('style', function (d) {
                     return 'fill:' + d.data.obj_type_color + '; stroke:#eee; stroke-width:1px;';
                 });

            nodes.append('text')
                 .attr('x', 13)
                 .attr('y', 4)
                 .text(function (d) {
                     return d.data.obj_type_title + ': ' + d.data.obj_title;
                 });
        }
        else
        {
            nodes.append('rect')
                 .attr('x', -(profile.width / 2))
                 .attr('y', pos_y)
                 .attr('width', profile.width)
                 .attr('height', (profile_rows * this.options.node_row_height))
                 .attr('class', function (d) {
                     return (d.data['root-object']) ? 'root-object' : null;
                 })
                 .attr('style', function (d) {
                     var style = '';

                     if (d.data.data['root-object'])
                     {
                         style += 'stroke:' + profile['highlight-color'] + ';';
                     }

                     if (d.data.data.doubling)
                     {
                         style += 'stroke-dasharray:7,2; stroke-width:3px;';
                     }

                     return style;
                 });

            for (i in profile.rows)
            {
                if (profile.rows.hasOwnProperty(i))
                {
                    row = profile.rows[i];

                    fillcolor = row.fillcolor || '';
                    fontcolor = row.fontcolor || '#000000';
                    text = null;
                    text_margin = 0;

                    if (!(Object.isString(fillcolor) && fillcolor.substr(0, 1) == '#'))
                    {
                        fillcolor = function (d) {
                            return object_types[d.data.data.obj_type_id].color;
                        };
                    }

                    nodes.append('rect')
                         .style('fill', fillcolor)
                         .attr('x', -(profile.width / 2))
                         .attr('y', pos_y)
                         .attr('width', profile.width)
                         .attr('height', this.options.node_row_height);

                    if (row.option == 'obj-type-title-icon')
                    {
                        nodes.append('image')
                             .attr('xlink:href', function (d) {
                                 return object_types[d.data.data.obj_type_id].icon
                             })
                             .attr('x', -((profile.width / 2) - 3))
                             .attr('y', (pos_y + 2))
                             .attr('width', 16)
                             .attr('height', 16);

                        text = nodes.append('text')
                                    .text(function (d) {
                                        return explorer.text_format.call(this, d.data.content[row.option], (profile.width - 30));
                                    });

                        if (row['font-align-right'])
                        {
                            text_margin = 5;
                        }
                        else
                        {
                            text.attr('x', -((profile.width / 2) - 22));
                        }
                    }
                    else if (row.option == 'obj-title-type-title-icon-cmdb-status')
                    {
                        nodes.append('image')
                             .attr('xlink:href', function (d) {
                                 return object_types[d.data.data.obj_type_id].icon
                             })
                             .attr('x', -((profile.width / 2) - 3))
                             .attr('y', (pos_y + 2))
                             .attr('width', 16)
                             .attr('height', 16);

                        nodes.append('rect')
                             .style('fill', function (d) {
                                 return d.data.content[row.option]['cmdb-color'];
                             })
                             .style('stroke', '#000000')
                             .style('stroke-width', 0.5)
                             .attr('rx', 3)
                             .attr('ry', 3)
                             .attr('x', ((profile.width / 2) - 13))
                             .attr('y', (pos_y + 4))
                             .attr('width', 10)
                             .attr('height', 12);

                        text = nodes.append('text')
                                    .text(function (d) {
                                        return explorer.text_format.call(this, d.data.content[row.option]['obj-type-title'] + ': ' + d.data.content[row.option]['obj-title'], (profile.width - 40));
                                    });

                        if (row['font-align-right'])
                        {
                            text_margin = 15;
                        }
                        else
                        {
                            text.attr('x', -((profile.width / 2) - 22));
                        }
                    }
                    else if (row.option == 'obj-title-cmdb-status')
                    {
                        nodes.append('rect')
                             .style('fill', function (d) {
                                 return d.data.content[row.option]['cmdb-color'];
                             })
                             .style('stroke', '#000000')
                             .style('stroke-width', 0.5)
                             .attr('rx', 3)
                             .attr('ry', 3)
                             .attr('x', ((profile.width / 2) - 13))
                             .attr('y', (pos_y + 4))
                             .attr('width', 10)
                             .attr('height', 12);

                        text = nodes.append('text')
                                    .text(function (d) {
                                        return explorer.text_format.call(this, d.data.content[row.option]['obj-title'], (profile.width - 40));
                                    });

                        if (row['font-align-middle'])
                        {
                            text_margin = 10;
                        }
                        else if (row['font-align-right'])
                        {
                            text_margin = 15;
                        }
                        else
                        {
                            text.attr('x', -((profile.width / 2) - 5));
                        }
                    }
                    else if (row.option == 'cmdb-status')
                    {
                        nodes.append('rect')
                             .style('fill', function (d) {
                                 return d.data.content[row.option].color;
                             })
                             .style('stroke', '#000000')
                             .style('stroke-width', 0.5)
                             .attr('rx', 3)
                             .attr('ry', 3)
                             .attr('x', -((profile.width / 2) - 3))
                             .attr('y', (pos_y + 4))
                             .attr('width', 10)
                             .attr('height', 12);

                        text = nodes.append('text')
                                    .text(function (d) {
                                        return explorer.text_format.call(this, d.data.content[row.option].title, (profile.width - 20));
                                    });

                        if (row['font-align-right'])
                        {
                            text_margin = 5;
                        }
                        else
                        {
                            text.attr('x', -((profile.width / 2) - 16));
                        }
                    }
                    else
                    {
                        text = nodes.append('text')
                                    .attr('x', -((profile.width / 2) - 5))
                                    .text(function (d) {
                                        return explorer.text_format.call(this, d.data.content[row.option], (profile.width - 15));
                                    });
                    }

                    if (text !== null)
                    {
                        text.attr('y', (pos_y + 4 + (this.options.node_row_height / 2))).style('fill', fontcolor);

                        if (row['font-align-middle'])
                        {
                            text.attr('x', -text_margin).style('text-anchor', 'middle');
                        }

                        if (row['font-align-right'])
                        {
                            text
                                .attr('x', function (d) {
                                    return ((profile.width / 2) - (this.getComputedTextLength() + text_margin));
                                })
                                .style('text-anchor', 'right');
                        }

                        if (row['font-bold'])
                        {
                            text.style('font-weight', 'bold');
                        }

                        if (row['font-italic'])
                        {
                            text.style('font-style', 'italic');
                        }

                        if (row['font-underline'])
                        {
                            text.style('text-decoration', 'underline');
                        }
                    }

                    pos_y += this.options.node_row_height;
                }
            }
        }
    },

    stop: function () {
        this.layout.stop();
    },
    
    zooming: function () {
        var g = this.vis || d3.select('#' + this.options.name + '_g');
        
        this.hide_tooltip();
        
        g.attr('transform', d3.event.transform);
    },

    zoom: function () {
        var newTranslate = [0, 0],
            newScale = 1;

        this.hide_tooltip();

        if (this.instance === 'CMDB_Explorer_Tree')
        {
            newTranslate = [(this.options.width / 2), (this.options.height / 2)];
        }
        
        this.svg.transition().duration(500).call(this.zoom_ev.transform, d3.zoomIdentity.translate(newTranslate[0], newTranslate[1]).scale(newScale));
    },

    text_format: function (text, width) {
        var result = text;

        if (width <= 0)
        {
            return '';
        }

        d3.select(this).text(result);

        while (this.getComputedTextLength() > width)
        {
            result = result.substr(0, (result.length - 2));
            d3.select(this).text(result);
        }

        if (result != text && width > 2)
        {
            result += '..';
        }

        return result;
    },

    toggle_obj_type_transparency: function (obj_types) {
        if (!Object.isArray(obj_types))
        {
            this.options.obj_type_filter = [];
            return false;
        }

        this.vis
            .selectAll('[data-obj-type-id]')
            .classed('transparent', function (d) {
                return !obj_types.in_array(d.data.data.obj_type_id);
            })
            .selectAll('image')
            .classed('opacity-30', function (d) {
                return !obj_types.in_array(d.data.data.obj_type_id);
            });

        this.options.obj_type_filter = obj_types;
    },

    // This method will prepare the tooltips for the GUI.
    prepare_tooltips: function () {
        if (this.options.tooltips)
        {
            if (!this.$tooltip_div)
            {
                this.$tooltip_div = new Element('div', {
                    className: 'mouse-pointer explorer-tooltip',
                    style:     'position:fixed; top:-20; left:-20;'
                });
            }

            this.$element.insert(this.$tooltip_div);

            this.vis.selectAll('.node')
                .on('mouseover', function (d) {
                    var style = {
                        top:    (d3.event.clientY - 5) + 'px',
                        left:   (d3.event.clientX - 5) + 'px',
                        height: '10px',
                        width:  '10px'
                    };

                    this.$tooltip_div.stopObserving('click').on('click', function () {
                        this.options.click(d);
                    }.bind(this));

                    this.$tooltip_div.stopObserving('dblclick').on('dblclick', function () {
                        this.options.dblclick(d);
                    }.bind(this));

                    new Tip(
                        this.$tooltip_div.setStyle(style),
                        new Element('p', {
                            className: 'p5',
                            stem:      false,
                            style:     'font-size:12px;'
                        }).update(d.data.data.obj_type_title + ': ' + d.data.data.obj_title),
                        {style: 'darkgrey'});
                }.bind(this));
        }

        return this;
    },

    // This method will be used when zooming / dragging to hide the tooltip.
    hide_tooltip: function () {
        if (this.options.tooltips && this.$element.down('.explorer-tooltip'))
        {
            this.$element.select('.explorer-tooltip').each(function ($tooltip) {
                $tooltip.setStyle({
                    top:  -20,
                    left: -20
                })
            });
        }
    }
});

/**
 * i-doit CMDB Explorer javascript graph class.
 *
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
window.CMDB_Explorer_Graph = Class.create(window.CMDB_Explorer, {
    initialize: function ($super, $el, data, options, profile, object_types) {
        this.instance = 'CMDB_Explorer_Graph';
        
        // Adding a few default options for this explorer view.
        this.options.gravity = .15;        // This is used to center all nodes. On "0" they will just float around.
        this.options.charge = -30;         // The attraction of the nodes under eachother.
        this.options.obj_type_filter = []; // This filter will set the selected object types transparent.
        
        idoit.callbackManager.registerCallback('visualization-toggle-obj-types', this.toggle_obj_type_transparency, this);
        
        $super($el, data, options, profile, object_types);
    },

    show_root_path: function (d) {
        var nodes = d3.select('.node.active').classed('active', false),
            node  = d3.select('[data-id="' + d.id + '"]').classed('active', true);

        nodes.select('rect').style('stroke', null);
        node.select('rect').style('stroke', this.profile['highlight-color']);

        if (this.profile.mikro)
        {
            nodes.select('text').style('font-weight', null);
            node.select('text').style('font-weight', 'bold');
        }

        this.vis.selectAll('.link')
            .classed('highlight', false)
            .style('stroke', null)
            .attr("marker-mid", "url(#end)")
            .filter(function (d2) {
                return (d2.source === d || d2.target === d);
            })
            .classed('highlight', true)
            .style('stroke', this.profile['highlight-color'])
            .attr("marker-mid", "url(#end-highlight)");
    },

    process: function () {
        var i,
            i2,
            width         = this.$element.getWidth(),
            height        = this.$element.getHeight(),
            linkDistance = (this.profile.mikro ? 200 : Math.sqrt(Math.pow(this.options.nodeWidth, 2) + Math.pow(this.options.nodeHeight, 2)) * 1.5),
            idMapper     = [],
            linkArray    = [];
        
        if (!this.layout)
        {
            this.layout = cola.d3adaptor(d3).convergenceThreshold(0.1);
        }

        if (!this.vis)
        {
            this.vis = this.svg.append('g')
                           .attr('id', this.options.name + '_g')
                           .attr('transform', 'translate(0,0) scale(1)');
        }

        // D3 WebCola
        this.layout
            .size([width, height])
            .linkDistance(linkDistance)
            .on("tick", function () {
                this.vis.selectAll('.node')
                    .attr('transform', function (d) {
                        return 'translate(' + d.x + ',' + d.y + ')';
                    });

                this.vis.selectAll('.link')
                    .attr('d', function (d) {
                        var dx = (d.target.x + d.source.x)/2,
                            dy = (d.target.y + d.source.y)/2;

                        return "M" + d.source.x + "," + d.source.y + "L" + dx + "," + dy + "L" + d.target.x + "," + d.target.y;
                    });

                if (Prototype.Browser.IE || !!navigator.userAgent.match(/Trident.*rv[ :]*11\./))
                {
                    // IE has a problem with paths which inherit markers. This is a "hack" to make it work more or less...
                    this.vis.selectAll('.link').each(function () {
                        this.parentNode.insertBefore(this, this);
                    });
                }

            }.bind(this));

        // Process the data.
        for (i in this.data)
        {
            if (this.data.hasOwnProperty(i))
            {
                if (idMapper.indexOf(parseInt(this.data[i].id)) == -1)
                {
                    // @todo  Check if this is really necessary (seems to happen only with d3 v4).
                    this.data[i].data.data = this.data[i].data;
                    this.data[i].data.content = this.data[i].content;
    
                    idMapper.push(parseInt(this.data[i].id));
                }
            }
        }

        // We need to do this separately, because the "idMapper" will now be filled.
        for (i in this.data)
        {
            if (this.data.hasOwnProperty(i))
            {
                for (i2 in this.data[i].children)
                {
                    if (this.data[i].children.hasOwnProperty(i2) && idMapper.indexOf(parseInt(this.data[i].children[i2])) != -1)
                    {
                        linkArray.push({
                            source: idMapper.indexOf(parseInt(this.data[i].id)),
                            target: idMapper.indexOf(parseInt(this.data[i].children[i2]))
                        });
                    }
                }

                this.data[i].id = null;
                delete this.data[i].children;
            }
        }

        this.render(linkArray);
        this.prepare_tooltips();
        this.options.append_styles.call(this);
    },

    render: function (list_array) {
        var nodeEnter,
            node,
            link;

        // D3 WebCola
        this.layout
            .nodes(this.data)
            .links(list_array)
            .start(20, 60, 200);

        node = this.vis.selectAll('.node')
                   .data(this.layout.nodes());

        // Declare the nodes.
        nodeEnter = node
            .enter().append("g")
            .style("opacity", 0)
            .attr("class", "node")
            .on('mouseover', this.options.mouseover)
            .on('mouseout', this.options.mouseout)
            .on('click', this.options.click)
            .on('dblclick', this.options.dblclick);
    
        this.vis.selectAll('.node')
            .interrupt().transition().duration(500)
            .style("opacity", 1);

        // Declare the links.
        link = this.vis.selectAll('.link')
                   .data(this.layout.links());

        link.enter()
            .insert("path", "g")
            .style("opacity", 0)
            .attr("d", function(d) {
                var dx = (d.target.x + d.source.x)/2,
                    dy = (d.target.y + d.source.y)/2;

                return "M" + d.source.x + "," + d.source.y + "L" + dx + "," + dy + "L" + d.target.x + "," + d.target.y;
            })
            .attr("class", "link")
            .attr("marker-mid", "url(#end)");
    
        this.vis.selectAll('.link')
            .interrupt().transition().duration(500)
            .style("opacity", 1);

        try
        {
            this.render_nodes(nodeEnter);
        }
        catch (e)
        {
            idoit.Notify.error(e);
        }

        if (Object.isArray(this.options.obj_type_filter))
        {
            this.toggle_obj_type_transparency(this.options.obj_type_filter);
        }
    }
});

/**
 * i-doit CMDB Explorer javascript tree class.
 *
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
window.CMDB_Explorer_Tree = Class.create(CMDB_Explorer, {
    initialize: function ($super, $el, data, options, profile, object_types) {
        this.instance = 'CMDB_Explorer_Tree';

        // Adding a few default options for this explorer view.
        this.options.vertical = true;       // Shall the tree be displayed from top to bottom or left to right?
        this.options.levelDistance = 100;  // This number defines the space between to tree levels.
        this.options.mirrored = false;      // This is used for a tree, which has its root at the bottom.
        this.options.top_tree = '';         // This is used for the zooming function (we need the ID of the top and bottom tree).
        this.options.bottom_tree = '';      // This is used for the zooming function (we need the ID of the top and bottom tree).
        this.options.obj_type_filter = [];  // This filter will set the selected object types transparent.

        idoit.callbackManager.registerCallback('visualization-toggle-orientation', this.toggle_orientation, this);
        idoit.callbackManager.registerCallback('visualization-toggle-obj-types', this.toggle_obj_type_transparency, this);

        $super($el, data, options, profile, object_types);
    
        this.layout = d3.tree()
                        .size(['100%', '100%'])
                        .separation(function () {
                            return 1;
                        });
    },
    
    appendChildren: function (children) {
        this.data = this.data.concat(children);
        
        return this;
    },
    
    toggleChildren: function (d, filter) {
        var i;
    
        for (i in this.data) {
            if (this.data.hasOwnProperty(i) && this.data[i].parent == d.id) {
                this.data[i].filtered = filter;
                
                this.toggleChildren(this.data[i], filter);
            }
        }
        
        return this;
    },
    
    findDataById: function (id) {
        var data = this.data.filter(function(d) {
            return d.id == id;
        });
        
        if (data.length) {
            return data[0];
        }
        
        return false;
    },
    
    show_root_path: function (d) {
        this.svg.select('.node.active').classed('active', false);

        var parent   = this.vis.select('[data-id="' + d.data.id + '"]').classed('active', true).data()[0],
            nodePath = [];

        // This is necessary to remove ALL highlighted nodes / links, because we work with two trees.
        this.svg
            .selectAll('.highlight').classed('highlight', false)
            .select('rect:not(.root-object)').style('stroke', null);

        while (parent !== null)
        {
            nodePath.push(parent);

            this.vis
                .select('[data-id="' + parent.id + '"]').classed('highlight', true)
                .select('rect').style('stroke', this.profile['highlight-color']);

            parent = parent.parent;
        }

        this.svg.selectAll('path.link')
            .style('stroke', null)
            .attr("marker-mid", "url(#end)")
            .filter(function (d) {
                return (nodePath.in_array(d.target));
            })
            .classed('highlight', true)
            .attr("marker-mid", "url(#end-highlight)")
            .style('stroke', this.profile['highlight-color']);
    },

    toggle_orientation: function () {
        this.options.vertical = !this.options.vertical;

        this.hide_tooltip();
        this.process();
    },
    
    process: function () {
        if (!this.vis)
        {
            this.vis = this.svg.append('g')
                           .attr('id', this.options.name + '_g')
                           .attr('transform', 'translate(' + (this.options.width / 2) + ',' + (this.options.height / 2) + ') scale(1)');
        }
    
        this.dataCache = this.data.filter(function(d) {
            return !d.filtered;
        });
    
        this.layout
            .nodeSize(this.options.vertical ? [
                this.options.nodeWidth,
                this.options.nodeHeight
            ] : [
                this.options.nodeHeight,
                this.options.nodeWidth
            ]);
        
        this.render();
        this.prepare_tooltips();
        this.options.append_styles.call(this);
    },

    render: function () {
        // @see  ID-5357  Sort the output
        this.dataCache.sort(function(a, b) {
            return a.name.localeCompare(b.name);
        });
        
        // Compute the new tree layout.
        var that       = this,
            root       = d3.stratify()
                           .id(function(d) { return d.id; })
                           .parentId(function(d) { return d.parent; })(this.dataCache),
            treeData   = this.layout(root),
            nodes      = treeData.descendants(),
            links      = treeData.links(),
            nodeMargin = 5,
            nodeEnter,
            node,
            link,
            i;
    
        var diagonal = function (s, d) {
            var sourceX,
                sourceY,
                targetX,
                targetY,
                tmp,
                delta = (that.options.levelDistance / 5);
        
            if (that.options.vertical) {
                if (that.options.mirrored) {
                    sourceX = d.x;
                    sourceY = -Math.abs(d.y) + (that.options.nodeHeight / 2) - nodeMargin;
                    targetX = s.x;
                    targetY = -Math.abs(s.y) - (that.options.nodeHeight / 2) + nodeMargin;
                } else {
                    sourceX = s.x;
                    sourceY = (s.y + (that.options.nodeHeight / 2) - nodeMargin);
                    targetX = d.x;
                    targetY = (d.y - (that.options.nodeHeight / 2) + nodeMargin);
                }
            
                return 'M' + sourceX + ' ' + sourceY +
                       'C' + sourceX + ' ' + (sourceY + delta) +
                       ',' + (sourceX + targetX) / 2 + ' ' + (sourceY + targetY) / 2 +
                       ',' + (sourceX + targetX) / 2 + ' ' + (sourceY + targetY) / 2 +
                       'C' + targetX + ' ' + (targetY - delta) +
                       ',' + targetX + ' ' + targetY +
                       ',' + targetX + ' ' + targetY;
            }
        
            if (that.options.mirrored) {
                sourceX = -Math.abs(d.y) + (that.options.nodeWidth / 2) - nodeMargin;
                sourceY = d.x;
                targetX = -Math.abs(s.y) - (that.options.nodeWidth / 2) + nodeMargin;
                targetY = s.x;
            } else {
                sourceX = (s.y + (that.options.nodeWidth / 2) - nodeMargin);
                sourceY = s.x;
                targetX = (d.y - (that.options.nodeWidth / 2) + nodeMargin);
                targetY = d.x;
            }
    
            return 'M' + sourceX + ' ' + sourceY +
                   'C' + (sourceX + delta / 2) + ' ' + sourceY +
                   ',' + (sourceX + targetX) / 2 + ' ' + (sourceY + targetY) / 2 +
                   ',' + (sourceX + targetX) / 2 + ' ' + (sourceY + targetY) / 2 +
                   'C' + (sourceX + targetX) / 2 + ' ' + targetY +
                   ',' + targetX + ' ' + targetY +
                   ',' + targetX + ' ' + targetY;
        };
        
        // Normalize for fixed-depth.
        for (i in nodes) {
            if (nodes.hasOwnProperty(i)) {
                nodes[i].y = nodes[i].depth * that.options.levelDistance;
    
                if (that.options.mirrored)
                {
                    nodes[i].y *= -1;
                }
            }
        }

        node = this.vis.selectAll("g.node")
                   .data(nodes, function (d) {
                       return d.id || (d.id = ++i);
                   });

        // Declare the nodes.
        nodeEnter = node
            .enter().append("g")
            .style("opacity", 0)
            .attr("class", "node")
            .attr("transform", function (d) {
                if (that.options.vertical) {
                    return "translate(" + d.x + "," + (d.y * 0.9) + ")";
                } else {
                    return "translate(" + (d.y * 0.9) + "," + d.x + ")";
                }
            })
            .on('mouseover', this.options.mouseover)
            .on('mouseout', this.options.mouseout)
            .on('click', this.options.click)
            .on('dblclick', this.options.dblclick);
    
        this.vis.selectAll("g.node")
            .interrupt().transition().duration(500)
            .style("opacity", 1)
            .attr("transform", function (d) {
                if (that.options.vertical) {
                    return "translate(" + d.x + "," + d.y + ")";
                } else {
                    return "translate(" + d.y + "," + d.x + ")";
                }
            });

        // Handle "exit" nodes - these are nodes, that are no longer being displayed (because "toggled" away or so).
        node.exit()
            .interrupt().transition().duration(500)
            .style("opacity", 0)
            .remove();

        // Declare the links.
        link = this.vis.selectAll("path.link")
                   .data(links, function (d) {
                       return d.target.data.id;
                   });

        link
            .enter()
            .insert("path", "g")
            .style("opacity", 0)
            .attr("class", "link")
            .attr("marker-mid", "url(#end)")
            .attr('d', function (d) {
                return diagonal(d.source, d.target);
            });
    
        this.vis.selectAll("path.link")
            .interrupt().transition().duration(500)
            .style("opacity", 1)
            .attr('d', function (d) {
                return diagonal(d.source, d.target);
            });

        // Handle "exit" links - these are links, that are no longer being displayed (because "toggled" away or so)...
        link.exit()
            .interrupt().transition().duration(500)
            .style("opacity", 0)
            .remove();

        this.render_nodes(nodeEnter);

        if (Object.isArray(this.options.obj_type_filter))
        {
            this.toggle_obj_type_transparency(this.options.obj_type_filter);
        }
    },

    // The "zooming" function for the tree graph.
    zooming: function () {
        var top       = d3.select('#' + this.options.top_tree + '_g'),
            bottom    = d3.select('#' + this.options.bottom_tree + '_g');

        this.hide_tooltip();

        if (top) {
            top.attr('transform', d3.event.transform);
        }

        if (bottom) {
            bottom.attr('transform', d3.event.transform);
        }
    }
});
