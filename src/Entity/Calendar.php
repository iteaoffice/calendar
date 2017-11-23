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

use Content\Entity\Image;
use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zend\Form\Annotation;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Calendar
 *
 * @ORM\Table(name="calendar")
 * @ORM\Entity(repositoryClass="Calendar\Repository\Calendar")
 */
class Calendar extends EntityAbstract implements ResourceInterface
{
    /**
     * Constant for final = -1 (draft)
     */
    public const FINAL_DRAFT = -1;
    /**
     * Constant for final = 1 (final)
     */
    public const FINAL_FINAL = 1;
    /**
     * Constant for final = 0 (tentative)
     */
    public const FINAL_TENTATIVE = 0;
    /**
     * Constant for not on homepage = 0 (not on homepage)
     */
    public const NOT_ON_HOMEPAGE = 0;
    /**
     * Constant for on homepage = 1 (on homepage)
     */
    public const ON_HOMEPAGE = 1;
    /**
     * Textual versions of the final
     *
     * @var array
     */
    protected static $finalTemplates
        = [
            self::FINAL_DRAFT     => 'txt-draft',
            self::FINAL_TENTATIVE => 'txt-tentative',
            self::FINAL_FINAL     => 'txt-final',
        ];
    /**
     * Textual versions of the on homepage
     *
     * @var array
     */
    protected static $onHomepageTemplates
        = [
            self::NOT_ON_HOMEPAGE => 'txt-not-on-homepage',
            self::ON_HOMEPAGE     => 'txt-on-homepage',
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
     * @ORM\Column(name="calendar", type="string", length=60, nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-calendar","help-block": "txt-calendar-explanation"})
     * @var string
     */
    private $calendar;
    /**
     * @ORM\Column(name="location", type="string", length=255, nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-location","help-block": "txt-location-explanation"})
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
     * @Annotation\Options({"label":"txt-date-from","help-block": "txt-date-from-explanation", "format": "Y-m-d H:i"})
     * @Annotation\Attributes({"step":"any"})
     * @var \DateTime
     */
    private $dateFrom;
    /**
     * @ORM\Column(name="date_end", type="datetime", nullable=false)
     * @Annotation\Type("\Zend\Form\Element\DateTime")
     * @Annotation\Options({"label":"txt-date-end","help-block": "txt-date-end-explanation", "format": "Y-m-d H:i"})
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
     * @Annotation\Attributes({"label":"txt-final"})
     * @Annotation\Options({"help-block":"txt-final-explanation"})
     * @var integer
     */
    private $final;
    /**
     * @ORM\Column(name="on_homepage", type="smallint", nullable=false)
     * @Annotation\Type("Zend\Form\Element\Radio")
     * @Annotation\Attributes({"array":"onHomepageTemplates"})
     * @Annotation\Attributes({"label":"txt-on-homepage"})
     * @Annotation\Options({"help-block":"txt-on-homepage-explanation"})
     * @var integer
     */
    private $onHomepage;
    /**
     * @ORM\Column(name="sequence", type="smallint", length=4, nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-sequence","help-block": "txt-calendar-sequence-explanation"})
     * @var int
     */
    private $sequence;
    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Textarea")
     * @Annotation\Options({"label":"txt-description","help-block": "txt-calendar-description-explanation"})
     * @var string
     */
    private $description;
    /**
     * @ORM\Column(name="url", type="string", length=60, nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Url")
     * @Annotation\Options({"label":"txt-url","help-block": "txt-calendar-url-explanation"})
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
     * @Annotation\Options({"label":"txt-image-url","help-block": "txt-image-url-explanation"})
     * @var string
     */
    private $imageUrl;
    /**
     * @ORM\ManyToOne(targetEntity="Content\Entity\Image", cascade={"persist"}, inversedBy="calendar")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="image_id", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Number")
     * @Annotation\Options({"label":"txt-calendar-image-label","help-block":"txt-calendar-image-help-block"})
     * @Annotation\Attributes({"placeholder":"txt-calendar-image-placeholder"})
     *
     * @var \Content\Entity\Image
     */
    private $image;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\Type", cascade="persist", inversedBy="calendar")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="type_id", nullable=false)
     * })
     * @Annotation\Type("DoctrineORMModule\Form\Element\EntitySelect")
     * @Annotation\Options({"target_class":"Calendar\Entity\Type","help-block":"txt-type-explanation"})
     * @Annotation\Attributes({"label":"txt-calendar-type", "help-block":"txt-type-explanation"})
     * @var \Calendar\Entity\Type
     */
    private $type;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", cascade="persist", inversedBy="calendar")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     * })
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
     * @ORM\OneToMany(targetEntity="Calendar\Entity\Schedule", cascade={"persist","remove"}, mappedBy="calendar")
     * @Annotation\Exclude()
     * @var \Calendar\Entity\Schedule[]|Collections\ArrayCollection
     */
    private $schedule;
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
     * @Annotation\Attributes({"label":"txt-program-call"})
     * @var \Program\Entity\Call\Call[]|Collections\ArrayCollection
     */
    private $call;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->calendarContact = new Collections\ArrayCollection();
        $this->document = new Collections\ArrayCollection();
        $this->schedule = new Collections\ArrayCollection();
        $this->call = new Collections\ArrayCollection();
    }

    /**
     * @return array
     */
    public static function getFinalTemplates(): array
    {
        return self::$finalTemplates;
    }

    /**
     * @return array
     */
    public static function getOnHomepageTemplates(): array
    {
        return self::$onHomepageTemplates;
    }

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
     * @param $property
     * @return bool
     */
    public function __isset($property)
    {
        return isset($this->$property);
    }

    /**
     * New function needed to make the hydrator happy
     *
     * @param Collections\Collection $collection
     */
    public function addCall(Collections\Collection $collection)
    {
        foreach ($collection as $call) {
            $this->call->add($call);
        }
    }

    /**
     * New function needed to make the hydrator happy
     *
     * @param Collections\Collection $collection
     */
    public function removeCall(Collections\Collection $collection)
    {
        foreach ($collection as $call) {
            $this->call->removeElement($call);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->calendar;
    }


    /**
     * @param bool $textual
     *
     * @return int|string
     */
    public function getFinal($textual = false)
    {
        if ($textual) {
            return self::$finalTemplates[$this->final];
        }

        return $this->final;
    }

    /**
     * @param int $final
     */
    public function setFinal($final)
    {
        $this->final = $final;
    }

    /**
     * @param bool $textual
     *
     * @return int|string
     */
    public function getOnHomepage($textual = false)
    {
        if ($textual) {
            return self::$onHomepageTemplates[$this->onHomepage];
        }

        return $this->onHomepage;
    }

    /**
     * @param int $onHomepage
     */
    public function setOnHomepage($onHomepage)
    {
        $this->onHomepage = $onHomepage;
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
     * @return Calendar
     */
    public function setId($id): Calendar
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * @param string $calendar
     *
     * @return Calendar
     */
    public function setCalendar($calendar): Calendar
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     *
     * @return Calendar
     */
    public function setLocation($location): Calendar
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return string
     */
    public function getDocRef()
    {
        return $this->docRef;
    }

    /**
     * @param string $docRef
     *
     * @return Calendar
     */
    public function setDocRef($docRef): Calendar
    {
        $this->docRef = $docRef;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @param \DateTime $dateFrom
     *
     * @return Calendar
     */
    public function setDateFrom($dateFrom): Calendar
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param \DateTime $dateEnd
     *
     * @return Calendar
     */
    public function setDateEnd($dateEnd): Calendar
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return Calendar
     */
    public function setDateCreated($dateCreated): Calendar
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * @param \DateTime $dateUpdated
     *
     * @return Calendar
     */
    public function setDateUpdated($dateUpdated): Calendar
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    /**
     * @return int
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @param int $sequence
     *
     * @return Calendar
     */
    public function setSequence($sequence): Calendar
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Calendar
     */
    public function setDescription($description): Calendar
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return Calendar
     */
    public function setUrl($url): Calendar
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDatePlan()
    {
        return $this->datePlan;
    }

    /**
     * @param \DateTime $datePlan
     *
     * @return Calendar
     */
    public function setDatePlan($datePlan)
    {
        $this->datePlan = $datePlan;

        return $this;
    }

    /**
     * @return string
     */
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     *
     * @return Calendar
     */
    public function setImageUrl($imageUrl): Calendar
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param Image $image
     *
     * @return Calendar
     */
    public function setImage($image): Calendar
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Type $type
     *
     * @return Calendar
     */
    public function setType($type): Calendar
    {
        $this->type = $type;

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
     * @return Calendar
     */
    public function setContact($contact): Calendar
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return \Contact\Entity\Contact[]|Collections\ArrayCollection
     */
    public function getCalendarContact()
    {
        return $this->calendarContact;
    }

    /**
     * @param \Contact\Entity\Contact[]|Collections\ArrayCollection $calendarContact
     *
     * @return Calendar
     */
    public function setCalendarContact($calendarContact)
    {
        $this->calendarContact = $calendarContact;

        return $this;
    }

    /**
     * @return Document[]|Collections\ArrayCollection
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param Document[]|Collections\ArrayCollection $document
     *
     * @return Calendar
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * @return Schedule[]|Collections\ArrayCollection
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * @param Schedule[]|Collections\ArrayCollection $schedule
     *
     * @return Calendar
     */
    public function setSchedule($schedule)
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * @return \Project\Entity\Calendar\Calendar
     */
    public function getProjectCalendar()
    {
        return $this->projectCalendar;
    }

    /**
     * @param \Project\Entity\Calendar\Calendar $projectCalendar
     *
     * @return Calendar
     */
    public function setProjectCalendar($projectCalendar)
    {
        $this->projectCalendar = $projectCalendar;

        return $this;
    }

    /**
     * @return Collections\ArrayCollection|\Program\Entity\Call\Call[]
     */
    public function getCall()
    {
        return $this->call;
    }

    /**
     * @param Collections\ArrayCollection|\Program\Entity\Call\Call[] $call
     *
     * @return Calendar
     */
    public function setCall($call)
    {
        $this->call = $call;

        return $this;
    }
}
