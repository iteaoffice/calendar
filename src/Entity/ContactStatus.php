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
     * @ORM\Column(name="status_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Type("\Zend\Form\Element\Hidden")
     *
     * @var int
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
     * @ORM\Column(name="status_change", type="string", nullable=false)
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

    public function __construct()
    {
        $this->calendarContact = new Collections\ArrayCollection();
    }

    public function __toString(): string
    {
        return (string)$this->status;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): ContactStatus
    {
        $this->id = $id;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): ContactStatus
    {
        $this->status = $status;
        return $this;
    }

    public function getStatusChange(): ?string
    {
        return $this->statusChange;
    }

    public function setStatusChange(?string $statusChange): ContactStatus
    {
        $this->statusChange = $statusChange;
        return $this;
    }

    public function getCalendarContact()
    {
        return $this->calendarContact;
    }

    public function setCalendarContact($calendarContact): ContactStatus
    {
        $this->calendarContact = $calendarContact;
        return $this;
    }
}
