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

use Calendar\Entity;
use Contact\Entity\Contact;

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
     * Constant to determine which affiliations must be taken from the database
     */
    const WHICH_UPCOMING = 'upcoming';
    const WHICH_UPDATED  = 'updated';
    const WHICH_PAST     = 'past';

    /**
     * @var Entity\Calendar
     */
    protected $calendar;
    /**
     * @var CalendarService
     */
    protected $calendarService;

    /**
     * @param int $id
     *
     * @return CalendarService;
     */
    public function setProjectId($id)
    {
        $this->setCalendar($this->findEntityById('calendar', $id));

        return $this;
    }

    /**
     * @param Contact $contact
     *
     * @return bool
     */
    public function calendarHasContact(Contact $contact)
    {
        $calendarContact = $this->getEntityManager()
            ->getRepository($this->getFullEntityName('contact'))
            ->findOneBy(array(
                'calendar' => $this->getCalendar(),
                'contact'  => $contact
            ));

        return !is_null($calendarContact);
    }

    /**
     * @param string $which
     *
     * @return Entity\Calendar[]
     */
    public function findCalendarItems($which = self::WHICH_UPCOMING)
    {
        return $this->getEntityManager()
            ->getRepository($this->getFullEntityName('calendar'))
            ->findCalendarItems($which);
    }


    /**
     * Return an array of all which-values
     *
     * @return array
     */
    public function getWhichValues()
    {
        return array(
            self::WHICH_UPCOMING,
            self::WHICH_UPDATED,
            self::WHICH_PAST
        );
    }

    /**
     * @param \Calendar\Entity\Calendar $calendar
     *
     * @return $this;
     */
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * @return \Calendar\Entity\Calendar
     */
    public function getCalendar()
    {
        return $this->calendar;
    }
}
