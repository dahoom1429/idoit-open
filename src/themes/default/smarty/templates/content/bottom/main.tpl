[{isys_group name="content" id="contentbottomcontent"}]

<div id="contentBottom">
	<div id="contentBottomContent">
		<div id="scroller" [{if $scrollerVisible}]class="overflow-scroller"[{/if}]>
			[{if isset($index_includes.contentbottomcontentadditionbefore)}]
				[{include file=$index_includes.contentbottomcontentadditionbefore}]
			[{/if}]

			[{include file=$index_includes.contentbottomcontent|default:"content/bottom/content/main.tpl"}]

			[{if isset($index_includes.contentbottomcontentaddition)}]
				[{if is_array($index_includes.contentbottomcontentaddition)}]
					[{foreach from=$index_includes.contentbottomcontentaddition item=addition_tpl}]
						[{include file=$addition_tpl}]
					[{/foreach}]
				[{else}]
					[{include file=$index_includes.contentbottomcontentaddition}]
				[{/if}]
			[{/if}]

			[{if $bShowCommentary == "1"}]
			<table class="contentTable commentaryTable" style="border-top:none;">
	            <tr>
	                <td class="key" style="vertical-align: top;">[{isys type="f_label" name="C__CMDB__CAT__COMMENTARY_$commentarySuffix" ident="LC__CMDB__CAT__COMMENTARY"}]</td>
					<td>[{isys type="f_wysiwyg" name="C__CMDB__CAT__COMMENTARY_$commentarySuffix"}]</td>
				</tr>
			</table>
			[{/if}]

			<input id="LogbookCommentary" name="LogbookCommentary" type="hidden" value="" />

			[{if isset($index_includes.contentbottomcontentadditionafter)}]
				[{include file=$index_includes.contentbottomcontentadditionafter}]
			[{/if}]
		</div>
	</div>
	[{/isys_group}]

	<script type="text/javascript">
		var $contentbottomcontent = $('contentBottomContent'),
			$csrf_field = $('_csrf_token');

		// Hide all open tooltips.
		Tips.hideAll();

		[{if !empty($csrf_value)}]
		if ($csrf_field) {
			$csrf_field.setValue('[{$csrf_value}]');
		}
		[{/if}]

		[{if isys_glob_is_edit_mode()}]
		var mandatory_fields = $contentbottomcontent.select('[data-mandatory-rule="1"]'); // .validate-mandatory

		if (mandatory_fields.length > 0)
		{
			var i, $el, $label;

			for (i in mandatory_fields)
			{
				if (mandatory_fields.hasOwnProperty(i))
				{
					$el = mandatory_fields[i];

					if ($label = $contentbottomcontent.down('label[for=' + $el.id + ']'))
					{
						// If we have a "label", we can be much more precise.
						$label.insert(new Element('strong', {className: 'text-red'}).update('*'));
					}
					else if ($el.up('td') && $el.up('td').previous('td.key'))
					{
						// This is the fallback, if no label could be found.
						$el.up('td').previous('td.key').insert(new Element('strong', {className: 'text-red'}).update('*'));
					}
				}
			}
		}

		// On every change we want to check for validation-issues.

        idoit.Require.require('validation', function () {
            $contentbottomcontent.on('change', '[data-validation-rule="1"]', function (ev) { // .validate-rule
                var $element = ev.findElement().removeClassName('box-red'),
                    identifier = $element.readAttribute('data-identifier'),
                    category = '',
                    validation = new Validation($element, {images_path:'[{$dir_images}]'});

                if ($element.retrieve('validating', false)) {
                    return;
                }

                $element.store('validating', true);

                // This is a kind of fallback for the overview-page.
                if (identifier == '' || identifier == null) {
                    identifier = $element.name;
                    if ($element.up('fieldset')) {
                        category = $element.up('fieldset').id
                    }
                }

                new Ajax.Request('?call=validate_field&ajax=1&func=validate', {
                    method: 'post',
                    parameters: {
                        identifier: identifier,
                        element_value: $element.getValue(),
                        category: category,
                        obj_id: ('[{$smarty.get.objID|strip_tags|escape}]' || '[{$object_id}]'),
                        obj_type_id: ('[{$smarty.get.objTypeID|strip_tags|escape}]' || '[{$object_type_id}]'),
                        category_entry_id: ('[{$smarty.get.cateID|strip_tags|escape}]' || '[{$category_entry_id}]')
                    },
                    onComplete: function (result) {
                        var json = result.responseJSON;

                        $element.store('validating', false);

                        if (json.success) {
                            validation.success();
                        } else {
                            validation.fail(json.message);
                        }

                        // Freeing memory.
                        validation = null;
                    }
                });
            });
        });
		[{/if}]

		// Enable chosen.
		$contentbottomcontent.select('select.chosen-select').each(function ($element) {
			var width = null;

			if ($element.getWidth() === 0)
			{
				width = '100%';
			}

			new Chosen($element, {
				default_multiple_text:     '[{isys type="lang" ident="LC__UNIVERSAL__CHOOSEN_PLACEHOLDER" p_bHtmlEncode=false}]',
				placeholder_text_multiple: '[{isys type="lang" ident="LC__UNIVERSAL__CHOOSEN_PLACEHOLDER" p_bHtmlEncode=false}]',
				no_results_text:           '[{isys type="lang" ident="LC__UNIVERSAL__CHOOSEN_EMPTY" p_bHtmlEncode=false}]',
				disable_search_threshold:  10,
				search_contains:           true,
				width:                     width
			});
		});


		[{if $scrollerVisible}]
        (function () {
            'use strict';

            var $scroller         = $('scroller'),
                $contentWrapper   = $('contentWrapper'),
                $contentHeader    = $('contentHeader'),
                $overflowScroller = new Element('div', {style: 'position:relative; height:100%; width:100%; overflow:auto;'}),
                resize            = Prototype.emptyFunction;

            if ($scroller.hasClassName('overflow-scroller'))
            {
                // Move the complete HTML inside our new "overflow scroller".
                $overflowScroller.update($scroller.innerHTML);

                // Then directly append the overflow scroller to the scroller.
                $scroller.update($overflowScroller);

                resize = function () {
                    var height = $contentWrapper.getHeight() - $contentHeader.getHeight();

                    $overflowScroller.setStyle({height: 'auto'});

                    if ($overflowScroller.getHeight() >= height) {
                        $overflowScroller.setStyle({height: (height - 1) + 'px'});
                    }
                };

                Event.observe(window, 'resize', resize);

                resize();
            }
        })();
		[{/if}]
	</script>
</div>
