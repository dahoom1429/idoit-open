<div id="catg-images">
	<div id="catg-images-droppable"></div>

	<div id="catg-images-gallery"></div>
	<br class="cb" />
</div>

<script type="text/javascript">
    (function () {
        'use strict';

        idoit.Require.require('fileUploader', function () {
            var $container        = $('catg-images'),
                $droppable        = $('catg-images-droppable'),
                $gallery          = $('catg-images-gallery'),
                images            = '[{$images}]'.evalJSON(),
                thumb_size        = 300,
                $orderButton      = '',
                $orderPopup       = new Element('div', {id: 'catg-images-order-popup', className: 'hide popup'}),
                $zoom_out_button  = new Element('button', {type: 'button', className: 'btn gallery-options', style: 'right:65px;'})
                    .update(new Element('img', {src: window.dir_images + 'icons/silk/magnifier_zoom_out.png'})),
                $zoom_null_button = new Element('button', {type: 'button', className: 'btn gallery-options', style: 'right:35px;'})
                    .update(new Element('img', {src: window.dir_images + 'icons/target.png'})),
                $zoom_in_button   = new Element('button', {type: 'button', className: 'btn gallery-options', style: 'right:5px;'})
                    .update(new Element('img', {src: window.dir_images + 'icons/silk/magnifier_zoom_in.png'})),
                i;

            // Reload tree.
            var tree_reloader = function () {
				[{if $smarty.get.objID > 0}]
                get_tree_by_object(
                    '[{$smarty.get.objID|strip_tags|escape}]',
	                [{$smarty.const.C__CMDB__VIEW__TREE_OBJECT}],
					[{if isset($smarty.get.catgID)}]'[{$smarty.get.catgID|strip_tags|escape}]'[{else}]null[{/if}],
					[{if isset($smarty.get.catsID)}]'[{$smarty.get.catsID|strip_tags|escape}]'[{else}]null[{/if}]
                );
				[{/if}]
            };

            var imageOrderPopup = Class.create({
                $popupContainer:  null,
                $imagesContainer: null,
                initialize:       function ($popupContainer, $imagesContainer) {
                    this.$popupContainer = $popupContainer;
                    this.$imagesContainer = $imagesContainer;

                    this.buildPopup();
                },

                buildPopup: function () {
                    this.$popupContainer
                        .update(new Element('h3', {className: 'popup-header'}).update('[{isys type="lang" ident="LC__CATG__IMAGES__ORDER_IMAGES"}]'))
                        .insert(new Element('div', {className: 'popup-content', style: 'position:absolute;top:33px;bottom:35px;'})
                            .update(new Element('ul', {className: 'list-style-none m0'})))
                        .insert(new Element('div', {className: 'popup-footer'})
                            .update(new Element('button', {type: 'button', className: 'btn'})
                                .update(new Element('img', {src: window.dir_images + 'icons/silk/disk.png', className: 'mr5'}))
                                .insert(new Element('span').update('[{isys type="lang" ident="LC__CATG__IMAGES__SAVE_ORDER"}]'))));
                },

                attachObserver: function () {
                    this.$popupContainer.down('.popup-footer .btn').on('click', this.saveImageOrder.bindAsEventListener(this));
                    this.$popupContainer.select('li[data-image-id]').invoke('on', 'mouseenter', this.highlightImage.bindAsEventListener(this));
                    this.$popupContainer.select('li[data-image-id]').invoke('on', 'mouseleave', this.lowlightImage.bindAsEventListener(this));
                },

                refreshPopupContent: function () {
                    var $list   = this.$popupContainer.down('ul'),
                        $images = this.$imagesContainer.select('div.gallery-item'),
                        that    = this;

                    $list.update(new Element('li')
                        .update(new Element('img', {src: window.dir_images + 'ajax-loading.gif', className: 'mr5'}))
                        .insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]')));

                    new Ajax.Request('?ajax=1&call=images&func=getImagesData', {
                        parameters: {
                            images: JSON.stringify($images.invoke('readAttribute', 'data-image-id'))
                        },
                        onComplete: function (xhr) {
                            var json = xhr.responseJSON, i, $li;

                            $list.update();

                            if (json.success) {
                                for (i in json.data) {
                                    if (!json.data.hasOwnProperty(i)) {
                                        continue;
                                    }

                                    $li = new Element('li', {'data-image-id': json.data[i].id})
                                        .update(new Element('div', {className: 'handle'}))
                                        .insert(new Element('span').update(json.data[i].imageName));

                                    $list.insert($li);
                                }
                            }

                            Sortable.destroy($list);

                            Sortable.create($list, {
                                overlap: 'vertical',
                                handle:  'handle',
                                onChange: function(){
                                    that.orderImagesGallery($list.select('li[data-image-id]').invoke('readAttribute', 'data-image-id'));
                                }
                            });

                            that.attachObserver();
                        }
                    });
                },

                saveImageOrder: function (ev) {
                    var imagesOrder = this.$popupContainer.select('li[data-image-id]').invoke('readAttribute', 'data-image-id'),
                        $button = ev.findElement('button');

                    $button.disable()
                        .down('img').writeAttribute('src', window.dir_images + 'ajax-loading.gif')
                        .next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

                    new Ajax.Request('?ajax=1&call=images&func=saveImagesOrder', {
                        parameters: {
                            images: JSON.stringify(imagesOrder)
                        },
                        onComplete: function (xhr) {
                            var json = xhr.responseJSON;

                            if (json.success) {
                                idoit.Notify.success('[{isys type="lang" ident="LC__INFOBOX__DATA_WAS_SAVED"}]');

                                this.orderImagesGallery(imagesOrder);
                            } else {
                                idoit.Notify.success('[{isys type="lang" ident="LC__INFOBOX__DATA_WAS_NOT_SAVED"}]');
                            }

                            $button.enable()
                                .down('img').writeAttribute('src', window.dir_images + 'icons/silk/disk.png')
                                .next('span').update('[{isys type="lang" ident="LC__CATG__IMAGES__SAVE_ORDER"}]');
                        }.bind(this)
                    });
                },

                highlightImage:function(ev) {
                    var imageId = ev.findElement('li').readAttribute('data-image-id');

                    this.$imagesContainer.down('[data-image-id="' + imageId + '"]').addClassName('active')
                },

                lowlightImage:function(ev) {
                    var imageId = ev.findElement('li').readAttribute('data-image-id');

                    this.$imagesContainer.down('[data-image-id="' + imageId + '"]').removeClassName('active')
                },

                orderImagesGallery: function (order) {
                    var i;

                    order.reverse();

                    for (i in order) {
                        if (!order.hasOwnProperty(i)) {
                            continue;
                        }

                        this.$imagesContainer.insert({top: this.$imagesContainer.down('[data-image-id="' + order[i] + '"]')});
                    }
                },

                open: function () {
                    this.refreshPopupContent();

                    this.$popupContainer.removeClassName('hide');
                },

                close: function () {
                    this.$popupContainer.addClassName('hide');
                }
            });

			[{if $is_allowed_to_edit}]
            new qq.FileUploader({
                element:                           $droppable,
                action:                            '[{$ajax_url}]&action=save',
                multiple:                          true,
                autoUpload:                        true,
                sizeLimit:                         5242880, // About 5 MB.
                allowedExtensions:                 ['bmp', 'png', 'jpg', 'jpeg', 'gif'],
                onUpload:                          function (id) {
                    // Create a blank "thumbnail" for the GUI.
                    $gallery.insert(render_thumb(id));
                },
                onComplete:                        function (id, filename, response) {
                    if (response.success && response.data.success && response.data.data > 0)
                    {
                        load_image(id, response.data.data);
                        tree_reloader();
                    }
                    else
                    {
                        $('thumb-' + id).addClassName('deleted');

                        setTimeout(function () {
                            $('thumb-' + id).remove();
                        }, 550);

                        idoit.Notify.error(filename + ': ' + (response.message || '[{isys type="lang" ident="LC__CATG__IMAGES__DELETE_IMAGE_ERROR"}]'), {sticky: true});
                    }
                },
                onProgress:                        function (id, filename, loaded, total) {
                    var $bar = $('thumb-' + id).down('.bar');

                    new Effect.Morph($bar, {
                        style:    'width:' + ((loaded / total) * 100) + '%',
                        duration: 0.1
                    });
                },
                onError:                           function (id, filename, response) {
                    // This does not get triggered reliably...
                    idoit.Notify.error(response.message || '[{isys type="lang" ident="LC__CATG__IMAGES__UPLOAD_IMAGE_ERROR"}] "' + filename + '".', {sticky: true})
                },
                dragText:                          '[{isys type="lang" ident="LC_FILEBROWSER__DROP_FILE"}]',
                multipleFileDropNotAllowedMessage: '[{isys type="lang" ident="LC_FILEBROWSER__SINGLE_FILE_UPLOAD"}]',
                uploadButtonText:                  '<img src="[{$dir_images}]icons/silk/zoom.png" alt="" class="vam mr5" style="margin-top:-1px; height:16px;" /><span style="vertical-align:baseline;">[{isys type="lang" ident="LC__UNIVERSAL__FILE_ADD"}]</span>',
                cancelButtonText:                  '&nbsp;',
                failUploadText:                    '[{isys type="lang" ident="LC__UNIVERSAL__ERROR"}]'
            });

            $orderButton = new Element('button', {type: 'button', className: 'btn gallery-options', style: 'right:100px;'})
                .update(new Element('img', {src: window.dir_images + 'icons/silk/timeline_marker_rotated.png'}));

            var imageOrderer = new imageOrderPopup($orderPopup, $gallery);

            $orderButton.on('click', function () {
                if ($orderPopup.hasClassName('hide')) {
                    imageOrderer.open();
                } else {
                    imageOrderer.close();
                }
            });
			[{/if}]

            for (i in images) {
                if (images.hasOwnProperty(i)) {
                    $gallery.insert(render_thumb('a' + i));

                    load_image('a' + i, images[i]);
                }
            }

            $container.on('click', 'button.image-deleter', function (ev) {
                var $button        = ev.findElement('button').disable(),
                    $gallery_item  = $button.up('.gallery-item');

                if (confirm('[{isys type="lang" ident="LC__CATG__IMAGES__DELETE_IMAGE_CONFIRM" p_bHtmlEncode=false}]'))
                {
                    new Ajax.Request('[{$ajax_url}]&action=delete', {
                        parameters: {
                            image_id: $gallery_item.readAttribute('data-image-id')
                        },
                        method:     "post",
                        onSuccess:  function (transport) {
                            var json = transport.responseJSON;

                            if (json.success && json.data) {
                                $gallery_item.addClassName('deleted');

                                setTimeout(function () {
                                    $gallery_item.remove();
                                }, 550);
                                tree_reloader();
                            } else {
                                $button.enable();
                                idoit.Notify.error(json.message || '[{isys type="lang" ident="LC__CATG__IMAGES__DELETE_IMAGE_ERROR"}]', {sticky: true});
                            }
                        }
                    });
                }
            });

            $container.on('click', 'img.thumb', function (ev) {
                var $popup      = $('popup'),
                    $img        = ev.findElement('img'),
                    window_size = document.viewport.getDimensions(),
                    width       = $img.naturalWidth + 30,
                    height      = $img.naturalHeight + 30,
                    ratio;

                if (width >= window_size.width || height >= window_size.height) {
                    if ((window_size.width - width) > (window_size.height - height)) {
                        ratio = (height / window_size.height);
                    } else {
                        ratio = (width / window_size.width);
                    }

                    width = (width / ratio) - 30;
                    height = (height / ratio) - 30;
                }

                width -= 20;
                height -= 20;

                if (width < 50) {
                    width = 50;
                }

                if (height < 50) {
                    height = 50;
                }

                $popup
                    .update(new Element('div', {className: 'p5', style: 'background:url("[{$dir_images}]pattern3.png"); overflow: auto;'})
                        .update(new Element('img', {src: $img.readAttribute('src'), className: 'm10 mouse-pointer', style: 'width:' + (width - 30) + 'px; height:' + (height - 30) + 'px;'})));

                $popup.down('img').on('click', function () {
                    popup_close();
                });

                popup_open('popup', width, height);
            });

            $zoom_out_button.on('click', function () {
                if (thumb_size > 50)
                {
                    thumb_size -= 50;

                    repaint();
                }
            });

            $zoom_null_button.on('click', function () {
                thumb_size = 300;

                repaint();
            });

            $zoom_in_button.on('click', function () {
                thumb_size += 50;

                repaint();
            });

            function render_thumb(id) {
                var $thumb    = new Element('div', {id: 'thumb-' + id, className: 'gallery-item', 'data-image-id': 0, style: 'width:' + thumb_size + 'px; height:' + thumb_size + 'px'}),
                    $deleter  = new Element('button', {type: 'button', className: 'btn image-deleter' })
	                    .update(new Element('img', {src: '[{$dir_images}]icons/silk/cross.png', className: 'mr5'}))
	                    .insert(new Element('span').update('[{isys type="lang" ident="LC_UNIVERSAL__DELETE"}]')),
                    $img      = new Element('img', {className: 'thumb'}).hide(),
                    $loader   = new Element('img', {src: '[{$dir_images}]ajax-loading.gif', className: 'loader'}),
                    $progress = new Element('div', {class: 'progress-bar'}).update(new Element('div', {className: 'bar'}));

				[{if !$is_allowed_to_delete}]
                $deleter = '';
				[{/if}]

                return $thumb.update($deleter).insert($img).insert($loader).insert($progress);
            }

            function load_image(id, data) {
                var $thumb = $('thumb-' + id).writeAttribute('data-image-id', data),
                    $img   = $thumb.down('.thumb');

                $img.on('load', function (ev) {
                    var $img         = ev.findElement('img'),
                        viewer_title = '[{isys type="lang" ident="LC__CATG__IMAGES__VIEW_BUTTON" p_bHtmlEncode=false}]'
	                        .replace('%s', $img.naturalWidth + 'x' + $img.naturalHeight);

                    $img
                        .writeAttribute('title', viewer_title)
                        .setStyle({margin: ((thumb_size - $img.getHeight()) / 2) + 'px ' + ((thumb_size - $img.getWidth()) / 2) + 'px'})
                        .next('.loader').fade()
                        .next('.progress-bar').morph('height:0; opacity:0;');

                    // The "appear" effect needs a callback, because some browsers will write attributes like ' width="299" '.
                    new Effect.Appear($img, {
                        duration:    0.5,
                        afterFinish: function () {
                            $img.writeAttribute({
                                width:  null,
                                height: null
                            });
                        }
                    });

                    // Update the "drop area" to the new height.
                    if ($container.down('.qq-upload-drop-area')) {
                        $container.down('.qq-upload-drop-area').setStyle({height: $container.getHeight() + 'px'});
                    }
                });

                $img.writeAttribute('src', '[{$image_url}]&[{$smarty.const.C__GET__FILE__ID}]=' + data);
            }

            function repaint() {
                $container.select('.gallery-item').each(function ($thumb) {
                    var $img = $thumb
                        .setStyle({width: thumb_size + 'px', height: thumb_size + 'px'})
                        .down('.thumb');

                    $img.setStyle({margin: ((thumb_size - $img.getHeight()) / 2) + 'px ' + ((thumb_size - $img.getWidth()) / 2) + 'px'});
                });

                // Update the "drop area" to the new height.
                if ($container.down('.qq-upload-drop-area')) {
                    $container.down('.qq-upload-drop-area').setStyle({height: $container.getHeight() + 'px'});
                }
            }

            // Add the "zoom" buttons.
            $droppable
                .insert($orderButton)
                .insert($zoom_out_button)
                .insert($zoom_null_button)
                .insert($zoom_in_button)
                .insert($orderPopup);
        });
    })();
</script>

<style type="text/css">
	#catg-images-droppable {
		position: relative;
		padding: 5px;
		height: 25px;
	}

	#catg-images-droppable .qq-upload-drop-area {
		position: absolute;
		width: 100%;
		height: 150px;
		top: -5px;
		left: -5px;
		background: rgba(0, 0, 0, .5);
	}

	#catg-images-droppable .qq-upload-list {
		position: absolute;
		right: 5px;
		z-index: 200;
	}

	#catg-images-droppable .qq-upload-list {
		display: none !important;
	}

	#catg-images-droppable .qq-upload-list li {
		margin-top: 5px;
	}

	#catg-images-droppable .qq-upload-drop-area span {
		color: #fff;
		font-size: 20px;
		font-weight: bold;
		text-shadow: 0 0 5px #000;
	}

	#catg-images-droppable .gallery-options {
		position: absolute;
		top: 5px;
		right: 5px;
		z-index: 100;
	}

	#catg-images-gallery {
		min-height: 300px;
	}

	#catg-images-gallery .progress-bar {
		height: 5px;
		background: #333;
	}

	#catg-images-gallery .progress-bar .bar {
		width: 1%;
		height: 5px;
		background: #090;
	}

	#catg-images-gallery div.gallery-item {
		position: relative;
		float: left;
		border: 1px solid #aaa;
		margin: 5px;
		background: #fff url('[{$dir_images}]pattern3.png');
		overflow: hidden;
		-webkit-transition: transform 500ms, opacity 500ms;
		-moz-transition: transform 500ms, opacity 500ms;
		transition: transform 500ms, opacity 500ms;
	}

	#catg-images-gallery div.gallery-item.active {
		border: 3px solid #a00;
		margin: 3px;
	}

	#catg-images-gallery div.gallery-item * {
		-webkit-transition: transform 500ms, opacity 500ms;
		-moz-transition: transform 500ms, opacity 500ms;
		transition: transform 500ms, opacity 500ms;
	}

	#catg-images-gallery div.gallery-item.deleted {
		transform: scale(0.5);
		opacity: 0;
	}

	#catg-images-gallery div.gallery-item button {
		opacity: 0;
		position: absolute;
		top: 5px;
		right: 5px;
	}

	/* This style is necessary, because a button will be moved down and right, if clicked. */
	#catg-images-gallery div.gallery-item button.image-deleter:active {
		top: 6px;
		right: 4px;
		left: auto;
	}

	#catg-images-gallery div.gallery-item:hover button {
		opacity: 1;
	}

	#catg-images-gallery img.loader {
		position: absolute;
		left: 50%;
		top: 50%;
		margin: -8px;
	}

	#catg-images-gallery img.thumb {
		max-width: 100%;
		max-height: 100%;
		cursor: pointer;
	}

	#catg-images-order-popup {
		position: absolute;
		top: 35px;
		right: 5px;
		width: 320px;
		height: 480px;
		z-index: 100;
	}

	#catg-images-order-popup li {
		padding: 5px;
		height: 25px;
		border-bottom: 1px solid #eee;
		clear: both;
	}

	#catg-images-order-popup li:last-of-type {
		border-bottom: none;
	}

	#catg-images-order-popup li span {
		float: left;
		width: 280px;
		text-overflow: ellipsis;
		white-space: nowrap;
		overflow: hidden;
	}

	#catg-images-order-popup .handle {
		float: left;
		width: 10px;
		height: 15px;
		margin-right: 5px;
		background: url('[{$dir_images}]icons/hatch.gif');
		cursor: ns-resize;
	}
</style>