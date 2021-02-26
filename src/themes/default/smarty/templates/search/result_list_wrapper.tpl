[{include file="content/navbar/main.tpl"}]
<div id="contentWrapper">
    [{include file="search/result_list.tpl"}]
</div>
<script type="text/javascript">
    [{if $errors}]
        document.observe('dom:loaded', function() {
            idoit.Notify.error('[{$errors}]');
        });
    [{/if}]
</script>