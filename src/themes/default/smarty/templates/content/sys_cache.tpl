<style type="text/css">
	fieldset {
		border: 1px solid #ccc;
	}

	fieldset legend {
		padding: 0 5px;
	}

    #sys_overlay {
        background: rgba(255, 255, 255, 0.5);
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 40;
    }

	#sys_cache div#cache.box,
	#sys_cache div#database.box,
	#sys_cache div#cmdbContent.box {
		width: 320px;
		margin-right: 10px;
		border-top: none;
	}

	#sys_cache div#cmdbContent.box {
		margin-right: 0;
	}

	#sys_cache div.box h3 {
		border-top: 1px solid #b7b7b7;
		border-bottom: 1px solid #b7b7b7;
	}

	#sys_cache #loading img,
	#sys_cache #loading span {
		vertical-align: middle;
	}

	#sys_cache #ajax_return ul {
		list-style: none;
		margin: 0;
	}

	#sys_cache #ajax_return ul i {
		color: #888;
	}
</style>

<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__SYSTEM__CACHE_DB"}]</h2>

<div id="sys_cache" class="p10" style="position:relative;">
    <div id="sys_overlay"></div>

    <p class="mb10 p5 box-yellow" style="position:relative;z-index: 50;">
        <img src="[{$dir_images}]icons/silk/error.png" class="mr5 vam" />
        <span class="vam">[{isys type="lang" ident="LC__SYSTEM__CACHE_DB__OVERLAY_WARNING" p_bHtmlEncode=false}]</span><br />
        <button type="button" class="btn mt10" onclick="$(this, 'sys_overlay').invoke('remove');">[{isys type="lang" ident="LC__SYSTEM__CACHE_DB__OVERLAY_WARNING_UNDERSTOOD"}]</button>
    </p>

	<div id="cache" class="box fl">
		<h3 class="gradient p5">[{isys type="lang" ident="LC__UNIVERSAL__CACHE"}]</h3>

		<div class="m10">
			[{foreach $cache_buttons as $name => $button}]
				[{isys type="f_button" p_bDisabled="0" p_onClick=$button.onclick p_strValue=$name p_strClass="btn-block mb5 `$button.css`" p_strStyle=$button.style}]
			[{/foreach}]
		</div>
	</div>

	<div id="database" class="box fl">
		<h3 class="gradient p5">[{isys type="lang" ident="LC__UNIVERSAL__DATABASE"}]</h3>

		<div class="m10">
			[{foreach $database_buttons as $name => $button}]
				[{isys type="f_button" p_bDisabled="0" p_onClick=$button.onclick p_strValue=$name p_strClass="btn-block mb5 `$button.css`" p_strStyle=$button.style}]
			[{/foreach}]
		</div>

		<h3 class="gradient p5 mt15">Export</h3>
		<div class="m10">
			<label for="mysqldump">"mysqldump" [{isys type="lang" ident="LC__UNIVERSAL__PATH"}]</label>
			<input type="text" id="mysqldump" class="input input-block mt5" name="mysqldump" size="40" value="[{$mysqldump}]"/>

			<table width="100%" class="mt15 mb15">
				<tr>
					<td>
						[{isys type="lang" ident="LC__UNIVERSAL__DATABASE"}]
					</td>
					<td>
						<label><input type="checkbox" checked="checked" id="system" name="system" value="1"/> System</label><br/>
						<label><input type="checkbox" checked="checked" id="mandator" name="mandator" value="1"/> [{isys type="lang" ident="LC__LOGIN__MANDATOR"}]</label>
					</td>
				</tr>
			</table>

            [{isys type="f_button" p_bDisabled="0" p_onClick="window.export_database();" p_strValue="Export"}]
		</div>
	</div>

    <div id="cmdbContent" class="box fl">
        <h3 class="gradient p5">[{isys type="lang" ident="LC__CMDB__CATG__OBJECT"}]</h3>
        <div class="m10" id="objects">
            [{foreach $object_buttons as $name => $button}]
            [{isys type="f_button" p_bDisabled="0" p_onClick=$button.onclick p_strValue=$name p_strClass="mb5 `$button.css`" p_strStyle=$button.style}]
            [{if $button.query}]<a href="#" class="btn fr mb5" data-query="[{$button.query}]"><img src="[{$dir_images}]icons/eye.png" alt="[{isys type="lang" ident="LC__UNIVERSAL__PREVIEW"}]" title="[{isys type="lang" ident="LC__UNIVERSAL__PREVIEW"}]" /></a>[{/if}]
            [{/foreach}]
	        <br class="cb" />
        </div>

        <h3 class="gradient p5">[{isys type="lang" ident="LC_UNIVERSAL__CATEGORIES"}]</h3>
        <div class="m10" id="categories">
            [{foreach $category_buttons as $name => $button}]
            [{isys type="f_button" p_bDisabled="0" p_onClick=$button.onclick p_strValue=$name p_strClass="btn-block mb5 `$button.css`" p_strStyle=$button.style}]
            [{/foreach}]
        </div>

        <h3 class="gradient p5">[{isys type="lang" ident="LC__SYSTEM__CACHE_DB__OTHERS"}]</h3>
        <div class="m10" id="others">
            [{foreach $other_buttons as $name => $button}]
            [{isys type="f_button" p_bDisabled="0" p_onClick=$button.onclick p_strValue=$name p_strClass="btn-block mb5 `$button.css`" p_strStyle=$button.style}]
            [{/foreach}]
        </div>
    </div>
	<div class="cb mb10"></div>
</div>

<fieldset class="overview">
	<legend><span>[{isys type="lang" ident="LC__SETTINGS__SYSTEM__SYS_MSG"}]</span></legend>
	<div class="p10 bg-white">
		<div id="loading" style="display: none;">
			<img src="[{$dir_images}]ajax-loading.gif" alt="[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]" />
			<span>[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span>
		</div>
		<div id="ajax_return"></div>
	</div>
</fieldset>

<input type="hidden" id="query" name="query" />

<script type="text/javascript">
    var ajax_start_count    = 0,
        ajax_finished_count = 0,
        $resultContainer    = $('ajax_return');

    window.disableButtons = function (identifier) {
        if (identifier !== '') {
            $$('#' + identifier + ' button').forEach(function (cacheButton) {
                if (cacheButton.value !== 'Export') {
                    cacheButton.disable();
                }
            });
        } else {
            var cacheButton = this.event.findElement('button');

            cacheButton.disable();
        }
    };

    window.flush_cache = function (type, $button) {
        $('loading').show();
        // If we get "true" as parameter, we flush every cache.
        if (type === true) {
            $$('.cache-button:not(:disabled)').invoke('simulate', 'click');
        } else {
            if (Object.isElement($button)) {
                $button.disable();
            }

            ajax_start_count++;
            new Ajax.Request('?ajax=1&' + type, {
                method:    'post',
                onSuccess: function (response) {
                    $resultContainer.insert({top:new Element('hr', {className:'mt5 mb5'})}).insert({top:response.responseText});
                    window.highlight_response();
                }
            });
        }
    };

    window.flush_database = function (type, confirmation, $button) {
        if (Object.isString(confirmation) && !confirmation.blank() && !confirm(confirmation)) {
            return;
        }

        $('loading').show();

        if (Object.isElement($button)) {
            $button.disable();
        }

        ajax_start_count++;

        new Ajax.Request('?ajax=1&moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&what=cache&do=' + type, {
            method:    'post',
            onSuccess: function (response) {
                $resultContainer.insert({top:new Element('hr', {className:'mt5 mb5'})}).insert({top:response.responseText});
                window.highlight_response();
            }
        });
    };

    window.flush_validation_cache = function ($button) {
        if (Object.isElement($button)) {
            $button.disable();
        }

        new Ajax.Request('?call=validate_field&ajax=1&func=reset_validation_cache', {
            method:    'post',
            onSuccess: function (transport) {
                var json = transport.responseJSON,
                    $msg = new Element('p');

                if (json.success) {
                    $msg.update('[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__CACHE_REFRESH"}]... ' + json.data + '!');
                } else {
                    $msg.addClassName('red').update('[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__CACHE_REFRESH"}]... ' + json.message + '!');
                }

                $resultContainer.insert({top:new Element('hr', {className:'mt5 mb5'})}).insert({top:$msg});
            }
        });
    };

    window.flush_objects = function (type, message, $button) {
        new Ajax.Request('?ajax=1&moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&what=cache&do=db_list_objects&param=' + type, {
            method:    'post',
            onSuccess: function (response) {
                $resultContainer.insert({top:new Element('hr', {className:'mt5 mb5'})}).insert({top:response.responseText});
                window.highlight_response();
            }
        });

        if (confirm(message)) {
            if (Object.isElement($button)) {
                $button.disable();
            }

            $('loading').show();
            // Remove objects with status as defined in "type".
            new Ajax.Request('?ajax=1&moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&what=cache&do=db_cleanup_objects&param=' + type, {
                method:    'post',
                onSuccess: function (response) {
                    $resultContainer.insert({top:new Element('hr', {className:'mt5 mb5'})}).insert({top:response.responseText});
                    window.highlight_response();
                    $('loading').hide();
                }
            });
        }
    };

    window.flush_categories = function (type, message, $button) {
        if (confirm(message)) {
            if (Object.isElement($button)) {
                $button.disable();
            }

            $('loading').show();
            // Remove objects with status as defined in "type".
            new Ajax.Request('?ajax=1&moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&what=cache&do=db_cleanup_categories&param=' + type, {
                method:    'post',
                onSuccess: function (response) {
                    $resultContainer.insert({top:new Element('hr', {className:'mt5 mb5'})}).insert({top:response.responseText});
                    window.highlight_response();
                    $('loading').hide();
                }
            });
        }
    };

    /**
     * Use this function to provide the backend with credentials (for added security).
     *
     * @param type
     * @param message
     * @param $button
     */
    window.processSecureAction = function (type, message, $button) {
        // Notify the uer about what is going to happen next.
        if (!confirm('[{isys type="lang" ident="LC__SYSTEM__CACHE_DB__SECURE" p_bHtmlEncode=false}]')) {
            return;
        }

        // Ask the user for credentials.
        var username = prompt('[{isys type="lang" ident="LC__SYSTEM__CACHE_DB__SECURE__ENTER_ADMIN_USER" p_bHtmlEncode=false}]', 'Username');
        var password = prompt('[{isys type="lang" ident="LC__SYSTEM__CACHE_DB__SECURE__ENTER_ADMIN_PASS" p_bHtmlEncode=false}]', 'Password');

        window.flush_other(type, message, $button, {user: username, pass: password});
    };

    window.flush_other = function (type, message, $button, params) {
        if (confirm(message)) {
            if (Object.isElement($button)) {
                $button.disable();
            }

            $('loading').show();
            // Remove objects with status as defined in "type".
            new Ajax.Request('?ajax=1&moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&what=cache&do=cleanup_other&param=' + type, {
                method:    'post',
                parameters: params || null,
                onSuccess: function (response) {
                    $resultContainer.insert({top:new Element('hr', {className:'mt5 mb5'})}).insert({top:response.responseText});
                    window.highlight_response();
                    $('loading').hide();
                }
            });
        }
    };

    window.export_database = function () {
        new Ajax.Request('?ajax=1&moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&what=cache&do=export', {
            method:     'post',
            parameters: {
                mysqldump: $('mysqldump').getValue(),
                system:    $('system').getValue(),
                mandator:  $('mandator').getValue()
            },
            onSuccess:  function (response) {
                $resultContainer.insert({top:new Element('hr', {className:'mt5 mb5'})}).insert({top:response.responseText});
                window.highlight_response();
            }
        });
    };

    window.search_index = function ($button) {
        if (confirm('[{isys type="lang" ident="LC__MODULE__SEARCH__START_INDEXING_CONFIRMATION" p_bHtmlEncode=false}]')) {
            if (Object.isElement($button)) {
                $button.disable();
            }

            $('loading').show();
            // Remove objects with status as defined in "type".
            new Ajax.Request('?ajax=1&moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&what=cache&do=search_index', {
                method:    'post',
                onSuccess: function (response) {
                    $resultContainer.insert({top:new Element('hr', {className:'mt5 mb5'})}).insert({top:response.responseText});
                    window.highlight_response();
                    $('loading').hide();
                }
            });
        }
    };

    window.highlight_response = function () {
        ajax_finished_count++;

        if (ajax_start_count == ajax_finished_count) {
            $('loading').hide();
        }

        new Effect.Highlight('ajax_return', {
            duration:     0.5,
            startcolor:   '#ffff99',
            endcolor:     '#ffffff',
            restorecolor: '#ffffff'
        });
    };

	(function(){
		'use strict';

		[{if file_exists($report_sql_path)}]
		[{include file=$report_sql_path}]

		$('cmdbContent').select('a[data-query]').invoke('on', 'click', function (ev) {
			var $button = ev.findElement('a');
			$('query').setValue($button.readAttribute('data-query'));
			get_popup('report', '', 800, 508, {func:'report_preview_sql'});
		});
		[{else}]
		$('cmdbContent').select('a[data-query]').invoke('addClassName', 'disabled');
		[{/if}]
	})();
</script>
