<table class="contentTable">
    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__FILE_VERSION_TITLE" name="C__CATS__FILE_VERSION_TITLE"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATS__FILE_VERSION_TITLE"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__FILE_VERSION_DESCRIPTION" name="C__CATS__FILE_VERSION_DESCRIPTION"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATS__FILE_VERSION_DESCRIPTION"}]</td>
    </tr>

	[{if $new_file_upload}]

	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__FILE_UPLOAD" name="C__CATS__FILE_UPLOAD"}]</td>
		<td class="value">[{isys type="f_file" name="C__CATS__FILE_UPLOAD"}]</td>
	</tr>

	[{else}]

    <tr>
        <td class="key">[{isys type="lang" ident="LC__CMDB__CATS__FILE_NAME"}]</td>
        <td class="value">[{isys type="f_data" name="C__CATS__FILE_NAME_ORIGINAL"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="lang" ident="LC__CMDB__CATS__FILE_MD5"}]</td>
        <td class="value">[{isys type="f_data" name="C__CATS__FILE_MD5"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="lang" ident="LC__CMDB__CATS__FILE_DIRECTORY"}]</td>
        <td class="value">[{isys type="f_data" name="C__CATS__FILE_DIRECTORY"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="lang" ident="LC__CMDB__CATS__FILE_REVISION"}]</td>
        <td class="value">[{isys type="f_data" name="C__CATS__FILE_REVISION"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="lang" ident="LC__CMDB__CATS__FILE_UPLOAD_FROM"}]</td>
        <td class="value">[{isys type="f_data" name="C__CATS__FILE_UPLOAD_FROM"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="lang" ident="LC__CMDB__CATS__FILE_UPLOAD_DATE"}]</td>
        <td class="value">[{isys type="f_data" name="C__CATS__FILE_UPLOAD_DATE"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="lang" ident="LC__CMDB__CATS__FILE__SIZE"}]</td>
        <td class="value">[{isys type="f_data" name="C__CATS__FILE_SIZE"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="lang" ident="LC__CMDB__CATS__FILE_DOWNLOAD"}]</td>
        <td class="value">
	        <a href="[{$download_link}]" class="ml20">
		        <img src="[{$dir_images}]icons/silk/disk.png" class="vam" /> <span class="vam">[{isys type="lang" ident="LC__CMDB__CATS__FILE_DOWNLOAD"}]</span>
            </a>
	        [{isys type="f_text" p_bInvisible=true name="C__CATS__FILE_VERSION_UPDATE" p_strValue="1"}]
        </td>
    </tr>

    [{/if}]
</table>