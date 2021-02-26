<?php

namespace idoit\Module\Multiedit\Component\Multiedit;

/**
 * Interface Renderable
 *
 * @package idoit\Module\Multiedit\Component\Multiedit
 */
interface Renderable
{
    /**
     * @param $objects
     * @param $value
     * @param $data
     *
     * @return mixed
     */
    public function render($objects, $value, $data);
}
