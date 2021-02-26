[{if $additional_object_table_data}]
    <div class="mainListContainer">
    [{if !empty($table_rows)}]
        [{$table_rows}]
    [{else}]
        [{$objectTableList}]
    [{/if}]
    </div>

    <div class="additionalListContainer">
        [{$additional_object_table_data}]
    </div>
[{else}]
    [{if !empty($table_rows)}]
        [{$table_rows}]
    [{else}]
        [{$objectTableList}]
    [{/if}]
    </div>
[{/if}]