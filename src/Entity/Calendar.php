<?php
/**
 * ITEA copyright message placeholder
 *
 * @category  Calendar
 * @package   Entity
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */
declare(strict_types=1);

namespace Calendar\Entity;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zend\Form\Annotation;

/**
 * @ORM\Table(name="calendar")
 * @ORM\Entity(repositoryClass="Calendar\Repository\Calendar")
 */
class Calendar extends AbstractEntity
{
    public const FINAL_DRAFT = -1;
    public const FINAL_FINAL = 1;
    public const FINAL_TENTATIVE = 0;

    public const NOT_ON_HOMEPAGE = 0;
    public const ON_HOMEPAGE = 1;

    public const NO_HIGHLIGHT = 0;
    public const HIGHLIGHT = 1;

    public const NO_OWN_EVENT = 0;
    public const OWN_EVENT = 1;

    public const NOT_PRESENT = 0;
    public const PRESENT = 1;

    private static $finalTemplates
        = [
            self::FINAL_DRAFT     => 'txt-draft',
            self::FINAL_TENTATIVE => 'txt-tentative',
            self::FINAL_FINAL     => 'txt-final',
        ];

    private static $onHomepageTemplates
        = [
            self::NOT_ON_HOMEPAGE => 'txt-not-on-homepage',
            self::ON_HOMEPAGE     => 'txt-on-homepage'
        ];

    private static $highlightTemplates
        = [
            self::NO_HIGHLIGHT => 'txt-no-highlight',
            self::HIGHLIGHT    => 'txt-highlight-on-event-page'
        ];

    private static $ownEventTemplates
        = [
            self::NO_OWN_EVENT => 'txt-no-own-event',
            self::OWN_EVENT    => 'txt-own-event'
        ];

    private static $presentTemplates
        = [
            self::NOT_PRESENT => 'txt-office-not-present',
            self::PRESENT     => 'txt-office-present'
        ];
    /**
     * @ORM\Column(name="calendar_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="calendar", type="string")
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-calendar-calendar-label","help-block": "txt-calendar-calendar-help-block"})
     * @Annotation\Attributes({"placeholder": "txt-calendar-calendar-placeholder"})
     * @var string
     */
    private $calendar;
    /**
     * @ORM\Column(name="location", type="string", length=255, nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-calendar-location-label","help-block": "txt-calendar-location-help-block"})
     * @Annotation\Attributes({"placeholder": "txt-calendar-location-placeholder"})
     * @var string
     */
    private $location;
    /**
     * @ORM\Column(name="docref", type="string", length=255, nullable=false, unique=true)
     * @Gedmo\Slug(fields={"calendar","location"})
     * @Annotation\Exclude()
     * @var string
     */
    private $docRef;
    /**
     * @ORM\Column(name="date_from", type="datetime", nullable=false)
     * @Annotation\Type("\Zend\Form\Element\DateTime")
     * @Annotation\Options({"label":"txt-calendar-date-from-label","help-block": "txt-calendar-date-from-help-block", "format": "Y-m-d H:i"})
     * @Annotation\Attributes({"step":"any"})
     * @var \DateTime
     */
    private $dateFrom;
    /**
     * @ORM\Column(name="date_end", type="datetime", nullable=false)
     * @Annotation\Type("\Zend\Form\Element\DateTime")
     * @Annotation\Options({"label":"txt-calendar-date-end-label","help-block": "txt-calendar-date-end-help-block", "format": "Y-m-d H:i"})
     * @Annotation\Attributes({"step":"any"})
     * @var \DateTime
     */
    private $dateEnd;
    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     * @Annotation\Exclude()
     * @var \DateTime
     */
    private $dateCreated;
    /**
     * @ORM\Column(name="date_updated", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     * @Annotation\Exclude()
     * @var \DateTime
     */
    private $dateUpdated;
    /**
     * @ORM\Column(name="final", type="smallint", nullable=false)
     * @Annotation\Type("Zend\Form\Element\Radio")
     * @Annotation\Attributes({"array":"finalTemplates"})
     * @Annotation\Attributes({"label":"txt-calendar-final-label"})
     * @Annotation\Options({"help-block":"txt-calendar-final-help-block"})
     * @var integer
     */
    private $final;
    /**
     * @ORM\Column(name="on_homepage", type="smallint", nullable=false)
     * @Annotation\Type("Zend\Form\Element\Radio")
     * @Annotation\Attributes({"array":"onHomepageTemplates"})
     * @Annotation\Attributes({"label":"txt-calendar-on-homepage-label"})
     * @Annotation\Options({"help-block":"txt-calendar-on-homepage-help-block"})
     * @var integer
     */
    private $onHomepage;
    /**
     * @ORM\Column(name="highlight", type="smallint", nullable=false)
     * @Annotation\Type("Zend\Form\Element\Radio")
     * @Annotation\Attributes({"array":"highlightTemplates"})
     * @Annotation\Attributes({"label":"txt-calendar-highlight-label"})
     * @Annotation\Options({"help-block":"txt-calendar-highlight-help-block"})
     * @var integer
     */
    private $highlight;
    /**
     * @ORM\Column(name="highlight_description", type="text", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Textarea")
     * @Annotation\Options({"label":"txt-calendar-highlight-description-label","help-block": "txt-calendar-highlight-description-help-block"})
     * @Annotation\Attributes({"placeholder": "txt-calendar-highlight-description-placeholder"})
     * @var string
     */
    private $highlightDescription;
    /**
     * @ORM\Column(name="own_event", type="smallint", nullable=false)
     * @Annotation\Type("Zend\Form\Element\Radio")
     * @Annotation\Attributes({"array":"ownEventTemplates"})
     * @Annotation\Attributes({"label":"txt-calendar-own-event-label"})
     * @Annotation\Options({"help-block":"txt-calendar-own-event-help-block"})
     * @var integer
     */
    private $ownEvent;
    /**
     * @ORM\Column(name="present", type="smallint", nullable=false)
     * @Annotation\Type("Zend\Form\Element\Radio")
     * @Annotation\Attributes({"array":"presentTemplates"})
     * @Annotation\Attributes({"label":"txt-calendar-present-label"})
     * @Annotation\Options({"help-block":"txt-calendar-present-help-block"})
     * @var integer
     */
    private $present;
    /**
     * @ORM\Column(name="sequence", type="smallint")
     * @Annotation\Type("\Zend\Form\Element\Number")
     * @Annotation\Options({"label":"txt-calendar-sequence-label","help-block":"txt-calendar-sequence-help-block"})
     * @var int
     */
    private $sequence;
    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Textarea")
     * @Annotation\Options({"label":"txt-calendar-description-label","help-block": "txt-calendar-description-help-block"})
     * @Annotation\Attributes({"placeholder": "txt-calendar-description-placeholder"})
     * @var string
     */
    private $description;
    /**
     * @ORM\Column(name="url", length=1000, type="string")
     * @Annotation\Type("\Zend\Form\Element\Url")
     * @Annotation\Options({"label":"txt-calendar-url-label","help-block": "txt-calendar-url-help-block"})
     * @Annotation\Attributes({"placeholder": "txt-calendar-url-placeholder"})
     * @var string
     */
    private $url;
    /**
     * @ORM\Column(name="date_plan", type="datetime", nullable=true)
     * @var \DateTime
     */
    private $datePlan;
    /**
     * @ORM\Column(name="image_url", type="string", length=125, nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-calendar-image-url-label","help-block": "txt-calendar-image-url-help-block"})
     * @Annotation\Attributes({"placeholder": "txt-calendar-image-url-placeholder"})
     * @var string
     */
    private $imageUrl;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\Type", cascade="persist", inversedBy="calendar")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="type_id", nullable=false)
     * @Annotation\Type("DoctrineORMModule\Form\Element\EntitySelect")
     * @Annotation\Options({"target_class":"Calendar\Entity\Type","help-block":"txt-calendar-type-help-block"})
     * @Annotation\Attributes({"label":"txt-calendar-type-label"})
     * @var \Calendar\Entity\Type
     */
    private $type;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", cascade="persist", inversedBy="calendar")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     * @Annotation\Exclude()
     * @var \Contact\Entity\Contact
     */
    private $contact;
    /**
     * @ORM\OneToMany(targetEntity="Calendar\Entity\Contact", cascade={"persist"}, mappedBy="calendar")
     * @Annotation\Exclude()
     * @var \Calendar\Entity\Contact[]|Collections\ArrayCollection
     */
    private $calendarContact;
    /**
     * @ORM\OneToMany(targetEntity="Calendar\Entity\Document", cascade={"persist","remove"}, mappedBy="calendar")
     * @ORM\OrderBy({"document"="ASC"})
     * @Annotation\Exclude()
     * @var \Calendar\Entity\Document[]|Collections\ArrayCollection
     */
    private $document;
    /**
     * @ORM\OneToOne(targetEntity="Project\Entity\Calendar\Calendar", cascade={"persist","remove"}, mappedBy="calendar")
     * @Annotation\Exclude()
     * @var \Project\Entity\Calendar\Calendar
     */
    private $projectCalendar;
    /**
     * @ORM\ManyToMany(targetEntity="Program\Entity\Call\Call", cascade={"persist"},inversedBy="calendar")
     * @ORM\JoinTable(name="programcall_calendar",
     *    joinColumns={@ORM\JoinColumn(name="calendar_id", referencedColumnName="calendar_id")},
     *    inverseJoinColumns={@ORM\JoinColumn(name="programcall_id", referencedColumnName="programcall_id")}
     * )
     * @ORM\OrderBy({"call"="ASC"})
     * @Annotation\Type("DoctrineORMModule\Form\Element\EntityMultiCheckbox")
     * @Annotation\Options({"target_class":"Program\Entity\Call\Call"})
     * @Annotation\Options({"help-block":"txt-calendar-program-call-help-block"})
     * @Annotation\Options({"label":"txt-program-call"})
     * @var \Program\Entity\Call\Call[]|Collections\ArrayCollection
     */
    private $call;

    public function __construct()
    {
        $this->calendarContact = new Collections\ArrayCollection();
        $this->document = new Collections\ArrayCollection();
        $this->call = new Collections\ArrayCollection();

//        $this->final = self::FINAL_DRAFT;
        $this->onHomepage = self::ON_HOMEPAGE;
        $this->highlight = self::NO_HIGHLIGHT;
        $this->ownEvent = self::NO_OWN_EVENT;
        $this->present = self::NOT_PRESENT;
    }

    public static function getFinalTemplates(): array
    {
        return self::$finalTemplates;
    }

    public static function getOnHomepageTemplates(): array
    {
        return self::$onHomepageTemplates;
    }

    public static function getHighlightTemplates(): array
    {
        return self::$highlightTemplates;
    }

    public static function getOwnEventTemplates(): array
    {
        return self::$ownEventTemplates;
    }

    public static function getPresentTemplates(): array
    {
        return self::$presentTemplates;
    }

    public function isHighlight(): bool
    {
        return $this->highlight === self::HIGHLIGHT;
    }

    public function isOwnEvent(): bool
    {
        return $this->ownEvent === self::OWN_EVENT;
    }

    public function isPresent(): bool
    {
        return $this->present === self::PRESENT;
    }

    public function isProject(): bool
    {
        return null !== $this->projectCalendar;
    }

    public function isReview(): bool
    {
        return null !== $this->projectCalendar;
    }

    public function isBirthday(): bool
    {
        return false;
    }

    public function isCall(): bool
    {
        return false;
    }

    public function onHomepage(): bool
    {
        return $this->isFinal() && $this->onHomepage === self::ON_HOMEPAGE;
    }

    public function isFinal(): bool
    {
        return $this->final === self::FINAL_FINAL;
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

    public function addCall(Collections\Collection $collection): void
    {
        foreach ($collection as $call) {
            $this->call->add($call);
        }
    }

    public function removeCall(Collections\Collection $collection): void
    {
        foreach ($collection as $call) {
            $this->call->removeElement($call);
        }
    }

    public function __toString(): string
    {
        return (string)$this->calendar;
    }

    public function getFinal(bool $textual = false)
    {
        if ($textual) {
            return self::$finalTemplates[$this->final];
        }

        return $this->final;
    }

    public function setFinal(int $final): Calendar
    {
        $this->final = $final;

        return $this;
    }

    public function getOnHomepage(bool $textual = false)
    {
        if ($textual) {
            return self::$onHomepageTemplates[$this->onHomepage];
        }

        return $this->onHomepage;
    }

    public function setOnHomepage(int $onHomepage): Calendar
    {
        $this->onHomepage = $onHomepage;

        return $this;
    }

    public function getHighlight(bool $textual = false)
    {
        if ($textual) {
            return self::$highlightTemplates[$this->highlight];
        }

        return $this->highlight;
    }


    public function setHighlight(int $highlight): Calendar
    {
        $this->highlight = $highlight;

        return $this;
    }

    public function getOwnEvent(bool $textual = false)
    {
        if ($textual) {
            return self::$ownEventTemplates[$this->ownEvent];
        }

        return $this->ownEvent;
    }

    public function setOwnEvent(int $ownEvent): Calendar
    {
        $this->ownEvent = $ownEvent;

        return $this;
    }

    public function getPresent(bool $textual = false)
    {
        if ($textual) {
            return self::$presentTemplates[$this->present];
        }

        return $this->present;
    }

    public function setPresent(int $present): Calendar
    {
        $this->present = $present;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): Calendar
    {
        $this->id = $id;

        return $this;
    }

    public function getCalendar(): ?string
    {
        return $this->calendar;
    }

    public function setCalendar($calendar): Calendar
    {
        $this->calendar = $calendar;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation($location): Calendar
    {
        $this->location = $location;

        return $this;
    }

    public function getDocRef(): ?string
    {
        return $this->docRef;
    }

    public function setDocRef($docRef): Calendar
    {
        $this->docRef = $docRef;

        return $this;
    }

    public function getDateFrom(): ?\DateTime
    {
        return $this->dateFrom;
    }

    public function setDateFrom($dateFrom): Calendar
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    public function getDateEnd(): ?\DateTime
    {
        return $this->dateEnd;
    }

    public function setDateEnd($dateEnd): Calendar
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getDateCreated(): ?\DateTime
    {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated): Calendar
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateUpdated(): ?\DateTime
    {
        return $this->dateUpdated;
    }

    public function setDateUpdated($dateUpdated): Calendar
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    public function setSequence(int $sequence): Calendar
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): Calendar
    {
        $this->description = $description;

        return $this;
    }

    public function getHighlightDescription(): ?string
    {
        return $this->highlightDescription;
    }

    public function setHighlightDescription(string $highlightDescription): Calendar
    {
        $this->highlightDescription = $highlightDescription;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): Calendar
    {
        $this->url = $url;

        return $this;
    }

    public function getDatePlan(): ?\DateTime
    {
        return $this->datePlan;
    }

    public function setDatePlan(\DateTime $datePlan): Calendar
    {
        $this->datePlan = $datePlan;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl($imageUrl): Calendar
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType($type): Calendar
    {
        $this->type = $type;

        return $this;
    }

    public function getContact(): ?\Contact\Entity\Contact
    {
        return $this->contact;
    }

    public function setContact(\Contact\Entity\Contact $contact): Calendar
    {
        $this->contact = $contact;

        return $this;
    }

    public function getCalendarContact()
    {
        return $this->calendarContact;
    }

    public function setCalendarContact($calendarContact)
    {
        $this->calendarContact = $calendarContact;

        return $this;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function setDocument($document): Calendar
    {
        $this->document = $document;

        return $this;
    }

    public function getProjectCalendar(): ?\Project\Entity\Calendar\Calendar
    {
        return $this->projectCalendar;
    }

    public function setProjectCalendar(\Project\Entity\Calendar\Calendar $projectCalendar): Calendar
    {
        $this->projectCalendar = $projectCalendar;

        return $this;
    }

    public function getCall()
    {
        return $this->call;
    }

    public function setCall($call): Calendar
    {
        $this->call = $call;

        return $this;
    }
}
