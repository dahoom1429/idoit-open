'use strict';

/**
 * Pie chart, built with d3 (v4).
 *
 * Use like:
 * new window.D3ChartPie($canvas, {...)})
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

window.D3ChartPie = Class.create({
    $canvas:  null,
    data:     [],
    countSum: 0,
    
    initialize: function ($canvas, options) {
        this.$canvas = $canvas.update();
        
        this.options = {
            width:                  this.$canvas.getWidth(),
            height:                 this.$canvas.getHeight(),
            applySVGMargin:         true,
            margin:                 {
                top:    20,
                right:  10,
                bottom: 0,
                left:   10
            },
            radiusMargin:           20,
            innerRadius:            0,
            hideLabelsLessThan:     1,
            showPercentageInCenter: false
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
            this.options.margin.bottom = 0;
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
        
        this.pie = d3.pie()
            .sort(null)
            .value(function (d) {
                return d.count;
            });
    },
    
    setData: function (data) {
        this.data = data;
        this.countSum = this.data.pluck('count').sum();
        
        this.data.forEach(function (d) {
            d.percentage = d.count / this.countSum;
        }.bind(this));
        
        return this;
    },
    
    update: function () {
        var that     = this,
            width    = this.options.width - (this.options.margin.left + this.options.margin.right),
            height   = this.options.height - (this.options.margin.top + this.options.margin.bottom),
            radius   = ((Math.min(width, height) / 2) - 30),
            arc      = d3.arc()
                .innerRadius(this.options.innerRadius)
                .outerRadius(radius - this.options.radiusMargin),
            labelArc = d3.arc()
                .innerRadius(radius)
                .outerRadius(radius);
        
        this.vis = this.svg
            .html(null)
            .attr('width', width)
            .attr('height', height)
            .append('g')
            .attr('transform', 'translate(' + (width / 2) + ',' + (height / 2) + ')');
        
        var g = this.vis.selectAll('.arc')
            .data(this.pie(this.data))
            .enter().append('g')
            .attr('class', 'arc')
            .attr('data-id', function (d) {
                return d.data.id;
            });
        
        var path = g.append('path')
            .attr('d', arc)
            .style('fill', function (d) {
                return d.data.color;
            });
        
        if (this.options.showPercentageInCenter) {
            var percentage = this.vis.append('g')
                .attr('transform', 'translate(0,10)')
                .append('text')
                .attr('text-anchor', 'middle')
                .style('font-size', '40px')
                .style('paint-order', 'stroke')
                .style('stroke', 'rgba(0,0,0,.4)')
                .style('stroke-width', '2px')
                .style('fill', '#000')
                .text('');
            
            path.on('mouseover', function (d) {
                percentage.text(d3.format(".1%")(d.data.percentage)).style('fill', d.data.color);
            });
        }
        
        g.append('text')
            .attr('transform', function (d) {
                return 'translate(' + labelArc.centroid(d) + ')';
            })
            .attr('text-anchor', 'middle')
            .text(function (d) {
                // Return the label if the value represents more than a certain percentage of the sum.
                if (that.countSum > 0 && (d.value / that.countSum) >= (that.options.hideLabelsLessThan / 100)) {
                    return d.data.label || d.data.count;
                }
                
                return '';
            });
        
        return this;
    },
    
    filter: function (dataId) {
        this.vis.selectAll('.arc').classed('filtered', true);
        this.vis.selectAll('.arc[data-id="' + dataId + '"]').classed('filtered', false);
        
        return this;
    },
    
    unfilter: function () {
        this.vis.selectAll('.arc').classed('filtered', false);
        
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