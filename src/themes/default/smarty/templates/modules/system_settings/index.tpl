[{if !$disableTabs}]
<div class="bg-white">
	<h3 class="fr m10">
		<a href="?moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&moduleSubID=[{$smarty.get.moduleSubID|strip_tags|escape}]&pID=[{$smarty.get.pID|escape}]&treeNode=[{$smarty.get.treeNode|strip_tags|escape}]&expert">
			[{isys type="lang" ident="LC__SYSTEM_SETTINGS__EXPERT_SETTINGS"}]
		</a>
	</h3>

	<ul id="objectTabs" class="m0 gradient browser-tabs">
		<li><a href="#system" data-tab="#system">[{isys type="lang" ident="LC__SYSTEM_SETTINGS__SYSTEM_WIDE"}]</a></li>
		<li><a href="#tenant" data-tab="#tenant">[{$tenantTab|default:"Tenant"}]</a></li>
	</ul>
[{else}]
<div class="bg-white border-bottom">
[{/if}]
	<div id="system">
		[{foreach $definition as $headline => $definition_content}]
		<h3 class="p5 gradient border-top border-bottom">[{isys type="lang" ident=$headline}]</h3>

		<table class="contentTable p0 mb10">
			<colgroup>
				<col style="width:170px;"/>
				<col style="width:360px"/>
			</colgroup>
			[{foreach $definition_content as $key => $setting}]
			[{if !isset($setting.hidden)}]
			<tr>
				<td class="key vat">
					<label for="[{$key}]">[{isys type="lang" ident=$setting.title}]</label>
				</td>
				<td class="pl20 vat">
					[{if $setting.type == 'select'}]

						<select name="settings[[{$systemWideKey}]][[{$key}]]" id="[{$key}]" class="input input-mini">
							[{foreach from=$setting.options item="optionTitle" key="option"}]
								<option value="[{$option}]" [{if (isset($settings.$key) && $option == $settings.$key) || (!isset($settings.$key) && $option == $setting.default)}]selected="selected"[{/if}]>[{isys type="lang" ident=$optionTitle}]</option>
							[{/foreach}]
						</select>

					[{elseif $setting.type == 'textarea'}]
						[{capture name="defaultValue" assign="defaultValue"}][{isys type="lang" ident=$setting.placeholder}][{/capture}]
						<textarea rows="8" class="input input-small" placeholder="[{$defaultValue}]" id="[{$key}]" name="settings[[{$systemWideKey}]][[{$key}]]">[{$settings.$key|default:$setting.default}]</textarea>

					[{elseif $setting.type == 'password'}]

						[{isys
							type="f_password"
							name="settings[`$systemWideKey`][`$key`]"
							id=$key
							p_strValue=$settings.$key|default:$setting.default
							p_strPlaceholder=$setting.placeholder
							p_strClass="input-small"
							p_bInfoIconSpacer=0}]

					[{else}]

						[{isys
							type="f_text"
							name="settings[`$systemWideKey`][`$key`]"
							id=$key
							p_strValue=$settings.$key|default:$setting.default
							p_strPlaceholder=$setting.placeholder
							p_strClass="input-small"
							p_validation_rule=$setting.type
							p_bInfoIconSpacer=0}]

					[{/if}]
				</td>
				<td class="pl5">
					[{if isset($setting.description)}]
					<img src="[{$dir_images}]icons/silk/information.png" class="vam" alt="*"/> [{isys type="lang" p_bHtmlEncode=false ident=$setting.description}]
					[{/if}]
				</td>
			</tr>
			[{/if}]
			[{/foreach}]
		</table>
		[{/foreach}]
	</div>

	<div id="tenant" class="mt5">
		[{foreach from=$tenant_definition item="definition_content" key="headline"}]
		<h3 class="p5 gradient border-bottom border-top">[{isys type="lang" ident=$headline}]</h3>

		<table class="contentTable p0 mb10">
			<colgroup>
				<col style="width:170px;"/>
				<col style="width:360px"/>
			</colgroup>
			[{foreach from=$definition_content item="setting" key="key"}]
			[{if !isset($setting.hidden)}]
			<tr>
				<td class="key vat">
					<label for="[{$key}]">[{isys type="lang" ident=$setting.title}]</label>
				</td>
				<td class="pl20 vat">

					[{if $setting.type == 'select'}]

						<select name="settings[[{$tenantWideKey}]][[{$key}]]" id="[{$key}]" class="input input-mini">
						[{foreach from=$setting.options item="optionTitle" key="option"}]
							<option value="[{$option}]" [{if (isset($tenant_settings.$key) && $tenant_settings.$key == $option) || (!isset($tenant_settings.$key) && $option == $setting.default)}]selected="selected"[{/if}]>[{isys type="lang" ident=$optionTitle}]</option>
						[{/foreach}]
						</select>

					[{elseif $setting.type == 'textarea'}]

						<textarea rows="8" class="input input-small" placeholder="[{$setting.placeholder}]" id="[{$key}]" name="settings[[{$tenantWideKey}]][[{$key}]]">[{$tenant_settings.$key|default:$setting.default}]</textarea>

					[{elseif $setting.type == 'password'}]

						[{isys
						type="f_password"
						name="settings[`$tenantWideKey`][`$key`]"
						id=$key
						p_strValue=$tenant_settings.$key|default:$setting.default
						p_strPlaceholder=$setting.placeholder
						p_strClass="input-small"
						p_bInfoIconSpacer=0}]

					[{else}]

						[{isys
						type="f_text"
						name="settings[`$tenantWideKey`][`$key`]"
						id=$key
						p_strValue=$tenant_settings.$key|default:$setting.default
						p_strPlaceholder=$setting.placeholder
						p_strClass="input-small"
						p_validation_rule=$setting.type
						p_bInfoIconSpacer=0}]

					[{/if}]
				</td>
				<td class="pl5">
					[{if isset($setting.description)}]
						<img src="[{$dir_images}]icons/silk/information.png" class="vam" alt="*"/> [{isys type="lang" p_bHtmlEncode=false ident=$setting.description}]
					[{/if}]
				</td>
			</tr>
			[{/if}]
			[{/foreach}]
		</table>
		[{/foreach}]
	</div>
</div>

<script type="text/javascript">
	(function(){
		'use strict';

		var $system = $('system');

		[{if !$disableTabs}]
		if ($('objectTabs')) {
			new Tabs('objectTabs', {
				wrapperClass: 'browser-tabs',
				contentClass: 'browser-tab-content',
				tabClass: 'text-shadow mouse-pointer'
			});
		}

		if ($system) {
			$system.down('h3.border-top').addClassName('mt5');
		}
		[{else}]
		if ($system) {
			$system.down('h3.border-top').removeClassName('border-top');
		}
		[{/if}]

        $('contentWrapper').on('change', '[data-validation-rule]', function (ev) {
            var $element   = ev.findElement('input'),
                value = $element.getValue(),
                newValue = value,
                rule = $element.readAttribute('data-validation-rule');

            switch (rule) {
	            case 'int':
	                // Remove everything that is not a digit.
                    newValue = parseInt(value.replace(/\D/g, ''));

                    if (isNaN(newValue)) {
                        newValue = 0;
                    }

	                break;

	            case 'float':
                    // First replace all commas with dots. Then remove everything that is not a digit or a dot. Then remove all dots from the beginning and end
                    newValue = parseFloat(value.replace(/,/g, '.').replace(/[^\d.]/g, '').replace(/(^\.+|\.+$)/, ''));

                    if (isNaN(newValue)) {
                        newValue = 0;
                    }

                    break;
            }

            value = value.toString();
            newValue = newValue.toString();

            if (value !== newValue) {
                idoit.Notify.info('[{isys type="lang" ident="LC__CMDB__SANITATION__CHANGED_VALUE" p_bHtmlEncode=false}]'.replace('%s', value.encodeHTML()).replace('%s', newValue.encodeHTML()), {life:10});
            }

            $element.setValue(newValue);
        });

        })();
</script>
