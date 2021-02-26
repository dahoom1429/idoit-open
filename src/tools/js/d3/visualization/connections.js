'use strict';

/**
 * Connection visualization, built with d3 (v3).
 *
 * Use like:
 * new window.ConnectionVisualization($visualization, {...)})
 *    .setData([[0,1], [1,2], [2,0]])
 *    .update();
 *
 * Data format (v1.0) makes use of 2 points per line to display a connection between two points.
 * The values describe the vertical index of these points, beginning with 0. Remember to set the "itemHeight" option accordingly.
 * [
 *    [y1, y2],
 *    [y1, y2],
 *    [y1, y2],
 *    [y1, y2],
 * ]
 *
 * @author     Leonard Fischer <lfischer@i-doit.com>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @version    1.0
 */

window.ConnectionVisualization = Class.create({
    $root: null,

    initialize: function ($root, options) {
        this.$root = $root;

        this.options = {
            width:                this.$root.getWidth(),
            height:               this.$root.getHeight(),
            itemHeight:           30,
            permanentLocal:       null,
            permanentDestination: null
        };

        Object.extend(this.options, options || {});

        this.svg = d3.select(this.$root).append('svg')
                     .attr('width', this.options.width)
                     .attr('height', this.options.height)
                     .append('g');
    },

    setData: function (data) {
        this.data = data;

        return this;
    },

    update: function () {
        var opts = this.options,
            selection,
            data = [],
            line = d3.line()
                     .x(function (d) {
                         switch (d[0])
                         {
                             default:
                             case 0:
                                 return 0;
                             case 1:
                                 return (opts.width * 0.2);
                             case 2:
                                 return opts.width - (opts.width * 0.2);
                             case 3:
                                 return opts.width;
                         }
                     })
                     .y(function (d) {
                         return opts.itemHeight + (d[1] * opts.itemHeight) - (opts.itemHeight / 2);
                     })
                     .curve(d3.curveBasis);

        for (i in this.data)
        {
            if (this.data.hasOwnProperty(i))
            {
                data.push([
                    [0, this.data[i][0]],
                    [1, this.data[i][0]],
                    [2, this.data[i][1]],
                    [3, this.data[i][1]]
                ]);
            }
        }

        selection = this.svg.selectAll('path').data(data);

        selection.enter()
                 .append('path')
                 .attr('data-source', function (d) { return d[0][1]; })
                 .attr('data-target', function (d) { return d[3][1]; })
                 .attr('d', line);

        selection.exit()
                 .remove();
    },

    reset: function () {
        this.data = [];

        this.update();
    },

    highlight: function (local, destination) {
        if (local === null && destination === null)
        {
            this.svg.selectAll('path').classed('opacity-30', false).classed('highlight', false);
        }

        if (local !== null)
        {
            this.svg.select('path[data-source="' + local + '"]').classed('highlight', true);
            this.svg.selectAll('path:not([data-source="' + local + '"])').classed('opacity-30', true);
        }

        if (this.options.permanentLocal !== null)
        {
            this.svg.select('path[data-source="' + this.options.permanentLocal + '"]').classed('highlight', true).classed('opacity-30', false);
        }

        if (destination !== null)
        {
            this.svg.select('path[data-target="' + destination + '"]').classed('highlight', true);
            this.svg.selectAll('path:not([data-target="' + destination + '"])').classed('opacity-30', true);
        }

        if (this.options.permanentDestination !== null)
        {
            this.svg.select('path[data-target="' + this.options.permanentDestination + '"]').classed('highlight', true).classed('opacity-30', false);
        }
    },

    highlightPermanent: function (local, destination) {
        this.options.permanentLocal = local;
        this.options.permanentDestination = destination;
    },

    updateDimension: function (width, height) {
        if (width !== null)
        {
            this.options.width = width;
        }

        if (height !== null)
        {
            this.options.height = height;
        }

        d3.select(this.$root).select('svg')
          .attr('width', this.options.width)
          .attr('height', this.options.height);
    }
});