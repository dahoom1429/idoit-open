<fieldset class="overview">
	<legend>
		<span class="searchOptions" style="border-top:0;">[{$headline|default:"-"}] |
			<label for="normalSearchRadio" class="ml10"><input type="radio" id="normalSearchRadio" name="search-mode" data-mode="[{\idoit\Module\Search\Query\Condition::MODE_DEFAULT}]" [{if $searchMode == \idoit\Module\Search\Query\Condition::MODE_DEFAULT}]checked="checked"[{/if}] /> Normal</label>
			<label for="deepSearchRadio" class="ml10"><input type="radio" id="deepSearchRadio" name="search-mode" data-mode="[{\idoit\Module\Search\Query\Condition::MODE_DEEP}]" [{if $searchMode == \idoit\Module\Search\Query\Condition::MODE_DEEP}]checked="checked"[{/if}] /> Deep Search</label>
		</span>
	</legend>

	<div class="mt10 search-results" id="searchResultList">
		[{if !$error}]
			[{$objectTableList}]
		[{else}]
			<div class="box-red m10 mt20 p10">[{$error}]</div>
		[{/if}]
	</div>

</fieldset>

<style type="text/css">
	div.fuzzy-search-checkbox
	{
		position:absolute;
		top:10px;
		right:20px;
		margin-top:-25px;
	}
	div.search-results table tr td:first-child {
		width:250px;
	}
	div.search-results table tr td:nth-child(2) {
		white-space: normal;
	}
</style>

<script type="text/javascript">
	(function () {
		"use strict";

		document.observe('dom:loaded', function() {
			var tableBody           = $$('#mainTable tbody')[0],
			    searchResults       = $('searchResultList'),
				currentSearch       = '[{$smarty.get.q|strip_tags|escape}]',
			    autostartDeepSearch = [{$autostartDeepSearch}],
			    hasResults          = [{intval($hasResults)}],
				lastJsonData        = JSON.parse('[{$initialResponse|addslashes}]'),
                searchResultLimit   = [{intval(isys_tenantsettings::get('search.limit', '2500'))}],
                searchResultStatus  = [{intval(isys_tenantsettings::get('search.index.include_archived_deleted_objects', false))}],
                rowTemplate         = new Template('<tr><td>#{source}</td><td><a href="#{link}">#{value}</a></td></tr>'),
                rowTemplateSatus   = new Template('<tr><td>#{source}</td><td>#{status}</td><td><a href="#{link}">#{value}</a></td></tr>'),
				noResults = '<div class="box-red m10 mt20 p10">[{isys type="lang" ident="LC__MODULE__SEARCH__NO_RESULTS" values=$smarty.get.q|strip_tags|escape}]</div>',
				loading = '<p class="box-grey mt20 m10 p10">[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</p>',
				searchResultCounter = $('searchResultCounter');

			var resultSetToTable = function(data) {
			    var statusHeader = '';
			    if (searchResultStatus) {
                    statusHeader = '<th>[{isys type="lang" ident="LC__UNIVERSAL__STATUS"}]</th>';
                }

				var table = '<table id="mainTable" cellspacing="0" class="mainTable"><tbody><tr><th>[{isys type="lang" ident="LC__UNIVERSAL__SOURCE"}]</th>' +
                                statusHeader +
                                '<th>[{isys type="lang" ident="LC__MODULE__SEARCH__FOUND_MATCH"}]</th></tr>';

				data.forEach(function (value) {
				    if (searchResultStatus) {
                        table += rowTemplateSatus.evaluate(value);
                    } else {
                        table += rowTemplate.evaluate(value);
                    }
				});

				table += '</table>';

				return table;
			};

			var mergeJsonData = function(data) {
				var flags = [],
				    mergedData = [];

				mergedData = data.concat(lastJsonData).filter(function(value) {
					if (flags[value['link']]) {
						return false;
					}

					flags[value['link']] = true;

					return true;
				});

				return mergedData;
			};

			var searchResultRequest = function(_currentSearch, searchMode, successCallback) {
				new Ajax.Request(window.www_dir + 'search?q=' + _currentSearch + searchMode, {
					method:    'get',
					onSuccess: successCallback
				});
			};

			var automaticDeepSearch = function () {
				if (!$('deepSearchRadio').checked && lastJsonData.length < searchResultLimit)
				{
					switch (autostartDeepSearch) {
						case [{\isys_module_search::AUTOMATIC_DEEP_SEARCH_ACTIVE}]: // Always start deep search
							if (hasResults) {
								tableBody.insert('<tr style="background-color: #ccc;"><td colspan="2">[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</td></tr>');
							} else {
								searchResults.insert(loading).setStyle('z-index:1001');
							}

							searchResultRequest(currentSearch, '&mode=3', function (xhr) {
								var mergedData = mergeJsonData(xhr.responseJSON);

								if (mergedData.length === 0) {
									searchResults.update(noResults);
								} else {
									mergedData.sort(function(a,b) {
										return (a.score < b.score) ? 1 : ((b.score < a.score) ? -1 : 0);
									});

									searchResults.update(resultSetToTable(mergedData));
									searchResultCounter.update(mergedData.length);
								}
							});
							break;
						case [{\isys_module_search::AUTOMATIC_DEEP_SEARCH_ACTIVE_EMPTY_RESULT}]: // Start deep search when no results are given
							if (!hasResults)
							{
								searchResults.update(loading);
								searchResultRequest(currentSearch, '&mode=3', function (xhr) {
									searchResults.update(resultSetToTable(xhr.responseJSON));
									searchResultCounter.update(xhr.responseJSON.length);
								});
							}
							break;
						case [{\isys_module_search::AUTOMATIC_DEEP_SEARCH_NONACTIVE}]: // Never start deep search automatically
							break;
					}
				}
			};

			$$('.searchOptions input').invoke('on', 'click', function (ev) {
				searchResults.update(loading).setStyle('z-index:1001');

				var searchMode = '';

				$$('span.searchOptions label input').each(function (el) {
					if (el.checked)
					{
						var type = el.getAttribute('data-mode');

						if (type !== "")
						{
							searchMode = '&mode=' + type;
						}
					}
				});

				searchResultRequest(currentSearch, searchMode, function (xhr) {
					if (xhr.responseJSON.length === 0) {
						searchResults.update(noResults);
					} else {
						searchResults.update(resultSetToTable(xhr.responseJSON));
						searchResultCounter.update(xhr.responseJSON.length);
					}
					automaticDeepSearch();
				});
			});

			// ID-3726: Automatic deep search
			automaticDeepSearch();

			if ($('cSpanRecFilter')) {
				$('cSpanRecFilter').hide();
			}

            /**
             * Load infrastructure tree
             *
             * @see ID-5888
             * @see ID-5889
             */
            var $infrastructureObjectTypeGroup = $('menuItem_[{$activeMainMenuItem}]');

            // Check whether object type group `infrastructure` exists
            if ($infrastructureObjectTypeGroup) {
                // Simulate click to load left tree
                $infrastructureObjectTypeGroup.down('a').simulate('click');
            } else {
                // Click on second element in main menu to prevent loading my-doit
                $('mainMenu').down('li a', 1).simulate('click');
            }
		});
	}());
</script>
