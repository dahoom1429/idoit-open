<div id="content" class="content">
    <h2>File-Update</h2>

    <p>The following files will be updated:</p>

    <fieldset class="filelist" id="filelist_truncate">
        <legend>Files ([{$g_filecount}])</legend>
        <div>[{$g_files|truncate:200:".."|nl2br}]</div>
        [{if strlen($g_files)>200}]
            <div style="text-align:right;">
                <a href="javascript:void(0);" onclick="$('filelist').writeAttribute('class', 'filelist'); $('filelist_truncate').writeAttribute('class', 'filelist hidden');">+ Expand</a>
            </div>
        [{/if}]
    </fieldset>

    [{if strlen($g_files)>200}]
        <fieldset class="filelist hidden" id="filelist">
            <legend>Files</legend>
            <div>[{$g_files|nl2br}]</div>
            <div style="text-align:right;">
                <a href="javascript:void(0);" onclick="$('filelist').writeAttribute('class', 'filelist hidden'); $('filelist_truncate').writeAttribute('class', 'filelist');">- Collapse</a>
            </div>
        </fieldset>
    [{/if}]

    [{if $g_not_writeable}]
        <div style="padding: 4px; margin: 10px 2px; background: #FFDDDD; border: 1px solid #FF4343;">
            <h3 style="margin-top:0;">Warning: File copy will not work properly.</h3>
            <p class="bold">
                Please make sure the apache user got write permissions to your complete i-doit directory and reload this page.<br />
                Linux users can use the following shell script: "[{$g_config.base_dir}]idoit-rights.sh set"
            </p>
            <input type="submit" value="Reload" />
        </div>
    [{/if}]

    [{if $g_filecount > 0}]
        <p style="margin: 0;">
            <input type="checkbox" [{if $g_not_writeable || isset($smarty.session.no_temp)}]checked="checked"[{/if}][{if $g_not_writeable}] disabled="disabled"[{/if}] name="no_temp" id="no_temp" value="true" style="vertical-align:middle;" />
            <label for="no_temp">Don't delete temp directories!</label>
        </p>
    [{/if}]

    <div style="padding: 4px; margin: 10px 2px; background: #FFDDDD; border: 1px solid #FF4343;">
        <p style="margin: 0;"><strong>We strongly recommend that you do a database backup of your system and tenant databases before starting this update !</strong></p>
    </div>

    <p style="margin:2px;">Click "Yes, i did a backup!" to start the update procedure.</p>

    <script type="text/javascript">
        Event.observe(window, 'load', function () {
            var $nextButton = $('btn_next');

            if ($nextButton) {
                $nextButton
                    .setStyle({width:'400px', fontWeight:'bold'})
                    .setValue('Yes, i did a backup! - Start the update');
            }
        });
    </script>
</div>

<div id="loadingTable" style="display:none;padding:10px;" class="loadingTable">
    <img src="[{$g_config.www_dir}]setup/images/main_installing.gif" style="vertical-align:middle;" />
    <strong>Update in progress, please wait ...</strong><br />
    <p>Depending on the size of your database and hardware performance, this update could take up to 15 minutes ...</p>
</div>
