<table class='contentTable'>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE_SA__ASSIGNED_DBMS' ident='LC__CATG__DATABASE_SA__ASSIGNED_DBMS'}]</td>
        <td class='value'>[{isys type='f_dialog' name='C__CATG__DATABASE_SA__ASSIGNED_DBMS' p_onChange="this.fire('dbmsSelection:updated');"}]</td>
    </tr>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE_SA__TITLE' ident='LC__CATG__DATABASE_SA__TITLE'}]</td>
        <td class='value'>[{isys type='f_text' name='C__CATG__DATABASE_SA__TITLE'}]</td>
    </tr>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE_SA__SIZE' ident='LC__CATG__DATABASE_SA__SIZE'}]</td>
        <td class='value'>
            [{isys type='f_text' name='C__CATG__DATABASE_SA__SIZE'}]
            [{isys title='DBMS' name='C__CATG__DATABASE_SA__SIZE_UNIT' type='f_dialog'}]
        </td>
    </tr>
    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE_SA__MAX_SIZE' ident='LC__CATG__DATABASE_SA__MAX_SIZE'}]</td>
        <td class='value'>
            [{isys type='f_text' name='C__CATG__DATABASE_SA__MAX_SIZE'}]
            [{isys title='DBMS' name='C__CATG__DATABASE_SA__MAX_SIZE_UNIT' type='f_dialog'}]
        </td>
    </tr>

    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE_SA__ASSIGNED_INSTANCE' ident='LC__CATG__DATABASE_SA__ASSIGNED_INSTANCE'}]</td>
        <td class='value'>[{isys type='f_dialog' name='C__CATG__DATABASE_SA__ASSIGNED_INSTANCE'}]</td>
    </tr>

    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE_SA__SCHEMAS' ident='LC__CATG__DATABASE_SA__SCHEMATA'}]</td>
        <td class='value'>[{isys type='f_popup' name='C__CATG__DATABASE_SA__SCHEMAS' p_strPopupType="dialog_plus"}]</td>
    </tr>

    <tr>
        <td class='key'>[{isys type='f_label' name='C__CATG__DATABASE_SA__ACCESS' ident='LC__CATG__DATABASE_SA__ACCESS'}]</td>
        <td class='value'>
            [{$databaseAccess}]
        </td>
    </tr>

</table>
<script language="JavaScript" type="text/javascript">
    (function () {
        "use strict";

        var $assignedDbms      = $('C__CATG__DATABASE_SA__ASSIGNED_DBMS'),
            objectId = '[{$objectId}]',
            $assignedInstances  = $('C__CATG__DATABASE_SA__ASSIGNED_INSTANCE');

        function setInstanceData (applicationId){
            new Ajax.Request(
                '[{$ajaxUrl}]',
                {
                    parameters: {
                        'applicationId': parseInt(applicationId),
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

                                    $assignedInstances.insert($optionElement);
                                }
                            }

                        } else {
                            idoit.Notify.error(json.message);
                        }

                    }.bind(this)
                });
        };

        if ($assignedDbms) {
            $assignedDbms.on('dbmsSelection:updated', function (){
                var $optionEle = new Element('option');
                $optionEle.setAttribute('value', '-1');
                $optionEle.innerHTML = '-';

                $assignedInstances.update($optionEle);

                if (this.value > 0){
                    setInstanceData(this.value);
                }
            });
        }
    }());
</script>