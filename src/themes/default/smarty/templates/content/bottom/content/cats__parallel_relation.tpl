<style type="text/css">
    #relpool li {
        clear: both;
        margin-bottom: 5px;
        padding: 5px;
        height: 20px;
        line-height: 20px;
        font-weight: bold;
    }

    #relpool li button {
        display: none;
    }

    [{if isys_glob_is_edit_mode()}]
    #relpool li:hover button {
        display: block;
    }
    [{/if}]
</style>

<table class="contentTable">
    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CMDB__LOGBOOK__TITLE" name="C__CMDB__CATS__RELPL__TITLE"}]</td>
        <td class="value">[{isys type="f_text" name="C__CMDB__CATS__RELPL__TITLE"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__PARALLEL_RELATION__THRESHOLD" name="C__CMDB__CATS__RELPL__THRESHOLD"}]</td>
        <td class="value">[{isys type="f_text" name="C__CMDB__CATS__RELPL__THRESHOLD"}]</td>
    </tr>
</table>

<h3 class="gradient text-shadow p5 border-top border-bottom">[{isys type="lang" ident="LC__RELATION__PARALLEL_RELATIONS"}]</h3>
[{if isys_glob_is_edit_mode()}]
<table class="contentTable">
    <tr>
        <td class="key">[{isys type="lang" ident="LC__RELATION__PARALLEL_RELATIONS" id="C__CMDB__CATS__RELPL__RELATION_POOL"}]</td>
        <td class="value">
            <div class="ml20 input-group input-size-normal">
                [{isys
                name="C__CMDB__CATS__RELPL__RELATION_POOL"
                id="C__CMDB__CATS__RELPL__RELATION_POOL"
                type="f_popup"
                p_strPopupType="browser_object_relation"
                multiselection=true
                relationOnly=true
                secongList="isys_cmdb_dao_category_g_relation::object_browser_get_data_by_object_and_relation_type"
                p_bInfoIconSpacer=0
                }]
            </div>
        </td>
    </tr>
</table>
[{else}]
<div class="p5">
    [{if $link_pool}]
        <ul id="relpool" class="list-style-none p0 m0">[{$link_pool}]</ul>
    [{else}]
        <span id="norelpool">Es wurden noch keine gleichgerichteten Beziehungen gebildet.</span>
    [{/if}]
</div>
[{/if}]

<script type="text/javascript">
    function detachRelationPool() {
        $('C__CMDB__CATS__RELPL__RELATION_POOL__HIDDEN').setValue('[]');
    }
</script>