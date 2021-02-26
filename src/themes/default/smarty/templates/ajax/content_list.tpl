[{if $index_includes.contenttop !== false}]
	[{include file=$index_includes.contenttop|default:"content/top/main.tpl"}]
[{/if}]

[{if !empty($table_rows)}]
	[{$table_rows|strip}]
[{else}]
	[{$objectTableList|strip}] 
[{/if}]