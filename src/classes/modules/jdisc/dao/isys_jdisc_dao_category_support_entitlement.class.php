<?php

/**
 * Class isys_jdisc_dao_category_support_entitlement
 */
class isys_jdisc_dao_category_support_entitlement extends isys_jdisc_dao_category implements isys_jdisc_dao_category_interface
{
    /**
     * @var string
     */
    protected $category = 'C__CATG__SUPPORT_ENTITLEMENT';

    /**
     * @var string
     */
    protected $title = 'LC__CMDB__CATG__SUPPORT_ENTITLEMENT';

    /**
     * @return string
     */
    private function getQuery()
    {
        return "SELECT * FROM device as d
            INNER JOIN supportentitlement s on d.id = s.deviceid %s";
    }

    /**
     * @param int $deviceId
     *
     * @return string
     */
    private function getCondition(int $deviceId)
    {
        return 'WHERE d.id = ' . $this->convert_sql_int($deviceId);
    }

    /**
     * @param $data
     *
     * @return array
     */
    private function prepareData($data)
    {
        return [
            'data_id'    => null,
            'properties' => [
                'partNumber'        => [
                    'tag'   => 'partNumber',
                    'value' => $data['partnumber'],
                    'title' => 'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__PARTNUMBER',
                ],
                'startDate' => [
                    'tag'        => 'startDate',
                    'value'      => $data['startdate'],
                    'title'      => 'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__START_DATE',
                ],
                'endDate'       => [
                    'tag'   => 'endDate',
                    'value' => $data['enddate'],
                    'title' => 'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__END_DATE',
                ],
                'description'   => [
                    'tag'   => 'description',
                    'value' => $data['description'],
                    'title' => 'LC__CMDB__CATG__DESCRIPTION',
                ]
            ]
        ];
    }

    /**
     * @param int   $deviceId
     * @param false $asRaw
     * @param array $deviceToObjectIds
     * @param array $idoitObjects
     * @param null  $currentObjectId
     *
     * @return array
     */
    public function getDataForImport($deviceId, $asRaw = false, $deviceToObjectIds = [], $idoitObjects = [], $currentObjectId = null)
    {
        $return = [];
        $result = $this->fetch(sprintf($this->getQuery(), $this->getCondition($deviceId)));
        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($result) . ' rows');

        while ($data = $this->m_pdo->fetch_row_assoc($result)) {
            if ($asRaw === true) {
                $return[] = $data;
            } else {
                $return[] = $this->prepareData($data);
            }
        }

        if ($asRaw === true || count($return) == 0) {
            return $return;
        } else {
            return [
                C__DATA__TITLE      => $this->language->get($this->title),
                'const'             => $this->category,
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $return
            ];
        }
    }
}
