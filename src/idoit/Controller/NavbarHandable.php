<?php

namespace idoit\Controller;

/**
 * i-doit Base Controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
/**
 * Interface Handable
 *
 * @package idoit\Controller
 */
interface NavbarHandable
{

    /**
     * Navbar button "delete" clicked
     *
     * @param \isys_register    $p_request
     * @param \isys_application $p_application
     *
     * @return \idoit\View\Renderable
     */
    public function onArchive(\isys_register $p_request, \isys_application $p_application);

    /**
     * Navbar button "cancel" clicked
     *
     * @param \isys_register    $p_request
     * @param \isys_application $p_application
     *
     * @return \idoit\View\Renderable
     */
    public function onCancel(\isys_register $p_request, \isys_application $p_application);

    /**
     * Default request (Gets called when no navmode is set)
     *
     * @param \isys_register    $p_request
     * @param \isys_application $p_application
     *
     * @return \idoit\View\Renderable
     */
    public function onDefault(\isys_register $p_request, \isys_application $p_application);

    /**
     * Navbar button "delete" clicked
     *
     * @param \isys_register    $p_request
     * @param \isys_application $p_application
     *
     * @return \idoit\View\Renderable
     */
    public function onDelete(\isys_register $p_request, \isys_application $p_application);

    /**
     * Navbar button "duplicate" clicked
     *
     * @param \isys_register    $p_request
     * @param \isys_application $p_application
     *
     * @return \idoit\View\Renderable
     */
    public function onDuplicate(\isys_register $p_request, \isys_application $p_application);

    /**
     * Navbar button "edit" clicked
     *
     * @param \isys_register    $p_request
     * @param \isys_application $p_application
     *
     * @return \idoit\View\Renderable
     */
    public function onEdit(\isys_register $p_request, \isys_application $p_application);

    /**
     * Navbar button "new" clicked
     *
     * @param \isys_register    $p_request
     * @param \isys_application $p_application
     *
     * @return \idoit\View\Renderable
     */
    public function onNew(\isys_register $p_request, \isys_application $p_application);

    /**
     * Navbar button "print" clicked
     *
     * @param \isys_register    $p_request
     * @param \isys_application $p_application
     *
     * @return \idoit\View\Renderable
     */
    public function onPrint(\isys_register $p_request, \isys_application $p_application);

    /**
     * Navbar button "purge" clicked
     *
     * @param \isys_register    $p_request
     * @param \isys_application $p_application
     *
     * @return \idoit\View\Renderable
     */
    public function onPurge(\isys_register $p_request, \isys_application $p_application);

    /**
     * Navbar button "quickpurge" clicked
     *
     * @param \isys_application $p_application
     *
     * @param \isys_register    $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onQuickPurge(\isys_register $p_request, \isys_application $p_application);

    /**
     * Navbar button "recycle" clicked
     *
     * @param \isys_register    $p_request
     * @param \isys_application $p_application
     *
     * @return \idoit\View\Renderable
     */
    public function onRecycle(\isys_register $p_request, \isys_application $p_application);

    /**
     * Navbar button "reset" clicked
     *
     * @param \isys_register    $p_request
     * @param \isys_application $p_application
     *
     * @return \idoit\View\Renderable
     */
    public function onReset(\isys_register $p_request, \isys_application $p_application);

    /**
     * Navbar button "save" clicked
     *
     * @param \isys_register    $p_request
     * @param \isys_application $p_application
     *
     * @return \idoit\View\Renderable
     */
    public function onSave(\isys_register $p_request, \isys_application $p_application);

    /**
     * Navbar button "up" clicked
     *
     * @param \isys_register    $p_request
     * @param \isys_application $p_application
     *
     * @return \idoit\View\Renderable
     */
    public function onUp(\isys_register $p_request, \isys_application $p_application);

}