<?php
/*
 * PHP Barcode Image Generator v1.0 [9/28/2000]
 * Copyright (C)2000 by Charles J. Scheffold - cs@wsia.fm
 *
 * This code is hereby released into the public domain.
 * Use it, abuse it, just don't get caught using it for something stupid.
 */

//-----------------------------------------------------------------------------
// Barcode Funktion
//-----------------------------------------------------------------------------
function Barcode39($barcode, $width, $height, $quality, $format, $text)
{
    switch ($format) {
        case 'JPEG':
            header('Content-type: image/jpeg');
            break;

        case 'GIF':
            header('Content-type: image/gif');
            break;
        default:
        case 'PNG':
            header('Content-type: image/png');
            break;
    }

    $im = ImageCreate($width, $height) or die('PHP GDlib needed for barceodes!');

    $White = imagecolorallocatealpha($im, 0, 0, 0, 127);
    $Black = ImageColorAllocate($im, 0, 0, 0);

    ImageInterLace($im, 1);

    $NarrowRatio = 20;
    $WideRatio = 55;
    $QuietRatio = 35;

    $nChars = (strlen($barcode) + 2) * ((6 * $NarrowRatio) + (3 * $WideRatio) + ($QuietRatio));
    $Pixels = $width / $nChars;
    $NarrowBar = (int)20 * $Pixels;
    $WideBar = (int)55 * $Pixels;
    $QuietBar = (int)35 * $Pixels;

    $ActualWidth = (($NarrowBar * 6) + ($WideBar * 3) + $QuietBar) * (strlen($barcode) + 2);

    if ($NarrowBar === 0 || $NarrowBar === $WideBar || $NarrowBar === $QuietBar || $WideBar === 0 || $WideBar === $QuietBar || $QuietBar === 0) {
        ImageString($im, 2, 0, 0, 'Image to small for this barcode!', $Black);
        OutputImage($im, $format, $quality);
        exit;
    }

    $CurrentBarX = (int)(($width - $ActualWidth) / 2);
    $Color = $White;
    $BarcodeFull = '*' . strtoupper($barcode) . '*';

    $BarcodeText = '| ' . strtoupper($barcode) . ' |';

    $FontNum = 3;
    $FontHeight = ImageFontHeight($FontNum);
    $FontWidth = ImageFontWidth($FontNum);
    if ($text != 0) {
        $CenterLoc = (int)(($width - 1) / 2) - (int)(($FontWidth * strlen($BarcodeText)) / 2);
        ImageString($im, $FontNum, $CenterLoc, $height - $FontHeight, $BarcodeText, $Black);
    } else {
        $FontHeight = -2;
    }

    $barcodeLength = mb_strlen($BarcodeFull);

    for ($i = 0;$i < $barcodeLength;$i++) {
        $StripeCode = Code39($BarcodeFull[$i]);

        for ($n = 0;$n < 9;$n++) {
            if ($Color == $White) {
                $Color = $Black;
            } else {
                $Color = $White;
            }

            switch ($StripeCode[$n]) {
                case '0':
                    ImageFilledRectangle($im, $CurrentBarX, 0, $CurrentBarX + $NarrowBar, $height - 1 - $FontHeight - 2, $Color);
                    $CurrentBarX += $NarrowBar;
                    break;

                case '1':
                    ImageFilledRectangle($im, $CurrentBarX, 0, $CurrentBarX + $WideBar, $height - 1 - $FontHeight - 2, $Color);
                    $CurrentBarX += $WideBar;
                    break;
            }
        }

        $Color = $White;
        ImageFilledRectangle($im, $CurrentBarX, 0, $CurrentBarX + $QuietBar, $height - 1 - $FontHeight - 2, $Color);
        $CurrentBarX += $QuietBar;
    }

    OutputImage($im, $format, $quality);
}

//-----------------------------------------------------------------------------
// Output Funktion
//-----------------------------------------------------------------------------
function OutputImage($im, $format, $quality)
{
    switch ($format) {
        case 'JPEG':
            ImageJPEG($im, '', $quality);
            break;

        case 'PNG':
            ImagePNG($im);
            break;

        case 'GIF':
            ImageGIF($im);
            break;
    }
}

//-----------------------------------------------------------------------------
// ASCII für Code39
//-----------------------------------------------------------------------------
function Code39($Asc)
{
    switch ($Asc) {
        case ' ':
            return '011000100';
        case '$':
            return '010101000';
        case '%':
            return '000101010';
        case '*':
            return '010010100'; // * Start/Stop
        case '+':
            return '010001010';
        case '|':
            return '010000101';
        case '.':
            return '110000100';
        case '/':
            return '010100010';
        case '-':
            return '010000101';
        case '0':
            return '000110100';
        case '1':
            return '100100001';
        case '2':
            return '001100001';
        case '3':
            return '101100000';
        case '4':
            return '000110001';
        case '5':
            return '100110000';
        case '6':
            return '001110000';
        case '7':
            return '000100101';
        case '8':
            return '100100100';
        case '9':
            return '001100100';
        case 'A':
            return '100001001';
        case 'B':
            return '001001001';
        case 'C':
            return '101001000';
        case 'D':
            return '000011001';
        case 'E':
            return '100011000';
        case 'F':
            return '001011000';
        case 'G':
            return '000001101';
        case 'H':
            return '100001100';
        case 'I':
            return '001001100';
        case 'J':
            return '000011100';
        case 'K':
            return '100000011';
        case 'L':
            return '001000011';
        case 'M':
            return '101000010';
        case 'N':
            return '000010011';
        case 'O':
            return '100010010';
        case 'P':
            return '001010010';
        case 'Q':
            return '000000111';
        case 'R':
            return '100000110';
        case 'S':
            return '001000110';
        case 'T':
            return '000010110';
        case 'U':
            return '110000001';
        case 'V':
            return '011000001';
        case 'W':
            return '111000000';
        case 'X':
            return '010010001';
        case 'Y':
            return '110010000';
        case 'Z':
            return '011010000';
        default:
            return '011000100';
    }
}

if (isset($_GET['text'])) {
    $text = $_GET['text'];
}
if (isset($_GET['barcode'])) {
    $barcode = $_GET['barcode'];
} else {
    trigger_error('Barcode: HTTP GET parameter "barcode" is missing', E_USER_ERROR);
    exit;
}

$barcode = preg_replace('/^.*?[_-]+(.*?)$/', "\\1", $barcode);

if (!isset($text)) {
    $text = 1;
}

$quality = 100;
$width = $_GET['width'] ?? 220;
$height = $_GET['height'] ?? 80;

if ($height > 500) {
    $height = 500;
}
if ($width > 500) {
    $width = 500;
}

if (!empty($format)) {
    $format = strtoupper($format);
} else {
    $format = 'PNG';
}

Barcode39($barcode, $width, $height, $quality, $format, $text);
