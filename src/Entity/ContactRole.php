<?php

/**
 * ITEA copyright message placeholder.
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\Entity;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;
use Laminas\Form\Annotation;

/**
 * @ORM\Table(name="calendar_contact_role")
 * @ORM\Entity(repositoryClass="Calendar\Repository\ContactRole")
 */
class ContactRole extends AbstractEntity
{
    public const ROLE_ATTENDEE = 1;
    public const ROLE_STG_REVIEWER = 7;
    public const ROLE_STG_SPARE_REVIEWER = 8;

    public static array $roles
        = [
            self::ROLE_ATTENDEE => 'txt-role-attendees',
            self::ROLE_STG_REVIEWER => 'txt-stg-reviewer',
            self::ROLE_STG_SPARE_REVIEWER => 'txt-stg-spare-reviewer',
        ];

    /**
     * @ORM\Column(name="role_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Type("\Laminas\Form\Element\Hidden")
     *
     * @var int
     */
    private $id;
    /**
     * @ORM\Column(name="role", type="string", nullable=false)
     * @Annotation\Type("\Laminas\Form\Element\Text")
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

    public function __toString(): string
    {
        return (string)$this->role;
    }

    public static function getRoles(): ?array
    {
        return self::$roles;
    }

    /**
     * @param array $roles
     */
    public static function setRoles(array $roles): void
    {
        self::$roles = $roles;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): ContactRole
    {
        $this->id = $id;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): ContactRole
    {
        $this->role = $role;
        return $this;
    }

    public function getCalendarContact()
    {
        return $this->calendarContact;
    }

    public function setCalendarContact($calendarContact): ContactRole
    {
        $this->calendarContact = $calendarContact;
        return $this;
    }
}
