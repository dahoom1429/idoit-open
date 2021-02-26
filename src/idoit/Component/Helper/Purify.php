<?php declare(strict_types = 1);

namespace idoit\Component\Helper;

use isys_application;

class Purify
{
    /**
     * @return array
     */
    private static function intValues()
    {
        return [
            defined_or_default('C__CMDB__GET__OBJECT'),
            defined_or_default('C__CMDB__GET__OBJECTTYPE'),
            defined_or_default('C__CMDB__GET__OBJECTGROUP'),
            defined_or_default('C__CMDB__GET__CATG'),
            defined_or_default('C__CMDB__GET__CATS'),
            defined_or_default('C__CMDB__GET__CATLEVEL'),
            defined_or_default('C__CMDB__GET__VIEWMODE'),
            defined_or_default('C__CMDB__GET__TREEMODE')
        ];
    }

    /**
     * Cast only i-doit specific get parameters C__CMDB__GET__OBJECT, C__CMDB__GET__OBJECTTYPE,
     * C__CMDB__GET__OBJECTGROUP, C__CMDB__GET__CATG, C__CMDB__GET__VIEWMODE ... to int
     *
     * @param $params
     *
     * @return mixed
     */
    public static function castIntValues($params)
    {
        $intValues = self::intValues();

        array_walk($params, function (&$value, $key) use ($intValues) {
            // have to additional check if $value is not null because in some cases we need it as NULL
            if (in_array($key, $intValues) && $value !== null) {
                $value = (int)$value;
            }
        });

        return $params;
    }

    /**
     * @param $params
     *
     * @return array
     */
    public static function purifyParams($params)
    {
        return isys_application::instance()->container->get('htmlpurifier')->purifyArray(self::castIntValues($params));
    }

    /**
     * @param $key
     * @param $params
     *
     * @return mixed
     */
    public static function purifyParameter($key, $params)
    {
        return isys_application::instance()->container->get('htmlpurifier')->purify($params[$key]);
    }

    /**
     * Purifies value
     *
     * @param $value
     *
     * @return mixed|object|null
     * @throws \Exception
     */
    public static function purifyValue($value)
    {
        return isys_application::instance()->container->get('htmlpurifier')->purify($value);
    }
}
