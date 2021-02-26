<?php

namespace idoit\Module\Cmdb\Model;

use idoit\Model\Model;
use isys_application;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class CiType extends Model
{
    /**
     * @var string
     */
    public $const = '';

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var bool
     */
    public $showInTree = true;

    /**
     * @var string
     */
    public $icon = '';

    /**
     * @var string
     */
    public $image = '';

    /**
     * @var bool
     */
    public $container = false;

    /**
     * @var int
     */
    public $status = C__RECORD_STATUS__NORMAL;

    /**
     * @var int
     */
    public $catsId = null;

    /**
     * @var int
     */
    public $groupId = 0;

    /**
     * @var string
     */
    public $sysIdPrefix = '';

    /**
     * @return array
     */
    public function columnMap()
    {
        return [
            'id'          => 'isys_obj_type__id',
            'title'       => 'isys_obj_type__title',
            'const'       => 'isys_obj_type__const',
            'showInTree'  => 'isys_obj_type__show_in_tree',
            'icon'        => 'isys_obj_type__icon',
            'image'       => 'isys_obj_type__obj_img_name',
            'status'      => 'isys_obj_type__status',
            'catsId'      => 'isys_obj_type__isysgui_cats__id',
            'sysIdPrefix' => 'isys_obj_type__sysid_prefix',
            'groupId'     => 'isys_obj_type__isys_obj_type_group__id',
            'container'   => 'isys_obj_type__container',
        ];
    }

    /**
     * @param $id
     * @param $title
     * @param $const
     *
     * @return CiType
     */
    public static function factory($id, $title, $const)
    {
        if ($id > 0) {
            $object = new self();
            $object->id = $id;
            $object->title = $title;
            $object->const = $const;

            return $object;
        } else {
            throw new \InvalidArgumentException('Could not instantiate CiType. Given ID is invalid.');
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return isys_application::instance()->container->get('language')
            ->get($this->title);
    }
}
