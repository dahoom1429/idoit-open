<div class="pt5 border-top w100">
    [{if isset($compressedMultivalueCategories) && $compressedMultivalueCategories == 1}]
        <div class="mb5 mr5 ml5 p10 box-blue">
            <img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" />
            [{isys type="lang" ident="LC__REPORT__VIEW__GROUPING_SORTING_HINT"}]
        </div>
    [{/if}]
    <div id="list"></div>
</div>

<script type="text/javascript">
    (function () {
        // Additional list parameter - Use it to overwrite default configuration
        var additionalListParameter = {};

        // Disable sorting for compressed multivalue mode
        [{if isset($compressedMultivalueCategories) && $compressedMultivalueCategories == 1}]
        // Disable sorting for all columns
        additionalListParameter.unsortedColumn = "[{$columnNames|escape:"javascript"}]".evalJSON();
        // Disable filtering in grouped mode
        additionalListParameter.filter = false;
        [{/if}]

        // Disable tr click
        [{if isset($trClickActive) && $trClickActive === false}]
            additionalListParameter.tr_click = false;
        [{/if}]

        window.build_table(
            'list',
            "[{$result|escape:"javascript"}]".evalJSON(),
                [{if $ajax_pager}]true[{else}]false[{/if}],
            '[{$ajax_url}]',
            '[{$preload_pages}]',
            '[{$max_pages}]',
            additionalListParameter
        );

        $('list')
            .up()
            .setStyle({
                'overflow':      'auto',
                'height':        ($('contentWrapper').getHeight() - $('reportHeader').getHeight() - 10) + 'px',
                'border-bottom': '0px'
            });
    })();
</script>