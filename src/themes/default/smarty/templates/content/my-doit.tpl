<input type="hidden" id="mydoitAction" name="mydoitAction" />

<h2 class="p5 gradient text-shadow">
	<a href="javascript:" class="fr" onclick="mydoit_trigger();">&times;</a>
	my-doit
</h2>
[{if file_exists('src/themes/default/smarty/templates/content/status.tpl')}]
	[{include file="content/status.tpl"}]
[{/if}]

<h3 class="p5 gradient text-shadow status-headline">
    <img src="[{$dir_images}]icons/silk/heart.png" class="vam mr5" />
    [{isys type="lang" ident="LC__MYDOIT__BOOKMARKS"}]
</h3>

<table class="listing">
	<tr>
		<td colspan="2" class="p5">
			<table width="100%" class="text-bold">
				<tr>
					<td style="width:50%">
						<button type="button" class="btn btn-small btn-block" onClick="mydoit_addBookmark();">
							<img src="[{$dir_images}]icons/silk/page_add.png" class="mr5" />
							<span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]</span>
						</button>
					</td>
					<td style="width:50%" class="pl5">
						<button type="button" class="btn btn-small btn-block" onClick="mydoit_deleteBookmark();">
							<img src="[{$dir_images}]icons/silk/page_delete.png" class="mr5" />
							<span>[{isys type="lang" ident="LC__NAVIGATION__NAVBAR__DELETE"}]</span>
						</button>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	[{*
		Dieser Block wird wiederholt und stellt jeweils einen Bookmark dar:
		my_doit.bookmarks = array(
			id => array(link => "...", text => "..."),
			id => array(link => "...", text => "..."),
			id => array(link => "...", text => "..."),
			id => array(link => "...", text => "..."),
			...
		)
	*}]
	[{if $mydoit.bookmarkCount ne 0}]
		[{foreach from=$mydoit.bookmarkList key="id" item="bookmark"}]
			<tr>
				<td style="width:15px;">
					<input type="checkbox" name="mydoitSelection[[{$id}]]" />
				</td>
				<td>
					<a href="[{$bookmark.link}]">[{$bookmark.text}]</a>
				</td>
			</tr>
		[{/foreach}]
	[{else}]
		<tr>
			<td colspan="2" class="p5">
				<span>[{isys type="lang" ident="LC__CMDB__FILTER__NOTHING_FOUND_STD"}]</span>
			</td>
		</tr>
	[{/if}]
	</table>

	<table cellpadding="0" cellspacing="0" border="0" style="border:0;width:100%;margin-bottom:25px;">
	<tr>
		<th colspan="2">
			<h3 class="p5 gradient text-shadow status-headline">
				<img src="[{$dir_images}]task/task.gif" class="vam" /> <span class="vam">[{isys type="lang" ident="LC__WORKFLOWS__MY"}]</span>
			</h3>
		</th>
	</tr>
    [{if isys_module_manager::instance()->is_active('workflow')}]
	<tr>
		<td colspan="2">
			<div id="tasks">
				<h4 class="m5">
				<img style="vertical-align:middle;" src="[{$dir_images}]task/task__assigned.gif" border="0">
				[{isys type="lang" ident="LC__WORKFLOW__ASSIGNED_TASKS"}]
				</h4>
				[{counter start=0 print=false}]

				[{if is_array($g_tasks__assigned) && count($g_tasks__assigned)>0}]
					[{foreach from=$g_tasks__assigned item=l_my_tasks}]
					<ul class="my_tasks">
						<li class="task_date">(<strong>[{counter}].</strong>) [{$l_my_tasks.date}]</li>
						<li class="task_link"><a href="[{$l_my_tasks.link}]">[{$l_my_tasks.title}]</a></li>
					</ul>
					[{/foreach}]
				[{else}]
					<span class="m5">[{isys type="lang" ident="LC__CMDB__FILTER__NOTHING_FOUND_STD"}]</span>
				[{/if}]

				<h4 class="m5">
				<img style="vertical-align:middle;" src="[{$dir_images}]task/task__assigned.gif" border="0">
				[{isys type="lang" ident="LC__WORKFLOW__ACTION__TYPE__ACCEPT"}]
				</h4>
				[{counter start=0 print=false}]

				[{if is_array($g_tasks__accepted) && count($g_tasks__accepted)>0}]
					[{foreach from=$g_tasks__accepted item=l_ac_tasks}]
					<ul class="my_tasks">
						<li class="task_date">(<strong>[{counter}].</strong>) [{$l_ac_tasks.date}]</li>
						<li class="task_link"><a href="[{$l_ac_tasks.link}]">[{$l_ac_tasks.title}]</a></li>
					</ul>
					[{/foreach}]
				[{else}]
					<span class="m5">[{isys type="lang" ident="LC__CMDB__FILTER__NOTHING_FOUND_STD"}]</span>
				[{/if}]

				<h4 class="m5">
				<img style="vertical-align:middle;" src="[{$dir_images}]task/task__created.gif" border="0">
				[{isys type="lang" ident="LC__WORKFLOW__CREATED_TASKS"}]
				</h4>
				[{if is_array($g_tasks__created) && count($g_tasks__created)>0}]
					[{foreach from=$g_tasks__created item=l_my_tasks}]
					<ul class="my_tasks">
						<li class="task_date">(<strong>[{counter}].</strong>) [{$l_my_tasks.date}]</li>
						<li class="task_link"><a href="[{$l_my_tasks.link}]">[{$l_my_tasks.title}]</a></li>
					</ul>
					[{/foreach}]
				[{else}]
					<span class="m5">[{isys type="lang" ident="LC__CMDB__FILTER__NOTHING_FOUND_STD"}]</span>
				[{/if}]
			</div>
		</td>
	</tr>
    [{/if}]
		[{*
	<tr>
		<th colspan="2">
			<h3 class="gradient text-shadow p5 status-headline"><img src="[{$dir_images}]icons/silk/heart.png" class="vam" /> [{isys type="lang" ident="LC__UNIVERSAL__SEARCH_FAVORITES"}]</h3>
		</th>
	</tr>
	<tr>
		<td colspan="2" class="navigation p5">
			<button type="button" class="btn btn-small btn-block bold" onClick="mysearch_delCriterion();">
				<img src="[{$dir_images}]icons/silk/page_delete.png" class="mr5" />
				<span>[{isys type="lang" ident="LC__NAVIGATION__NAVBAR__DELETE"}]</span>
			</button>
		</td>
	</tr>
	[{if $mysearch_addCriterion.bookmarkCount ne 0}]
	<tr>
		<td width="25px" align="center">
			<input type="checkbox" id="mysearchMarkAll" onclick="CheckAllBoxes(this,'mysearchCheckbox');" />
		</td>
		<td>
			<strong>[{isys type="lang" ident="LC__UNIVERSAL__MARK_ALL"}]</strong>
		</td>
	</tr>
	[{foreach from=$mysearch_addCriterion.bookmarkList key="id" item="bookmark"}]
	<tr>
		<td width="25px" align="center">
			<input type="checkbox" name="mysearchSelection[[{$id}]]" class="mysearchCheckbox" />
		</td>
		<td>
			<a href="[{$bookmark.link}]">[{$bookmark.text|truncate:61}]</a>
		</td>
	</tr>
	[{/foreach}]
	[{else}]
	<tr>
		<td colspan="2" class="p5">
			<span>[{isys type="lang" ident="LC__CMDB__FILTER__NOTHING_FOUND_STD"}]</span>
		</td>
	</tr>
	[{/if}]
		*}]
</table>
