<?php
/**
 * ITEA copyright message placeholder.
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Calendar\Entity;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * CalendarSchedule.
 *
 * @ORM\Table(name="calendar_schedule")
 * @ORM\Entity
 */
class Schedule
{
    /**
     * @ORM\Column(name="schedule_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="date_start", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    private $dateStart;
    /**
     * @ORM\Column(name="date_end", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    private $dateEnd;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\Calendar", cascade="persist", inversedBy="schedule")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="calendar_id", referencedColumnName="calendar_id", nullable=false)
     * })
     *
     * @var \Calendar\Entity\Calendar
     */
    private $calendar;
    /**
     * @ORM\OneToMany(targetEntity="Calendar\Entity\ScheduleContact", cascade={"persist"}, mappedBy="schedule")
     * @Annotation\Exclude()
     *
     * @var \Calendar\Entity\ScheduleContact[]
     */
    private $scheduleContact;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->scheduleContact = new Collections\ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Schedule
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @param \DateTime $dateStart
     *
     * @return Schedule
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param \DateTime $dateEnd
     *
     * @return Schedule
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * @return Calendar
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * @param Calendar $calendar
     *
     * @return Schedule
     */
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * @return ScheduleContact[]
     */
    public function getScheduleContact()
    {
        return $this->scheduleContact;
    }

    /**
     * @param ScheduleContact[] $scheduleContact
     *
     * @return Schedule
     */
    public function setScheduleContact($scheduleContact)
    {
        $this->scheduleContact = $scheduleContact;

        return $this;
    }
}
