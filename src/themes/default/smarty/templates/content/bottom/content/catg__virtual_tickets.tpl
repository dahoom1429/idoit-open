<input type="hidden" id="identificator_search" value="0" />

<div class="p10">
	[{if $ticket_new_url.use_queue != 0}]
		<select id="select_queue" name="queue_name" class="input input-small mr10">
			[{foreach $ticket_new_url.select_queue as $ticket_key => $ticket_data}]
				<option value="[{$ticket_key}]">[{$ticket_data}]</option>
			[{/foreach}]
		</select>
	[{/if}]

	<button id="new_ticket" class="btn">
		<img src="[{$dir_images}]icons/silk/page_white.png" class="mr5" /><span>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_NEW"}]</span>
	</button>
</div>

[{if is_array($workstation)}]

	[{foreach from=$workstation.components key="ticket" item="ticket_object"}]
		<h2 class="gradient p10"><a class="black" href="?objID=[{$ticket_object.object_id}]">[{$ticket_object.object_title}] ([{$ticket_object.object_type}])</a></h2>
		<table class="m10 listing" id="tickets_table" cellpadding="0" cellspacing="0">
			<thead>
			<tr>
				<th>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_SUBJECT"}]</th>
				<th>URL</th>
			</tr>
			</thead>
			<tbody>
			[{if (is_array($ticket_object.tickets))}]
				[{foreach from=$ticket_object.tickets key="ticket_id" item="ticket"}]
					[{if ($ticket_id > 0)}]
						<tr class="listRow mouse-pointer [{cycle values="odd,even"}]" data-trigger="ticketDataTemplate_[{$ticket_id}]">
							<td>[{$ticket.subject}]</td>
							<td>
								<a href="[{$ticket.link}]" class="btn" target="_blank">
									<img src="[{$dir_images}]icons/silk/link.png" class="mr5" />
									<span>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__OPEN_TTS"}]</span>
								</a>
							</td>
						</tr>
						<tr style="display:none;">
							<td colspan="2"></td>
						</tr>
					[{else}]
						<tr class="no_tickets">
							<td colspan="2">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__NO_TICKETS_FOR_OBJECT"}]</td>
						</tr>
					[{/if}]
				[{/foreach}]
			[{/if}]
			</tbody>
		</table>
	[{/foreach}]
[{else}]
	<table class="listing m10 mainTable mainTableHover" id="tickets_table" cellpadding="0" cellspacing="0">
		<thead>
		<tr>
			<th>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_SUBJECT"}]</th>
			<th>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_QUEUE"}]</th>
			<th>[{isys type="lang" ident="LC__UNIVERSAL__STATUS"}]</th>
			<th>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_PRIORITY"}]</th>
			<th>[{isys type="lang" ident="LC__UNIVERSAL__DATE_CREATED"}]</th>
			<th>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_LASTUPDATED"}]</th>
			<th></th>
		</tr>
		</thead>
		<tbody>
		[{if ($tts_processing_error)}]
			<tr>
				<td colspan="7">
					<div class="box-red p5">
						<p>[{$tts_processing_error}]</p>
					</div>
				</td>
			</tr>
		[{else}]
			[{if is_array($tickets) && count($tickets) > 0}]
				[{foreach $tickets as $ticket_id => $ticket}]
					[{if $ticket_id > 0}]
						<tr class="listRow mouse-pointer [{cycle values="odd,even"}]" data-trigger="ticketDataTemplate_[{$ticket_id}]">
							<td data-sort="[{$ticket.subjectsort}]">[{$ticket.subject}]</td>
							<td data-sort="[{$ticket.queue}]">[{$ticket.queue}]</td>
							<td data-sort="[{$ticket.status}]">[{$ticket.status}]</td>
							<td data-sort="[{$ticket.priority}]">[{$ticket.priority}]</td>
							<td data-sort="[{$ticket.created}]">[{$ticket.created}]</td>
							<td data-sort="[{$ticket.lastupdated}]">[{$ticket.lastupdated}]</td>
							<td data-sort="[{$ticket.link}]">
								<a href="[{$ticket.link}]" class="btn" target="_blank">
									<img src="[{$dir_images}]icons/silk/link.png" class="mr5" />
									<span>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__OPEN_TTS"}]</span>
								</a>
							</td>
						</tr>
						<tr class="hide" id="ticketDataTemplate_[{$ticket_id}]">
							<td colspan="7">
								<table class="contentTable">
									<tbody>
									<tr>
										<td class="key">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_OWNER"}]</td>
										<td class="value">[{$ticket.owner}]</td>
									</tr>
									<tr>
										<td class="key">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_REQUESTOR"}]</td>
										<td class="value">[{$ticket.requestor}]</td>
									</tr>
									<tr>
										<td class="key">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_STARTTIME"}]</td>
										<td class="value">[{$ticket.starts}]</td>
									</tr>
									<tr>
										<td class="key">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_LASTUPDATED"}]</td>
										<td class="value">[{$ticket.lastupdated}]</td>
									</tr>
									<tr>
										<td class="key">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_CATEGORY"}]</td>
										<td class="value">[{$ticket.customcategory}]</td>
									</tr>
									<tr>
										<td class="key">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_OBJECTS"}]</td>
										<td class="value">[{$ticket.customobjects}]</td>
									</tr>
									<tr>
										<td class="key">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_OBJPRIORITY"}]</td>
										<td class="value">[{$ticket.custompriority}]</td>
									</tr>
									</tbody>
								</table>
							</td>
						</tr>
					[{/if}]
				[{/foreach}]

			[{else}]
				<tr class="no_tickets">
					<td colspan="7">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__NO_TICKETS_FOR_OBJECT"}]</td>
				</tr>
			[{/if}]
		[{/if}]
		</tbody>
	</table>
[{/if}]

<script type="text/javascript">
    (function () {
        'use strict';

        $('tickets_table').on('click', 'tr[data-trigger]', function (ev) {
            var $tr = $(ev.findElement('tr').readAttribute('data-trigger'));

            if ($tr) {
                $tr.toggleClassName('hide');
            }
        });

        $('new_ticket').on('click', function () {
            var $selectQueue = $('select_queue'), queue_option;

            if ([{$ticket_new_url.use_queue|default:'null'}] != 0 && $selectQueue) {
                queue_option = $selectQueue.getValue();
            }

            window.open('[{$ticket_new_url.url}]' + (queue_option || ''), '_blank');
        });

        $('tickets_table').on('click', 'th', function(ev) {
            var $table, rows, switching, currentRowIndex, currentRowDetailsIndex, nextRowIndex, nextRowDetailsIndex, currentRow, nextRow, currentValue, nextValue, selectedColumn, shouldSwitch, dir, switchcount = 0;
            $table = $("tickets_table");
            switching = true;
            dir = 'desc';
            while (switching) {
                switching = false;
                rows = $table.rows;
                if (rows.length == 0) {
                    break;
                }
                for (currentRowIndex = 1; currentRowIndex < rows.length; currentRowIndex++) {
                    currentRowDetailsIndex = currentRowIndex + 1;
                    nextRowIndex = currentRowIndex + 2;
                    nextRowDetailsIndex = currentRowIndex + 3;
                    currentRow = rows[currentRowIndex];
                    nextRow = rows[nextRowIndex];
                    if (!currentRow.hasClassName('listRow') || nextRow == null) {
                        continue;
                    }
                    currentValue = currentRow.getElementsByTagName('TD')[ev.target.cellIndex].readAttribute('data-sort').toLowerCase();
                    nextValue = nextRow.getElementsByTagName('TD')[ev.target.cellIndex].readAttribute('data-sort').toLowerCase();
                    if ((dir == 'desc' && currentValue < nextValue) || (dir == 'asc' && currentValue > nextValue)) {
                        shouldSwitch = true;
                        break;
                    }
                }
                if (shouldSwitch && rows[currentRowIndex + 3]) {
                    rows[currentRowIndex].parentNode.insertBefore(rows[nextRowIndex], rows[currentRowIndex]);
                    rows[currentRowIndex].parentNode.insertBefore(rows[nextRowDetailsIndex], rows[currentRowDetailsIndex]);
                    switching = true;
                    switchcount++;
                } else if (switchcount == 0 && dir == "desc") {
                    dir = "asc";
                    switching = true;
                }
            }

            selectedColumn = $table.getElementsByClassName('selectedColumn');
            if (selectedColumn.length > 0) {
                selectedColumn[0].removeClassName('selectedColumn').removeClassName('desc').removeClassName('asc');
            }

            switch (dir)
            {
                case 'asc':
                    ev.target.addClassName('asc').addClassName('selectedColumn');
                    break;
                case 'desc':
                    ev.target.addClassName('desc').addClassName('selectedColumn');
                    break;
            }
        });
    })();
</script>