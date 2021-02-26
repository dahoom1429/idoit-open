<div class="p10">
    <ul id="tabs">
        <li>
            <a href="#tab1">[{isys type="lang" ident="LC__CMDB__OBJTYPE__CLUSTER"}] </a>
        </li>
        <li>
            <a href="#tab2">[{isys type="lang" ident="LC__CMDB__CATG__MANAGED_OBJECTS__PHYSICAL_HOSTS"}] </a>
        </li>
        <li>
            <a href="#tab3">[{isys type="lang" ident="LC__CMDB__CATG__MANAGED_OBJECTS__VIRTUAL_COMPUTERS"}]</a>
        </li>
    </ul>

    <div id="tab1" class="p10">
        <table class="listing" cellpadding="0" cellspacing="0">
            <colgroup>
                <col width="5%" />
                <col width="15%" />
                <col width="15%" />
                <col width="20%" />
                <col width="15%" />
            </colgroup>
            [{if is_array($cluster_objects) && count($cluster_objects)}]
            <thead>
            <tr>
                <th>[{isys type="lang" ident="LC__UNIVERSAL__ID"}]</th>
                <th>[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TITLE"}]</th>
                <th>[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TYPE"}]</th>
            </tr>
            </thead>
            <tbody>
            [{foreach from=$cluster_objects item=objectData key=objectID}]
                <tr>
                    <td>
                        [{$objectID}]
                    </td>
                    <td>
                        [{$objectData['link']}]
                    </td>
                    <td>
                        [{$objectData['type']}]
                    </td>
                </tr>
                [{/foreach}]
            </tbody>
            [{else}]
            <tr>
                <td>
                    [{isys type="lang" ident="LC__CMDB__CATG__MANAGED_OBJECTS__NO_CLUSTERS_FOUND"}]
                </td>
            </tr>
            [{/if}]
        </table>
    </div>

    <div id="tab2" class="p10">
        <table class="listing" cellpadding="0" cellspacing="0">

            <colgroup>
                <col width="5%" />
                <col width="15%" />
                <col width="15%" />
                <col width="15%" />
                <col width="10%" />
                <col width="25%" />
            </colgroup>
            [{if is_array($physical_objects) && count($physical_objects)}]
                <thead>
                <tr>
                    <th>[{isys type="lang" ident="LC__UNIVERSAL__ID"}]</th>
                    <th>[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TITLE"}]</th>
                    <th>[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TYPE"}]</th>
                    <th>[{isys type="lang" ident="LC__CATG__OPERATING_SYSTEM"}]</th>
                    <th>[{isys type="lang" ident="LC__CATG__IP__PRIMARY_ADDRESS"}]</th>
                    <th>[{isys type="lang" ident="LC__CMDB__CATG__SERIAL"}]</th>
                    <th>[{isys type="lang" ident="LC__CMDB__OBJTYPE__CLUSTER"}]</th>
                </tr>
                </thead>
                <tbody>
                [{foreach from=$physical_objects item=objectData key=objectID}]
                    <tr>
                        <td>
                            [{$objectID}]
                        </td>
                        <td>
                            [{$objectData['link']}]
                        </td>
                        <td>
                            [{$objectData['type']}]
                        </td>
                        <td>
                            [{$objectData['os']}]
                        </td>
                        <td>
                            [{$objectData['primary_ip']}]
                        </td>
                        <td>
                            [{$objectData['serial']}]
                        </td>
                        <td>
                            [{$objectData['parent']}]
                        </td>
                    </tr>
                [{/foreach}]
                </tbody>
            [{else}]
                <tr>
                    <td>
                        [{isys type="lang" ident="LC__CMDB__CATG__MANAGED_OBJECTS__NO_PHYSICAL_HOSTS_FOUND"}]
                    </td>
                </tr>
            [{/if}]
        </table>
    </div>

    <div id="tab3" class="p10">
        <table class="listing" cellpadding="0" cellspacing="0">
            <colgroup>
                <col width="5%" />
                <col width="15%" />
                <col width="15%" />
                <col width="15%" />
                <col width="10%" />
                <col width="25%" />
            </colgroup>
            [{if is_array($virtual_computers) && count($virtual_computers)}]
            <thead>
            <tr>
                <th>[{isys type="lang" ident="LC__UNIVERSAL__ID"}]</th>
                <th>[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TITLE"}]</th>
                <th>[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TYPE"}]</th>
                <th>[{isys type="lang" ident="LC__CATG__OPERATING_SYSTEM"}]</th>
                <th>[{isys type="lang" ident="LC__CATG__IP__PRIMARY_ADDRESS"}]</th>
                <th>[{isys type="lang" ident="LC__CMDB__CATG__SERIAL"}]</th>
                <th>[{isys type="lang" ident="LC__CMDB__CATG__MANAGED_OBJECTS__HOST"}]</th>
            </tr>
            </thead>
            <tbody>
                [{foreach from=$virtual_computers item=objectData key=objectID}]
                <tr>
                    <td>
                        [{$objectID}]
                    </td>
                    <td>
                        [{$objectData['link']}]
                    </td>
                    <td>
                        [{$objectData['type']}]
                    </td>
                    <td>
                        [{$objectData['os']}]
                    </td>
                    <td>
                        [{$objectData['primary_ip']}]
                    </td>
                    <td>
                        [{$objectData['serial']}]
                    </td>
                    <td>
                        [{$objectData['parent']}]
                    </td>
                </tr>
                [{/foreach}]
            </tbody>
            [{else}]
                <tr>
                    <td>
                        [{isys type="lang" ident="LC__CMDB__CATG__MANAGED_OBJECTS__NO_VIRTUAL_COMPUTERS_FOUND"}]
                    </td>
                </tr>
            [{/if}]
        </table>
    </div>
</div>

<script type="text/javascript">
    (function () {
        "use strict";

        new Tabs('tabs');
    }());
</script>