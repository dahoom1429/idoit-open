<?php

use idoit\Component\Template\Cache\Memcache;
use idoit\Component\Template\Cache\Memcached;

/**
 * i-doit
 *
 * Smarty Wrapper - implements the Smarty API
 * But remember, this is something like an
 * abstract library integration layer.
 *
 * Note: Smarty is loaded via composer.
 *
 * @package    i-doit
 * @subpackage Libraries
 * @author     Dennis Stücken <dstuecken@i-doit.de>
 * @version    1.3
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_library_smarty extends Smarty
{
    /**
     * @var int File permission mask for the cache files
     */
    public $_file_perms = 0664;

    /**
     * @var int Directory permission mask for the cache directory
     */
    public $_dir_perms = 0775;

    /**
     * Registers object to be used in templates
     *
     * @param  string  $object        name of template object
     * @param  object  $object_impl   the referenced PHP object to register
     * @param  array   $allowed       list of allowed methods (empty = all)
     * @param  boolean $smarty_args   smarty argument format, else traditional
     * @param  array   $block_methods
     *
     * @return $this
     * @throws SmartyException
     */
    public function register_object($object, $object_impl, $allowed = [], $smarty_args = true, $block_methods = [])
    {
        $allowed = (array) $allowed;
        $smarty_args = (bool) $smarty_args;

        $this->registerObject($object, $object_impl, $allowed, $smarty_args, $block_methods);

        return $this;
    }

    /**
     *
     * @throws SmartyException
     */
    public function __construct()
    {
        parent::__construct();

        $this->addPluginsDir(__DIR__ . '/smarty/plugins/');
    }
}
