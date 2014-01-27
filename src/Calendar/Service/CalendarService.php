<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Service
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Service;

/**
 * CalendarService
 *
 * this is a generic wrapper service for all the other services
 *
 * First parameter of all methods (lowercase, underscore_separated)
 * will be used to fetch the correct model service, one exception is the 'linkModel'
 * method.
 *
 */
class CalendarService extends ServiceAbstract
{
    /**
     * @var CalendarService
     */
    protected $calendarService;

    /**
     * Find 1 entity based on the name
     *
     * @param   $entity
     * @param   $name
     *
     * @return object
     */
    public function findEntityByName($entity, $name)
    {
        return $this->getEntityManager()->getRepository($this->getFullEntityName($entity))->findOneBy(
            array('name' => $name)
        );
    }
}
