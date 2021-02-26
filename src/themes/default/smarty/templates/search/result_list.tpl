<script type="text/javascript">
[{include file="search/search_javascript.js"}]
</script>

<div id="idCriterias"></div>

<div id="contentHeader" class="contentHeaderSmall">
	<h2 class="m0 fl">[{$searchResultHeader}] ([{$searchword|default:"-"}])</h2>
    <div class="fr">
		<span id="cSpanRecFilter">
            <div class="right paging fl">

                [{if is_array($pages) && count($pages) > 0}]
                <div class="pager-current fl">
                    <select name="current_page" id="current_page"  onchange="mysearch_change('index.php','ResponseContainerList');">
                        [{html_options selected=$page_start options=$pages}]
                    </select>
                </div>
                [{/if}]

                <div class="pager-results fl">
                    [{if $page_current != "" && $page_max != "" && $page_info != ""}]
                    <div style="display:inline;margin:5px 0;">
                        <img class="vam" style="margin-top:-2px;" src="[{$dir_images}]/icons/silk/page_white_stack.png" width="15px" height="15px" alt="" />
                        <span>[{$page_info}]</span>
                    </div>
                    [{/if}]

                    [{if $page_results >0}]
                    <div style="display:inline;margin:1px;">
                        <img class="vam" style="margin-top:-2px;" src="[{$dir_images}]/icons/navbar/new_icon_inactive.png" width="15px" height="15px" alt="" />
                        <span>[{$page_results}]</span>
                    </div>
                    [{/if}]
                </div>
            </div>
		</span>
    </div>
</div>

<script>document.forms["isys_form"].action = "[{$search_url}]";</script>
<input type="hidden" id="what" name="s" value="[{$searchword}]">

<div id="ResponseContainerList" style="overflow-x:auto;">
	[{include file="search/response_container.tpl"}]
</div>
