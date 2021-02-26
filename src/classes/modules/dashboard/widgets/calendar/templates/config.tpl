<div id="widget-config-popup">
	<table class="contentTable">
		<tr>
			<td class="key">
				[{isys type="f_label" name="widget-config-title" ident="LC__WIDGET__CALENDAR_CONFIG__TITLE"}]
			</td>
			<td class="value">
				[{isys type="f_text" id="widget-config-title" p_strClass="input-small" p_strValue=$rules.title}]
			</td>
		</tr>
		<tr>
			<td class="key">
				[{isys type="f_label" name="widget-popup-config-obj-events" ident="LC__WIDGET__CALENDAR_CONFIG__DISPLAY_OBJ_EVENTS"}]
			</td>
			<td>
				<input type="checkbox" class="ml20 mt5" name="widget-popup-config-obj-events" id="widget-popup-config-obj-events" [{if $rules.object_events}]checked="checked"[{/if}] /><br />
				<p class="ml20">[{isys type="lang" ident="LC__WIDGET__CALENDAR_CONFIG__DISPLAY_OBJ_EVENTS_DESCRIPTION"}]</p>
			</td>
		</tr>
		<!-- <tr>
			<td class="key">
				[{isys type="f_label" name="widget-popup-config-holiday-events" ident="LC__WIDGET__CALENDAR_CONFIG__DISPLAY_HOLIDAY_EVENTS"}]
			</td>
			<td>
				<input type="checkbox" class="ml20 mt5" name="widget-popup-config-holiday-events" id="widget-popup-config-holiday-events" [{if $rules.holiday_events}]checked="checked"[{/if}] /><br />
			</td>
		</tr> -->
	</table>

	<h3 class="p5 gradient text-shadow border mt10">[{isys type="lang" ident="LC__WIDGET__CALENDAR_CONFIG__ADD_NEW_EVENT"}]</h3>
	<table class="contentTable" style="border-top: none;">
		<tr>
			<td class="key">
				[{isys type="f_label" name="event-title" ident="LC__WIDGET__CALENDAR_CONFIG__EVENT_NAME"}]
			</td>
			<td class="value">
				[{isys type="f_text" id="event-title" p_strClass="input-small"}]
			</td>
		</tr>
		<tr>
			<td class="key">
				[{isys type="f_label" name="event-date" ident="LC__WIDGET__CALENDAR_CONFIG__EVENT_DATE"}]
			</td>
			<td class="value">
				[{isys type="f_popup" name="event-date" p_strPopupType="calendar" p_bTime="0" p_strClass="input-mini" p_strPlaceholder="dd.mm.yyyy"}]
			</td>
		</tr>
		<tr>
			<td class="key">
				[{isys type="f_label" name="event-type" ident="LC__WIDGET__CALENDAR_CONFIG__EVENT_TYPE"}]
			</td>
			<td class="value">
				[{isys type="f_dialog" name="event-type" p_arData=$event_types p_strClass="input-small" p_bDbFieldNN=true}]
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<button type="button" id="event-add-button" class="btn btn-small ml20">
					<img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__WIDGET__CALENDAR_CONFIG__ADD_NEW_EVENT"}]</span>
				</button>
			</td>
		</tr>
	</table>

	<div class="mt10 gradient p5 border">[{isys type="lang" ident="LC__WIDGET__CALENDAR_CONFIG__EXISTING_EVENT"}]</div>
	<ul id="widget-config-list">
		[{foreach from=$events item=event}]
		<li data-name="[{$event.name}]" data-date="[{$event.date}]" data-type="[{$event.type}]">
			<img src="[{$dir_images}]icons/silk/cross.png" class="fr delete mouse-pointer" alt="[{isys type="lang" ident="LC__WIDGET__CALENDAR_CONFIG__DELETE_EVENT"}]" title="[{isys type="lang" ident="LC__WIDGET__CALENDAR_CONFIG__DELETE_EVENT"}]" />
			<span class="handle">&nbsp;&nbsp;&nbsp;</span>
			[{$event.date}]: [{$event.name}] ([{$event.LC_type}])
		</li>
		[{/foreach}]
	</ul>
</div>

<script type="text/javascript">
	function update_config () {
		var event_list = $$('#widget-config-list li'),
			event_list_length = event_list.length,
			i,
			events = [],
			config = {
				'title':$F('widget-config-title'),
				'object_events':$('widget-popup-config-obj-events').checked
//				'holiday_events':$('widget-popup-config-holiday-events').checked
			};

		$('widget-popup-config-changed').setValue(1);

		if (event_list_length > 0) {
			for (i=0; i<event_list_length; i++) {
				events.push({name:event_list[i].readAttribute('data-name'), date:event_list[i].readAttribute('data-date'), type:event_list[i].readAttribute('data-type')});
			}

			config.events = events;
		}

		$('widget-popup-config-hidden').setValue(Object.toJSON(config));
	}

	function reset_observer () {
		Sortable.destroy('widget-config-list');

		$('widget-config-title', 'widget-popup-config-obj-events'/*, 'widget-popup-config-holiday-events'*/)
			.invoke('stopObserving')
			.invoke('on', 'change', update_config);

		Sortable.create('widget-config-list', {
			handle:'handle',
			onChange:update_config
		});
	}

	$('event-add-button').on('click', function () {
		$('event-title', 'event-date__VIEW').invoke('up', 'tr').invoke('removeClassName', 'box-red');

		if ($F('event-title').blank()) {
			$('event-title').up('tr').addClassName('box-red');
			return;
		}

		if ($F('event-date__VIEW').blank()) {
			$('event-date__VIEW').up('tr').addClassName('box-red');
			return;
		}

		var event_name = $F('event-title'),
			event_date = $F('event-date__VIEW'),
			event_type = $F('event-type');

		$('widget-config-list').insert(new Element('li', {'data-name':event_name, 'data-date':event_date, 'data-type':event_type})
			.update(new Element('img', {src:'[{$dir_images}]icons/silk/cross.png', className:'fr delete mouse-pointer', alt:'[{isys type="lang" ident="LC__WIDGET__CALENDAR_CONFIG__DELETE_EVENT"}]', title:'[{isys type="lang" ident="LC__WIDGET__CALENDAR_CONFIG__DELETE_EVENT"}]'}))
			.insert(new Element('span', {className:'vam handle'}).update('&nbsp;&nbsp;&nbsp;'))
			.insert(new Element('span', {className:'vam'}).update(' ' + event_date + ': ' + event_name + ' (' + $$('#event-type option:selected')[0].innerHTML + ')')));

		update_config();
		reset_observer();
	});

	$('widget-config-list').on('click', '.delete', function (ev) {
		ev.findElement().up('li').remove();
		update_config();
	});

	update_config();
	reset_observer();
</script>