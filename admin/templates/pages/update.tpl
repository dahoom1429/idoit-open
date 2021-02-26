<script type="text/javascript">
	// This needs to be implemented ABOVE the content area.
	var formdata = new Hash,
		default_pager_callback = function () {return true;};

	// This will be overwritten by the single "step"-templates (if necessary).
	window.prev_callback = window.next_callback = default_pager_callback;
</script>

<div class="gradient content-header">
	<img src="../images/icons/silk/arrow_refresh.png" class="vam mr5" /><span class="bold text-shadow headline vam">Update</span>
</div>

<div id="innercontent">
	[{if $error}]
	<div id="error" class="error p10 mt0"><strong>Error:</strong><br /><br />[{$error}]</div>
	[{/if}]

	[{if $output}]
	<div id="note" class="note p10 mt0">[{$output}]</div>
	[{/if}]

	<table width="100%">
		<tr>
			<td style="width:20%; vertical-align: top;">
				<ul class="update-steps">
					[{foreach from=$steps key="key" item="step"}]
					<li><span class="step">[{$key}]</span> <span>[{$step.title}]</span><img class="fr" src="../images/empty.gif" /></li>
					[{/foreach}]
				</ul>

				<button type="button" id="update-button-prev" class="btn" accesskey="p"><img src="../images/icons/silk/bullet_arrow_left.png" /><span>Previous</span></button>
				<button type="button" id="update-button-next" class="btn fr mr15" accesskey="n"><span>Next</span><img src="../images/icons/silk/bullet_arrow_right.png" /></button>
			</td>
			<td style="width:80%; vertical-align: top;">
				<form id="update-form">
					<div id="update-error" class="error p5 hide mb10"></div>
					<div id="update-content">[{$update_content}]</div>
				</form>
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	if ($('update-button-next'))
	{
		$('update-button-next').focus();
	}

	var list_of_steps = $$('ul.update-steps li'),
		current_step = parseInt('[{$current_step}]'),
		current_step_el = list_of_steps[0],
		blingbling_interval = 0,
		blingbling_effect;

	list_of_steps.invoke('removeClassName', 'active').each(function(el, i) {
		i ++;

		if (i == current_step) {
			el.addClassName('active');

			current_step_el = el;
		}

		if (i > current_step) {
			el.addClassName('inactive');
		}
	});

	$('update-button-next').on('click', function () {
		if (! window.next_callback()) {
			return false;
		}

		// We disable both buttons, so we can't skip a step by "fast clicking" before the next step was loaded via ajax.
		$('update-button-prev').disable();
		$('update-button-next').disable();

		if (blingbling_interval > 0) {
			clearInterval(blingbling_interval);
			blingbling_effect.cancel();
			current_step_el.removeAttribute('style');

			blingbling_interval = 0;
		}

		$('update-error').addClassName('hide');
		$('update-content').setOpacity(0.5);

		current_step_el.down('img').writeAttribute('src', '../images/ajax-loading-red.gif');

		formdata.set('step-' + current_step, $('update-form').serialize());

		new Ajax.Request('?req=update&step=' + (current_step + 1),
			{
				method: 'post',
				parameters:{current_formdata:Object.toJSON(formdata.toJSON())},
				onSuccess: function (response) {
					var json = response.responseJSON;

					if (json.success) {
						// Remove the "active" state and change the ajax-loader to a tick.
						current_step_el.removeClassName('active').down('img').writeAttribute('src', '../images/icons/silk/tick.png');

						// Set the "current step element" to the next one.
						current_step_el = list_of_steps[current_step].removeClassName('inactive').addClassName('active');

						// After the GUI updates, we can change the step-counter.
						current_step ++;

						// Now we insert the loaded step into the HTML.
						$('update-content').setOpacity(1).update(json.data);

						window.prev_callback = window.next_callback = default_pager_callback;

						first_last_callback();

						if ($('update-button-next'))
						{
							$('update-button-next').focus();
						}
					} else {
						// Remove the "active" state and change the ajax-loader to a tick.
						current_step_el.down('img').writeAttribute('src', '../images/icons/silk/error.png');

						// Display the content area normally.
						$('update-content').setOpacity(1);

						// We do not need to reset the "prev_callback" and "next_callback" functions, because the page remains unchanged.
						$('update-error').removeClassName('hide').update(json.message);

						blingbling_interval = setInterval(function () {
							blingbling_effect = new Effect.Highlight(current_step_el);
						}.bind(this), 2500);
					}
				}
			});
	});

	$('update-button-prev').on('click', function () {
		if (! window.prev_callback()) {
			return false;
		}

		// We disable both buttons, so we can't skip a step by "fast clicking" before the last step was loaded via ajax.
		$('update-button-prev').disable();
		$('update-button-next').disable();

		if (blingbling_interval > 0) {
			clearInterval(blingbling_interval);
			blingbling_effect.cancel();
			current_step_el.removeAttribute('style');

			blingbling_interval = 0;
		}

		$('update-error').addClassName('hide');
		$('update-content').setOpacity(0.5);

		new Ajax.Request('?req=update&step=' + (current_step - 1),
			{
				method: 'post',
				onSuccess: function (response) {
					var json = response.responseJSON;

					if (json.success) {
						// Remove the "active" state and change the ajax-loader to a tick.
						current_step_el.removeClassName('active').addClassName('inactive').down('img').writeAttribute('src', '../images/empty.gif');

						// Set the "current step element" to the next one.
						current_step_el = list_of_steps[current_step - 2].addClassName('active');
						current_step_el.down('img').writeAttribute('src', '../images/empty.gif');

						// After the GUI updates, we can change the step-counter.
						current_step --;

						// Now we insert the loaded step into the HTML.
						$('update-content').setOpacity(1).update(json.data);

						window.prev_callback = window.next_callback = default_pager_callback;

						first_last_callback();
					}
				}
			});
	});

	function first_last_callback () {
		$('update-button-next', 'update-button-prev').invoke('enable').invoke('removeClassName', 'disabled');

		if (current_step == 1 || current_step > 4) {
			$('update-button-prev').disable().addClassName('disabled');
		}

		if (current_step == list_of_steps.length) {
			$('update-button-next').disable().addClassName('disabled');
		}
	}

	first_last_callback();
</script>