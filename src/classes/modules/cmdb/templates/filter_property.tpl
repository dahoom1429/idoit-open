[{if $property['type']=='list'}]
    [{isys type="f_dialog"
        p_strTable=$property['params']['p_strTable']
        condition=$property['params']['condition']
        default=$property['params']['default']
        p_bDbFieldNN=$property['params']['p_bDbFieldNN']
        p_arData=$property['params']['p_arData']
        name='default_filter_value'
        p_strClass='input-mini'
        inputGroupMarginClass="ml20"
        p_strSelectedID=$filterValue
        default=null
        allow_empty=true
        p_bEditMode=1
    }]
[{else}]
    [{isys type="f_text"
        name='default_filter_value'
        p_strValue=$filterValue
        p_bEditMode=1
        p_strClass='input-mini'
    }]
[{/if}]