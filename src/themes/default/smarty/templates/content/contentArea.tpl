<ul id="breadcrumb" class="noprint">
    [{isys type="breadcrumb_navi" name="breadcrumb" p_home=1 p_prepend="<li>" p_append="</li>"}]

    [{if $trialInfo}]
        <li class="text-bold text-red">
            [{$trialInfo.message}]
        </li>
    [{/if}]
</ul>

<div id="logged-in-user-container">
    <span id="logged-in-user-name" class="mr5">[{$user_name|default:"Unknown"}]</span>
    <strong id="logged-in-user-tenant" class="mr20">@[{$g_mandant_name}]</strong>
    <img class="user-image" src="[{$user_image_url}]" alt="Profilbild" />

    <div id="logged-in-user-popdown-wrapper">
        <div id="logged-in-user-popdown">
            <div class="popup-content">
                <table>
                    <tr>
                        <td>[{isys type="lang" ident="LC__LOGIN__LOGGED_IN_AS"}]</td>
                        <td>
                            <a href="[{$g_link__user}]"><img alt="" src="[{$dir_images}]icons/silk/user_gray.png" title="[{isys type="lang" ident="LC__MODULE__USER_SETTINGS__TITLE"}]" /></a>
                            <span>[{$full_user_name|default:"Unknown"}]</span>
                        </td>
                    </tr>
                    <tr>
                        <td>[{isys type="lang" ident="LC__LOGIN__MANDATOR"}]</td>
                        <td id="logged-in-user-popdown-tenant-selector">
                            <button type="button" class="btn btn-block">
                                <img src="[{$dir_images}]icons/silk/database_copy.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__CHANGE_TENANTS"}]</span>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>[{isys type="lang" ident="LC__LANGUAGEEDIT__TABLEHEADER_LANGUAGE"}]</td>
                        <td>
                            <ul class="list-style-none m0 f16">
                                [{if $flag_de}]
                                    <li class="fl flag de"><a href="[{$flag_de}]">&nbsp;</a></li>
                                [{/if}]
                                [{if $flag_en}]
                                    <li class="fl flag us"><a href="[{$flag_en}]">&nbsp;</a></li>
                                [{/if}]
                            </ul>
                        </td>
                    </tr>
                </table>

                <img class="user-image" src="[{$user_image_url}]" alt="Profilbild" />
            </div>

            <div class="popup-footer">
                <a href="[{$g_link__settings}]" class="fl">
                    <img alt="" src="[{$dir_images}]icons/silk/cog.png" class="mr5" /><span>[{isys type="lang" ident="LC__NAVIGATION__MAINMENU__TITLE_ADMINISTRATION"}]</span>
                </a>

                <a href="[{$g_link__logout}]" class="fr">
                    <span>[{isys type="lang" ident="LC__NAVIGATION__MAINMENU__TITLE_LOGOUT"}]</span><img alt="" src="[{$dir_images}]icons/silk/delete.png" class="ml5" />
                </a>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        (function () {
            'use strict';

            var $tenantSwitchContainer = $('logged-in-user-popdown-tenant-selector'),
                $tenantSwitchButton    = $tenantSwitchContainer.down('button');

            $tenantSwitchButton.on('click', function () {
                $tenantSwitchButton.down('img').writeAttribute('src', window.dir_images + 'ajax-loading.gif');

                new Ajax.Updater($tenantSwitchContainer, '?ajax=1&call=fetch_mandators');
            });
        })();
    </script>

    [{* This is the original "login" display from "searchBar.tpl".
    <span class="login-string">
        <span>[{isys type="lang" ident="LC__LOGIN__LOGGED_IN_AS"}] <strong>[{$session->get_current_username()|default:"Unknown"}]</strong> in </span>
        <span id="mandator_selection">
            <span onmouseover="new Ajax.Updater('mandator_selection','?ajax=1&call=fetch_mandators');">
                <strong title="[{isys type="lang" ident="LC__UNIVERSAL__CURRENT_MANDANT"}]">[{$g_mandant_name}]</strong>
            </span>
        </span>
    </span>
    *}]
</div>

<div id="main_content">
    [{include file=$index_includes.contentarea|default:"content/main.tpl"}]
</div>

<div id="infoBox">
    <div class="message">[{$infobox->show_html()|strip}]</div>
    <div class="version">i-doit [{$gProductInfo.version}] [{$gProductInfo.step}] [{$gProductInfo.type}]</div>
</div>
