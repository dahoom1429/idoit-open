<table class='contentTable'>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE_TABLE__ASSIGNED_DATABASE' ident='LC__CATG__DATABASE_TABLE__ASSIGNED_DATABASE'}]</td>
        <td class='value'>[{isys type='f_dialog' name='C__CATG__DATABASE_TABLE__ASSIGNED_DATABASE' p_onChange="this.fire('databaseSelection:updated');"}]</td>
    </tr>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE_TABLE__VIEW_INSTANCE' ident='LC__CATG__DATABASE_TABLE__VIEW_INSTANCE'}]</td>
        <td class='value'>[{isys type='f_text' name='C__CATG__DATABASE_TABLE__VIEW_INSTANCE' p_bReadonly=true}]</td>
    </tr>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE_TABLE__SCHEMA' ident='LC__CATG__DATABASE_TABLE__SCHEMA'}]</td>
        <td class='value'>[{isys type='f_dialog' name='C__CATG__DATABASE_TABLE__SCHEMA'}]</td>
    </tr>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE_TABLE__TITLE' ident='LC__CATG__DATABASE_TABLE__TITLE'}]</td>
        <td class='value'>[{isys type='f_text' name='C__CATG__DATABASE_TABLE__TITLE'}]</td>
    </tr>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE_TABLE__ROW_COUNT' ident='LC__CATG__DATABASE_TABLE__ROW_COUNT'}]</td>
        <td class='value'>[{isys type='f_text' name='C__CATG__DATABASE_TABLE__ROW_COUNT'}]</td>
    </tr>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE_TABLE__SIZE' ident='LC__CATG__DATABASE_TABLE__SIZE'}]</td>
        <td class='value'>
            [{isys type='f_text' name='C__CATG__DATABASE_TABLE__SIZE'}]
            [{isys title='DBMS' name='C__CATG__DATABASE_TABLE__SIZE_UNIT' type='f_dialog'}]
        </td>
    </tr>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE_TABLE__MAX_SIZE' ident='LC__CATG__DATABASE_TABLE__MAX_SIZE'}]</td>
        <td class='value'>
            [{isys type='f_text' name='C__CATG__DATABASE_TABLE__MAX_SIZE'}]
            [{isys title='DBMS' name='C__CATG__DATABASE_TABLE__MAX_SIZE_UNIT' type='f_dialog'}]
        </td>
    </tr>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE_TABLE__SCHEMA_SIZE' ident='LC__CATG__DATABASE_TABLE__SCHEMA_SIZE'}]</td>
        <td class='value'>
            [{isys type='f_text' name='C__CATG__DATABASE_TABLE__SCHEMA_SIZE'}]
            [{isys title='DBMS' name='C__CATG__DATABASE_TABLE__SCHEMA_SIZE_UNIT' type='f_dialog'}]
        </td>
    </tr>
</table>
<script language="JavaScript" type="text/javascript">
    (function () {
        "use strict";
        var $assignedDatabase      = $('C__CATG__DATABASE_TABLE__ASSIGNED_DATABASE'),
            objectId = '[{$objectId}]',
            $assignedSchemas  = $('C__CATG__DATABASE_TABLE__SCHEMA');

        function setSchemaData (id){
            new Ajax.Request(
                '[{$ajaxUrl}]',
                {
                    parameters: {
                        'databaseId': parseInt(id),
                        'objectId': parseInt(objectId)
                    },
                    method:     'post',
                    onComplete: function (xhr) {
                        var json = xhr.responseJSON,
                            $optionElement, i;

                        if (json.success) {
                            var instanceData = json.data;

                            for (i in instanceData){
                                if (instanceData.hasOwnProperty(i)){
                                    $optionElement = new Element('option');
                                    $optionElement.setAttribute('value', i);
                                    $optionElement.innerHTML = instanceData[i];

                                    $assignedSchemas.insert($optionElement);
                                }
                            }

                        } else {
                            idoit.Notify.error(json.message);
                        }

                    }.bind(this)
                });
        };

        if ($assignedDatabase) {
            $assignedDatabase.on('databaseSelection:updated', function (){
                var $optionEle = new Element('option');
                $optionEle.setAttribute('value', '-1');
                $optionEle.innerHTML = '-';

                $assignedSchemas.update($optionEle);

                if (this.value > 0){
                    setSchemaData(this.value);
                }
            });
        }
    }());
</script>