<?php
namespace idoit\Module\Multiedit\Component\Multiedit\Formatter\Text;

use idoit\Component\Property\Property;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Formatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterInterface;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Value;
use isys_smarty_plugin_f_text;
use isys_convert;

/**
 * Class TextArea
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter\Text
 */
class Timeperiod extends Formatter implements FormatterInterface
{

    /**
     * @var string
     */
    protected static $type = C__PROPERTY__INFO__TYPE__TIMEPERIOD;

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return string|void
     * @throws \Exception
     */
    public static function formatCell($valueFormatter)
    {
        $property = $valueFormatter->getProperty();
        $value = ($valueFormatter->getValue() ?: new Value());

        $objectId = $valueFormatter->getObjectId();
        $entryId = $valueFormatter->getEntryId();

        $params = $property->getUi()->getParams();
        $params['name'] = null;
        $reference = $property->getData()->getReferences();
        $id = $valueFormatter->getPropertyKey();
        $fromName = null;
        $toName = null;

        if ($id && !$valueFormatter->isDeactivated()) {
            $fromName = $id . "_from[{$objectId}-{$entryId}]";
            $toName = str_replace('_from', '_to', $fromName);
        }

        if ($params['p_strValue'] instanceof \isys_callback) {
            $request = (new \isys_request())
                ->set_category_data_id($valueFormatter->getEntryId())
                ->set_object_id($valueFormatter->getObjectId());

            $params['p_strValue'] = $params['p_strValue']->execute($request);
        } elseif (\isys_format_json::is_json($value->getValue())) {
            $params['p_strValue'] = \isys_format_json::decode($value->getValue());
        } elseif ($value->getValue() !== $value->getViewValue()) {
            $params['p_strValue'] = $value->getViewValue();
        } else {
            $params['p_strValue'] = $value->getValue();
        }

        if (strpos($value->getViewValue(), ' - ')) {
            list($fromValue, $toValue) = explode(' - ', $value->getViewValue());
        } elseif (is_array($params['p_strValue'])) {
            $fromValue = $params['p_strValue']['from'];
            $toValue = $params['p_strValue']['to'];
        }

        $params['p_bEditMode'] = true;
        $params['p_strClass'] = ($params['p_strClass'] ? preg_replace('/(input-[a-z0-9]*)/', 'input-mini', $params['p_strClass']) : 'input-mini');

        $plugin = new isys_smarty_plugin_f_text();

        if ($valueFormatter->isDisabled()) {
            unset($params['p_strValue']);
            $params['p_bDisabled'] = true;
            $params['p_strClass'] .= ' multiedit-disabled ';
        }

        $params['inputGroupMarginClass'] = '';
        $type = self::$type;

        $content = "<td data-cell-type='{$type}' data-old-value='{$value->getViewValue()}' data-sort='{$value->getViewValue()}' data-key='{$valueFormatter->getPropertyKey()}' class='multiedit-table-td'>%s</td>";

        $toParams = $params;
        $params['p_strValue'] = $fromValue;
        $params['p_strClass'] .= " {$id}_from";
        $toParams['p_strValue'] = $toValue;
        $toParams['p_strClass'] .= " {$id}_to";

        if ($fromName !== null && $toName !== null) {
            $params['name'] = $fromName;
            $toParams['name'] = $toName;

            $params['p_onChange'] = "window.multiEdit.changed(null, '{$params['name']}');";
            $toParams['p_onChange'] = "window.multiEdit.changed(null, '{$toParams['name']}');";
        }

        if ($valueFormatter->isChangeAllRowsActive() && $params['name'] !== null) {
            $params['p_onChange'] .= ";window.multiEdit.overwriteAll(this, '{$id}_from', 'text');";
            $toParams['p_onChange'] .= ";window.multiEdit.overwriteAll(this, '{$id}_to', 'text');";
            unset($params['p_bDisabled']);
        }

        $pluginContent = $plugin->navigation_edit(\isys_application::instance()->container->template, $params) .
            $plugin->navigation_edit(\isys_application::instance()->container->template, $toParams);


        return sprintf($content, $pluginContent);
    }

    /**
     * @param Value    $value
     * @param Property $property
     *
     * @return mixed|void
     */
    public static function checkFilter($value, $property)
    {
        // TODO: Implement checkFilter() method.
    }
}
