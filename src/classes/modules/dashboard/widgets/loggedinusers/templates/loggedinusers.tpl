<h3 class="p5 border-bottom border-grey gradient text-shadow">[{isys type="lang" ident="LC__WIDGET__LOGGED_IN_USERS"}]</h3>
<div class="p5" style="overflow-x:auto;">
    <button type="button" id="[{$unique_id}]_logged-in-users-refresh" class="btn">
        <img src="[{$dir_images}]ajax-loading.gif" alt="" style="display:none;" class="vam ajax-loader"/>
        <img src="[{$dir_images}]icons/silk/arrow_refresh.png" class="mr5" />
        <span>[{isys type="lang" ident="LC__UNIVERSAL__REFRESH"}]</span>
    </button>
    <table cellspacing="0" cellpadding="2" class="" style="margin:0;text-align: left;width:100%;line-height:18px;">
        <thead>
        <tr style="line-height:20px;">
            <th class="border-bottom border-grey" style="width:50%">[{isys type="lang" ident="LC__CATG__CONTACT_IDOIT_USER"}]</th>
            <th class="border-bottom border-grey" style="width:50%">[{isys type="lang" ident="LC__WIDGET__LOGGED_IN_USERS__LAST_ACTION"}]</th>
        </tr>
        </thead>
        <tbody id="[{$unique_id}]_content">
        [{foreach from=$tabledata item=row}]
            <tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
                <td>[{$row.title_link}]</td>
                <td>[{$row.last_action}]</td>
            </tr>
            [{/foreach}]
        </tbody>
    </table>
</div>

<script type="text/javascript">
    $('[{$unique_id}]_logged-in-users-refresh').on('click', function(){

        $('[{$unique_id}]_content').hide();
        $('[{$unique_id}]_logged-in-users-refresh').down().next().hide();
        $('[{$unique_id}]_logged-in-users-refresh').down().show();

        new Ajax.Updater('[{$unique_id}]_content', '[{$ajax_url}]', {
            method: 'post',
            onComplete: function () {
                $('[{$unique_id}]_logged-in-users-refresh').down().hide();
                $('[{$unique_id}]_logged-in-users-refresh').down().next().show();

                new Effect.SlideDown($('[{$unique_id}]_content'), {duration:0.8});
            }
        });
    });
</script>