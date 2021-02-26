'use strict';

/**
 * Bar chart, built with d3 (v4).
 *
 * Use like:
 * new window.D3ChartBar($canvas, {...)})
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

window.D3ChartBar = Class.create({
    $canvas: null,
    data:    [],
    maxSum:  0,
    
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
        this.data = data;
        this.maxSum = d3.max(this.data, function (d) {
            return d.count;
        });
        
        return this;
    },
    
    update: function () {
        var that             = this,
            width            = this.options.width - (this.options.margin.left + this.options.margin.right),
            height           = this.options.height - (this.options.margin.top + this.options.margin.bottom),
            axisTopMargin    = 10,
            axisLeftMargin   = 30,
            axisBottomMargin = 20,
            x,
            y;
        
        if (this.maxSum > 999) {
            axisLeftMargin = 55;
        }
        
        if (this.maxSum > 99999) {
            axisLeftMargin = 85;
        }
        
        this.vis = this.svg
            .html(null)
            .attr('width', width)
            .attr('height', height)
            .append('g')
            .attr('transform', 'translate(' + axisLeftMargin + ', ' + axisTopMargin + ')');
        
        if (this.options.vertical) {
            x = d3.scaleBand()
                .rangeRound([0, (width - axisLeftMargin)])
                .padding(0.05)
                .domain(this.data.map(function (d) {
                    return d.id;
                }));
            
            y = d3.scaleLinear()
                .rangeRound([(height - (axisTopMargin + axisBottomMargin)), 0])
                .domain([0, 1.1 * this.maxSum]);
            
            // Draw the left axis and legend.
            this.vis.append('g')
                .attr('class', 'axis')
                .call(d3.axisLeft(y).ticks(this.options.ticks));
            
            // Draw the grid.
            this.vis.append('g')
                .attr('class', 'grid')
                .call(d3.axisLeft(y).ticks(this.options.ticks).tickSize(-(width - axisLeftMargin)).tickFormat(''));
        } else {
            axisLeftMargin = 20;
            
            this.vis.attr('transform', 'translate(' + axisLeftMargin + ', ' + (height - axisBottomMargin) + ')');
            
            x = d3.scaleLinear()
                .rangeRound([0, (width - (axisTopMargin + axisBottomMargin))])
                .domain([0, 1.1 * this.maxSum]);
            
            y = d3.scaleBand()
                .rangeRound([0, (height - axisBottomMargin)])
                .padding(0.05)
                .domain(this.data.map(function (d) {
                    return d.id;
                }));
            
            // Draw the left axis and legend.
            this.vis.append('g')
                .attr('class', 'axis')
                .call(d3.axisBottom(x).ticks(this.options.ticks));
            
            // Draw the grid.
            this.vis.append('g')
                .attr('class', 'grid')
                .call(d3.axisBottom(x).ticks(this.options.ticks).tickSize(-(height - axisBottomMargin)).tickFormat(''));
        }
        
        // Draw the bars (or "rectangles").
        this.vis.selectAll('.bar')
            .data(this.data)
            .enter().append('rect')
            .attr('class', 'bar')
            .attr('x', function (d) {
                if (that.options.vertical) {
                    return x(d.id);
                }
                
                return 0;
            })
            .attr('y', function (d) {
                if (that.options.vertical) {
                    return y(d.count)
                }
                
                return y(d.id) - (height - axisBottomMargin);
            })
            .attr('width', function (d) {
                if (that.options.vertical) {
                    return x.bandwidth();
                }
                
                return 1 + x(d.count);
            })
            .attr('height', function (d) {
                if (that.options.vertical) {
                    return 1 + ((height - (axisTopMargin + axisBottomMargin)) - y(d.count));
                }
                
                return y.bandwidth();
                
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