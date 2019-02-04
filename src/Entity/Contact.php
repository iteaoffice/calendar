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

use Doctrine\ORM\Mapping as ORM;

/**
 * Contact.
 *
 * @ORM\Table(name="calendar_contact")
 * @ORM\Entity(repositoryClass="Calendar\Repository\Contact")
 */
class Contact extends AbstractEntity
{
    public const STATUS_ALL = 1;
    public const STATUS_NO_DECLINED = 2;
    /**
     * @ORM\Column(name="calendar_contact_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\ContactRole", cascade="persist", inversedBy="calendarContact")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="role_id", nullable=false)
     *
     * @var \Calendar\Entity\ContactRole
     */
    private $role;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\Calendar", cascade="persist", inversedBy="calendarContact")
     * @ORM\JoinColumn(name="calendar_id", referencedColumnName="calendar_id")
     *
     * @var \Calendar\Entity\Calendar
     */
    private $calendar;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\ContactStatus", cascade="persist", inversedBy="calendarContact")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="status_id", nullable=false)
     *
     * @var \Calendar\Entity\ContactStatus
     */
    private $status;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", cascade="persist", inversedBy="calendarContact")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id")
     *
     * @var \Contact\Entity\Contact
     */
    private $contact;

    public function __get($property)
    {
        return $this->$property;
    }

    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    public function __isset($property)
    {
        return isset($this->$property);
    }

    public function __toString(): string
    {
        return (string)$this->role;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): Contact
    {
        $this->id = $id;

        return $this;
    }

    public function getRole(): ?ContactRole
    {
        return $this->role;
    }

    public function setRole($role): Contact
    {
        $this->role = $role;

        return $this;
    }

    public function getCalendar(): ?Calendar
    {
        return $this->calendar;
    }

    public function setCalendar($calendar): Contact
    {
        $this->calendar = $calendar;

        return $this;
    }

    public function getStatus(): ?ContactStatus
    {
        return $this->status;
    }

    public function setStatus($status): Contact
    {
        $this->status = $status;

        return $this;
    }

    public function getContact(): ?\Contact\Entity\Contact
    {
        return $this->contact;
    }

    public function setContact($contact): Contact
    {
        $this->contact = $contact;

        return $this;
    }
}
