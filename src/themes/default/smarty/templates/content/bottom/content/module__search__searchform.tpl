<script type="text/javascript">
	[{include file="search/search_javascript.js"}]
</script>

<input type="hidden" name="id" value="[{$nID}]" />
<input type="hidden" value="0" id="theValue" />

<input type="hidden" name="C__SEARCH_OBJECTTYPES_HIDEN" id="obj_inputHiden" />
<input type="hidden" name="C__SEARCH_CATEGORIES_HIDEN" id="catgs_inputHiden" />

[{include file=$index_includes.navbar|default:"content/navbar/main.tpl"}]

<div id="contentWrapper" style="background-color:#fff;">
	<div id="advancedSearch">
		<h2 class="p5 border-bottom gradient">[{isys type="lang" ident="LC__MODULE__SEARCH__TITEL"}]</h2>

		<div class="p10">
			<table>
				<tr>
					<td valign="middle">
						<label>[{isys type="lang" ident="LC_UNIVERSAL__OBJECT_TYPES"}]</label><br />

						<div onclick="new Effect.toggle('objselectoverlay', 'slide', {duration:0.1});">
							<input type="text" id="obj_input" class="input input-small" readonly="readonly" name="C__SEARCH_OBJECTTYPES" value="[{if $highlighted_objtypes != ''}][{$highlighted_objtypes}][{else}][{isys type="lang" ident="LC__CMDB__RECORD_STATUS__ALL"}][{/if}]" style="background:#eee;" />
							<span class="arrow vam border mouse-pointer" style="margin-left:-4px; padding: 3px 2px 4px; border-left:0;">
								<img src="[{$dir_images}]icons/silk/bullet_arrow_down.png" alt="v" class="vam" />
							</span>
						</div>

						<div id="objselectoverlay" class="multiselect" style="display:none;">
							<ul>
								<li>
									<a href="javascript:" class="fr mr5 bold"
									   onclick="new Effect.toggle('objselectoverlay', 'slide', {duration:0.1});">&times;</a>

									<label class="bold">
										<input [{if $objtypes_fav == ''}]checked="checked"[{/if}] type="checkbox"
										       value="" class="obj" id="select_all_obj"
										       onclick="CheckAllBoxes(this, 'obj'); mysearch_updateTypeInput(this,this.value,'obj');" />
										[{isys type="lang" ident="LC__UNIVERSAL__CHOOSE_ALL"}]
									</label>

								</li>
								[{foreach item=obj_info from=$obj_names}]
									<li>
										<label>
											<input [{if $objtypes_fav == '' || $obj_info.isys_obj_type__id|in_array:$objtypes_fav}]checked="checked"[{/if}]
											       type="checkbox"
											       name="[{isys type='lang' ident=$obj_info.isys_obj_type__title}]"
											       class="obj" value="[{$obj_info.isys_obj_type__id}]"
											       onclick="mysearch_updateTypeInput(this.name,this.value,'obj')">
											[{isys type="lang" ident=$obj_info.isys_obj_type__title}]
										</label>
									</li>
								[{/foreach}]
							</ul>
						</div>
					</td>
					<td><img src="[{$dir_images}]empty.gif" width="10px" /></td>
					<td style="width:310px;">
						<label>[{isys type="lang" ident="LC__MODULE__SEARCH__CATG"}]</label><br />

						<div onclick="new Effect.toggle('catgselectoverlay', 'slide', {duration:0.1});">
							<input type="text" id="catgs_input" class="input input-small" readonly="readonly" name="C__SEARCH_OBJECTTYPES" value="[{if $highlighted_catg != ''}][{$highlighted_catg}][{else}][{isys type="lang" ident="LC__CMDB__RECORD_STATUS__ALL"}][{/if}]" style="background:#eee;" />
							<span class="arrow vam border mouse-pointer" style="margin-left:-4px; padding: 3px 2px 4px; border-left:0;">
								<img src="[{$dir_images}]icons/silk/bullet_arrow_down.png" alt="v" class="vam" />
							</span>
						</div>
						<div id="catgselectoverlay" class="multiselect" style="display:none;">
							<ul>
								<li>
									<a href="javascript:" class="fr mr5 bold"
									   onclick="new Effect.toggle('catgselectoverlay', 'slide', {duration:0.1});">&times;</a>

									<label class="bold">
										<input [{if $categories_fav == ''}]checked="checked"[{/if}] type="checkbox"
										       value="" class="catgs" id="select_all_catgs"
										       onclick="CheckAllBoxes(this, 'catgs');mysearch_updateTypeInput(this,this.value,'catgs');" />
										[{isys type="lang" ident="LC__UNIVERSAL__CHOOSE_ALL"}]
									</label>
								</li>
								[{foreach item=category_arr from=$catg_names key=category_type}]
                                    <li>
                                        <label class="bold ml15">
                                            [{$category_type}]
                                        </label>
                                    </li>

                                    [{foreach from=$category_arr item=category}]
                                        <li>
                                            <label>
                                                <input [{if $categories_fav == '' || isset($categories_fav[$category.key])}]checked="checked"[{/if}]
                                                       type="checkbox" name="[{isys type='lang' ident=$category.title}]"
                                                       class="catgs" value="[{$category.key}]"
                                                       onclick="mysearch_updateTypeInput(this.name,this.value,'catgs')">
                                                [{isys type="lang" ident=$category.title}]
                                            </label>
                                        </li>
                                    [{/foreach}]
								[{/foreach}]
							</ul>
						</div>
					</td>
				</tr>
			</table>

			<br />

			<table border="0" cellpadding="3" cellspacing="1">
				<tr>
					<td id="secondtd" width="200px" valign="bottom" class="secondtdt">
						<input type="search" name="C__SEARCH_TEXT[]" id="searchText" size="55" autosave="idoit.search"
						       placeholder="[{isys type="lang" ident="LC__UNIVERSAL__SEARCH"}]" value=""
						       class="input input-small" style="padding:3px;"
						       onkeypress="if((event.which&&event.which==13)||(event.keyCode&&event.keyCode==13)) { mysearch_change('index.php','ResponseContainer'); return false;}else return true;" />
					</td>
					<td>
						<div id="selectop" style="display:none">
							<select name="C__SEARCH_OPTION_LOP" id="search_option_op" class="input input-mini">
								<option value='AND'>[{isys type="lang" p_func="strtoupper" ident="LC__UNIVERSAL__AND"}]</option>
								<option value='OR' selected='yes'>[{isys type="lang" p_func="strtoupper" ident="LC_UNIVERSAL__OR"}]</option>
							</select>
						</div>
					</td>
				</tr>
			</table>

			<div id="addedDiv"></div>

			<div id="idAddElement" class="mt5">
				<button type="button" class="btn" onclick="mysearch_addElement();">
					<img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__MODULE__SEARCH__ADDSEAECHFIELD"}]</span>
				</button>
			</div>

			<div id="idRemoveElement" style="display:none" class="mt5">
				<button type="button" class="btn" onclick="mysearch_rem(1);">
					<img src="[{$dir_images}]icons/silk/delete.png" class="mr5" /><span>[{isys type="lang" ident="LC__MODULE__SEARCH__REMOVELASTFIELD"}]</span>
				</button>
			</div>

			<div class="mt15 mb15">
				<label><input type="checkbox" class="checkbox" value="1" id="worts" name="worts"> [{isys type="lang" ident="LC__MODULE__SEARCH__WORDSONLY"}]</label>
				<label><input type="checkbox" class="checkbox" value="1" id="casesensitiv" name="casesensitiv"> [{isys type="lang" ident="LC__MODULE__SEARCH__CASESENSITIV"}]</label>
			</div>

			<button class="btn" type="button" onclick='mysearch_change("index.php","ResponseContainer");'>
				<img src="[{$dir_images}]icons/silk/magnifier.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__SEARCH"}]</span>
			</button>
			<button class="btn" type="button" onclick="mysearch_addCriterias(0);">
				<img src="[{$dir_images}]icons/silk/heart.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__ADD_TO_SEARCH_FAVORITES"}]</span>
			</button>
		</div>
	</div>

	<div id="errors-message" class="box-red bold p5 mt5" style="display:none;">
		<img src="[{$dir_images}]icons/alert-icon.png" class="vam" />
		<span class="vam"></span>
	</div>

	<div id="ResponseContainer"></div>
</div>

<script type="text/javascript">
	[{if $errors}]
		document.observe('dom:loaded', function() {
			idoit.Notify.error('[{$errors}]');
		});
	[{/if}]

	[{if $search_favorites != ""}]
		mysearch_change("index.php", "ResponseContainer", "[{$searchfavorites}]");
	[{/if}]
</script>
