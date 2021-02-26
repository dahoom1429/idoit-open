<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFilter
 */

/**
 * Smarty trimwhitespace outputfilter plugin
 *
 * Trim unnecessary whitespace from HTML markup.
 *
 * @author   Rodney Rehm
 *
 * @param string                   $source input string
 * @param Smarty_Internal_Template $smarty Smarty object
 *
 * @return string filtered output
 * @todo     substr_replace() is not overloaded by mbstring.func_overload - so this function might fail!
 */
function smarty_outputfilter_TrimWhiteSpaceEnhanced($source, Smarty_Internal_Template $smarty)
{
    $store = [];
    $_store = $_offset = 0;
    // Unify Line-Breaks to \n
    $source = preg_replace('#\xD#', '', $source);
    // capture Internet Explorer Conditional Comments
    if (preg_match_all('#<!--\[[^\]]+\]>.*?<!\[[^\]]+\]-->#is', $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $store[$_store] = $match[0][0];
            $_length = strlen($match[0][0]);
            $replace = '@!@SMARTY:' . $_store . ':SMARTY@!@';
            $source = substr_replace($source, $replace, $match[0][1] - $_offset, $_length);
            $_offset += $_length - strlen($replace);
            ++$_store;
        }
    }
    // Strip all HTML-Comments
    $source = preg_replace('#<!--.*?-->#ms', '', $source);
    // capture html elements not to be messed with
    $_offset = 0;
    if (preg_match_all('#<(script|pre|textarea|style)[^>]*>(.*?)</\\1>#is', $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            if ((strtolower($match[1][0]) == 'script') && (trim($match[2][0]) != '')) {
                $store[] = '<script type="text/javascript">' . smarty_outputfilter_TrimWhiteSpaceJS($match[2][0], $smarty) .

                    '</script>';
            } elseif ((strtolower($match[1][0]) == 'style') && (trim($match[2][0]) != '')) {
                $store[] = '<style type="text/css">' . smarty_outputfilter_TrimWhiteSpaceCSS($match[2][0], $smarty) .

                    '</style>';
            } else {
                $store[] = $match[0][0];
            }
            $_length = strlen($match[0][0]);
            $replace = '@!@SMARTY:' . $_store . ':SMARTY@!@';
            $source = substr_replace($source, $replace, $match[0][1] - $_offset, $_length);
            $_offset += $_length - strlen($replace);
            ++$_store;
        }
    }
    $expressions = [
        // replace multiple spaces between tags by a single space
        // can't remove them entirely, becaue that might break poorly implemented CSS display:inline-block elements
        '#(:SMARTY@!@|>|\S)\s+(?=@!@SMARTY:|<|\S)#s'                      => '\1 \2',
        // remove spaces between attributes (but not in attribute values!)
        '#(([a-z0-9]\s*=\s*(["\'])[^\3]*?\3)|<[a-z0-9_]+)\s+([a-z/>])#is' => '\1 \4',
        // note: for some very weird reason trim() seems to remove spaces inside attributes.
        // maybe a \0 byte or something is interfering?
        '#^\s+<#s'                                                        => '<',
        '#>\s+$#s'                                                        => '>',
        '#\s*(/)?\s*>#s'                                                  => '\1>'
    ];
    $source = preg_replace(array_keys($expressions), array_values($expressions), $source);
    // capture html elements not to be messed with
    $_offset = 0;
    if (preg_match_all('#@!@SMARTY:([0-9]+):SMARTY@!@#is', $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $_length = strlen($match[0][0]);
            $replace = $store[$match[1][0]];
            $source = substr_replace($source, $replace, $match[0][1] + $_offset, $_length);
            $_offset += strlen($replace) - $_length;
            ++$_store;
        }
    }

    return $source;
}

function smarty_outputfilter_TrimWhiteSpaceCSS($source, Smarty_Internal_Template $smarty)
{
    $expressions = [
        // No line breaks
        '#\xD|\xA#'              => '',
        // No CSS comment
        '#/\*.*?\*/#s'           => '',
        // No white spaces except from attribute values
        '#\s*(:|;|\{|\}|,)\s*#s' => '\1',
        // No multiple white spaces within attribute values
        '#\s+#'                  => ' '
    ];
    $source = preg_replace(array_keys($expressions), array_values($expressions), $source);

    return $source;
}

function smarty_outputfilter_TrimWhiteSpaceJS($source, Smarty_Internal_Template $smarty)
{
    $expressions = [
        # capture CDATA. Only one-line-CDATA is possible, and only one START per line:
        # Allowed: // <![CDATA[ _____ ]]>
        # Allowed: // <![CDATA[
        #          // ]]>
        '#//.*?<!\[CDATA\[(.*?)]]>#i' => '@!@CDATA:\1:CDATA@!@',
        '#//.*?<!\[CDATA\[.*?#i'      => '@!@CDATA:',
        '#//.*?]]>.*?#i'              => ':CDATA@!@',
        // No JS comments
        '#/\*.*?\*/#s'                => '',
        '#//.*#'                      => '',
        // Restore CDATA
        '#@!@CDATA:#'                 => '/*<![CDATA[*/',
        '#:CDATA@!@#'                 => '/*]]>*/',
        // No line breaks
        '#\xD|\xA#'                   => '',
    ];
    $source = preg_replace(array_keys($expressions), array_values($expressions), $source);
    $store = [];
    $_store = $_offset = 0;
    // strings
    $_offset = 0;
    if (preg_match_all('#(\'|")((.*?)(\\\\)*(\\\1)*)*\1#s', $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $store[$_store] = $match[0][0];
            $_length = strlen($match[0][0]);
            $replace = '@!@SMARTY:' . $_store . ':SMARTY@!@';
            $source = substr_replace($source, $replace, $match[0][1] - $_offset, $_length);
            $_offset += $_length - strlen($replace);
            ++$_store;
        }
    }
    // trim whitespaces
    $source = preg_replace('#\s*([^a-zA-Z])\s*#', '\1', $source);
    // redo replacements
    $_offset = 0;
    if (preg_match_all('#@!@SMARTY:([0-9]+):SMARTY@!@#is', $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $_length = strlen($match[0][0]);
            $replace = $store[$match[1][0]];
            $source = substr_replace($source, $replace, $match[0][1] + $_offset, $_length);
            $_offset += strlen($replace) - $_length;
            ++$_store;
        }
    }

    return $source;
}