<div style="background:[{$color}]; color:[{$fontcolor}]; min-height:28px;" class="p5">
	[{if $title}]<h3 class="mb10">[{$title}]</h3>[{/if}]
	<div id="[{$unique_id}]_text" class="wysiwyg">
		[{$note}]
	</div>
</div>

<script type="text/javascript">
	// A double-click on the note will activate the "inline editing".
	$('[{$unique_id}]_text').on('dblclick', function () {
		var js_[{$unique_id}] = CKEDITOR.replace("[{$unique_id}]_text", {
			// Load the Simple Box plugin.
			extraPlugins: "",
			language: "de",
			allowedContent: true,
			toolbar: [
				{"name":"basicstyles","items":["Bold","Italic","Underline","Strike","-","RemoveFormat"]},
				{"name":"script","items":["Subscript","Superscript"]},
				{"name":"paragraph","items":["NumberedList","BulletedList"]},
				{"name":"indent","items":["Outdent","Indent"]},
				{"name":"UndoRedo","items":["Undo","Redo"]},
				{"name":"tools","items":["Maximize"]}
			],
			extraAllowedContent: "script",
			menuGroups: "clipboard,table,anchor,link,image",
			removeButtons: "",
			entities: false,
			on: {
				change: function (evt) {
					this.updateElement();
				}
			}
		});

		[{if $note_empty}]
		this.update().setStyle({minHeight: '25px'});
		[{/if}]

		this.addClassName('border p5')
			.setStyle({backgroundColor:'#fff'})
			.insert({after:
				new Element('button', {type:'button', className:'btn ml5 abort'})
					.update(new Element('img', {src:'[{$dir_images}]icons/silk/cross.png', className:"mr5"}))
					.insert(new Element('span').update('[{isys type="lang" ident="LC__WIDGET__CONFIG__ABORT"}]'))
			}).insert({after:
				new Element('button', {type:'button', className:'btn mt5 accept'})
					.update(new Element('img', {src:'[{$dir_images}]icons/silk/tick.png', className:"mr5"}))
					.insert(new Element('span').update('[{isys type="lang" ident="LC__WIDGET__CONFIG__ACCEPT"}]'))
			});

        this.next('button.accept').on('click', function () {
            var el     = $('[{$unique_id}]'),
                config = {
                    fontcolor: '[{$fontcolor}]',
                    color:     '[{$color}]',
                    title:     '[{$title|escape:"javascript"}]',
                    note:      js_[{$unique_id}].getData()
                };

            window.dashboard.save_config_and_reload_widget('[{$ajax_url}]', {
                id:        el.readAttribute('data-id'),
                unique_id: el.id,
                config:    Object.toJSON(config)
            });
        }.bind(this));

		this.next('button.abort').on('click', function () {
			window.dashboard.reload_widget($('[{$unique_id}]'));
		});

		this.focus();
	});
</script>