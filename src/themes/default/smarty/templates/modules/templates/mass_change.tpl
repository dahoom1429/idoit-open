<script type="text/javascript">
[{include file="modules/templates/templates.js"}]
</script>

<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__MASS_CHANGE"}]</h2>

<div class="p10">
	<h3 class="mb5">1. [{isys type="lang" ident="LC__MASS_CHANGE__CHOOSE_OBJECTS_TO_BE_CHANGED"}]</h3>

    [{isys
        name="selected_objects"
        type="f_popup"
        id="object_browser"
        multiselection=true
        p_bInfoIconSpacer=0
        p_strPopupType="browser_object_ng"
        callback_accept="idoit.callbackManager.triggerCallback('activate-mass-change-btn');"
        callback_detach="idoit.callbackManager.triggerCallback('activate-mass-change-btn');"}]

	<br class="cb" />

	<h3 class="mt15 mb5">2. [{isys type="lang" ident="LC__MASS_CHANGE__SELECT_TEMPLATE_FOR_MASS_CHANGES"}]</h3>

	[{if !$hasTemplates}]
		<p>[{isys type="lang" ident="LC__MASS_CHANGE__MASS_CHANGES_DESCRIPTION_CONTENT"}]</p>
		<br />
	[{/if}]

	<label>
		[{isys type="f_dialog" name="templates" p_bSort=false p_onChange="select_single_template(this);" disableInputGroup=true p_bInfoIconSpacer=0}]
	</label>

	[{if !empty($field_disabled)}]
	<span id="C__CATG__IP__MESSAGES" class="input box-red">[{isys type="lang" ident="LC__MASS_CHANGE__NO_TEMPLATES_AVAILABLE"}]</span>
	[{/if}]

	<div class="container mt5" id="selected_templates">
		<div class="sortable">
			<ul id="template_list" class="list-style-none m0 p0">
			</ul>
		</div>
	</div>
	<div class="cb mb5"></div>

    <h3 class="mb5" style="margin-top: 1em; margin-bottom: 0.5em;">3. [{isys type='lang' ident='LC__MASS_CHANGE__OPTIONS'}]</h3>

    <h4 style="margin-top: 1em; margin-bottom: 0.5em;">3.1 [{isys type='lang' ident='LC__MASS_CHANGE__HANDLING_EMPTY_FIELDS'}]</h4>

    <label><input type="radio" value="[{$keep}]" name="empty_fields" checked="checked" [{$field_disabled}]/> [{isys type='lang' ident='LC__MASS_CHANGE__IGNORE_EMPTY_FIELDS'}]</label><br />
    <label><input type="radio" value="[{$clear}]" name="empty_fields" [{$field_disabled}]/> [{isys type='lang' ident='LC__MASS_CHANGE__CLEAR_FIELDS'}]</label>

    <h4 style="margin-top: 1em; margin-bottom: 0.5em;">3.2 [{isys type='lang' ident='LC__MASS_CHANGE__HANDLING_MULTI-VALUED_CATEGORIES'}]</h4>

    <label><input type="radio" value="[{$untouched}]" name="multivalue_categories" checked="checked" [{$field_disabled}]/> [{isys type='lang' ident='LC__MASS_CHANGE__KEEP_CATEGORY_ENTRIES_UNTOUCHED'}]</label><br />
    <label><input type="radio" value="[{$add}]" name="multivalue_categories" [{$field_disabled}]/> [{isys type='lang' ident='LC__MASS_CHANGE__ADD_CATEGORY_ENTRIES'}]</label><br />
    <label><input type="radio" value="[{$delete_add}]" name="multivalue_categories" [{$field_disabled}]/> [{isys type='lang' ident='LC__MASS_CHANGE__DELETE_BEFORE_ADD_CATEGORY_ENTRIES'}]</label><br />

	<h4 style="margin-top: 1em; margin-bottom: 0.5em;">3.3 [{isys type='lang' ident='LC__MASS_CHANGE__LOG_LEVEL'}]</h4>

	<label>
		<input type="radio" name="log-level" value="C__ERROR" checked="checked" />
		[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__LOGGING__SIMPLE"}]
	</label><br />
	<label>
		<input type="radio" name="log-level" value="C__INFO" />
		[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__LOGGING__NORMAL"}]
	</label><br />
	<label>
		<input type="radio" name="log-level" value="C__DEBUG" />
		[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__LOGGING__ALL"}]
	</label><br />

    <h4 style="margin-top: 1em; margin-bottom: 0.5em;">3.4 [{isys type='lang' ident='LC__MASS_CHANGE__OTHER_OPTIONS'}]</h4>
    <label>
        <input type="checkbox" name="overwrite-cmdb-status" checked>
        [{isys type="lang" ident="LC__MASS_CHANGE__OVERWRITE_CMDB_STATUS"}]
    </label><br />

    <h3 class="mb5" style="margin-top: 1em; margin-bottom: 0.5em;">4. [{isys type="lang" ident="LC__MASS_CHANGE__APPLY_MASS_CHANGE"}]</h3>

    [{isys type="f_submit" id="apply_mass_change" name="apply_mass_change" p_bDisabled=1 p_strValue="LC__MASS_CHANGE__APPLY_MASS_CHANGE" p_bEditMode="1"}]

    <img style="display:none;" id="loader" class="vam mr5" src="images/ajax-loading.gif" />

	<br />

	<iframe id="iframe" name="iframe" src="" class="mt10 border" style="width:50%;height:250px;display:none;"></iframe>
</div>

<script type="text/javascript">
    (function() {

        var activate_mass_change_btn = function()
        {
            if($('templates').value != -1 && $('selected_objects__HIDDEN').value != '')
            {
                $('apply_mass_change').removeClassName('disabled');
                $('apply_mass_change').removeAttribute('disabled');
            }
            else
            {
                $('apply_mass_change').addClassName('disabled');
                $('apply_mass_change').writeAttribute('disabled');
            }
        };

        new $('apply_mass_change').on('click', function() {
            $('isys_form').target = 'iframe';
            $('loader').show();
            $('iframe').appear();

            delay(function () {
                $('loader').hide();
            }, 3500, 'masschange.loader');
        });

        idoit.callbackManager.registerCallback('activate-mass-change-btn', activate_mass_change_btn);

        $('templates').on('change', activate_mass_change_btn);
    })();
</script>
