<table class="contentTable">
    [{if isys_glob_is_edit_mode()}]
    <tr>
        <td class="key">[{isys type="lang" ident="LC__CMDB__CATG__IMAGE_UPLOADED_IMAGES"}]</td>
        <td class="value">[{isys type="f_dialog" p_bDbFieldNN=0 name="C__CATG__IMAGE_SELECTION" id="C__CATG__IMAGE_SELECTION"}]</td>
    </tr>
    [{/if}]
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__IMAGE_UPLOAD' ident="LC__CMDB__CATG__IMAGE_OBJ_FILE"}]</td>
        <td class="value">
            [{isys type="f_file" name="C__CATG__IMAGE_UPLOAD"}]
            [{if isset($g_image_url)}]
            <a class="btn ml5" href="?[{$g_image_url}]" target="_blank">
                <img src="[{$dir_images}]icons/silk/disk.png" class="mr5" />
                <span>[{isys type="lang" ident="LC__UNIVERSAL__DOWNLOAD_FILE"}]</span>
            </a>
            [{/if}]
            [{if isys_glob_is_edit_mode()}]
            <p class="ml20 mt5 p5 box-blue">
                <img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" />
                <span class="vam">[{isys type="lang" ident="LC__CMDB__CATG__IMAGE_DESCRIPTION"}]</span>
            </p>
            [{/if}]
        </td>
    </tr>
</table>

<script type="text/javascript">
    (function () {
        'use strict';

        const $imageSelection = $('C__CATG__IMAGE_SELECTION');
        const $imageHeader = $('object_image_header');

        if ($imageSelection && $imageHeader) {
            $imageSelection.on('change', function (ev) {
                const imagePath = $imageSelection.getValue();

                if (imagePath === '-1') {
                    $imageHeader.writeAttribute('src', '[{$default_image}]')
                } else {
                    $imageHeader.writeAttribute('src', '?moduleID=[{$smarty.const.C__MODULE__CMDB}]&file_manager=image&file=' + (imagePath).split(/[\\/]/g).pop())
                }
            });
        }
    })();
</script>