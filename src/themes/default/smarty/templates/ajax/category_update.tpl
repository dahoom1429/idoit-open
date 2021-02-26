<script type="text/javascript">
	(function () {
		'use strict';

		var $contentbottomcontent = $('contentBottomContent'),
		    $csrf_field = $('_csrf_token'),
			failedElements = [], i, $el, $img;

		[{if !empty($csrf_value)}]
		if ($csrf_field) {
			$csrf_field.setValue('[{$csrf_value}]');
		}
		[{/if}]

		$contentbottomcontent.select('img[data-validation-error]').each(function($el) {
			Tips.remove($el.removeClassName('mouse-pointer').writeAttribute({src: '[{$dir_images}]empty.gif', 'data-validation-error': null}));
		});

		// Remove all "error" fields, before adding new ones. See: ID-1664.
		$contentbottomcontent.select('.input-error').invoke('removeClassName', 'input-error');

		[{foreach $validation_errors as $key => $error}]
		failedElements.push({
			id: '[{$key}]',
			message: '[{$error.message|escape:"javascript"}]'
		});
		[{/foreach}]

		for (i in failedElements) {
			if (failedElements.hasOwnProperty(i)) {
				$el = $(failedElements[i].id);

				if (!$el) {
					$el = $contentbottomcontent.down('[name="' + failedElements[i].id + '"]');
				}

				if (!$el) {
					$el = $(failedElements[i].id + '__VIEW');
				}

				if ($el) {
					$el.addClassName('input-error');
					$img = $el.previous('img.infoIcon');

					if (!$img) {
						$img = $el.up('td').down('img.infoIcon');
					}

					if ($img) {
						new Tip($img.addClassName('mouse-pointer').writeAttribute({src: '[{$dir_images}]icons/alert-icon.png', 'data-validation-error': 1}),
							new Element('p', {
								className: 'p5',
								style: 'font-size:12px;'
							}).update(failedElements[i].message),
							{showOn: 'click', hideOn: 'click', style: 'darkgrey'});
					}
				}
			}
		}

		var $breadcrumbElements = $$('#breadcrumb li a');

		[{if $smarty.post.C__CATG__GLOBAL_TITLE}]
		$breadcrumbElements.forEach(function (element) {
			if (element.textContent == 'ID: [{$smarty.get.objID}]') {
				element.textContent = '[{$smarty.post.C__CATG__GLOBAL_TITLE|escape}]';
			}
		});
		[{/if}]

		[{if $smarty.post.C__OBJTYPE__IMG_NAME}]
			var objTypeImagesHelpFields = $$('#objTypeImagesHelp td'),
				selected = $$('#objTypeImagesHelp td[title="[{$smarty.post.C__OBJTYPE__IMG_NAME}]"]');

			objTypeImagesHelpFields.forEach(function (field) {
				field.removeClassName('selected');
			});

			selected[0].addClassName('selected');
		[{/if}]

		var $navbarLink = $$('#navbar_item_8');
		$navbarLink[0].href = '?[{$current_link}]';

		// ID-7656: Updating target of cancel button to redirect correctly
        var $cancelButton = $('navbar_item_C__NAVMODE__CANCEL');
        if ($cancelButton) {
            $cancelButton.setAttribute('onClick', 'document.isys_form.navMode.value=[{$smarty.const.C__NAVMODE__CANCEL}];form_submit();');
        }

		var $contentBottom = $('contentBottomContent');

		// ID-3140: Update p_strSelectedID after update according to chosen dialog value
        $contentBottom.select('select').forEach(function (dialog) {
			var $strSelectedID = $contentBottom.down('input[name="SM2__' + dialog.name + '[p_strSelectedID]"]');
			if ($strSelectedID) {
                $strSelectedID.setValue(dialog.getValue());
			}
		});

		// ID-6986: Update regular inputs' p_strValue after update
        $contentBottom.select('input[type="text"]').forEach(function(input) {
            var $strValue = $contentBottom.down('input[name="SM2__' + input.name + '[p_strValue]"]');
            if ($strValue) {
                $strValue.setValue(input.getValue());
            }
        });

		[{if $categoryID}]
		// Add category ID to our action parameters.
		change_action_parameter('cateID', '[{$categoryID}]');
		[{/if}]

		[{if $redirectAfterSave}]
		// Redirect if necessary. This will be used, when header("Location: ..."); is not possible (ajax save).
		window.location.href = '[{$redirectAfterSave}]';
		[{/if}]

		[{if $smarty.get.objID > 0}]
		// Reload tree.
		get_tree_by_object('[{$smarty.get.objID|strip_tags|escape}]', [{$smarty.const.C__CMDB__VIEW__TREE_OBJECT}],
			[{if isset($smarty.get.catgID)}]'[{$smarty.get.catgID|strip_tags|escape}]'[{else}]null[{/if}],
			[{if isset($smarty.get.catsID)}]'[{$smarty.get.catsID|strip_tags|escape}]'[{else}]null[{/if}]);
		[{/if}]
	})();
</script>
