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

use Project\Entity\Project;
use Calendar\Entity\Calendar;

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
    const WHICH_UPCOMING    = 'upcoming';
    const WHICH_UPDATED     = 'updated';
    const WHICH_PAST        = 'past';
    const WHICH_REVIEWS     = 'reviews';
    const WHICH_ON_HOMEPAGE = 'on-homepage';

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
    public function setCalendarId($id)
    {
        $this->setCalendar($this->findEntityById('Calendar', $id));

        return $this;
    }

    /**
     * @param $docRef
     *
     * @return null|Entity\Calendar
     */
    public function findCalendarByDocRef($docRef)
    {
        $calendar = $this->getEntityManager()->getRepository($this->getFullEntityName('Calendar'))->findOneBy(
            array(
                'docRef' => $docRef
            )
        );

        if (is_null($calendar)) {
            return null;
        }

        return $calendar;
    }

    /**
     * @param Contact $contact
     *
     * @return bool
     */
    public function calendarHasContact(Contact $contact)
    {
        $calendarContact = $this->getEntityManager()
            ->getRepository($this->getFullEntityName('Contact'))
            ->findOneBy(array(
                'calendar' => $this->getCalendar(),
                'contact'  => $contact
            ));

        return !is_null($calendarContact);
    }

    /**
     * This function will return a boolean value to see if a contact can view the calendar
     *
     * @param Contact $contact
     *
     * @return bool
     */
    public function canViewCalendar(Contact $contact)
    {
        return $this->getEntityManager()
            ->getRepository($this->getFullEntityName('Calendar'))
            ->canViewCalendar($this->getCalendar(), $contact);
    }

    /**
     * @param string $which
     * @param null   $year
     *
     * @return \Doctrine\ORM\Query
     */
    public function findCalendarItems($which = self::WHICH_UPCOMING, $year = null)
    {
        $contact = $this->getServiceLocator()->get('zfcuser_auth_service')->getIdentity();

        return $this->getEntityManager()
            ->getRepository($this->getFullEntityName('Calendar'))
            ->findCalendarItems($which, true, $contact, $year);
    }

    /**
     * @param Project $project
     *
     * @return Calendar[]
     */
    public function findCalendarByProject(Project $project)
    {
        $calendar = array();

        /**
         * Add the calendar items from the project
         */
        foreach ($project->getProjectCalendar() as $calendarItem) {
            $calendar[$calendarItem->getCalendar()->getId()] = $calendarItem->getCalendar();
        }

        foreach ($project->getCall()->getCalendar() as $calendarItem) {
            $calendar[$calendarItem->getId()] = $calendarItem->getCalendar();
        }

        return $calendar;
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
            self::WHICH_PAST,
            self::WHICH_REVIEWS
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
