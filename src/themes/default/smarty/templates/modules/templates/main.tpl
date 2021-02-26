[{* Display table of templates *}]
[{$tableList}]
<input type="hidden" id="type" name="type" value="[{$rec_status}]" />

[{* Show error notifications *}]
[{if !empty($rankingErrors)}]
<script type="text/javascript">
	[{foreach from=$rankingErrors item=error}]
        idoit.Notify.error('[{$error}]');
	[{/foreach}]
</script>
[{/if}]