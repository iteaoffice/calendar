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

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;
use Zend\Validator\Callback;

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
     * Constant for final = -1 (draft)
     */
    const FINAL_DRAFT = -1;
    /**
     * Constant for not on homepage = 0 (not on homepage)
     */
    const NOT_ON_HOMEPAGE = 0;
    /**
     * Constant for on homepage = 1 (on homepage)
     */
    const ON_HOMEPAGE = 1;
    /**
     * Textual versions of the final
     *
     * @var array
     */
    protected $finalTemplates = array(
        self::FINAL_DRAFT     => 'txt-draft',
        self::FINAL_TENTATIVE => 'txt-tentative',
        self::FINAL_FINAL     => 'txt-final'
    );
    /**
     * Textual versions of the on homepage
     *
     * @var array
     */
    protected $onHomepageTemplates = array(
        self::NOT_ON_HOMEPAGE => 'txt-not-on-homepage',
        self::ON_HOMEPAGE     => 'txt-on-homepage'
    );
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
     * @Annotation\Required(true)
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
     * @Annotation\Required(true)
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
     * @Annotation\Required(true)
     * @var integer
     */
    private $final;
    /**
     * @ORM\Column(name="on_homepage", type="smallint", nullable=false)
     * @Annotation\Type("Zend\Form\Element\Radio")
     * @Annotation\Attributes({"array":"onHomepageTemplates"})
     * @Annotation\Attributes({"label":"txt-on-homepage"})
     * @Annotation\Options({"help-block":"txt-on-homepage-explanation"})
     * @Annotation\Required(true)
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
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\Type", cascade="persist", inversedBy="calendar")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="type_id", nullable=false)
     * })
     * @Annotation\Type("DoctrineORMModule\Form\Element\EntitySelect")
     * @Annotation\Options({"target_class":"Calendar\Entity\Type","help-block":"txt-type-explanation"})
     * @Annotation\Attributes({"label":"txt-calendar-type", "required":"true","help-block":"txt-type-explanation"})
     * @var \Calendar\Entity\Type
     */
    private $type;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", cascade="persist", inversedBy="calendar")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     * })
     * @Annotation\Exclude()
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
     * @ORM\OneToMany(targetEntity="Calendar\Entity\Document", cascade={"persist","remove"}, mappedBy="calendar")
     * @Annotation\Exclude()
     * @var \Calendar\Entity\Document[]
     */
    private $document;
    /**
     * @ORM\OneToMany(targetEntity="Calendar\Entity\Schedule", cascade={"persist","remove"}, mappedBy="calendar")
     * @Annotation\Exclude()
     * @var \Calendar\Entity\Schedule[]
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
     * @var \Program\Entity\Call\Call[]
     */
    private $call;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->calendarContact = new Collections\ArrayCollection();
        $this->document        = new Collections\ArrayCollection();
        $this->schedule        = new Collections\ArrayCollection();
        $this->call            = new Collections\ArrayCollection();
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
     * New function needed to make the hydrator happy
     *
     * @param Collections\Collection $collection
     */
    public function addCall(Collections\Collection $collection)
    {
        foreach ($collection as $call) {
            //            $call->node = $this;
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

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'location',
                        'required' => false,
                        'filters'  => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                        ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'       => 'dateFrom',
                        'required'   => true,
                        'filters'    => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                        ),
                        'validators' => array(
                            array(
                                'name'    => 'DateTime',
                                'options' => array(
                                    'pattern' => 'yyyy-mm-dd HH:mm',
                                )
                            )
                        )
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'       => 'dateEnd',
                        'required'   => true,
                        'filters'    => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                        ),
                        'validators' => array(
                            array(
                                'name'    => 'DateTime',
                                'options' => array(
                                    'pattern' => 'yyyy-mm-dd HH:mm',
                                )
                            ),
                            array(
                                'name'    => 'Callback',
                                'options' => array(
                                    'messages' => array(
                                        Callback::INVALID_VALUE => 'The end date should be greater than start date',
                                    ),
                                    'callback' => function ($value, $context = array()) {
                                        $dateFrom = \DateTime::createFromFormat('Y-m-d H:i', $context['dateFrom']);
                                        $dateEnd  = \DateTime::createFromFormat('Y-m-d H:i', $value);

                                        return $dateEnd > $dateFrom;
                                    },
                                ),
                            ),
                        )
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'       => 'final',
                        'required'   => true,
                        'validators' => array(
                            array(
                                'name'    => 'InArray',
                                'options' => array(
                                    'haystack' => array_keys($this->getFinalTemplates())
                                )
                            )
                        )
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'       => 'onHomepage',
                        'required'   => true,
                        'validators' => array(
                            array(
                                'name'    => 'InArray',
                                'options' => array(
                                    'haystack' => array_keys($this->getOnHomepageTemplates())
                                )
                            )
                        )
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'       => 'sequence',
                        'required'   => false,
                        'filters'    => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                        ),
                        'validators' => array(
                            array('name' => 'Int')
                        )
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'url',
                        'required' => false,
                        'filters'  => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                        ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'imageUrl',
                        'required' => false,
                        'filters'  => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                        ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'call',
                        'required' => false,
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
        return (string) $this->calendar;
    }

    /**
     * @return array
     */
    public function getFinalTemplates()
    {
        return $this->finalTemplates;
    }

    /**
     * @return array
     */
    public function getOnHomepageTemplates()
    {
        return $this->onHomepageTemplates;
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
     * @param bool $textual
     *
     * @return int|string
     */
    public function getFinal($textual = false)
    {
        if ($textual) {
            return $this->finalTemplates[$this->final];
        }

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
     * @param \Program\Entity\Call\Call[] $call
     */
    public function setCall($call)
    {
        $this->call = $call;
    }

    /**
     * @return \Program\Entity\Call\Call[]
     */
    public function getCall()
    {
        return $this->call;
    }

    /**
     * @param string $docRef
     */
    public function setDocRef($docRef)
    {
        $this->docRef = $docRef;
    }

    /**
     * @return string
     */
    public function getDocRef()
    {
        return $this->docRef;
    }

    /**
     * @param int $onHomepage
     */
    public function setOnHomepage($onHomepage)
    {
        $this->onHomepage = $onHomepage;
    }

    /**
     * @param bool $textual
     *
     * @return int|string
     */
    public function getOnHomepage($textual = false)
    {
        if ($textual) {
            return $this->onHomepageTemplates[$this->onHomepage];
        }

        return $this->onHomepage;
    }

    /**
     * @param int $sequence
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    /**
     * @return int
     */
    public function getSequence()
    {
        return $this->sequence;
    }
}
