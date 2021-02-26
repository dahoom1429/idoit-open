<fieldset class="overview">
    <legend><span>[{$C__OCS__NAME}] ([{$C__OCS__ID}]):</span></legend>

    <table class="contentTable">
        <!--<tr>
            <td class="key">[{isys type="lang" ident="LC__OBJTYPE__OPERATING_SYSTEM"}]:</td>
            <td class="value pl10"> [{$C__OCS__OS_NAME}]</td>
        </tr>-->
        <!--<tr>
            <td class="key">CPU:</td>
            <td class="value pl10"> [{$C__OCS__PROCESSOR}]</td>
        </tr>-->
        <!--<tr>
            <td class="key">[{isys type="lang" ident="LC__CMDB__CATG__CPU_FREQUENCY"}]:</td>
            <td class="value pl10"> [{$C__OCS__CPU_SPEED}] MHz</td>
        </tr>-->
        <!--<tr>
            <td class="key">[{isys type="lang" ident="LC__CMDB__CATG__MEMORY"}]:</td>
            <td class="value pl10"> [{$C__OCS__MEMORY}] MB</td>
        </tr>-->
        <tr>
            <td class="key">[{isys type="lang" ident="LC__CATP__IP__ADDRESS"}]:</td>
            <td class="value pl10"> [{$C__OCS__IP}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type="lang" ident="LC__MODULE__IMPORT__OCS__IMPORTED"}]:</td>
            <td class="value pl10"> [{if $imported != null}]<span class="green">[{isys_locale::get_instance()->fmt_date($imported)}][{else}]<span class="grey">[{isys type="lang" ident="LC__MODULE__IMPORT__NOT_IMPORTED"}][{/if}]</span></td>
        </tr>
        <tr>
            <td class="key">[{isys type="lang" ident="LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE"}]:</td>
            <td class="value pl10"> [{html_options name=C__OCS__OBJTYPE id=C__OCS__OBJ_TYPE options=$object_types selected=$objTypeID}]</td>
        </tr>
        <tr>
            <td class="key">
                [{isys type="f_label" name="ocs_overwrite_hostaddress_port_single" ident="LC__MODULE__OCS_IMPORT__OVERWRITE_HOSTADDRESS_AND_PORTS"}]
            </td>
            <td class="value pl10">
                [{html_options name="ocs_overwrite_hostaddress_port_single" id="ocs_overwrite_hostaddress_port_single" options=$yes_no_selection selected=0}]
            </td>
        </tr>
        <tr>
            <td class="key">
                [{isys type="f_label" name="ocs_overwrite_all_cats_single" ident="LC__MODULE__OCS_IMPORT__OVERWRITE_ALL_CATEGORIES"}]
            </td>
            <td class="value pl10">
                [{html_options name="ocs_overwrite_all_cats_single" id="ocs_overwrite_all_cats_single" options=$yes_no_selection selected=0}]
            </td>
        </tr>
	    <tr>
		    <td class="key">
			    [{isys type="f_label" name="ocs_logging" ident="LC__MODULE__JDISC__IMPORT__LOGGING"}]
		    </td>
		    <td class="value pl10">
			    <select name="" id="ocs_logging_single">
				    <option value="0" selected="selected">[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__LOGGING_LESS"}]</option>
				    <option value="1">[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__LOGGING_DETAIL"}]</option>
				    <option value="2">[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__LOGGING_DEBUG"}]</option>
			    </select>
		    </td>
	    </tr>
    </table>
</fieldset>

<div>
    <fieldset class="overview">
        <legend><span>[{isys type="lang" ident="LC__MODULE__OCS_IMPORT__CATEGORY_SELECTION"}]:</span></legend>

        <p class="p10">
            <label class="bold"><input type="checkbox" class="categories" onclick="CheckAllBoxes(this, 'categories')"
                                       checked="checked" /> [{isys type="lang" ident="LC__CMDB__RECORD_STATUS__ALL"}]</label><br /><br />

            <label><input type="checkbox" class="categories" checked="checked" name="category[]"
                          value="[{$smarty.const.C__CATG__CPU}]" /> [{isys type="lang" ident=LC__CMDB__CATG__CPU}]</label><br />
            <!--<label><input type="checkbox" class="categories" checked="checked" name="category[]"
                          value="[{$smarty.const.C__CATG__MEMORY}]" /> [{isys type="lang" ident=LC__CMDB__CATG__MEMORY}]</label><br />-->
            <label><input type="checkbox" class="categories" checked="checked" name="category[]"
                          value="[{$smarty.const.C__CATG__APPLICATION}]" /> [{isys type="lang" ident=LC__CMDB__CATG__APPLICATION}]</label><br />
            <label><input type="checkbox" class="categories" checked="checked" name="category[]"
                          value="[{$smarty.const.C__CATG__NETWORK}]" /> [{isys type="lang" ident=LC__CMDB__CATG__NETWORK}]</label><br />
            <label><input type="checkbox" class="categories" checked="checked" name="category[]"
                          value="[{$smarty.const.C__CATG__STORAGE}]" /> [{isys type="lang" ident=LC__CATG__STORAGE}]</label><br />
            <!--<label><input type="checkbox" class="categories" checked="checked" name="category[]"
                          value="[{$smarty.const.C__CATG__GRAPHIC}]" /> [{isys type="lang" ident=LC__CMDB__CATG__GRAPHIC}]</label><br />-->
            <!--<label><input type="checkbox" class="categories" checked="checked" name="category[]"
                          value="[{$smarty.const.C__CATG__SOUND}]" /> [{isys type="lang" ident=LC__CMDB__CATG__SOUND}]</label><br />-->
            <label><input type="checkbox" class="categories" checked="checked" name="category[]"
                          value="[{$smarty.const.C__CATG__MODEL}]" /> [{isys type="lang" ident=LC__CMDB__CATG__MODEL}]</label><br />
            <label><input type="checkbox" class="categories" checked="checked" name="category[]"
                          value="[{$smarty.const.C__CATG__UNIVERSAL_INTERFACE}]" /> [{isys type="lang" ident=LC__CMDB__CATG__UNIVERSAL_INTERFACE}]</label><br />
            <!--<label><input type="checkbox" class="categories" checked="checked" name="category[]"
                          value="[{$smarty.const.C__CATG__DRIVE}]" /> [{isys type="lang" ident=LC__STORAGE_DRIVE}]</label>-->
        </p>
    </fieldset>
</div>

<div class="m5">
    [{isys type="f_button" icon="images/icons/silk/arrow_left.png" p_strValue="LC__UNIVERSAL__BACK" p_onClick="ocs_list();" p_bEditMode=1}]
    [{isys type="f_button" icon="images/icons/silk/database_copy.png" p_strValue="LC__UNIVERSAL__IMPORT" p_bEditMode="1" p_onClick="ocs_import($C__OCS__ID, $('C__OCS__OBJ_TYPE').value, true);"}]
</div>
