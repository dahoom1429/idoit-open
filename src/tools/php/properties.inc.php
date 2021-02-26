<?php

/**
 * !! DEPRECATED !!
 *
 * This file was accessible over `load=api_properties` and responsible for
 * displaying property information for each category. This was especially
 * helpful for creating read/write request for the json api.
 *
 * Because of the newly created section inside of i-doit settings this page
 * is obsolete and will only redirect to it.
 *
 * We should consider to delete it in the future.
 *
 * Date: 21. August 2018
 */
header('Location: ?moduleID=' . (defined('C__MODULE__SYSTEM') ? C__MODULE__SYSTEM : '') . '&what=apiCategoryConfiguration');