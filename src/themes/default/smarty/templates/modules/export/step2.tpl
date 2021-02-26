<script type="text/javascript">
function show_categories(p_object){
    if(p_object.value == "csv"){
        $('categorie_list').hide();
        $('csv_import_types').show();
    } else{
        $('categorie_list').show();
        $('csv_import_types').hide();
    }
}
</script>

<style type="text/css">
    .export-label-float {
        float: left;
        width: 220px;
        height: auto;
        margin: 5px;
        cursor: pointer;
    }
</style>

<h2 class="p10 gradient">[{isys type='lang' ident='LC__CMDB__EXPORT'}]</h2>

<fieldset class="overview">
    [{if $smarty.post.export_filter eq '1'}]

    <legend><span>[{isys type='lang' ident='LC__UNIVERSAL__STEP_2'}]: [{isys type='lang' ident='LC__CMDB__EXPORT__FILTER_OBJECTS'}]</span></legend>

    <div class="pt20 p10">
        <input type="hidden" name="object_ids" id="object_ids" value="[]" />
        [{isys name="object_ids" type="f_popup" p_bInfoIconSpacer=0 p_strPopupType="browser_object_ng" multiselection=true}]
        <br class="cb" />
    </div>

    [{elseif $smarty.post.export_filter eq '2'}]

    <legend><span>[{isys type='lang' ident='LC__UNIVERSAL__STEP_2'}]: [{isys type='lang' ident='LC__CMDB__EXPORT__FILTER_OBJECT_TYPES'}]</span></legend>

    <div class="pt20 p10">
        <label>
            <input type="checkbox" onclick="CheckAllBoxes(this, 'objecttypes')" />
            [{isys type='lang' ident='LC__UNIVERSAL__SELECT_ALL_OBJECT_TYPES'}]
        </label>

        <br />

        <div style="width:60%;">
            [{foreach from=$objecttypes key=i item=ot}]
            <label class="export-label-float">
                <input type="checkbox" name="objecttype[]" class="objecttypes" value="[{$ot.isys_obj_type__id}]" /> [{isys type='lang' ident=$ot.isys_obj_type__title}]
            </label>
            [{/foreach}]
        </div>
    </div>

    [{elseif $smarty.post.export_filter eq '3'}]

    <legend><span>[{isys type='lang' ident='LC__UNIVERSAL__STEP_2'}]: [{isys type='lang' ident='LC__CMDB__EXPORT__FILTER_LOCATION'}]</span></legend>

    <div class="pt20 p10">
        [{isys name="C__CATG__LOCATION_PARENT" id="C__CATG__LOCATION_PARENT" type="f_popup" p_strPopupType="browser_location" p_strStyle="width:600px;" p_bInfoIconSpacer=0 containers_only=true}]
        <br class="cb" />
    </div>

    [{elseif $smarty.post.export_filter eq '100'}]

    [{/if}]
</fieldset>

<fieldset class="overview">
    <legend><span>[{isys type='lang' ident='LC__UNIVERSAL__STEP_3'}]: [{isys type='lang' ident='LC__UNIVERSAL__FORMAT'}]</span></legend>

    <div class="pt20 p10">
        <label>
            <div class="fl">[{isys type='lang' ident='LC__UNIVERSAL__FORMAT'}]</div>
            [{isys type="f_dialog" name="type" p_arData=$export_types onchange="show_categories(this);" p_strClass="input-mini" p_bDbFieldNN=1}]
            <br class="cb" />
        </label>
    </div>
</fieldset>

<fieldset class="overview">
    <legend><span>[{isys type='lang' ident='LC__UNIVERSAL__STEP_4'}]: [{isys type='lang' ident='LC__CMDB__EXPORT__FILTER_CATEGORIES'}]</span></legend>

    <div id="categorie_list" class="pt20 p10">
        <h4>[{isys type='lang' ident='LC__CMDB__OBJTYPE__CATG'}]</h4>
        <label class="m5 display-block">
            <input checked="checked" onclick="$('categories').select('input.categories').invoke('disable');" type="radio" name="all_categories" value="all"/>
            [{isys type='lang' ident='LC__CMDB__EXPORT__ALL_CATEGORIES'}]
        </label>
        <label class="m5 display-block">
            <input type="radio" onclick="$('categories').select('input.categories').invoke('enable');" name="all_categories" value="spec"/>
            [{isys type='lang' ident='LC__CMDB__EXPORT__SELECTED_CATEGORIES'}]
        </label>

        <div id="categories">
            <label class="m5">
                <input type="checkbox" class="categories" onclick="CheckAllBoxes(this, 'categories')" disabled="disabled" checked="checked"/>
                [{isys type='lang' ident='LC__UNIVERSAL__SELECT_ALL_CATEGORIES'}]
            </label>

            <br/>

            <div style="width: 60%">
                [{foreach $categories as $cat}]
                <label class="export-label-float">
                    <input type="checkbox" class="categories" disabled="disabled" [{if $cat.id != $smarty.const.C__CATG__LOGBOOK}]checked="checked"[{/if}] name="category[]" value="[{$cat.id}]"/>
                    [{isys type="lang" ident=$cat.title}]
                </label>
                [{/foreach}]
            </div>

            <br class="cb"/>

            <h4 class="mt10">[{isys type='lang' ident='LC__CMDB__OBJTYPE__CATS'}]</h4>
            <div style="width: 60%">
                <label class="export-label-float">
                    <input type="checkbox" name="export_specific_categories" checked="checked"/>
                    [{isys type='lang' ident='LC__MODULE__EXPORT__EXPORT_SPECIFIC_CATG'}]
                </label>
            </div>

            <br class="cb"/>

            [{if (!empty($custom_categories))}]
            <h4 class="mt10">[{isys type='lang' ident='LC__CMDB__CATG__CUSTOM_CATEGORY'}]</h4>

            <label class="m5 display-block">
                <input type="checkbox" checked="checked" onclick="$('custom-categories').select('input').invoke('setValue', (this.checked ? 1 : 0))" />
                [{isys type='lang' ident='LC__UNIVERSAL__SELECT_ALL_CATEGORIES'}]
            </label>

            <div id="custom-categories" style="width: 60%">
                [{foreach $custom_categories as $cat}]
                <label class="export-label-float">
                    <input type="checkbox" checked="checked" name="custom_category[]" value="[{$cat.isysgui_catg_custom__id}]"/>
                    [{isys type="lang" ident=$cat.isysgui_catg_custom__title}]
                </label>
                [{/foreach}]
            </div>
            [{/if}]

            <br class="cb"/>
        </div>

        <p class="mb5" id="csv_import_types" style="display:none;">
            Type: [{html_options name=csv_type options=$csv_export_types}]
        </p>
    </div>
</fieldset>

<fieldset class="overview">
    <legend><span>[{isys type='lang' ident='LC__UNIVERSAL__STEP_5'}]: [{isys type='lang' ident='LC__SETTINGS__SYSTEM__OPTIONS'}]</span></legend>

    <div class="pt20 p10">
        <label><input type="radio" value="0" name="export_save" /> [{isys type='lang' ident='LC__CMDB__EXPORT__VIEW'}]</label><br />
        <label><input type="radio" value="1" name="export_save" checked="checked" /> [{isys type='lang' ident='LC__CMDB__EXPORT__DOWNLOAD'}]</label><br />
        <label><input type="radio" value="2" name="export_save" /> [{isys type='lang' ident='LC__CMDB__EXPORT__SAVE_AS'}]</label>
        <input type="text" name="export_save_filename" size="25" value="temp/idoit_export.xml" class="input input-small" />
    </div>
</fieldset>

<fieldset class="overview">
    <legend><span>[{isys type='lang' ident='LC__UNIVERSAL__STEP_6'}]: [{isys type='lang' ident='LC__UNIVERSAL__TEMPLATE'}]</span></legend>

    <div class="pt20 p10">
        <label>
            <input type="checkbox" name="options_save" value="1" />&nbsp;[{isys type='lang' ident='LC__CMDB__EXPORT__CREATE_TEMPLATE'}]
        </label>
        <br />
        <label>
            [{isys type='lang' ident='LC__UNIVERSAL__TITLE'}] <input type="text" name="options_save_filename" size="25" value="" class="input input-small" />
        </label>
    </div>
</fieldset>

<div class="p10">
    <input type="hidden" id="step" name="step" value="3" />
    <input type="hidden" id="step" name="export_filter" value="[{$smarty.post.export_filter}]" />
    <input type="submit" class="btn" onclick="$('step').value = '1';" value="[{isys type='lang' ident='LC__UNIVERSAL__BACK'}]" />
    <input type="submit" class="btn" value="[{isys type='lang' ident='LC__CMDB__EXPORT__START'}]" />
</div>