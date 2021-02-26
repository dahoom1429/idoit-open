<?php

namespace idoit\Tree;

/**
 * i-doit Tree Node Wrapper
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Node extends \isys_tree_node
{
    /**
     * @var int
     */
    private static $idCounter = 0;

    /**
     * @var bool
     */
    public $accessRight = true;

    /**
     * @var string
     */
    public $cssClass = '';

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var string
     */
    public $image = '';

    /**
     * @var string
     */
    public $link;

    /**
     * @var string
     */
    public $onclick = '';

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $tooltip = '';

    /**
     * Parent node
     *
     * @var Node
     */
    protected $m_parent;

    /**
     * Factory method for chaining
     *
     * @param string $title
     * @param string $link
     * @param string $image
     * @param string $onclick
     * @param string $tooltip
     * @param string $cssClass
     * @param bool   $accessRight
     *
     * @return Node
     */
    public static function factory($title, $link, $image = '', $onclick = '', $tooltip = '', $cssClass = '', $accessRight = true)
    {
        return new self($title, $link, $image, $onclick, $tooltip, $cssClass, $accessRight);
    }

    /**
     * @return Node
     */
    public function get_parent()
    {
        return $this->m_parent;
    }

    /**
     * @param string $title
     * @param string $link
     * @param string $image
     * @param string $onclick
     * @param string $tooltip
     * @param string $cssClass
     * @param bool   $accessRight
     */
    public function __construct($title, $link, $image = '', $onclick = '', $tooltip = '', $cssClass = '', $accessRight = true)
    {
        $this->id = self::$idCounter++;

        $this->title = $title;
        $this->link = $link;
        $this->accessRight = $accessRight;

        if ($image) {
            $this->image = $image;
        }

        if ($onclick) {
            $this->onclick = $onclick;
        }

        if ($tooltip) {
            $this->tooltip = $tooltip;
        }

        if ($cssClass) {
            $this->cssClass = $cssClass;
        }

        parent::__construct([]);
    }
}
