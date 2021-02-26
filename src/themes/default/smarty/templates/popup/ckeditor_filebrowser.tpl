<style type="text/css">
    body {
        background: #fff;
    }

    #ckeditor-filebrowser {
	    border: none;
    }

    #ckeditor-filebrowser .grid-item {
        height: 200px;
        width: 200px;
	    background: #fff url("[{$dir_images}]pattern3.png");
	    border:1px solid #aaa;
	    margin: 5px;
	    cursor: pointer;
    }

    #ckeditor-filebrowser .grid-item img {
        max-height: 100%;
        max-width: 100%;
    }

    #ckeditor-filebrowser .grid-item.selected {
	    border:3px solid #a00;
	    margin: 3px;
    }
</style>

<div id="ckeditor-filebrowser" class="popup">
	<h2 class="popup-header">[{isys type="lang" ident="LC__UNIVERSAL__CHOOSE_FILE_NOW"}]</h2>

	<div id="ckeditor-filebrowser-gallery" class="popup-content">
		[{foreach $files as $file}]
		<div class="fl grid-item">
			<div class="thumb">
				<img src="[{$file}]"/>
			</div>
		</div>
		[{foreachelse}]
		<p class="info m5 p5"><img src="[{$dir_images}]icons/silk/information.png" class="mr5 vam"/><span class="vam">[{$message}]</span></p>
		[{/foreach}]
	</div>

	<div class="popup-footer">
		<button id="ckeditor-filebrowser-accept" type="button" class="btn">
			<img src="[{$dir_images}]icons/silk/tick.png" class="mr5"/><span>[{isys type="lang" ident="LC_UNIVERSAL__ACCEPT"}]</span>
		</button>
		<button id="ckeditor-filebrowser-abort" type="button" class="btn ml5">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5"/><span>[{isys type="lang" ident="LC_UNIVERSAL__ABORT"}]</span>
		</button>
		<button id="ckeditor-filebrowser-delete" type="button" class="btn ml20">
			<img src="[{$dir_images}]icons/silk/bin_closed.png" class="mr5"/><span>[{isys type="lang" ident="LC_UNIVERSAL__DELETE"}]</span>
		</button>
	</div>
</div>

<script type="text/javascript">
    (function () {
        'use static';

        var $popup = $('ckeditor-filebrowser'),
	        $container = $('ckeditor-filebrowser-gallery'),
            $button_accept = $('ckeditor-filebrowser-accept'),
            $button_abort = $('ckeditor-filebrowser-abort'),
            $button_delete = $('ckeditor-filebrowser-delete').disable(),
	        resizeGallery = function () {
			    $popup.down('.popup-content').setStyle({
				    height: document.viewport.getDimensions().height - ($popup.down('.popup-header').getHeight() + $popup.down('.popup-footer').getHeight()) + 'px'
			    });
		    },
	        delete_image = function () {
		        if (confirm('[{isys type="lang" ident="LC__UNIVERSAL__DELETE_FILE_CONFIRM" p_bHtmlEncode=false}]')) {
			        var $selection = $container.down('.selected');

			        new Ajax.Request('[{$delete_url}]', {
				        parameters:{
					        file: $selection.down('img').readAttribute('src')
				        },
				        method:'post',
				        onSuccess:function (transport) {
					        var json = transport.responseJSON;

					        if (json.success) {
						        $button_delete.disable().down('img').writeAttribute('src', '[{$dir_images}]icons/silk/bin_closed.png');
						        idoit.Notify.success('[{isys type="lang" ident="LC__UNIVERSAL__FILE_DELETED" p_bHtmlEncode=false}]');

						        new Effect.Morph($selection, {
							        style:'width:0; height:0; border-width:0; opacity:0;',
							        afterFinish: function () {
								        $selection.up('.grid-item').remove();
							        }
						        });
					        } else {
						        idoit.Notify.error('[{isys type="lang" ident="LC__UNIVERSAL__FILE_NOT_DELETED" p_bHtmlEncode=false}] - ' + json.message, {sticky:true});
					        }
				        }
			        });
		        }
	        },
            select_image = function (ev) {
	            $container.select('.selected').invoke('removeClassName', 'selected');

                ev.findElement('.grid-item').addClassName('selected');

	            $button_delete.enable().down('img').writeAttribute('src', '[{$dir_images}]icons/silk/bin_empty.png');
            };

        $container.select('.grid-item')
            .invoke('observe', 'click', select_image)
            .invoke('observe', 'dblclick', function (ev) {
                select_image(ev);
                $button_accept.simulate('click')
            });

        // Action for the "accept" button.
        $button_accept.on('click', function () {
            var $selection = $container.down('.selected').down('img');

            if ($selection) {
                // "opener" is the window element, which opened this popup.
                opener.CKEDITOR.tools.callFunction('[{$ckeditor_func_num}]', $selection.readAttribute('src'), '');
            }

            $button_abort.simulate('click');
        });

	    // Add the delete action.
	    $button_delete.on('click', delete_image);

        // Action for the "abort" button.
        $button_abort.on('click', function () {
            self.close();
        });

        // Align all images vertically. We use "window.onload" because this will be triggered AFTER all images are loaded!
	    window.onload = function() {
            $container.select('.thumb img').each(function ($el) {
                var margin_top = (200 - $el.getHeight()) / 2;

                // This is necessary, so that the images don't fly around because a wrong calculation "before" the images have been loaded.
                if (margin_top > 0 && margin_top < 100) {
                    $el.setStyle({marginTop:margin_top + 'px'});
                }
            });
        };

	    Event.observe(window, 'resize', resizeGallery);

	    resizeGallery();
    })();
</script>