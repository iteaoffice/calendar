<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Calendar
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        https://itea3.org
 */

namespace Calendar\Service;

use Calendar\Options\ModuleOptions as ModuleOptions;

/**
 * @category   Calendar
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @license    https://itea3.org/licence.txt proprietary
 *
 * @link       https://itea3.org
 */
interface ModuleOptionAwareInterface
{
    /**
     * Get config.
     *
     * @return ModuleOptions.
     */
    public function getOptions();

    /**
     * Set options.
     *
     * @param ModuleOptions $options the value to set.
     *
     * @return $this
     */
    public function setOptions(ModuleOptions $options);
}
