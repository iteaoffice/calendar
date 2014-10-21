<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category  Calendar
 * @package   Service
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Service;

use Calendar\Entity;
use Calendar\Entity\Calendar;
use Calendar\Entity\Contact as CalendarContact;
use Contact\Entity\Contact;
use Project\Entity\Project;
use Calendar\Service\ModuleOptionAwareInterface;

/**
 *
 */
class CalendarService extends ServiceAbstract
  implements  ModuleOptionAwareInterface
{
    /**
     * Constant to determine which affiliations must be taken from the database
     */
    const WHICH_UPCOMING = 'upcoming';
    const WHICH_UPDATED = 'updated';
    const WHICH_PAST = 'past';
    const WHICH_REVIEWS = 'project-reviews';
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
     * @return bool
     */
    public function isEmpty()
    {
        return is_null($this->calendar) || is_null($this->calendar->getId());
    }

    /**
     * @param $docRef
     *
     * @return null|Entity\Calendar
     */
    public function findCalendarByDocRef($docRef)
    {
        $calendar = $this->getEntityManager()->getRepository($this->getFullEntityName('Calendar'))->findOneBy(
            [
                'docRef' => $docRef
            ]
        );
        if (is_null($calendar)) {
            return null;
        }

        return $calendar;
    }

    /**
     * @return CalendarOptionsInterface
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param ModuleOptions $options
     *
     * @return ServiceAbstract
     */
    public function setOptions(\Calendar\Options\ModuleOptions  $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param Calendar $calendar
     * @param Contact  $contact
     *
     * @return bool
     */
    public function calendarHasContact(Calendar $calendar, Contact $contact)
    {
        $calendarContact = $this->getEntityManager()
            ->getRepository($this->getFullEntityName('Contact'))
            ->findOneBy(
                [
                    'calendar' => $calendar,
                    'contact'  => $contact
                ]
            );

        return !is_null($calendarContact);
    }

    /**
     * @param  string            $which
     * @param  Contact           $contact
     * @return CalendarContact[]
     */
    public function findCalendarContactByContact($which = self::WHICH_UPCOMING, Contact $contact = null)
    {
        return $this->getEntityManager()
            ->getRepository($this->getFullEntityName('Contact'))
            ->findCalendarContactByContact($which, $contact);
    }

    /**
     * @param Contact  $contact
     * @param Calendar $calendar
     *
     * @return CalendarContact
     */
    public function findCalendarContactByContactAndCalendar(Contact $contact, Calendar $calendar)
    {
        return $this->getEntityManager()
            ->getRepository($this->getFullEntityName('Contact'))
            ->findCalendarContactByContactAndCalendar($contact, $calendar);
    }

    /**
     * @param Calendar $calendar
     *
     * @return CalendarContact[]
     */
    public function findCalendarContactsByCalendar(Calendar $calendar)
    {
        return $this->getEntityManager()
            ->getRepository($this->getFullEntityName('Contact'))
            ->findCalendarContactsByCalendar($calendar);
    }

    /**
     * This function will return a boolean value to see if a contact can view the calendar
     *
     * @param Contact $contact
     *
     * @return bool
     */
    public function canViewCalendar(Contact $contact = null)
    {
        return $this->getEntityManager()
            ->getRepository($this->getFullEntityName('Calendar'))
            ->canViewCalendar($this->getCalendar(), $contact);
    }

    /**
     * @param string  $which
     * @param Contact $contact
     * @param integer $year
     *
     * @return \Doctrine\ORM\Query
     */
    public function findCalendarItems($which = self::WHICH_UPCOMING, Contact $contact = null, $year = null)
    {
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
        $calendar = [];
        /**
         * Add the calendar items from the project
         */
        foreach ($project->getProjectCalendar() as $calendarItem) {
            $calendar[$calendarItem->getCalendar()->getId()] = $calendarItem->getCalendar();
        }
        foreach ($project->getCall()->getCalendar() as $calendarItem) {
            $calendar[$calendarItem->getId()] = $calendarItem;
        }

        return $calendar;
    }

    /**
     * @param CalendarContact $calendarContact
     * @param                 $status
     */
    public function updateContactStatus(CalendarContact $calendarContact, $status)
    {
        $calendarContact->setStatus(
            $this->getEntityManager()->getReference($this->getFullEntityName('ContactStatus'), $status)
        );
        $this->updateEntity($calendarContact);
    }

    /**
     * @param Calendar $calendar
     *
     * @return CalendarContact[]
     */
    public function findGeneralCalendarContactByCalendar(Calendar $calendar)
    {
        return $this->getEntityManager()
            ->getRepository($this->getFullEntityName('Contact'))
            ->findGeneralCalendarContactByCalendar($calendar);
    }

    /**
     * Return an array of all which-values
     *
     * @return string[]
     */
    public function getWhichValues()
    {
        return [
            self::WHICH_UPCOMING,
            self::WHICH_UPDATED,
            self::WHICH_PAST,
            self::WHICH_REVIEWS
        ];
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
