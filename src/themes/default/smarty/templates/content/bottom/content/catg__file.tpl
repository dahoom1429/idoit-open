<table class="contentTable">
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__FILE_OBJ_FILE' ident="LC__CMDB__CATG__FILE_OBJ_FILE"}]</td>
        <td class="value">
	        <div id="C__CATG__FILE__FILEUPLOAD"></div>
	        [{isys type="f_popup" p_strPopupType="browser_file" name="C__CATG__FILE_OBJ_FILE" p_bDbFieldNN="0"}]
        </td>
    </tr>
	<tr class="on-page-upload hide">
		<td class="key">[{isys type="f_label" name="C__CATG__FILE__FILEUPLOAD_FILENAME" ident="LC__CMDB__CATS__FILE_NAME"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__FILE__FILEUPLOAD_FILENAME"}]</td>
	</tr>
	<tr class="on-page-upload hide">
		<td class="key">[{isys type="f_label" name="C__CATG__FILE__FILEUPLOAD_FILECATEGORY" ident="LC__CMDB__CATG__GLOBAL_CATEGORY"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__FILE__FILEUPLOAD_FILECATEGORY" p_strTable="isys_file_category"}]</td>
	</tr>
	<tr class="on-page-upload hide">
		<td class="key"></td>
		<td class="value">
			<button type="button" class="ml20 btn" id="C__CATG__FILE__FILEUPLOAD_BUTTON">
				<img src="[{$dir_images}]icons/silk/arrow_up.png" class="mr5" />
				<span>[{isys type="lang" ident="LC_FILEBROWSER__UPLOAD"}]</span>
			</button>
		</td>
	</tr>

    [{if $file_uploaded}]
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__FILE_NAME' ident="LC__CMDB__CATS__FILE_NAME"}]</td>
        <td class="value">[{isys type="f_data" name="C__CATG__FILE_NAME"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='lang' ident="LC__CMDB__CATS__FILE_DOWNLOAD"}]</td>
        <td class="value">
	        [{if $allowedToView}]
	        <a class="btn ml20 text-normal" href="[{$download_link}]" target="_blank">
		        <img src="[{$dir_images}]icons/silk/disk.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__DOWNLOAD_FILE"}]</span>
	        </a>
	        [{else}]
		        <p class="p5 ml20 box-yellow">
			        <img src="[{$dir_images}]icons/silk/error.png" class="mr5 vam" /><span>[{isys type="lang" ident="LC__CMDB__CATG__FILE__DOWNLOAD_MISSING_VIEW_RIGHT"}]</span>
		        </p>
	        [{/if}]
        </td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>
    [{/if}]

    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__FILE__FILE_LINK" name="C__CATG__FILE_LINK"}]</td>
        <td class="value">[{isys type="f_link" name="C__CATG__FILE_LINK"}]</td>
    </tr>
</table>

<script type="text/javascript" src="[{$dir_tools}]js/ajax_upload/fileuploader.js"></script>
[{if isys_glob_is_edit_mode()}]
<script type="text/javascript">
	(function () {
		'use strict';

		var $fileBrowserView = $('C__CATG__FILE_OBJ_FILE__VIEW'),
			$uploadField  = $('C__CATG__FILE__FILEUPLOAD'),
		    $uploadButton = $('C__CATG__FILE__FILEUPLOAD_BUTTON'),
		    $uploadRows   = $$('.on-page-upload'),
		    $fileName     = $('C__CATG__FILE__FILEUPLOAD_FILENAME'),
		    uploader      = new qq.FileUploader({
			    element:                           $uploadField,
			    action:                            '?call=file&func=create_new_file_version&ajax=1',
			    multiple:                          false,
			    autoUpload:                        false,
			    params:                            {
				    action: 'upload'
			    },
			    onSubmit:                          function (id, filename) {
				    // Display the "new file" table rows.
				    $uploadRows.invoke('removeClassName', 'hide');

				    $fileName.writeAttribute({
					    placeholder: filename,
					    value: filename
				    });

				    $fileBrowserView.writeAttribute({
					    placeholder: '[{isys type="lang" ident="LC_FILEBROWSER__NEW_FILE"}]',
					    value: '[{isys type="lang" ident="LC_FILEBROWSER__NEW_FILE"}]'
				    });
			    },
			    onCancel:                          function () {
				    // Hide the "new file" table rows.
				    $uploadRows.invoke('addClassName', 'hide');

				    $fileName.writeAttribute({
					    placeholder: '',
					    value: ''
				    });

				    $fileBrowserView.writeAttribute({
					    placeholder: '[{isys type="lang" ident="LC__CMDB__BROWSER_OBJECT__NONE_SELECTED"}]',
					    value: '[{isys type="lang" ident="LC__CMDB__BROWSER_OBJECT__NONE_SELECTED"}]'
				    });
			    },
			    onComplete:                        function (id, fileName, result) {
				    // Set the VIEW and HIDDEN field accordingly
				    uploader.clearStoredFiles();

				    $('C__CATG__FILE_OBJ_FILE__HIDDEN').setValue(result.data.objectID);
				    $('C__CATG__FILE_OBJ_FILE__VIEW')
						    .setValue(result.data.objectTypeTitle + ' » ' + result.data.objectTitle)
						    .writeAttribute('data-last-value', result.data.objectTypeTitle + ' » ' + result.data.objectTitle)
						    .highlight();

				    $uploadRows.invoke('addClassName', 'hide');
			    },
			    onError:                           function (id, filename, xhr) {
				    // Error handling.
				    idoit.Notify.error(xhr.responseText, {sticky: true});
			    },
			    dragText:                          '[{isys type="lang" ident="LC_FILEBROWSER__DROP_FILE"}]',
			    uploadButtonText:                  '', // This will be hidden anyway...
			    cancelButtonText:                  '&nbsp;',
			    failUploadText:                    '[{isys type="lang" ident="LC__UNIVERSAL__ERROR"}]',
			    multipleFileDropNotAllowedMessage: '[{isys type="lang" ident="LC_FILEBROWSER__SINGLE_FILE_UPLOAD"}]'
		    });

		$uploadButton.on('click', function () {
			var filename = $fileName.getValue();

			if (filename.blank())
			{
				new Effect.Highlight($fileName, {
					startcolor:   '#ffB7B7',
					endcolor:     '#fbfbfb',
					restorecolor: '#fbfbfb'
				});

				return;
			}

			// First we save the object via ajax request, then we upload the image.
			new Ajax.Request('?call=json&action=createObject&ajax=1', {
				parameters: {
					objectTitle:  filename,
					objectTypeID: '[{$smarty.const.C__OBJTYPE__FILE}]'
				},
				method:     'post',
				onComplete: function (xhr) {
					var obj_id = xhr.responseJSON;

					if (obj_id > 0)
					{
						uploader._options.params = {
							obj_id:    obj_id,
							obj_title: filename,
							category:  $('C__CATG__FILE__FILEUPLOAD_FILECATEGORY').getValue(),
							is_ie:     Prototype.Browser.IE
						};

						// Finally uplaod the stored file.
						uploader.uploadStoredFiles();
					}
					else
					{
						idoit.Notify.error(xhr.responseText, {sticky: true});
					}
				}
			});
		});
	})();
</script>

<style type="text/css">
	#C__CATG__FILE__FILEUPLOAD .qq-upload-button,
	#C__CATG__FILE__FILEUPLOAD .qq-upload-list{
		display: none;
	}

	#C__CATG__FILE__FILEUPLOAD .qq-upload-drop-area {
		box-sizing: border-box;
		color: #000;
		font-size: 13px;
		font-weight: normal;
		height: 24px;
		left: 18px;
		min-height: 28px;
		text-align: left;
		top: -2px;
		width: 564px;
	}
</style>
[{/if}]