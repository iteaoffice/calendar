<?php
/**
 * ITEA copyright message placeholder.
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\Entity;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * CalendarContactRole.
 *
 * @ORM\Table(name="calendar_contact_status")
 * @ORM\Entity
 */
class ContactStatus
{
    public const STATUS_TENTATIVE = 1;
    public const STATUS_ACCEPT = 2;
    public const STATUS_DECLINE = 3;
    /**
     * @ORM\Column(name="status_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="status", type="string", length=45, nullable=false)
     *
     * @var string
     */
    private $status;
    /**
     * @ORM\Column(name="image", type="string", length=45, nullable=false)
     *
     * @var string
     */
    private $image;
    /**
     * @ORM\Column(name="status_change", type="string", length=45, nullable=false)
     *
     * @var string
     */
    private $statusChange;
    /**
     * @ORM\OneToMany(targetEntity="Calendar\Entity\Contact", cascade={"persist"}, mappedBy="status")
     * @Annotation\Exclude()
     *
     * @var \Calendar\Entity\Contact[]
     */
    private $calendarContact;
    /**
     * @ORM\OneToMany(targetEntity="Calendar\Entity\ScheduleContact", cascade={"persist"}, mappedBy="status")
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
     * @return string
     */
    public function __toString()
    {
        return (string)$this->status;
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
     * @return ContactStatus
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return ContactStatus
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     *
     * @return ContactStatus
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatusChange()
    {
        return $this->statusChange;
    }

    /**
     * @param string $statusChange
     *
     * @return ContactStatus
     */
    public function setStatusChange($statusChange)
    {
        $this->statusChange = $statusChange;

        return $this;
    }

    /**
     * @return Contact[]
     */
    public function getCalendarContact()
    {
        return $this->calendarContact;
    }

    /**
     * @param Contact[] $calendarContact
     *
     * @return ContactStatus
     */
    public function setCalendarContact($calendarContact)
    {
        $this->calendarContact = $calendarContact;

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
     * @return ContactStatus
     */
    public function setScheduleContact($scheduleContact)
    {
        $this->scheduleContact = $scheduleContact;

        return $this;
    }
}
