Position.includeScrollOffsets = true;

var g_templates = [],
    g_sortable;

function select_template($template) {
    var selection = $template.getValue(), i;
    
    for (i in selection) {
        if (! selection.hasOwnProperty(i)) {
            continue;
        }
    
        // If the template has already been selected - skip it.
        if (g_templates.any(function(template) { return template.template_id == selection[i]; })) {
            continue;
        }
        
        g_templates.push({
            el: new Element('li', {className: 'p5 tpl_li', id: 'tpl_li_' + g_templates.length})
                    .update(new Element('div', {className: 'fr'})
                        .update(new Element('button', {type: 'button', title: 'Remove template from list', className: 'btn btn-small', onClick: 'delete_template("' + selection[i] + '");'})
                            .update(new Element('img', {src: window.dir_images + 'icons/silk/cross.png'}))))
                    .insert(new Element('div', {id: 'xtmp_val_' + g_templates.length})
                        .update(new Element('img', {className: 'fl mr5', src: window.dir_images + 'ajax-loading.gif'}))
                        .insert(new Element('strong').update(idoit.Translate.get('LC__UNIVERSAL__LOADING')))),
            template_id: selection[i],
            index: g_templates.length,
            input: new Element('input', {'name': 'templates[]', 'type': 'hidden', 'value': selection[i]})
        });


        if ($F('object_title').blank()) {
            $('object_title').setValue($template.down(':selected').innerText);
        }
    }
    
    print_list();
}

function select_single_template(p_template) {
    var selection = $F(p_template);

    // -1 is a special value for the nothing selected
    
    if (selection == -1)
    {
        $('template_list').update();
        return;
    }

    g_templates = [{
        el: new Element('li', {className: 'p5 tpl_li', id: 'tpl_li_0'})
                /*
                .update(new Element('div', {className: 'fr'})
                    .update(new Element('button', {type: 'button', title: 'Remove template from list', className: 'btn btn-small', onClick: 'delete_template("0");'})
                        .update(new Element('img', {src: window.dir_images + 'icons/silk/cross.png'}))))
                */
                .insert(new Element('div', {id: 'xtmp_val_0'})
                    .update(new Element('img', {className: 'fl mr5', src: window.dir_images + 'ajax-loading.gif'}))
                    .insert(new Element('strong').update(idoit.Translate.get('LC__UNIVERSAL__LOADING')))),
        template_id: selection,
        index: 0,
        input: new Element('input', {'name': 'templates[]', 'type': 'hidden', 'value': selection})
    }];

    print_list();
}

function delete_template(tplId) {
    // Remove the selected template from the list.
    g_templates = g_templates.filter(function(tpl) {
        return tpl.template_id != tplId
    });

    print_list();
}

function print_list() {
    var active_templates = 0,
        tpl_list = $('template_list').update(),
        is_special_template = true;

    g_templates.each(function (i) {
        if (i != undefined && typeof i == 'object' && i.index != -1) {
            is_special_template &= i.template_id < 0;
            active_templates++;

            // The "clone" is necessary for IE browsers to display the selected templates correctly.
            tpl_list.insert(i.el.clone(true)).insert(i.input);

            new Ajax.Updater(
                'xtmp_val_' + i.index,
                document.location.href + '&call=template_content&ajax=1&template_id=' + i.template_id
            );
        }
    });

    if ($('sel_count'))
    {
        $('sel_count').update(active_templates);
    }

    g_sortable = Sortable.create('template_list', {
        scroll: (Prototype.Browser.Gecko) ? 'contentArea' : window,
        onChange: function (el) {

            var tpl_new = [];
            $$('.tpl_li').each(function (li) {
                var tpl_id = li.id.split('_')[2];

                tpl_new[tpl_new.length] = g_templates[tpl_id];
            });

            g_templates = tpl_new;
        }
    });

    if ($('create_template') && $('object_type'))
    {
        $('create_template').disabled = !($('object_type').value != -1 && active_templates > 0);
    }

    ['empty_fields', 'multivalue_categories', 'log-level'].each(function(name) {
        $$('[name="' + name + '"]').each(function(input) {
            input.disabled = is_special_template;
        });
    });
}

function loader_hide() {
    $('loader').hide();
    document.isys_form.target = '';
}

function tpl_loader_hide() {
    $('tpl_loader').hide();
}
