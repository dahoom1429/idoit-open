'use strict';

function smartyFilesUpload(id, type, options) {
    const $container = $(id);
    
    if (!$container) {
        return;
    }

    const uploader = new qq.FileUploader({
        element: $container.down('.container'),
        params: {type: type},
        action: options.ajaxURL,
        multiple: false,
        autoUpload: false,
        sizeLimit: (options.sizeLimit || 0),
        allowedExtensions: (options.validExtensions || []),
        onSubmit: function (id, filename) {
            $uploadButton.removeClassName('hide');
            $fileSearchButton.addClassName('hide');
        },
        onComplete: function (id, filename, json) {
            if (json.success) {
                idoit.callbackManager.triggerCallback('smarty-ajax-file-upload', json.data);
            } else {
                idoit.Notify.error(json.message);
            }
            uploader.clearStoredFiles();
            $uploadButton.addClassName('hide');
            $fileSearchButton.removeClassName('hide');
        },
        onCancel: function (id, filename) {
            $uploadButton.addClassName('hide');
            $fileSearchButton.removeClassName('hide');
        },
        onError: function (id, filename, xhr) {
            idoit.Notify.error('Failed to upload');
            
            uploader.clearStoredFiles();
            $uploadButton.addClassName('hide');
            $fileSearchButton.removeClassName('hide');
        },
        dragText: idoit.Translate.get('LC_FILEBROWSER__DROP_FILE'),
        multipleFileDropNotAllowedMessage: idoit.Translate.get('LC_FILEBROWSER__SINGLE_FILE_UPLOAD'),
        uploadButtonText: '<img src="' + window.dir_images + 'icons/silk/zoom.png" alt="" class="vam mr5" style="margin-top:-1px;" />' +
                          '<span class="text-normal" style="vertical-align:baseline;">' + idoit.Translate.get('LC__UNIVERSAL__FILE_ADD') + '</span>',
        cancelButtonText: '&nbsp;',
        failUploadText: idoit.Translate.get('LC__UNIVERSAL__ERROR')
    });
    
    $container.down('.btn').on('click', function () {
        uploader.uploadStoredFiles();
    });
    
    // Re-select this button, because it gets created by the file-uploader.
    const $uploadButton = $container.down('button');
    const $fileSearchButton = $container.down('.qq-upload-button');
}
