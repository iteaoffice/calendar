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
use Zend\InputFilter\InputFilterInterface;

/**
 * Contact
 *
 * @ORM\Table(name="calendar_contact")
 * @ORM\Entity(repositoryClass="Calendar\Repository\Contact")
 */
class Contact extends EntityAbstract
{
    /**
     * @ORM\Column(name="calendar_contact_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var integer
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\ContactRole", cascade="persist", inversedBy="calendarContact")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="role_id", referencedColumnName="role_id", nullable=false)
     * })
     * @var \Calendar\Entity\ContactRole
     */
    private $role;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\Calendar", cascade="persist", inversedBy="calendarContact")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="calendar_id", referencedColumnName="calendar_id")
     * })
     * @var \Calendar\Entity\Calendar
     */
    private $calendar;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\ContactStatus", cascade="persist", inversedBy="calendarContact")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="status_id", nullable=false)
     * })
     * @var \Calendar\Entity\ContactStatus
     */
    private $status;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", cascade="persist", inversedBy="calendarContact")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id")
     * })
     * @var \Contact\Entity\Contact
     */
    private $contact;

    /**
     * Magic Getter
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
     * Magic Setter
     *
     * @param $property
     * @param $value
     *
     * @return void
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return sprintf("%s:%s", __CLASS__, $this->id);
    }

    /**
     * Set input filter
     *
     * @param InputFilterInterface $inputFilter
     *
     * @return void
     * @throws \Exception
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Setting an inputFilter is currently not supported");
    }

    /**
     * @return \Zend\InputFilter\InputFilter|\Zend\InputFilter\InputFilterInterface
     */
    public function getInputFilter()
    {
        return [];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->role;
    }

    /**
     * @param \Calendar\Entity\Calendar $calendar
     */
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;
    }

    /**
     * @return \Calendar\Entity\Calendar
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * @param \Contact\Entity\Contact $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return \Contact\Entity\Contact
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
     * @param \Calendar\Entity\ContactRole $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * @return \Calendar\Entity\ContactRole
     */
    public function getRole()
    {
        return $this->role;
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
