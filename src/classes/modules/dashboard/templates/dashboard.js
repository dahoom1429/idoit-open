window.dashboard = {
	/**
	 * Method for (re)loading a certain widget.
	 * @param  el
	 * @param  options
	 */
    reload_widget: function (el, options) {
        var hash = (window.location.href.search('lang=') > 0 ? (+new Date()) : '[{if isys_application::instance()->container->request->request->get("login_username")}][{time()}][{/if}]'),
            opts = {
                id:         el.readAttribute('data-id'),
                identifier: el.readAttribute('data-identifier'),
                config:     (el.hasAttribute('data-config') ? el.readAttribute('data-config') : null),
                unique_id:  el.id,
                default:    (window.default_dashboard || 1)
            };
        
        options = Object.extend(opts, options);
        
        new Ajax.Request('[{$widget_ajax_url}]&func=load_widget&hash=' + hash, {
            parameters: options,
            method:     'post',
            onSuccess:  function (xhr) {
                var json = xhr.responseJSON;
                
                if (json)
                {
                    if (json.success)
                    {
                        el.update(json.data);
                    }
                    else
                    {
                        el.update(new Element('p', {className: 'p5 m10 box-red'}).update(json.message));
                    }
                    
                    this.add_title_bar(el.id);
                }
                else
                {
                    el.update(xhr.responseText);
                }
            }.bind(this)
        });
    },

	/**
	 * Method for creating the title-bar (including "edit" and "delete"-buttons).
	 * @param  unique_id
	 */
	add_title_bar: function (unique_id) {
		var el = $(unique_id),
			block = new Element('div', {className: 'widget-title'})
				.update(new Element('strong', {className: 'gradient text-shadow'})
					.update(el.readAttribute('data-title')));

		// Only display the "edit" and "delete" buttons, if we are not displaying the default dashboard.
		if (window.default_dashboard == 0) {
			// Only display the "edit" button, if the widget is configurable and we are not in "default" mode.
			if (el.readAttribute('data-configurable') == 1 && window.is_allowed_to_configure_widgets == 1) {
				block.insert(
					new Element('button', {type:'button', className: 'btn edit', title: '[{isys type="lang" ident="LC__WIDGET__EDIT"}]'})
					.update(new Element('img', {className: 'vam', src: '[{$dir_images}]icons/silk/pencil.png'})));
			}

			if (el.readAttribute('data-removable') == 1 && window.is_allowed_to_configure_dashboard == 1) {
				block.insert(
					new Element('button', {type:'button', className: 'btn delete', title: '[{isys type="lang" ident="LC__WIDGET__REMOVE"}]'})
					.update(new Element('img', {className: 'vam', src: '[{$dir_images}]icons/silk/cross.png'})));
			}
		}

		$(unique_id).insert(block);
	},

	save_config_and_reload_widget: function (ajax_url, options) {
		// options needs "id", "unique_id" and "config"
		new Ajax.Request(ajax_url,
			{
				parameters: options,
				method: 'post',
				onSuccess: function (response) {
					var json = response.responseJSON,
						second_overlay = $('widget-popup-overlay');

                    if (second_overlay) {
	                    second_overlay.remove();
                    }

					if (json.success) {
						$(options.unique_id).update(json.data);
						window.dashboard.add_title_bar(options.unique_id);

						popup_close($('widget-container-popup'));
					} else {
						idoit.Notify.error(json.message);
						popup_close($('widget-container-popup'));
					}
				}
			});
	}
};