<?php
/**
 * Template for QR Code popup.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

$l_obj_id = filter_input(INPUT_GET, 'objID', FILTER_VALIDATE_INT) ?: 0;
$l_url = filter_input(INPUT_GET, 'url', FILTER_VALIDATE_URL) ?: '';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>i-doit barcode</title>
    <meta name="author" content="synetics gmbh" />
    <meta name="description" content="i-doit" />

    <script type="text/javascript" language="JavaScript" src="../../js/prototype/prototype.js"></script>
    <script type="text/javascript" language="JavaScript" src="../../js/scriptaculous/src/scriptaculous.js?load=effects"></script>

    <style type="text/css">
        body {
            font-family: "Lucida Grande", Tahoma, Arial, Helvetica, sans-serif;
            color: #000;
            font-size: 10pt;
        }

        #qrcode {
            text-align: center;
        }

        #description {
            margin: 0;
            text-align: unset;
        }

        #logo {
            margin: 5px;
        }
    </style>
</head>
<body>

<table id="qrcode">
    <tr>
        <td>
            <img id="code" src="<?php echo $l_url; ?>images/ajax-loading.gif" alt="Error loading the QR Code" />
        </td>
        <td>
            <div id="description"></div>
        </td>
        <td>
            <img id="logo" src="<?php echo $l_url; ?>images/ajax-loading.gif" alt="" />
        </td>
    </tr>
</table>

<?php if (!empty($l_url)) : ?>
<script type="text/javascript">
    new Ajax.Request('<?php echo $l_url; ?>?ajax=1&call=qrcode&func=load_qr_code', {
        method:     'post',
        parameters: {
            objID:<?php echo $l_obj_id; ?>
        },
        onSuccess:  function (response) {
            var json = response.responseJSON;

            $('code').writeAttribute('src', 'qr_img.php?s=2&d=' + json.data.url);
            $('logo').writeAttribute('src', json.data.logo);

            if (json.data.description) {
                $('description').update(json.data.description);
            }

            // We need this timer for the browser to correctly detect the image heights...
            setTimeout('calc_sizes_and_print()', 100);
        }
    });

    function calc_sizes_and_print() {
        // Now we try to set the logo to the same size as the QR Code.
        $('logo').writeAttribute('height', $('code').getHeight() + 'px');

        if (window.print) {
            window.print();
        }
    }
</script>
<?php endif; ?>
</body>
</html>
