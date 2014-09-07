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

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * CalendarContactRole
 *
 * @ORM\Table(name="calendar_contact_status")
 * @ORM\Entity
 */
class ContactStatus
{
    const STATUS_TENTATIVE = 1;
    const STATUS_ACCEPT = 2;
    const STATUS_DECLINE = 3;
    /**
     * @ORM\Column(name="status_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="status", type="string", length=45, nullable=false)
     * @var string
     */
    private $status;
    /**
     * @ORM\Column(name="image", type="string", length=45, nullable=false)
     * @var string
     */
    private $image;
    /**
     * @ORM\Column(name="status_change", type="string", length=45, nullable=false)
     * @var string
     */
    private $statusChange;
    /**
     * @ORM\OneToMany(targetEntity="Calendar\Entity\Contact", cascade={"persist"}, mappedBy="status")
     * @Annotation\Exclude()
     * @var \Calendar\Entity\Contact[]
     */
    private $calendarContact;
    /**
     * @ORM\OneToMany(targetEntity="Calendar\Entity\ScheduleContact", cascade={"persist"}, mappedBy="status")
     * @Annotation\Exclude()
     * @var \Calendar\Entity\ScheduleContact[]
     */
    private $scheduleContact;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->scheduleContact = new Collections\ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->status;
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
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $statusChange
     */
    public function setStatusChange($statusChange)
    {
        $this->statusChange = $statusChange;
    }

    /**
     * @return string
     */
    public function getStatusChange()
    {
        return $this->statusChange;
    }

    /**
     * @param \Calendar\Entity\Contact[] $calendarContact
     */
    public function setCalendarContact($calendarContact)
    {
        $this->calendarContact = $calendarContact;
    }

    /**
     * @return \Calendar\Entity\Contact[]
     */
    public function getCalendarContact()
    {
        return $this->calendarContact;
    }

    /**
     * @param \Calendar\Entity\ScheduleContact[] $scheduleContact
     */
    public function setScheduleContact($scheduleContact)
    {
        $this->scheduleContact = $scheduleContact;
    }

    /**
     * @return \Calendar\Entity\ScheduleContact[]
     */
    public function getScheduleContact()
    {
        return $this->scheduleContact;
    }
}
