<?php
/**
 * ITEA copyright message placeholder
 *
 * @category    Calendar
 * @package     Entity
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Entity;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\Factory as InputFactory;
use Zend\Form\Annotation;
use Zend\Permissions\Acl\Resource\ResourceInterface;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Calendar
 *
 * @ORM\Table(name="calendar")
 * @ORM\Entity(repositoryClass="Calendar\Repository\Calendar")
 */
class Calendar extends EntityAbstract
{
    /**
     * Constant for final = 0 (tentative)
     */
    const FINAL_TENTATIVE = 0;
    /**
     * Constant for final = 1 (final)
     */
    const FINAL_FINAL = 1;
    /**
     * @ORM\Column(name="calendar_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="calendar", type="string", length=60, nullable=true)
     * @var string
     */
    private $calendar;
    /**
     * @ORM\Column(name="location", type="string", length=255, nullable=true)
     * @var string
     */
    private $location;
    /**
     * @ORM\Column(name="date_from", type="datetime", nullable=false)
     * @var \DateTime
     */
    private $dateFrom;
    /**
     * @ORM\Column(name="date_end", type="datetime", nullable=false)
     * @var \DateTime
     */
    private $dateEnd;
    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     * @var \DateTime
     */
    private $dateCreated;
    /**
     * @ORM\Column(name="date_updated", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     * @var \DateTime
     */
    private $dateUpdated;
    /**
     * @ORM\Column(name="final", type="smallint", nullable=false)
     * @var integer
     */
    private $final;
    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     * @var string
     */
    private $description;
    /**
     * @ORM\Column(name="url", type="string", length=60, nullable=true)
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
     * @var string
     */
    private $imageUrl;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\Type", cascade="persist", inversedBy="calendar")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="type_id", nullable=false)
     * })
     * @var \Calendar\Entity\Type
     */
    private $type;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", cascade="persist", inversedBy="calendar")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     * })
     * @var \Calendar\Entity\Contact
     */
    private $contact;
    /**
     * @ORM\OneToMany(targetEntity="Calendar\Entity\Contact", cascade={"persist"}, mappedBy="calendar")
     * @Annotation\Exclude()
     * @var \Calendar\Entity\Contact[]
     */
    private $calendarContact;
    /**
     * @ORM\OneToMany(targetEntity="Calendar\Entity\Document", cascade={"persist"}, mappedBy="calendar")
     * @Annotation\Exclude()
     * @var \Calendar\Entity\Document[]
     */
    private $document;
    /**
     * @ORM\OneToMany(targetEntity="Calendar\Entity\Schedule", cascade={"persist"}, mappedBy="calendar")
     * @Annotation\Exclude()
     * @var \Calendar\Entity\Schedule[]
     */
    private $schedule;
    /**
     * @ORM\OneToOne(targetEntity="Project\Entity\Calendar\Calendar", cascade={"persist"}, mappedBy="calendar")
     * @Annotation\Exclude()
     * @var \Project\Entity\Calendar\Calendar
     */
    private $projectCalendar;
    /**
     * @ORM\OneToOne(targetEntity="Program\Entity\Call\Calendar", cascade={"persist"}, mappedBy="calendar")
     * @Annotation\Exclude()
     * @var \Program\Entity\Call\Calendar
     */
    private $callCalendar;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->calendarContact = new Collections\ArrayCollection();
        $this->document        = new Collections\ArrayCollection();
        $this->schedule        = new Collections\ArrayCollection();
        $this->projectCalendar = new Collections\ArrayCollection();
        $this->callCalendar    = new Collections\ArrayCollection();
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
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'calendar',
                        'required' => true,
                        'filters'  => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                        ),
                    )
                )
            );

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->calendar;
    }

    /**
     * @param string $calendar
     */
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;
    }

    /**
     * @return string
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * @param \Calendar\Entity\Contact[] $calendarContact
     */
    public function setCalendarContact($calendarContact)
    {
        $this->calendarContact = $calendarContact;
    }

    /**
     * @return \Calendar\Entity\Contact[]
     */
    public function getCalendarContact()
    {
        return $this->calendarContact;
    }

    /**
     * @param \Calendar\Entity\Contact $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return \Calendar\Entity\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param \DateTime $dateCreated
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateEnd
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;
    }

    /**
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param \DateTime $dateFrom
     */
    public function setDateFrom($dateFrom)
    {
        $this->dateFrom = $dateFrom;
    }

    /**
     * @return \DateTime
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @param \DateTime $datePlan
     */
    public function setDatePlan($datePlan)
    {
        $this->datePlan = $datePlan;
    }

    /**
     * @return \DateTime
     */
    public function getDatePlan()
    {
        return $this->datePlan;
    }

    /**
     * @param \DateTime $dateUpdated
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->dateUpdated = $dateUpdated;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param \Calendar\Entity\Document[] $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }

    /**
     * @return \Calendar\Entity\Document[]
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param int $final
     */
    public function setFinal($final)
    {
        $this->final = $final;
    }

    /**
     * @return int
     */
    public function getFinal()
    {
        return $this->final;
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
     * @param string $imageUrl
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param \Calendar\Entity\Schedule[] $schedule
     */
    public function setSchedule($schedule)
    {
        $this->schedule = $schedule;
    }

    /**
     * @return \Calendar\Entity\Schedule[]
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * @param \Calendar\Entity\Type $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return \Calendar\Entity\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param \Project\Entity\Calendar\Calendar $projectCalendar
     */
    public function setProjectCalendar($projectCalendar)
    {
        $this->projectCalendar = $projectCalendar;
    }

    /**
     * @return \Project\Entity\Calendar\Calendar
     */
    public function getProjectCalendar()
    {
        return $this->projectCalendar;
    }

    /**
     * @param \Program\Entity\Call\Calendar $callCalendar
     */
    public function setCallCalendar($callCalendar)
    {
        $this->callCalendar = $callCalendar;
    }

    /**
     * @return \Program\Entity\Call\Calendar
     */
    public function getCallCalendar()
    {
        return $this->callCalendar;
    }
}
