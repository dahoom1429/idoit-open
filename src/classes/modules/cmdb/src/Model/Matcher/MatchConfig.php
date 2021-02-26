<?php

namespace idoit\Module\Cmdb\Model\Matcher;

use idoit\Component\ContainerFacade;
use idoit\Component\Provider\DiInjectable;
use idoit\Exception\Exception;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class MatchConfig
{
    use DiInjectable;

    /**
     * Match profile id
     *
     * @var int
     */
    protected $id;

    /**
     * Title of this matching profile
     *
     * @var string
     */
    protected $title;

    /**
     * Bitwise storage for matching identifiers (Based on idoit\Module\Cmdb\Model\Matcher\Identifier)
     *
     * @var int
     */
    protected $bits;

    /**
     * Minmum amount of matches
     *
     * @var int
     */
    protected $minMatch;

    /**
     * @var MatchDao
     */
    protected $dao;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return int
     */
    public function getBits()
    {
        return $this->bits;
    }

    /**
     * @param int $bits
     *
     * @return $this
     */
    public function setBits($bits)
    {
        $this->bits = $bits;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinMatch()
    {
        return $this->minMatch;
    }

    /**
     * @param int $minMatch
     *
     * @return $this
     */
    public function setMinMatch($minMatch)
    {
        $this->minMatch = $minMatch;

        return $this;
    }

    /**
     * @return MatchDao
     */
    public function getDao()
    {
        return $this->dao;
    }

    /**
     * @param MatchDao $dao
     *
     * @return $this
     */
    public function setDao($dao)
    {
        $this->dao = $dao;

        return $this;
    }

    /**
     * Return isys_obj_match entry by id.
     *
     * @param int $isysObjMatchId
     *
     * @return $this
     */
    public function load($isysObjMatchId)
    {
        $data = $this->dao->retrieve('SELECT * FROM isys_obj_match WHERE isys_obj_match__id = ' . $this->dao->convert_sql_int($isysObjMatchId))
            ->get_row();

        if (!$data) {
            throw new Exception(sprintf('Matching profile "%s" was not found', $isysObjMatchId));
        }

        $this->id = $data['isys_obj_match__id'];
        $this->title = $data['isys_obj_match__title'];
        $this->bits = $data['isys_obj_match__bits'];
        $this->minMatch = $data['isys_obj_match__min_match'];

        return $this;
    }

    /**
     * @param int             $profileId
     * @param ContainerFacade $di
     *
     * @return MatchConfig
     */
    public static function factory($profileId, ContainerFacade $di)
    {
        $config = new self($di);

        return $config->load($profileId);
    }

    /**
     * MatchConfig constructor.
     *
     * @param ContainerFacade $di
     */
    public function __construct(ContainerFacade $di)
    {
        $this->setDi($di);
        $this->dao = new MatchDao($di->database);
    }

}