<?php
/**
 * ITEA copyright message placeholder
 *
 * @category  Calendar
 * @package   Entity
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CalendarScheduleContact
 *
 * @ORM\Table(name="calendar_schedule_contact")
 * @ORM\Entity
 */
class ScheduleContact
{
    /**
     * @ORM\Column(name="schedule_contact_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var integer
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\ContactStatus", cascade="persist", inversedBy="scheduleContact")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="status_id", nullable=false)
     * })
     * @var \Calendar\Entity\ContactStatus
     */
    private $status;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\Schedule", cascade="persist", inversedBy="scheduleContact")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="schedule_id", referencedColumnName="schedule_id")
     * })
     * @var \Calendar\Entity\Schedule
     */
    private $schedule;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", cascade="persist", inversedBy="scheduleContact")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id")
     * })
     * @var \Contact\Entity\Contact
     */
    private $contact;

    /**
     * @param mixed $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return mixed
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Calendar\Entity\Schedule $schedule
     */
    public function setSchedule($schedule)
    {
        $this->schedule = $schedule;
    }

    /**
     * @return \Calendar\Entity\Schedule
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * @param \Calendar\Entity\ContactStatus $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return \Calendar\Entity\ContactStatus
     */
    public function getStatus()
    {
        return $this->status;
    }
}
