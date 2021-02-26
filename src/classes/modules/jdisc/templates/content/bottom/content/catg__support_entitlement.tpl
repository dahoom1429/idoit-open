<style type="text/css">
    .support-entitlement {
        margin-left: 20px !important;
        height:18px;
        margin:-2px 2px 0;
        display: inline-block;
    }
</style>

<table class="contentTable">
    <tr>
        <td class="key">[{isys type="f_label" name="C__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE" ident="LC__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE"}]</td>
        <td class="value">[{isys type="f_data" p_bInfoIconSpacer=0 p_strValue=$statusMarker id="C__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CMDB__CATG__SUPPORT_ENTITLEMENT__PARTNUMBER" ident="LC__CMDB__CATG__SUPPORT_ENTITLEMENT__PARTNUMBER"}]</td>
        <td class="value">[{isys type="f_text" name="C__CMDB__CATG__SUPPORT_ENTITLEMENT__PARTNUMBER"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CMDB__CATG__SUPPORT_ENTITLEMENT__START_DATE" ident="LC__CMDB__CATG__SUPPORT_ENTITLEMENT__START_DATE"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="calendar" enableCloseOnBlur=1 name="C__CMDB__CATG__SUPPORT_ENTITLEMENT__START_DATE" p_onChange="window.setSupportStatus();" cellCallback="window.setSupportStatus"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CMDB__CATG__SUPPORT_ENTITLEMENT__END_DATE" ident="LC__CMDB__CATG__SUPPORT_ENTITLEMENT__END_DATE"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="calendar" enableCloseOnBlur=1 name="C__CMDB__CATG__SUPPORT_ENTITLEMENT__END_DATE" p_onChange="window.setSupportStatus();" cellCallback="window.setSupportStatus"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CMDB__CATG__SUPPORT_ENTITLEMENT__EXPIRES" ident="LC__CMDB__CATG__SUPPORT_ENTITLEMENT__EXPIRES"}]</td>
        <td class="value">[{isys type="f_data" p_strValue=$expiredInDays id="C__CMDB__CATG__SUPPORT_ENTITLEMENT__EXPIRES"}]</td>
    </tr>
</table>
<script type="text/javascript">
    (function () {
        'use strict';

        var calculateExpiration = function (){
            var dayDifference = -1,
                now = new Date(),
                endDate = $('C__CMDB__CATG__SUPPORT_ENTITLEMENT__END_DATE__HIDDEN').getValue().split('-'),
                startDate = $('C__CMDB__CATG__SUPPORT_ENTITLEMENT__START_DATE__HIDDEN').getValue().split('-'),
                startDateObject,
                endDateObject;

            startDateObject = new Date();
            startDateObject.setFullYear(startDate[0]);
            startDateObject.setMonth(startDate[1] - 1);
            startDateObject.setDate(startDate[2]);

            if (now.getTime() >= startDateObject.getTime()) {
                startDateObject = now;
            } else{
                return null;
            }

            endDateObject = new Date();
            endDateObject.setFullYear(endDate[0]);
            endDateObject.setMonth(endDate[1] - 1);
            endDateObject.setDate(endDate[2]);

            if (endDateObject.getTime() >= startDateObject.getTime()) {
                dayDifference = Math.floor((endDateObject.getTime() - startDateObject.getTime()) / (1000 * 3600 * 24));
            }
            return dayDifference;
        };

        window.setSupportStatus = function () {
            var dayDifference,
                marker = document.createElement('div'),
                activeTxt = '[{isys type="lang" ident="LC__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE_ACTIVE" p_bHtmlEncode=0}]',
                inActiveTxt = '[{isys type="lang" ident="LC__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE_INACTIVE" p_bHtmlEncode=0}]',
                notStartedTxt = '[{isys type="lang" ident="LC__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE_NOT_STARTED" p_bHtmlEncode=0}]',
                txt = inActiveTxt
            ;

            marker.addClassName('cmdb-marker support-entitlement');
            marker.writeAttribute('style', 'background-color: #BC0A19');

            $('C__CMDB__CATG__SUPPORT_ENTITLEMENT__EXPIRES').update('[{isys_tenantsettings::get("gui.empty_value", "-")}]');

            if ($F('C__CMDB__CATG__SUPPORT_ENTITLEMENT__END_DATE__VIEW').blank()) {
                $('C__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE').update(marker).insert(inActiveTxt);
                return;
            }

            $('C__CMDB__CATG__SUPPORT_ENTITLEMENT__EXPIRES').update('[{isys type="lang" ident="LC__CMDB__CATG__SUPPORT_ENTITLEMENT__EXPIRED" p_bHtmlEncode=0}]');

            dayDifference = calculateExpiration();
            if (dayDifference == null) {
                $('C__CMDB__CATG__SUPPORT_ENTITLEMENT__EXPIRES').update(notStartedTxt);
            }

            if (dayDifference >= 0 && dayDifference != null){
                $('C__CMDB__CATG__SUPPORT_ENTITLEMENT__EXPIRES').update(dayDifference);
                marker.writeAttribute('style', 'background-color: #33C20A');
                txt = activeTxt;
            }
            $('C__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE').update(marker).insert(txt);
        };

        if ($('C__CMDB__CATG__SUPPORT_ENTITLEMENT__END_DATE__HIDDEN')) {
            window.setSupportStatus();
        }
    }());
</script>