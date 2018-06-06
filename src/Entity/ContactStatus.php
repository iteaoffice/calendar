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
class ContactStatus extends AbstractEntity
{
    public const STATUS_TENTATIVE = 1;
    public const STATUS_ACCEPT = 2;
    public const STATUS_DECLINE = 3;
    /**
     * @ORM\Column(name="status_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Type("\Zend\Form\Element\Hidden")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="status", type="string", nullable=false)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-calendar-contact-status-status-label","help-block": "txt-calendar-contact-status-status-help-block"})
     * @Annotation\Attributes({"placeholder":"txt-calendar-contact-status-status-placeholder"})
     *
     * @var string
     */
    private $status;
    /**
     * @ORM\Column(name="status_change", type="string", length=45, nullable=false)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-calendar-contact-status-status-change-label","help-block": "txt-calendar-contact-status-status-change-help-block"})
     * @Annotation\Attributes({"placeholder":"txt-calendar-contact-status-status-change-placeholder"})
     *
     * @var string
     */
    private $statusChange;
    /**
     * @ORM\OneToMany(targetEntity="Calendar\Entity\Contact", cascade={"persist"}, mappedBy="status")
     * @Annotation\Exclude()
     *
     * @var \Calendar\Entity\Contact[]|Collections\ArrayCollection
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
    public function __toString(): string
    {
        return (string)$this->status;
    }

    /**
     * @param $property
     *
     * @return bool
     */
    public function __isset($property)
    {
        return isset($this->$property);
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
    public function setId($id): ContactStatus
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return ContactStatus
     */
    public function setStatus(string $status): ContactStatus
    {
        $this->status = $status;
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
    public function setStatusChange(string $statusChange): ContactStatus
    {
        $this->statusChange = $statusChange;
        return $this;
    }

    /**
     * @return Contact[]|Collections\ArrayCollection
     */
    public function getCalendarContact()
    {
        return $this->calendarContact;
    }

    /**
     * @param Contact[]|Collections\ArrayCollection $calendarContact
     *
     * @return ContactStatus
     */
    public function setCalendarContact($calendarContact): ContactStatus
    {
        $this->calendarContact = $calendarContact;
        return $this;
    }
}
