<div id="catg-custom-[{$catg_custom_id}]" class="pt5 pb5">
    <input type="hidden" name="catg_custom_id" value="[{$catg_custom_id}]" />
    [{foreach $fields as $key => $field}]
        [{if $field.displayInTable}]
        <table class="contentTable p0">
            <tr [{if ($field.visibility == 'hidden')}]class="hide"[{/if}]>
                <td class="key vat">[{isys type='f_label' name="C__CATG__CUSTOM__`$key`" ident=$field.title}]</td>
                <td class="value" [{if $field.show_inline}]data-inline="yup"[{/if}] id="field-[{$key}]">[{include file=$field.template}]</td>
            </tr>
        </table>
        [{else}]
            [{if $field.popup eq "report_browser" && $listing}]
                [{include file="src/classes/modules/report/templates/report_execute.tpl"}]
                [{if isset($reportExecutionFailed)}]
                    <p class="box-red m10 p10">
                        [{isys type="lang" ident="LC__MODULE__CUSTOM_FIELDS__REPORT_EXECUTION_FAULTY"}]
                    </p>
                [{/if}]
            [{else}]
                [{if file_exists($field.template)}]
                    [{include file=$field.template}]
                [{else}]
                    <p class="box-red p5 m5">The template for the attribute "[{$field.title|escape:"html"}]" could not be found.</p>
                    <!-- [{var_dump($field)}] -->
                [{/if}]
            [{/if}]
        [{/if}]
    [{/foreach}]
</div>

<style type="text/css">
    [data-contains-inline] {
        clear: both;
    }

    [data-contains-inline] > div {
        float: left;
    }
</style>

<script type="text/javascript">
    (function () {
        'use strict';

        idoit.Require.require('fileUploader');

        try {
            var $customCategoryContainer = $('catg-custom-[{$catg_custom_id}]'),
                $currentTable,
                $currentTD,
                $currentItem,
                $currentLabel,
                $targetTable;

            while ($currentTD = $customCategoryContainer.down('[data-inline]')) {
                // Remove the attribute to prevent multiple iterations.
                $currentTD.writeAttribute('data-inline', null);

                $currentItem = $currentTD.down('div');

                // If there is no immediate DIV, cancel.
                if (!$currentItem) {
                    continue;
                }

                $currentTable = $currentTD.up('table');

                // We can't use a 'label' selector here because it won't exist in view mode.
                $currentLabel = $currentTable.down('td').innerText;

                $targetTable = $currentTable.previous('table');

                // Append the current label to the target label (if it contains something)
                if (!$currentLabel.blank()) {
                    $targetTable.down('td').insert(' / ' + $currentLabel);
                }

                // Write the 'data-contains-inline' attribute and move the current item here.
                $targetTable.down('td', 1).writeAttribute('data-contains-inline', 'yup').insert($currentItem);

                $currentTable.remove();
            }

            // Now we make the "inline items" fit nicely:
            var $inlineContainer = $customCategoryContainer.select('[data-contains-inline]');

            $inlineContainer.each(function($container) {
                var chilrenCount = $container.select('> div').length;

                $container
                    .select('.input-size-normal')
                    .invoke('removeClassName', 'input-size-normal')
                    .invoke('addClassName', chilrenCount >= 3 ? 'input-size-mini' : 'input-size-small');
            });
        } catch (e) {
            idoit.Notify.warning('A problem occured while re-arranging the fields: ' + e);
        }
    })();
</script>
