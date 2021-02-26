<div id="infoWidgetWrapper" class="m0 p10" style="height:auto;">
    <select name="layer3net" id="layer3net" onchange="$('isys_form').submit();">
        [{foreach from=$layer3networks item=net}]
            <option value="[{$net.isys_obj__id}]"[{if $net.isys_obj__id eq $smarty.post.layer3net}] selected="selected"[{/if}]>
                [{$net.isys_obj__title}] ([{$net.isys_cats_net_list__address}])
            </option>
        [{/foreach}]
    </select>
</div>

<div id="networkPlan" class="explorer text-shadow"></div>
<div id="infoWidget" class="box-blue p10" style="display: none;"></div>

<style type="text/css">
    #networkPlan {
        position: absolute;
        font-weight: bold;
        top: 45px;
        right: 0;
        bottom: 0;
        left: 0;
        overflow: hidden;
    }

    #infoWidget {
        position: absolute;
        z-index: 1000;
        right: 10px;
        top: 54px;
        width: 250px;
    }
</style>

<script type="text/javascript">
    (function () {
        idoit.Require.require(['jit'], function () {
            [{include file="./view_network_plan.js"}]

            var json = JSON.parse('[{$data|escape:"javascript"|default:'{"id":0, "name":"", "data":{"objectType":"Network Plan"}, "children": []}'}]');

            // Load data.
            rgraph.loadJSON(json);
            rgraph.compute('end');
            rgraph.fx.animate({
                modes:    ['polar'],
                duration: 800
            });
        })
    })();
</script>