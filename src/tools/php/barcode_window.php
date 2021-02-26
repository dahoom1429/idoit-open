<?php
/**
 * Handler for QR Requests
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

$l_url = http_build_query($_GET);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>i-doit barcode</title>
    <meta name="author" content="synetics gmbh" />
    <meta name="description" content="i-doit" />

    <script type="text/javascript" language="JavaScript" src="../js/prototype/prototype.js"></script>
    <script type="text/javascript" language="JavaScript" src="../js/scriptaculous/src/scriptaculous.js?load=effects"></script>
</head>
<body>
<img style="display:none;" id="barcode_image" src="barcode.php?<?php echo $l_url; ?>" border="0" alt="Error loading barcode image" />

<script type="text/javascript" language="JavaScript">
    Event.observe($('barcode_image'), 'load', function () {
        new Effect.Grow('barcode_image', {
            duration:    0.4,
            afterFinish: function () {
                if (window.print) window.print();
            }
        });
        
    });
</script>
</body>
</html>
