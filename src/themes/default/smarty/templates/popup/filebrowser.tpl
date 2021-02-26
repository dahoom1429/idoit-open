<style type="text/css">
    #file_browser #file_browser_filetree {
        width: 565px;
        overflow: auto;
        float: left;
	    padding: 5px;
    }

    #file_browser #file_browser_filetree div.node span {
        max-width: 450px;
    }

    /* Special styles for the right box */
    #file_browser #file_browser_fileinfo {
        width: 350px;
        height: 100%;
        float: right;
        border-left: 1px solid #aaa;
        background: #eee;
	    overflow-x: hidden;
    }

    #file_browser #file_browser_fileinfo table {
        display: block;
    }

    #file_browser #file_browser_fileinfo table td.value div {
        overflow: hidden;
    }

    #file_browser #file_browser_fileinfo table td.key {
        text-align: right;
        font-weight: bold;
        vertical-align: top;
        padding-right: 10px;
        width: 70px;
    }

	#file_browser #file_browser_fileinfo table td.value {
		width: 260px;
	}

	#file_browser_upload .qq-upload-list {
		width: 275px;
		float:none;
		display:block;
	}

	#file_browser_upload .qq-upload-cancel {
		margin-right: 0;
	}

	#file_browser_upload .qq-upload-button {
		float: none;
	}

	#file_browser_new_file label {
		display: block;
	}

	#file_browser_upload_wrapper {
		border: 1px dashed #888;
	}
</style>

<div id="file_browser">
	<h3 class="popup-header">
		<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]icons/close-circle.png" />
		<span>[{isys type="lang" ident="LC__POPUP__BROWSER__FILE_TITLE"}]</span>
	</h3>

	<div class="popup-content">
		<div id="file_browser_filetree">
            [{$browser}]
		</div>

		<div id="file_browser_fileinfo">
			<fieldset class="overview">
				<legend><span style="border-top:none;">[{isys type="lang" ident="LC_FILEBROWSER__SEARCH_FILE"}]</span></legend>

				<div class="mt10">
					[{isys type="f_text" name="file_browser_search" p_strPlaceholder="LC__UNIVERSAL__SEARCH" p_bInfoIconSpacer=0 p_strClass="input-small m5" p_bEditMode=1 editmode=true}]
				</div>
			</fieldset>

			<fieldset class="overview">
				<legend><span>[{isys type="lang" ident="LC_FILEBROWSER__UPLOAD_NEW_FILE"}]</span></legend>

				<div id="file_browser_new_file" class="p5 mt10">
					<p class="mb5 center">[{$new_file_description}]</p>

					<div class="mt5 mb5 p5" id="file_browser_upload_wrapper">
						<div id="file_browser_upload"></div>
					</div>

					[{isys type="f_label" name="file_browser_new_file_name" ident="LC__CMDB__CATS__FILE_NAME"}]
					[{isys type="f_text" p_strClass="input-small" name="file_browser_new_file_name" p_bInfoIconSpacer=0 p_strPlaceholder="LC__CMDB__CATS__FILE_NAME"}]

					<br class="cb" />

					[{isys type="f_label" name="file_browser_new_file_category" ident="LC__CMDB__CATG__GLOBAL_CATEGORY"}]
					[{isys type="f_popup" p_strClass="input-small" p_strPopupType="dialog_plus" name="file_browser_new_file_category" p_strTable="isys_file_category" p_bInfoIconSpacer=0}]

					<br class="cb" />

					<button class="btn btn-block mt10" type="button" id="file_browser_upload_button"><img src="[{$dir_images}]icons/silk/arrow_up.png" class="mr5" /><span>[{isys type="lang" ident="LC_FILEBROWSER__UPLOAD"}]</span></button>
				</div>
			</fieldset>

			<fieldset class="overview">
				<legend><span>[{isys type="lang" ident="LC_FILEBROWSER__SELECTED_FILE"}]</span></legend>

				<table class="mt10">
					<tr>
						<td class="key">[{isys type="lang" ident="LC__CMDB__CATS__FILE_NAME"}]</td>
						<td class="value"><div id="file_browser_finfo_filename"></div></td>
					</tr>
					<tr>
						<td class="key">[{isys type="lang" ident="LC_UNIVERSAL__OBJECT"}]</td>
						<td class="value"><div id="file_browser_finfo_object"></div></td>
					</tr>
					<tr>
						<td class="key">[{isys type="lang" ident="LC_UNIVERSAL__REVISION"}]</td>
						<td class="value"><div id="file_browser_finfo_revision"></div></td>
					</tr>
					<tr>
						<td class="key">[{isys type="lang" ident="LC__CMDB__CATS__FILE__SIZE"}]</td>
						<td class="value"><div id="file_browser_finfo_filesize"></div></td>
					</tr>
					<tr>
						<td class="key">[{isys type="lang" ident="LC_UNIVERSAL__UPLOAD_BY"}]</td>
						<td class="value"><div id="file_browser_finfo_uploaded_by"></div></td>
					</tr>
					<tr>
						<td class="key">[{isys type="lang" ident="LC_UNIVERSAL__DATE"}]</td>
						<td class="value"><div id="file_browser_finfo_created_at"></div></td>
					</tr>
					<tr>
						<td class="key">[{isys type="lang" ident="LC_UNIVERSAL__CATEGORY"}]</td>
						<td class="value"><div id="file_browser_finfo_category"></div></td>
					</tr>
				</table>
			</fieldset>
		</div>
	</div>

	<div class="popup-footer">
		<button id="file_browser_save" type="button" class="btn">
			<img class="mr5" src="[{$dir_images}]icons/silk/tick.png"><span>[{isys type="lang" ident="LC_UNIVERSAL__ACCEPT"}]</span>
		</button>

		<button id="file_browser_close" type="button" class="btn ml5 popup-closer">
			<img class="mr5" src="[{$dir_images}]icons/silk/cross.png"><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
		</button>
	</div>
</div>

<script type="text/javascript">
	(function () {
		"use strict";

		var $new_filename = $('file_browser_new_file_name'),
			$file_tree = $('file_browser_filetree'),
			file_infos = JSON.parse('[{$file_infos|json_encode|escape:"javascript"}]'),
			selection = null;

		$('popup').setStyle({height:'auto'});

		$file_tree.on('click', '.file-object', function (ev) {
			var $el = ev.findElement('span');

			if ($el.up('.nodeDisabled')) {
			    // Do not allow to select disabled objects.
			    return;
			}

			$file_tree.select('.file-object.text-bold').invoke('removeClassName', 'text-bold');

			if ($el.hasAttribute('data-file-version-id')) {
				$el.addClassName('text-bold');

				select_file($el.readAttribute('data-file-version-id'));
			} else {
				idoit.Notify.warning('[{isys type="lang" ident="LC_FILEBROWSER__NO_FILE_FOUND"}]', {life:5});
			}
		});

		var select_file = function select_file (file_id) {
			var file = (file_infos.hasOwnProperty(file_id)) ? file_infos[file_id] : null;

			if (file !== null) {
				$('file_browser_finfo_filename').update(file.filename);
				$('file_browser_finfo_object').update(file.fileobj_name + ' (#' + file.obj_id + ')');
				$('file_browser_finfo_revision').update(file.filerevision);
				$('file_browser_finfo_filesize').update(file.filesize);
				$('file_browser_finfo_uploaded_by').update(file.uploaded_by);
				$('file_browser_finfo_created_at').update(file.created_at);
				$('file_browser_finfo_category').update(file.category);

	            selection = {
		            'obj_id':file.obj_id,
				    'obj_title':file.fileobj_name
	            }
	        }
	    };

		if ($file_tree.down('.text-bold[data-file-version-id]')) {
			select_file($file_tree.down('.text-bold[data-file-version-id]').readAttribute('data-file-version-id'));
	    }

		$('file_browser_search').on('keyup', function () {
			var search = this.value,
	            search_regex;

	        if (search.blank()) {
		        $file_tree.select('div.node').invoke('setOpacity', 1);
	        } else {
		        search_regex = new RegExp(search, 'i');

		        $file_tree.select('div.node').each(function (el) {
			        if (search_regex.test(el.down('span').innerHTML)) {
				        el.setOpacity(1);

				        // Open all necessary nodes to display the found node.
	                    window.file_browser_filetree.openTo(el.readAttribute('data-nodeid'));
	                } else {
				        el.setOpacity(0.3);
	                }
		        }.bind(this));
	        }
	    });

		$('file_browser_save').on('click', function () {
			if (selection !== null) {
                var callback_func = "[{$callback_accept}]";
                var $hiddenElement = ($('[{$return_name}]'.split('[').join('__HIDDEN[')) ? $('[{$return_name}]'.split('[').join('__HIDDEN[')) : $('[{$return_name}]'.concat('__HIDDEN')));
                var $viewElement = ($('[{$return_name}]'.split('[').join('__VIEW[')) ? $('[{$return_name}]'.split('[').join('__VIEW[')) : $('[{$return_name}]'.concat('__VIEW')));

                $viewElement.value = '[{isys type="lang" ident="LC__CMDB__OBJTYPE__FILE"}] >> ' + selection.obj_title;
				$hiddenElement.value = selection.obj_id;

                if(callback_func != "")
                {
                    eval(callback_func);
                }
			}

			popup_close($('popup_commentary'));
		});

		$$('.popup-closer').invoke('on', 'click', function () {
			popup_close($('popup_commentary'));
		});

		[{if !$upload_rights}]
		$('file_browser_new_file').hide();
		[{/if}]

	    var uploader = new qq.FileUploader({
	        element:$('file_browser_upload'),
	        action:'?call=file&func=create_new_file_version&ajax=1',
		    multiple:false,
	        autoUpload:false,
		    onSubmit:function(id, filename) {
			    $new_filename.setValue(filename);
		    },
	        onComplete:function(id, filename, response) {
	            if (response.success == true || response.success == 'true') {
	                load_file_tree();

		            $new_filename.setValue();
		            uploader.clearStoredFiles();
	            }
	        },

	        dragText: '[{isys type="lang" ident="LC_FILEBROWSER__DROP_FILE"}]',
	        multipleFileDropNotAllowedMessage: '[{isys type="lang" ident="LC_FILEBROWSER__SINGLE_FILE_UPLOAD"}]',
	        uploadButtonText: '<img src="[{$dir_images}]icons/silk/zoom.png" alt="" class="vam mr5" style="margin-top:-1px; height:16px;" /><span style="vertical-align:baseline;">[{isys type="lang" ident="LC__UNIVERSAL__FILE_ADD"}]</span>',
	        cancelButtonText: '&nbsp;',
	        failUploadText: '[{isys type="lang" ident="LC__UNIVERSAL__ERROR"}]'
	    });

		uploader.clearStoredFiles();

	    $('file_browser_upload_button').on('click', function() {
		    var file_title = $new_filename.getValue();

		    if (file_title.blank()) {
			    new Effect.Highlight($new_filename, {startcolor:'#ffB7B7', endcolor:'#fbfbfb', restorecolor:'#fbfbfb'});
			    return;
	        }

		    // First we save the object via ajax request, then we upload the image.
	        new Ajax.Request('?call=json&action=createObject&ajax=1',
	            {
	                parameters:{
	                    'objectTitle': file_title,
	                    'objectTypeID': '[{$smarty.const.C__OBJTYPE__FILE}]'
	                },
	                method:'post',
	                onSuccess:function (transport) {
		                var obj_id = transport.responseJSON;

		                if (obj_id > 0) {
					        uploader._options.params = {
						        obj_id:obj_id,
						        category:$('file_browser_new_file_category').getValue(),
						        is_ie:Prototype.Browser.IE
					        };

						    // Finally uplaod the stored file.
					        uploader.uploadStoredFiles();
	                    } else {
			                // @todo Fehlermeldung
	                    }
	                }
	            });
	    });

		var load_file_tree = function load_file_tree () {
	        new Ajax.Request('?call=file&func=get_file_tree_data&ajax=1',
	            {
	                method:'post',
	                onSuccess:function (transport) {
	                    var json = transport.responseJSON;

		                $file_tree.update(json.tree);
	                    file_infos = json.file_infos;
	                }
	            });
	    };
	}());
</script>