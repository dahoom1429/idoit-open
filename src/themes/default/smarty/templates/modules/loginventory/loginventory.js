function show_loginventory_objects(p_id){
    $('loginventory_object_list').childElements().each(function(ele){
        ele.remove();
    });

    $('loginventory_submitter').hide();
    $('loginventory_multi_import_done').hide();
    $('loginventory_error').hide();

    if(p_id > 0){

        var ordered_by = $('table_ordered_by').value;

        if(ordered_by != ''){
            if(ordered_by == 'ASC'){
                $('table_ordered_by').value = 'DESC';
                $('loginventory_header_name').removeClassName('desc');
                $('loginventory_header_name').addClassName('asc');
            } else{
                $('table_ordered_by').value = 'ASC';
                $('loginventory_header_name').removeClassName('asc');
                $('loginventory_header_name').addClassName('desc');
            }
        } else{
            $('table_ordered_by').value = 'DESC';
        }

        l_url = String(document.location);

        new Ajax.Request('?ajax=1&call=loginventory_import&func=object_list',
            {
                parameters: {
                    id:p_id,
                    table_order:ordered_by
                },
                method:'post',
                onComplete:function(transport) {

                    var json = transport.responseText.evalJSON();
                    if(json == false){
                        new Effect.Appear('loginventory_error', {duration:1.5});
                    } else{

                        var tr_list = '';
                        var odd_even = 'CMDBListElementsOdd';

                        json.each(function(ele){
                            $('loginventory_object_list').insert(
                                new Element('tr', {className: 'listRow ' + odd_even}).update(
                                    new Element('td').update(
                                        new Element('input', {type:'checkbox', className:'checkbox', name:'id[]', value:ele.LA_ID, id:'chb_'+ele.LA_ID}).observe('click', function(){
                                            if($('chb_'+ele.LA_ID).checked)
                                                $('chb_'+ele.LA_ID).checked = false;
                                            else $('chb_'+ele.LA_ID).checked = true;
                                        })
                                    ).observe('click', function(){
                                        if($('chb_'+ele.LA_ID).checked)
                                            $('chb_'+ele.LA_ID).checked = false;
                                        else $('chb_'+ele.LA_ID).checked = true;
                                    })
                                ).insert(
                                    new Element('td').update(ele.LI_PCNAME).observe('click', function(){
                                        if($('chb_'+ele.LA_ID).checked)
                                            $('chb_'+ele.LA_ID).checked = false;
                                        else $('chb_'+ele.LA_ID).checked = true;
                                    })
                                ).insert(
                                    new Element('td').update(
                                        ((ele.imported != null)? ele.imported: '[{isys type="lang" ident="LC__MODULE__IMPORT__NOT_IMPORTED"}]')
                                    ).observe('click', function(){
                                        if($('chb_'+ele.LA_ID).checked)
                                            $('chb_'+ele.LA_ID).checked = false;
                                        else $('chb_'+ele.LA_ID).checked = true;
                                    })
                                )

                            );
                            odd_even = (odd_even == 'CMDBListElementsOdd')? 'CMDBListElementsEven': 'CMDBListElementsOdd';
                        });

                        $('loginventory_submitter').show();
                    }
                }
            });
    } else if(p_id == undefined){
        new Effect.Appear('loginventory_error', {duration:1.5});
    }
}

function loginventory_multi_import() {
    if ($('isys_form').getInputs('checkbox').pluck('checked').any() == false)
        return null;

    $('loginventory_multi_import_done').update('Importing.. Please wait..');
    new Effect.Appear('loginventory_multi_import_done', {duration:0.8});

    aj_submit('?[{$smarty.const.C__GET__MODULE_ID}]=[{$smarty.const.C__MODULE__LOGINVENTORY}]&request=import', 'post', 'loginventory_multi_import_done', 'isys_form');
}