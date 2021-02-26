<?php
namespace idoit\Module\Multiedit\Component\Synchronizer\Converter\G\Sla;

use idoit\Module\Multiedit\Component\Synchronizer\Converter\ConvertInterface;
use isys_format_json;

class MondayTime implements ConvertInterface
{

    /**
     * @param array $value
     */
    public function convertValue($value)
    {
        $from = null;
        $to = null;

        if (is_array($value)) {
            $from = isset($value['from']) ? $value['from']: $value[0];
            $to = isset($value['to']) ? $value['to']: $value[1];
        } elseif (isys_format_json::is_json($value)) {
            $value = isys_format_json::decode($value);

            $from = isset($value['from']) ? $value['from']: $value[0];
            $to = isset($value['to']) ? $value['to']: $value[1];
        }

        if ($from !== null) {
            $from = \isys_cmdb_dao_category_g_sla::calculate_time_to_seconds($from);
        }

        if ($to !== null) {
            $to = \isys_cmdb_dao_category_g_sla::calculate_time_to_seconds($to);
        }

        return isys_format_json::encode(['from' => $from, 'to' => $to]);
    }
}
