<style type="text/css">
    #import_inventory tr .btn {
        visibility: hidden;
    }

    #import_inventory tr:hover .btn {
        visibility: visible;
    }
</style>

<table class="mainTable" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th>[{isys type="lang" ident="LC__UNIVERSAL__FILE_TITLE"}]</th>
        <th>[{isys type="lang" ident="LC__MODULE__IMPORT__EXPORT_TYPE"}]</th>
        <th>[{isys type="lang" ident="LC__SETTINGS__SYSTEM__OPTIONS"}]</th>
    </tr>
    </thead>
    <tbody>
    [{foreach from=$import_files item="im"}]
        [{if ($im.type == 'inventory')}]
        <tr id="[{$im.stripped}]">
            <td class="mouse-pointer" onclick="$('currentImportFileInventory').update('[{$im.filename}] : ');select_importfile('inventory', '[{if isys_tenantsettings::get('system.dir.import-uploads') != ''}][{rtrim(isys_tenantsettings::get('system.dir.import-uploads'), '/')}]/[{$tenant_id}]/[{else}]./imports/[{$tenant_id}]/[{/if}][{$im.filename}]', $(this));">
                <strong>[{$im.stripped}]</strong>
            </td>
            <td class="mouse-pointer" onclick="$('currentImportFileInventory').update('[{$im.filename}] : ');select_importfile('inventory', '[{if isys_tenantsettings::get('system.dir.import-uploads') != ''}][{rtrim(isys_tenantsettings::get('system.dir.import-uploads'), '/')}]/[{$tenant_id}]/[{else}]./imports/[{$tenant_id}]/[{/if}][{$im.filename}]', $(this));">
                <span>xml</span>
            </td>
            <td>
                <button type="button" class="fr btn btn-small mr5" onclick="delete_import('[{$im.filename}]');new Effect.SlideUp('[{$im.stripped}]')">
                    <img src="[{$dir_images}]icons/silk/cross.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__DELETE_FILE"}]</span>
                </button>

                <a href="[{$im.download}]" class="fr btn btn-small mr5">
                    <img src="[{$dir_images}]icons/silk/disk.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__DOWNLOAD_FILE"}]</span>
                </a>
            </td>
        </tr>
        [{/if}]
    [{/foreach}]
    </tbody>
</table>

<h3 class="mt10 border-bottom border-top gradient p5">[{isys type="lang" ident="LC__MASS_CHANGE__OPTIONS"}]</h3>

<table class="contentTable m5">
    <tr>
        <td class="key">[{isys type="lang" ident="LC__CMDB__OBJTYPE"}]</td>
        <td class="value pl20">[{html_options id=obj_type name=obj_type options=$object_types selected=10 class="input input-small"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="lang" ident="LC__MODULE__IMPORT__IMPORT_IN_OBJECT"}]</td>
        <td class="value pl20">
            <input type="text" name="object_id__HIDDEN" id="object_id__HIDDEN" class="input input-small" value="" onkeyup="object_retrieve(this.value);" />
        </td>
    </tr>
    <tr id="object_retrieve_row" style="display:none;">
        <td class="key">[{isys type="lang" ident="LC__CMDB__CATG__CONTACT_TITLE"}]</td>
        <td class="value pl20"><span id="object_retrieve">-</span></td>
    </tr>
    <tr>
        <td class="key"></td>
        <td class="value pl20">
            <label title="Dies gilt insbesondere auch fuer manuell eingepflegte Daten!">
                <input type="checkbox" id="force" name="force" value="1" style="vertical-align:middle;">
                [{isys type="lang" ident="LC__UNIVERSAL__OVERWRITE_EXISTING_ENTRIES"}]
            </label>
        </td>
    </tr>
</table>

<div class="m5">
    <span id="currentImportFileInventory" class="bold"></span>
    <button type="button" id="inventory_import_button" class="btn" onClick="submit_import('hinventory', 'import_result_inventory')" disabled="disabled">
        <span>[{isys type="lang" ident="LC__UNIVERSAL__IMPORT"}] &raquo;</span>
    </button>
</div>

<pre class="m5 mt15 bg-lightgrey border" id="import_result_inventory" style="height:400px;display:none;overflow:scroll;font-family:Courier New, Monospace;"></pre>

<div class="m5 mt15">
    [{if $g_list}]
        <button type="button" class="btn" onclick="$('inventoryObjects').toggle();">
            <span>[{isys type="lang" ident="LC__IMPORT__INVENTORY_OBJECTS"}]</span>
        </button>
    [{/if}]
    <a href="?moduleID=[{$smarty.const.C__MODULE__IMPORT}]&param=[{$smarty.const.C__IMPORT__GET__DOWNLOAD}]&file=hi" class="btn">
        <span>[{isys type="lang" ident="LC__UNIVERSAL__HINVENTORY_SCRIPTS_DOWNLOAD"}]</span>
    </a>
</div>

<hr class="mt10 mb15" />

<div style="display:none;" id="inventoryObjects">
    [{$g_list}]
</div>
