<?php
/**
 * ARTEMIS-IA Office copyright message placeholder
 *
 * PHP Version 5
 *
 * @category    Calendar
 * @package     Service
 * @author      Andre Hebben <andre.hebben@artemis-ia.eu>
 * @copyright   2007-2014 ARTEMIS-IA Office
 * @license     http://debranova.org/license.txt proprietary
 * @link        http://debranova.org
 */
namespace Calendar\Service;

use Calendar\Options\ModuleOptions as ModuleOptions;

/**
 *
 *
 * @category   Calendar
 * @package    Service
 * @author     Andre Hebben <andre.hebben@artemis-ia.eu>
 * @license    http://debranova.org/licence.txt proprietary
 * @link       http://debranova.org
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
