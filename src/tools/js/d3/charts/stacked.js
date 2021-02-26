'use strict';

/**
 * Stacked bar chart, built with d3 (v4).
 *
 * Use like:
 * new window.D3ChartStacked($canvas, {...)})
 *    .setData([ ... ])
 *    .update();
 *
 * Each data item needs to provide three values: id, color and count. It should look like this:
 * [
 *    {id:1, color:'#fff', count:15},
 *    {id:2, color:'#f00', count:25},
 *    {id:3, color:'#0f0', count:35},
 *    {id:4, color:'#00f', count:45},
 *    {id:5, color:'#000', count:55},
 * ]
 *
 * @author     Leonard Fischer <lfischer@i-doit.com>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @version    1.0
 */

window.D3ChartStacked = Class.create({
    $canvas: null,
    data:    [],
    
    initialize: function ($canvas, options) {
        this.$canvas = $canvas.update();
        
        this.options = {
            width:          this.$canvas.getWidth(),
            height:         this.$canvas.getHeight(),
            applySVGMargin: true,
            margin:         {
                top:    20,
                right:  10,
                bottom: 10,
                left:   10
            },
            ticks:          10,
            vertical:       true
        };
        
        Object.extend(this.options, options || {});
        
        // The next for IFs are necessary because "Object.extend" does not handle nested values.
        if (!this.options.margin.hasOwnProperty('top')) {
            this.options.margin.top = 20;
        }
        
        if (!this.options.margin.hasOwnProperty('right')) {
            this.options.margin.right = 10;
        }
        
        if (!this.options.margin.hasOwnProperty('bottom')) {
            this.options.margin.bottom = 10;
        }
        
        if (!this.options.margin.hasOwnProperty('left')) {
            this.options.margin.left = 10;
        }
        
        this.svg = d3.select(this.$canvas).append('svg');
        
        if (this.options.applySVGMargin) {
            this.svg
                .style('margin-left', this.options.margin.left + 'px')
                .style('margin-top', this.options.margin.top + 'px');
        }
    },
    
    setData: function (data) {
        this.data = data.reverse();
        this.sum = d3.sum(this.data.map(function (d) {
            return d.count;
        }));
        
        var previousCount = 0;
        
        this.data.map(function (d) {
            d.prev = previousCount;
            
            previousCount += d.count;
        });
        
        return this;
    },
    
    update: function () {
        var that             = this,
            width            = this.options.width - (this.options.margin.left + this.options.margin.right),
            height           = this.options.height - (this.options.margin.top + this.options.margin.bottom),
            axisTopMargin    = 10,
            axisLeftMargin   = 40,
            axisBottomMargin = 20,
            x,
            y;
        
        this.vis = this.svg
            .html(null)
            .attr('width', width)
            .attr('height', height)
            .append('g')
            .attr('transform', 'translate(' + axisLeftMargin + ', ' + axisTopMargin + ')');
        
        if (this.options.vertical) {
            y = d3.scaleLinear()
                .rangeRound([(height - (axisTopMargin + axisBottomMargin)), 0])
                .domain([0, this.sum]);
            
            // Draw the left axis and legend.
            this.vis.append('g')
                .attr('class', 'axis')
                .call(d3.axisLeft(y).ticks(this.options.ticks));
        } else {
            axisLeftMargin = 20;
            
            this.vis.attr('transform', 'translate(' + axisLeftMargin + ', ' + (height - axisBottomMargin) + ')');
            
            x = d3.scaleLinear()
                .rangeRound([0, (width - (axisTopMargin + axisBottomMargin))])
                .domain([0, this.sum]);
            
            // Draw the left axis and legend.
            this.vis.append('g')
                .attr('class', 'axis')
                .call(d3.axisBottom(x).ticks(this.options.ticks));
        }
        
        // Draw the bars (or "rectangles").
        this.vis.selectAll('.bar')
            .data(this.data)
            .enter().append('rect')
            .attr('class', 'bar')
            .attr('x', function (d) {
                if (that.options.vertical) {
                    return 1;
                }
                
                return (width - (axisTopMargin + axisBottomMargin)) - (x(d.prev) + x(d.count));
            })
            .attr('y', function (d) {
                if (that.options.vertical) {
                    return (height - (axisTopMargin + axisBottomMargin)) - y(d.prev);
                }
                
                return -(height - (axisTopMargin + axisBottomMargin));
            })
            .attr('width', function (d) {
                if (that.options.vertical) {
                    return width - (axisTopMargin + axisBottomMargin);
                }
                
                return x(d.count);
            })
            .attr('height', function (d) {
                if (that.options.vertical) {
                    return 1 + ((height - (axisTopMargin + axisBottomMargin)) - y(d.count));
                }
                
                return (height - (axisTopMargin + axisBottomMargin));
                
            })
            .attr('data-id', function (d) {
                return d.id;
            })
            .style('fill', function (d) {
                return d.color;
            });
        
        // Clean-up the grid.
        this.vis.selectAll('.grid path,.grid text').remove();
        this.vis.selectAll('.grid line').attr('stroke', 'rgba(0,0,0,.15)').attr('stroke-dasharray', '2,2');
        
        return this;
    },
    
    filter: function (dataId) {
        this.vis.selectAll('.bar').classed('filtered', true);
        this.vis.selectAll('.bar[data-id="' + dataId + '"]').classed('filtered', false);
        
        return this;
    },
    
    unfilter: function () {
        this.vis.selectAll('.bar').classed('filtered', false);
        
        return this;
    },
    
    updateDimension: function (width, height) {
        if (Object.isUndefined(width)) {
            width = this.$canvas.clientWidth || this.$canvas.getWidth();
        }
        
        if (Object.isUndefined(height)) {
            height = this.$canvas.clientHeight || this.$canvas.getHeight();
        }
        
        this.options.width = width;
        this.options.height = height;
        
        return this;
    }
});