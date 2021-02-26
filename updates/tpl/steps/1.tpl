<h2>i-doit Update</h2>
<table class="info">
    <colgroup>
        <col width="150" />
    </colgroup>
    <tr>
        <td colspan="2"><h3>Compatibility check</h3></td>
    </tr>
    <tr>
        <td class="key">Operating System:</td>
        <td>[{$g_os.name}]</td>
    </tr>
    <tr>
        <td class="key">version:</td>
        <td>[{$g_os.version}]</td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>
    [{if $php_version_message}]
        <tr>
            <td class="key">PHP version</td>
            <td><p class="box-[{$php_version_message_color}] bold p5">[{$php_version_message}]</p></td>
        </tr>
    [{else}]
        <tr>
            <td class="key">PHP version</td>
            <td><img src="[{$dir_images}]icons/silk/tick.png" class="vam mr5" /><span class="vam">[{$smarty.const.PHP_VERSION}] (PHP [{$smarty.const.UPDATE_PHP_VERSION_MINIMUM_RECOMMENDED}] recommended)</span></td>
        </tr>
    [{/if}]
    [{if $sql_version_error}]
        <tr>
            <td class="key">[{if $dbTitle != ''}][{$dbTitle}][{else}]MySQL[{/if}] version</td>
            <td><p class="box-red bold p5">[{$sql_version_error}]</p></td>
        </tr>
    [{else}]
        <tr>
            <td class="key">[{if $dbTitle != ''}][{$dbTitle}][{else}]MySQL[{/if}] version</td>
            <td>
                [{if $miniumDbVersion != ''}]
                [{$currentDbVersion}] ([{$dbTitle}] [{$recommendedDbVersion}] recommended)
                [{else}]
                [{$smarty.const.MYSQL_VERSION_MINIMUM}] (MySQL [{$smarty.const.MYSQL_VERSION_MINIMUM_RECOMMENDED}] recommended)
                [{/if}]
            </td>
        </tr>
    [{/if}]
    [{if $addon_version_notification}]
        <tr>
            <td class="key">Add-on versions</td>
            <td>
                <p class="box-yellow bold p5">[{$addon_version_notification}]</p>
            </td>
        </tr>
    [{/if}]
    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td class="key" style="vertical-align: top;">PHP Settings</td>
        <td>
            <ul>
                [{foreach $php_settings as $setting => $data}]
                    <li><strong>[{$setting}]</strong> <span style="float:left; width:50px;">[{$data.value}]</span> [{if $data.check}]<img
                            src="[{$dir_images}]icons/silk/tick.png" />[{else}]<img src="[{$dir_images}]icons/silk/cross.png" /><span
                                    class="red">[{$data.message}]</span>[{/if}]</li>
                [{/foreach}]
            </ul>
        </td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td class="key" style="vertical-align: top;">PHP Extensions</td>
        <td>
            <ul>
                [{foreach $dependencies as $dependency => $module}]
                    [{if $dependency == "mysql" && version_compare($smarty.const.PHP_VERSION, '5.6') === 1}]
                        <li><strong>[{$dependency}] <img src="[{$dir_images}]icons/silk/information.png" class="mouse-help"
                                                         title="Used by [{$module|implode:', '}]" /></strong> [{if extension_loaded("mysqli")}]<img
                                src="[{$dir_images}]icons/silk/tick.png" />
                                <span class="green">OK</span>
                            [{else}]<img src="[{$dir_images}]icons/silk/cross.png" />
                                <span class="red">NOT FOUND</span>
                            [{/if}]</li>
                    [{else}]
                        <li><strong>[{$dependency}] <img src="[{$dir_images}]icons/silk/information.png" class="mouse-help"
                                                         title="Used by [{$module|implode:', '}]" /></strong> [{if extension_loaded($dependency)}]<img
                                src="[{$dir_images}]icons/silk/tick.png" />
                                <span class="green">OK</span>
                            [{else}]<img src="[{$dir_images}]icons/silk/cross.png" />
                                <span class="red">NOT FOUND</span>
                            [{/if}]</li>
                    [{/if}]
                [{/foreach}]
            </ul>
        </td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td class="key" style="vertical-align: top;">Apache modules</td>
        <td>
            <ul>
                [{foreach $apache_dependencies as $dependency => $module}]
                    <li>
                        <strong>[{$dependency}]
                            <img src="[{$dir_images}]icons/silk/information.png" class="mouse-help" title="Used by [{$module|implode:', '}]" />
                        </strong>
                        [{if isys_update::is_webserver_module_installed($dependency)}]
                            <img src="[{$dir_images}]icons/silk/tick.png" />
                            <span class="green">OK</span>
                        [{else}]
                            <img id="webserver_rewrite_status_icon" src="[{$dir_images}]icons/silk/cross.png" />
                            <span id="webserver_rewrite_status" class="red">NOT FOUND</span>
                            [{if $dependency==='mod_rewrite'}]
                                <button type="button" id="mod_rewrite_test_button" class="testButton"><img src="[{$dir_images}]icons/silk/server.png" class="mr5" /><span>Test</span></button>
                            [{/if}]
                        [{/if}]
                    </li>
                [{/foreach}]
            </ul>
        </td>
    </tr>
    <tr>
        <td colspan="2"><h3>i-doit</h3></td>
    </tr>
    <tr>
        <td class="key">Current version</td>
        <td>[{$g_info.version|default:"<= 0.9"}]</td>
    </tr>
    <tr>
        <td class="key">Current revision</td>
        <td>[{$g_info.revision|default:"<= 2500"}]</td>
    </tr>
</table>

<style type="text/css">
    ul, li {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    li strong {
        clear: both;
        width: 110px;
        display: block;
        float: left;
    }

    li strong img {
        height: 12px;
    }

    li strong,
    li span,
    li img {
        vertical-align: middle;
    }

    li strong,
    li img {
        margin-right: 5px;
    }

    .mouse-help {
        cursor: help;
    }

    span.green {
        color: #009900;
    }

    span.red {
        color: #AA0000;
    }
    .testButton {
        width: 100px;
        background-color: #eee;
        color: #000000;
        border: 1px solid #888888;
        height: 20px;
        margin: 0px 20px;
        vertical-align: top;
    }
    .testButton:hover, .testButton:focus {
        cursor: pointer;
        background-color: #bbb;
        border: 1px solid #444;
    }
</style>

<script type="text/javascript">
    (function () {
        'use strict';

        var $testButton = $('mod_rewrite_test_button');
        var $nextButton = $('main_next');

        if ($testButton) {
            var $modRewriteStatus = $('webserver_rewrite_status');
            var $modRewriteStatusIcon = $('webserver_rewrite_status_icon');

            $testButton.on('click', function () {
                $testButton
                    .disable()
                    .down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
                    .next('span').update('Loading');

                $modRewriteStatus
                    .update('...')
                    .removeClassName('red')
                    .removeClassName('green');
                $modRewriteStatusIcon.writeAttribute('src', '[{$dir_images}]ajax-loading.gif')

                new Ajax.Request('mod-rewrite-test', {
                    onFailure:  function (xhr) {
                        $testButton
                            .enable()
                            .down('img').writeAttribute('src', '[{$dir_images}]icons/silk/server.png')
                            .next('span').update('Test');
                        $modRewriteStatusIcon.writeAttribute('src', '[{$dir_images}]icons/silk/cross.png')
                        $modRewriteStatus
                            .update('ERROR')
                            .addClassName('red');
                    },
                    onComplete: function (xhr) {
                        $testButton
                            .enable()
                            .down('img').writeAttribute('src', '[{$dir_images}]icons/silk/server.png')
                            .next('span').update('Test');
                        if (xhr.status == 200) {
                            $modRewriteStatusIcon.writeAttribute('src', '[{$dir_images}]icons/silk/tick.png')
                            $modRewriteStatus
                                .update('OK')
                                .addClassName('green');
                            if ($nextButton) {
                                $nextButton.enable();
                            }
                        } else {
                            $modRewriteStatusIcon.writeAttribute('src', '[{$dir_images}]icons/silk/cross.png')
                            $modRewriteStatus
                                .update('NOT FOUND')
                                .addClassName('red');
                        }
                    }
                });
            });
        }

    })();
</script>
