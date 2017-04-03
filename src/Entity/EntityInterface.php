<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

namespace Calendar\Entity;

/**
 * Interface EntityInterface
 *
 * @package Calendar\Entity
 */
interface EntityInterface
{
    public function __get($property);

    public function __set($property, $value);

    public function getId();
}
