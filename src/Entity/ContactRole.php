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

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * CalendarContactRole.
 *
 * @ORM\Table(name="calendar_contact_role")
 * @ORM\Entity
 */
class ContactRole
{
    const ROLE_ATTENDEE = 1;
    /**
     * @ORM\Column(name="role_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="role", type="string", length=45, nullable=false)
     *
     * @var string
     */
    private $role;
    /**
     * @ORM\OneToMany(targetEntity="Calendar\Entity\Contact", cascade={"persist"}, mappedBy="role")
     * @Annotation\Exclude()
     *
     * @var \Calendar\Entity\Contact[]
     */
    private $calendarContact;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->calendarContact = new Collections\ArrayCollection();
    }

    /**
     * Return the name of the role.
     *
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
     * @return ContactRole
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param string $role
     *
     * @return ContactRole
     */
    public function setRole($role)
    {
        $this->role = $role;

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
     * @return ContactRole
     */
    public function setCalendarContact($calendarContact)
    {
        $this->calendarContact = $calendarContact;

        return $this;
    }
}
