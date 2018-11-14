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
 * @ORM\Table(name="calendar_contact_role")
 * @ORM\Entity(repositoryClass="Calendar\Repository\ContactRole")
 */
class ContactRole extends AbstractEntity
{
    public const ROLE_ATTENDEE = 1;
    public const ROLE_STG_REVIEWER = 7;
    public const ROLE_STG_SPARE_REVIEWER = 8;

    /**
     * @var array Lookup table for the roles
     */
    public static $roles
        = [
            self::ROLE_ATTENDEE           => 'txt-role-attendees',
            self::ROLE_STG_REVIEWER       => 'txt-stg-reviewer',
            self::ROLE_STG_SPARE_REVIEWER => 'txt-stg-spare-reviewer',
        ];

    /**
     * @ORM\Column(name="role_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Type("\Zend\Form\Element\Hidden")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="role", type="string", length=45, nullable=false)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-calendar-contact-role-role-label","help-block": "txt-calendar-contact-role-role-help-block"})
     * @Annotation\Attributes({"placeholder":"txt-calendar-contact-role-role-placeholder"})     *
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

    public function __construct()
    {
        $this->calendarContact = new Collections\ArrayCollection();
    }

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

    /**
     * @param int $id
     *
     * @return ContactRole
     */
    public function setId($id): ContactRole
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
    public function setRole(string $role): ContactRole
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
    public function setCalendarContact(array $calendarContact): ContactRole
    {
        $this->calendarContact = $calendarContact;
        return $this;
    }
}
