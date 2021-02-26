var infovis = $('networkPlan');
var w = infovis.offsetWidth - 50, h = infovis.offsetHeight;
var rgraph = new $jit.RGraph({
    //Where to append the visualization
    injectInto: 'networkPlan',
    width:      w,
    height:     h,
    //Optional: create a background canvas that plots
    //concentric circles.
    background: {
        CanvasStyles: {
            strokeStyle: '#ccc'
        }
    },
    //Add navigation capabilities:
    //zooming by scrolling and panning.
    Navigation: {
        enable:  true,
        panning: true,
        zooming: 5
    },
    //Set Node and Edge styles.
    Node:       {
        overridable:  false,
        type:         'circle',
        alpha:        1,
        autoHeight:   false,
        autoWidth:    false,
        lineWidth:    1,
        angularWidth: 5,
        span:         1,
        dim:          4,
        color:        "#ccc"
    },
    Edge:       {
        overridable: false,
        type:        'line',
        lineWidth:   0.2,
        dim:         1,
        alpha:       1,
        lineColor:   "#555",
        color:       "#888"
    },
    
    onBeforeCompute: function (node) {
    
    },
    
    Events: {
        enable:       true,
        onRightClick: Prototype.emptyFunction,
        onMouseEnter: function (e) {
            var info = '<h2 class="mb5"><img src="' + e.data.image + '" alt="?" style="height:18px;width:18px;" class="border vam" /> ' + e.name + '</h2>' +
                       '<div class="fr">' + e.data.cmdbStatus + '</div>' +
                       '<div class="bold">' + e.data.objectType + '</div>' +
                       '<div class="fr bold">' + e.data.hostname + '</span>';
            '<span>' + e.data.ipAddress + '</span>';
            
            $('infoWidget').update(info).show();
        },
        onMouseLeave: function () {
            $('infoWidget').hide();
        }
    },
    
    //Add the name of the node in the correponding label
    //and a click handler to move the graph.
    //This method is called once, on label creation.
    onCreateLabel: function (domElement, node) {
        domElement.innerHTML = node.name;
        domElement.onclick = function () {
            rgraph.onClick(node.id, {
                onComplete: function () {
                
                }
            });
        };
    },
    //Change some label dom properties.
    //This method is called each time a label is plotted.
    onPlaceLabel:  function (domElement, node) {
        var style = domElement.style;
        style.display = '';
        style.cursor = 'pointer';
        if (node._depth <= 1)
        {
            style.fontSize = "0.9em";
            style.color = "#222";
            
        }
        else if (node._depth == 2)
        {
            style.fontSize = "0.7em";
            style.color = "#888";
        }
        else if (node._depth >= 3)
        {
            style.fontSize = "0.6em";
            style.color = "#888";
        }
        
        var left = parseInt(style.left) + 25;
        var w = domElement.offsetWidth;
        style.left = (left - w / 2) + 'px';
        
        style.top = parseInt(style.top) + 5 + 'px';
    }
});