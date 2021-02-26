<h2>Step 4: Framework configuration</h2>
<table class="stepTable">
    <tr>
        <td colspan="3" class="stepHeadline">Admin-Center credentials</td>
    </tr>

    <tr>
        <td>&nbsp;</td>
        <td class="stepConfTitle">Username:</td>
        <td class="stepConfContent">
            <input class="confInputDir" type="text" name="config.adminauth.username" id="config.adminauth.username"
                   value="[CONFIG.ADMINAUTH.USERNAME]" />
        </td>
    </tr>

    <tr>
        <td>&nbsp;</td>
        <td class="stepConfTitle">Password:</td>
        <td class="stepConfContent">
            <input class="confInputDir" type="password" name="config.adminauth.password" id="config.adminauth.password"
                   value="[CONFIG.ADMINAUTH.PASSWORD]" />
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td class="stepConfTitle">License authorization token:</td>
        <td class="stepConfContent">
            <input class="confInputDir" type="text" name="config.license.token" id="config.license.token"
                   value="[CONFIG.LICENSE.TOKEN]" />
        </td>
    </tr>
    <tr>
        <td colspan="3" style="padding-left:10px;color:grey;">
            The i-doit Admin-Center is an administrational interface for managing mandators and i-doit licences.
            <strong>You can leave the password blank to disable it.</strong>
        </td>
    </tr>
</table>

<script type="text/javascript">
    // Pre submit validation.
    function form_onsubmit()
    {
        return true;
    }

    function select_by_text(p_element, p_text) {
        var i, e;

        for (i = 0; i < document.getElementsByName(p_element).length; i++)
        {
            if (document.getElementsByName(p_element)[i].options)
            {
                e = document.getElementsByName(p_element)[i];
            }
        }

        if (e)
        {
            for (i = 0; i < e.options.length; i++)
            {
                if (e.options[i].value == p_text)
                {
                    e.options.selectedIndex = i;
                    break;
                }
            }
        }
    }


    //select_by_text('config.base.barcodes', '[CONFIG.BASE.BARCODES]');
</script>