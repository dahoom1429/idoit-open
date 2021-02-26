<style type="text/css">
    #system-overview table.listing tbody tr {
        border-top: 1px solid #888888;
    }
</style>

<div id="system-overview">
    <h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__MODULE__SYSTEM__OVERVIEW"}] & Config Check</h2>

    <h3 class="p5 gradient">System</h3>

    <table class="listing" style="border-left: 0;">
        <colgroup>
            <col width="200" />
            <col width="350" />
        </colgroup>
        <tbody>
        <tr>
            <td>[{isys type="lang" ident="LC__MODULE__SYSTEM__OVERVIEW__TIME_DIFFERENCE"}] PHP/MySQL</td>
            <td><strong>[{$timeDiffOut.php}] / [{$timeDiffOut.mysql}]</strong></td>
            <td>
                [{if $timeDiffOut.diff < 2}]
            <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                <span class="vam text-green">OK</span>
                [{else}]
            <img src="[{$dir_images}]icons/silk/cross.png" class="vam" />
                <span class="vam text-red">FAIL</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_COUNT"}] [{$tenant}]</td>
            <td><strong>[{$objectCount}]</strong></td>
            <td></td>
        </tr>
        <tr>
            <td>Operating System</td>
            <td><strong>[{$os}]</strong></td>
            <td>
                [{if $isWindows}]
                <p class="mb10 text-yellow"><img src="[{$dir_images}]icons/silk/error.png" class="vam" /> UNIX/Linux Recommended</p>
                [{/if}]
                <p class="mb10">Please make sure that your operating system meets the system requirements</p>
                <a href="https://kb.i-doit.com/display/de/Systemvoraussetzungen#Systemvoraussetzungen-Betriebssystem" target="_blank">
                    [{isys type="lang" ident="LC__LOCALE__GERMAN"}] <img src="[{$dir_images}]icons/silk/link_go.png" class="vam" />
                </a>
                [{isys type="lang" ident="LC_UNIVERSAL__OR"}]
                <a href="https://kb.i-doit.com/display/en/System+Requirements#SystemRequirements-OperatingSystem" target="_blank">
                    [{isys type="lang" ident="LC__LOCALE__ENGLISH"}] <img src="[{$dir_images}]icons/silk/link_go.png" class="vam" />
                </a>
            </td>
        </tr>
        <tr>
            <td>PHP Version</td>
            <td><strong>[{$php_version}]</strong> (>[{$php_version_recommended}] recommended)
                [{if $php_version_status === 'unsupported'}]
                    <p class="box box-red p5 text-justify" style="font-size: 0.9em">
                        Your PHP version is currently not officially supported.
                        Please have a look at the official system requirements in the <a href="https://kb.i-doit.com/display/en/System+Requirements">Knowledge Base</a>.
                    </p>
                [{elseif $php_version_status === 'failed'}]
                    <p class="box box-red p5 text-justify" style="font-size: 0.9em">
                        Minimum Requirement PHP version is [{$php_version_minimum}].
                        We urgently advise you to update your system to PHP [{$php_version_recommended}],
                        since the PHP version you are using is not supported for any security issues and/or does not get any updates.
                        See <a href="https://php.net/supported-versions.php">https://php.net/supported-versions.php</a> for details.
                        If you need help updating your PHP version, please open a ticket at <a href="https://help.i-doit.com">https://help.i-doit.com</a>, our support team is happy to help you.
                    </p>
                [{elseif $php_version_status === 'deprecated'}]
                    <p class="box box-red p5 text-justify" style="font-size: 0.9em">
                        We discourage the use of PHP version below [{$php_version_deprecated}].
                        We will drop support for it in a future release.
                        We urgently advise you to update your system to PHP [{$php_version_recommended}],
                        since the PHP version you are using is not supported for any security issues and/or does not get any updates.
                        See <a href="http://php.net/supported-versions.php">http://php.net/supported-versions.php</a> for details.
                        If you need help updating your PHP version, please open a ticket at <a href="https://help.i-doit.com">https://help.i-doit.com</a>, our support team is happy to help you.
                    </p>
                [{elseif $php_version_status === 'not_recommended'}]
                    <p class="box box-blue p5 text-justify" style="font-size: 0.9em">
                        You are not using the recommended PHP version [{$php_version_recommended}] on your system.
                        We urgently advise you to update your system to PHP [{$php_version_recommended}].
                        See <a href="https://php.net/supported-versions.php">https://php.net/supported-versions.php</a> for details.
                        If you need help updating your PHP version, please open a ticket at <a href="https://help.i-doit.com">https://help.i-doit.com</a>, our support team is happy to help you.
                    </p>
                [{/if}]
            </td>
            <td>
                [{if $php_version_status === 'ok' || $php_version_status === 'not_recommended'}]
                <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                <span class="vam text-green">OK</span>
                [{elseif $php_version_status === 'unsupported' || $php_version_status === 'failed'}]
                <img src="[{$dir_images}]icons/silk/cross.png" class="vam" />
                <span class="vam text-red">FAIL</span>
                [{else}]
                <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                <span class="vam text-yellow">WARNING</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>i-doit Code Version</td>
            <td><strong>[{$idoit_version.version}]</strong> [{$idoit_version.step}]</td>
            <td></td>
        </tr>
        <tr>
            <td>[{$db_title}] Version</td>
            <td><strong>[{$db_version}]</strong> (>[{$db_version_recommended}] recommended)
                [{if ($db_unsupported_version)}]
                <p class="box box-red p5 text-justify" style="font-size: 0.9em">
                    <strong>WARNING!</strong>
                    <br/>
                    You have [{$db_title}] [{$db_version}]. You are running i-doit with a [{$db_title}] version that is currently not officially supported.
                    please have a look at the official system requirements in the <a href=\"https://kb.i-doit.com/display/en/System+Requirements\">Knowledge Base</a>
                </p>
                [{elseif ($db_deprecated_version)}]
                <p class="box box-red p5 text-justify" style="font-size: 0.9em">
                    <strong>WARNING!</strong>
                    <br/>
                    We discourage the use of [{$db_title}] version below [{$db_deprecated_version}].
                    We will drop support for it in a future release.
                    Please upgrade MariaDB to one of the stable versions.
                    We recommend version [{$db_version_recommended}] for the best user experience.
                </p>
                [{/if}]
            </td>
            <td>
                [{if ($db_discourage_version || $db_deprecated_version)}]
                <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                <span class="vam text-yellow">WARNING</span>
                [{else}]
                <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        [{if $idoit_info.version}]
            <tr>
                <td>i-doit Database Version</td>
                <td><strong>[{$idoit_info.version}]</strong> Revision [{$idoit_info.revision}]</td>
                <td>
                    [{if $idoit_info.version != $idoit_version.version}]
                        <img src="[{$dir_images}]icons/silk/cross.png" class="vam" />
                        <strong class="vam text-red">FAIL</strong>
                        <br />
                        DB VERSION DOES NOT MATCH CODE VERSION!
                        <br />
                        UPDATE YOUR CODE OR DATABASE!!
                    [{/if}]
                </td>
            </tr>
        [{/if}]
        <tr>
            <td>Database size</td>
            <td><strong>[{$db_size}]</strong></td>
            <td></td>
        </tr>
        [{if $update_error_msg}]
            <tr>
                <td>Updates</td>
                <td>[{$update_error_msg}]</td>
                <td>
                    <img src="[{$dir_images}]icons/silk/cross.png" class="vam" />
                    <strong class="vam text-red">FAIL</strong><br />
                </td>
            </tr>
        [{elseif !$update}]
            <tr>
                <td>Updates</td>
                <td>[{$update_msg}]</td>
                <td>
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                </td>
            </tr>
        [{else}]
            <tr>
                <td>Updates</td>
                <td>
                    <img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" /><strong class="vam text-blue">There is a new i-doit version
                        available!</strong><br />
                    <strong>[{$update.version}]</strong> Revision [{$update.revision}] (Released: [{$update.release|date_format:"%d.%m.%Y"}])
                </td>
                <td>
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    [{if $gProductInfo.type == 'PRO'}]
                        <span class="vam"><a href="https://login.i-doit.com">Download Update from https://login.i-doit.com</a></span>
                    [{else}]
                        <span class="vam"><a href="[{$update.filename}]">Download Update from https://www.i-doit.org</a></span>
                    [{/if}]
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">
                <span class="text-grey">
                    You need to extract the downloaded update into your i-doit source directory:<br />
                    <strong>[{$config.base_dir}]</strong> and then open the <a href="updates/">i-doit update manager</a>.
                </span>
                </td>
            </tr>
        [{/if}]
        <tr>
            <td>Browser (client)</td>
            <td>
                <p class="mb10">[{isys type="lang" ident="LC__CMDB__TREE__SYSTEM__TOOLS__OVERVIEW__CLIENT_BROWSER"}]</p>

                <a target="_blank" href="https://kb.i-doit.com/display/de/Systemvoraussetzungen#Systemvoraussetzungen-Client">[{isys type="lang" ident="LC__LOCALE__GERMAN"}] <img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>
                [{isys type="lang" ident="LC_UNIVERSAL__OR"}]
                <a target="_blank" href="https://kb.i-doit.com/display/en/System+Requirements#SystemRequirements-Client">[{isys type="lang" ident="LC__LOCALE__ENGLISH"}] <img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>
            </td>
            <td></td>
        </tr>
        <tr>
            <td>
                Configuration examples
            </td>
            <td>
                <a target="_blank" href="https://kb.i-doit.com/display/de/Systemeinstellungen">[{isys type="lang" ident="LC__LOCALE__GERMAN"}] <img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>
                [{isys type="lang" ident="LC_UNIVERSAL__OR"}]
                <a target="_blank" href="https://kb.i-doit.com/display/en/System+Settings">[{isys type="lang" ident="LC__LOCALE__ENGLISH"}] <img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>
            </td>
            <td></td>
        </tr>
        </tbody>
    </table>

    <h3 class="p5 gradient">PHP.ini Settings</h3>

    <table class="listing" style="border-left: 0;">
        <colgroup>
            <col width="200" />
            <col width="350" />
        </colgroup>
        <tbody>
        <tr>
            <td>max_execution_time</td>
            <td>
                [{if $php.max_execution_time > 0}]
                    <strong>[{$php.max_execution_time}]</strong>
                    s
                [{else}]
                    <strong>infinite</strong>
                [{/if}]
            </td>
            <td>
                [{if $php.max_execution_time < 180 && $php.max_execution_time != 0}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">>180 recommended</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>upload_max_filesize</td>
            <td><strong>[{$php.upload_max_filesize}]</strong></td>
            <td>
                [{if isys_convert::to_bytes($php.upload_max_filesize) < isys_convert::to_bytes('128M')}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">>=128M recommended, 64M OK</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>post_max_size</td>
            <td><strong>[{$php.post_max_size}]</strong></td>
            <td>
                [{if isys_convert::to_bytes($php.post_max_size) < isys_convert::to_bytes('128M')}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">>=128M recommended, 64M OK</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>allow_url_fopen</td>
            <td><strong>[{$php.allow_url_fopen}]</strong></td>
            <td>
                [{if !$php.allow_url_fopen || $php.allow_url_fopen == 'Off'}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">Enable in order to use web requests (used for automatic updates, report browser, etc.)</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>max_input_vars</td>
            <td><strong>[{$php.max_input_vars}]</strong></td>
            <td>
                [{if $php.max_input_vars != 0 && intval($php.max_input_vars) < 10000}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">>= 10000</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>file_uploads</td>
            <td><strong>[{$php.file_uploads}]</strong></td>
            <td>
                [{if !$php.file_uploads || $php.file_uploads == 'Off'}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">Enable in order to upload files (https://php.net/file-uploads)</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>memory_limit</td>
            <td><strong>[{$php.memory_limit}]</strong></td>
            <td>
                [{if $php.memory_limit != 0 && isys_convert::to_bytes($php.memory_limit) < isys_convert::to_bytes('256M')}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">>=256M recommended</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        </tbody>
    </table>

    <h3 class="p5 gradient">MySQL Settings</h3>

    <table class="listing" style="border-left: 0;">
        <colgroup>
            <col width="200" />
            <col width="350" />
        </colgroup>
        <tbody>
        <tr>
            <td>innodb_buffer_pool_size</td>
            <td><strong>[{$mysql.innodb_buffer_pool_size/1024/1024}] MB</strong></td>
            <td>
                [{if $mysql.innodb_buffer_pool_size/1024/1024 < 1024}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">>=1024MB recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.6/en/innodb-buffer-pool.html"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>max_allowed_packet</td>
            <td><strong>[{$mysql.max_allowed_packet/1024/1024}] MB</strong></td>
            <td>
                [{if $mysql.max_allowed_packet/1024/1024 < 128}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">>=128MB recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.5/en/packet-too-large.html"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>query_cache_limit</td>
            <td><strong>[{$mysql.query_cache_limit/1024/1024}] MB</strong></td>
            <td>
                [{if $mysql.query_cache_limit/1024/1024 < 5}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">5MB recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#sysvar_query_cache_limit"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>query_cache_size</td>
            <td><strong>[{$mysql.query_cache_size/1024/1024}] MB</strong></td>
            <td>
                [{if $mysql.query_cache_size/1024/1024 > 80}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow"><=80M recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#sysvar_query_cache_size"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>tmp_table_size</td>
            <td><strong>[{$mysql.tmp_table_size/1024/1024}] MB</strong></td>
            <td>
                [{if $mysql.tmp_table_size < 33554432}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">32M recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#tmp_table_size"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]

                [{if $mysql.tmp_table_size != $mysql.max_heap_table_size}]
                    <br />
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">The value of max_heap_table_size will be used, since it overrides the value of tmp_table_size.</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>max_heap_table_size</td>
            <td><strong>[{$mysql.max_heap_table_size/1024/1024}] MB</strong></td>
            <td>
                [{if $mysql.max_heap_table_size < 33554432}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">32M recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#sysvar_max_heap_table_size"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>join_buffer_size</td>
            <td><strong>[{$mysql.join_buffer_size}] bytes</strong></td>
            <td>
                [{if $mysql.join_buffer_size > 262144}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">262144 recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#join_buffer_size"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>sort_buffer_size</td>
            <td><strong>[{$mysql.sort_buffer_size}] bytes</strong></td>
            <td>
                [{if $mysql.sort_buffer_size > 262144}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">262144 recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#sort_buffer_size"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>innodb_sort_buffer_size</td>
            <td><strong>[{$mysql.innodb_sort_buffer_size/1024/1024}] MB</strong></td>
            <td>
                [{if $mysql.innodb_sort_buffer_size/1024/1024 < 64}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">64M recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#innodb_sort_buffer_size"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td>innodb_log_file_size</td>
            <td><strong>[{$mysql.innodb_log_file_size/1024/1024}] MB</strong></td>
            <td>
                [{if $mysql.innodb_log_file_size/1024/1024 < 512}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">>=512M recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#innodb_log_file_size"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        <tr>
            <td class="text-grey">datadir</td>
            <td class="text-grey">[{$mysql.datadir}]</td>
            <td>
                <img src="[{$dir_images}]icons/silk/information.png" class="vam" />
                <span class="vam text-green">INFO</span>
            </td>
        </tr>
        </tbody>
    </table>

    <h3 class="p5 gradient">PHP Extension</h3>

    <table class="listing" style="border-left: 0;">
        <colgroup>
            <col width="200" />
            <col width="350" />
        </colgroup>
        <tbody>
        [{foreach $php_dependencies as $dependency => $modules}]
            <tr>
                <td>[{$dependency}]</td>
                <td>[{$modules|implode:", "}]</td>
                <td>
                    [{if $dependency == "mysql" && version_compare($smarty.const.PHP_VERSION, '5.6') === 1}]
                        [{if extension_loaded("mysqli")}]<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{else}]<img src="[{$dir_images}]icons/silk/cross.png" class="vam" />
                            <span class="vam text-red">ERROR</span>
                        [{/if}]
                    [{else}]
                        [{if extension_loaded($dependency)}]<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{else}]<img src="[{$dir_images}]icons/silk/cross.png" class="vam" />
                            <span class="vam text-red">ERROR</span>
                        [{/if}]
                    [{/if}]
                </td>
            </tr>
        [{/foreach}]

        <tr>
            <td>SNMP</td>
            <td>CMDB</td>
            <td>
                [{if !extension_loaded("snmp")}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">WARNING</span>
                    <span class="vam"> - Extension needed for SNMP Connections. (Category SNMP or PDU)</span>
                [{else}]
                    <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                    <span class="vam text-green">OK</span>
                [{/if}]
            </td>
        </tr>
        </tbody>
    </table>

    <h3 class="p5 gradient">Apache Modules</h3>

    <table class="listing" style="border-left: 0;">
        <colgroup>
            <col width="200" />
            <col width="350" />
        </colgroup>
        <tbody>
        <tr>
            <td>mod_rewrite</td>
            <td>[{$apache_dependencies['mod_rewrite']|implode:", "}]</td>
            <td>
                [{if isys_core::is_webserver_module_installed('mod_rewrite')}]
                    [{if isys_core::is_webserver_module_configured('mod_rewrite')}]
                        <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                        <span class="vam text-green">OK</span>
                    [{else}]
                        <img src="[{$dir_images}]icons/silk/cross.png" class="vam" />
                        <span class="vam text-red">Please verify that the apache module "mod_rewrite" is correctly configured. An automatic check identified that it is not.</span>
                        <button type="button" id="mod_rewrite_test_button" class="btn ml5 mr5"><img src="[{$dir_images}]icons/silk/server.png" class="mr5" /><span>Test</span></button>
                    [{/if}]
                [{else}]
                    <img src="[{$dir_images}]icons/silk/error.png" class="vam" />
                    <span class="vam text-yellow">i-doit could not verify that the apache module "mod_rewrite" is correctly configured.</span>
                    <button type="button" id="mod_rewrite_test_button" class="btn ml5 mr5"><img src="[{$dir_images}]icons/silk/server.png" class="mr5" /><span>Test</span></button>
                [{/if}]
            </td>
        </tr>
        [{foreach $apache_dependencies as $dependency => $modules}]
            [{if $dependency !== 'mod_rewrite'}]
            <tr>
                <td>[{$dependency}]</td>
                <td>[{$modules|implode:", "}]</td>
                <td>
                    [{if isys_core::is_webserver_module_installed($dependency)}]
                        [{if isys_core::is_webserver_module_configured($dependency)}]
                            <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{else}]
                            <img src="[{$dir_images}]icons/silk/cross.png" class="vam" />
                            <span class="vam text-red">
                            Please verify that the apache module "[{$dependency}]" is correctly configured. An automatic check identified that it is not.</span>
                        [{/if}]
                    [{else}]
                        <img src="[{$dir_images}]icons/silk/cross.png" class="vam" />
                        <span class="vam text-red">ERROR</span>
                    [{/if}]
                </td>
            </tr>
            [{/if}]
        [{/foreach}]
        </tbody>
    </table>

    <h3 class="p5 gradient">Rights & Directories</h3>

    <table class="listing" style="border-left: 0;">
        <colgroup>
            <col width="200" />
            <col width="350" />
        </colgroup>
        <tbody>
        [{foreach $rights as $k => $r}]
            <tr>
                <td>[{$k|capitalize}]</td>
                <td><code>[{$r.dir}]</code></td>
                <td>
                    [{assign var=chk value=$r.chk}]
                    [{if $r.chk}]
                        <img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
                        <span class="vam text-green">[{$r.msg}]</span>
                    [{else}]
                        <img src="[{$dir_images}]icons/silk/cross.png" class="vam" />
                        <span class="vam text-red text-bold">NOT [{$r.msg}]</span>
                        [{if $r.note}]
                            <br />
                            <span class="vam text-bold">[{$r.note}]</span>
                        [{/if}]
                    [{/if}]
                </td>
            </tr>
        [{/foreach}]
        </tbody>
    </table>
</div>

<script type="text/javascript">
    (function () {
        'use strict';

        var $testButton = $('mod_rewrite_test_button'),
            $modRewriteIcon, $modRewriteStatus;

        if ($testButton) {
            $modRewriteStatus = $testButton.up('td').down('span');
            $modRewriteIcon = $testButton.up('td').down('img');

            $testButton.on('click', function () {
                $testButton
                    .disable()
                    .down('img').writeAttribute('src', window.dir_images + 'ajax-loading.gif')
                    .next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

                $modRewriteStatus
                    .update('...')
                    .removeClassName('text-red')
                    .removeClassName('text-yellow')
                    .removeClassName('text-green');

                new Ajax.Request(window.www_dir + 'mod-rewrite-test', {
                    parameters: {
                        start: (new Date()).getTime() / 1000
                    },
                    onFailure:  function (xhr) {
                        var json = xhr.responseJSON;

                        $modRewriteStatus.addClassName('text-red').update('HTTP-Status: ' + xhr.status + ' (' + xhr.statusText + '), Message: ' + (json && json.message ? json.message : xhr.responseText));
                    },
                    onComplete: function (xhr) {
                        var json = xhr.responseJSON;

                        $testButton
                            .enable()
                            .down('img').writeAttribute('src', window.dir_images + 'icons/silk/server.png')
                            .next('span').update('Test');

                        if (json) {
                            if (json.success) {
                                $modRewriteIcon.writeAttribute('src', window.dir_images + 'icons/silk/tick.png');
                                $modRewriteStatus
                                    .addClassName('text-green')
                                    .writeAttribute('title', 'HTTP-Status: ' + xhr.status + ' (' + xhr.statusText + '), Timing: ' + json.data.delta.toFixed(4) + 'ms')
                                    .update('OK');
                            } else {
                                $modRewriteIcon.writeAttribute('src', window.dir_images + 'icons/silk/error.png');
                                $modRewriteStatus
                                    .addClassName('text-yellow')
                                    .writeAttribute('title', 'HTTP-Status: ' + xhr.status + ' (' + xhr.statusText + '), Message: ' + (json.message || xhr.responseText))
                                    .update('OK, but there was a different problem: ' + (json.message || xhr.responseText));
                            }
                        } else {
                            $modRewriteIcon.writeAttribute('src', window.dir_images + 'icons/silk/cross.png');
                            $modRewriteStatus
                                .addClassName('text-red')
                                .writeAttribute('title', 'HTTP-Status: ' + xhr.status + ' (' + xhr.statusText + '), Message: ' + xhr.responseText)
                                .update('ERROR');
                        }
                    }
                });
            });
        }

    })();
</script>
