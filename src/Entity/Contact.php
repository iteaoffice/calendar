<?php
/**
 * ITEA copyright message placeholder.
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

namespace Calendar\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Contact.
 *
 * @ORM\Table(name="calendar_contact")
 * @ORM\Entity(repositoryClass="Calendar\Repository\Contact")
 */
class Contact extends EntityAbstract implements ResourceInterface
{
    const STATUS_ALL = 1;
    const STATUS_NO_DECLINED = 2;
    /**
     * @ORM\Column(name="calendar_contact_id", length=10, type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\ContactRole", cascade="persist", inversedBy="calendarContact")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="role_id", referencedColumnName="role_id", nullable=false)
     * })
     *
     * @var \Calendar\Entity\ContactRole
     */
    private $role;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\Calendar", cascade="persist", inversedBy="calendarContact")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="calendar_id", referencedColumnName="calendar_id")
     * })
     *
     * @var \Calendar\Entity\Calendar
     */
    private $calendar;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\ContactStatus", cascade="persist", inversedBy="calendarContact")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="status_id", nullable=false)
     * })
     *
     * @var \Calendar\Entity\ContactStatus
     */
    private $status;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", cascade="persist", inversedBy="calendarContact")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id")
     * })
     *
     * @var \Contact\Entity\Contact
     */
    private $contact;

    /**
     * Magic Getter.
     *
     * @param $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        return $this->$property;
    }

    /**
     * Magic Setter.
     *
     * @param $property
     * @param $value
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->role;
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
     * @return Contact
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return ContactRole
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param ContactRole $role
     *
     * @return Contact
     */
    public function setRole($role)
    {
        $this->role = $role;

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
     * @return Contact
     */
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * @return ContactStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param ContactStatus $status
     *
     * @return Contact
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \Contact\Entity\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param \Contact\Entity\Contact $contact
     *
     * @return Contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }
}
