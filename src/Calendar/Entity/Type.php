<?php
/**
 * ITEA copyright message placeholder.
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Calendar\Entity;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * CalendarType.
 *
 * @ORM\Table(name="calendar_type")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Type
{
    /**
     * @ORM\Column(name="type_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="type", type="string", length=30, nullable=false)
     *
     * @var string
     */
    private $type;
    /**
     * @ORM\Column(name="color", type="string", length=7, nullable=true)
     *
     * @var string
     */
    private $color;
    /**
     * @ORM\Column(name="color_font", type="string", length=7, nullable=true)
     *
     * @var string
     */
    private $colorFont;
    /**
     * @ORM\Column(name="url", type="string", length=30, nullable=true)
     *
     * @var string
     */
    private $url;
    /**
     * @ORM\Column(name="autoplan", type="smallint", nullable=false)
     *
     * @var integer
     */
    private $autoPlan;
    /**
     * @ORM\OneToMany(targetEntity="\Calendar\Entity\Calendar", cascade={"persist"}, mappedBy="type")
     * @Annotation\Exclude()
     *
     * @var \Calendar\Entity\Calendar
     */
    private $calendar;
    /**
     * @ORM\ManyToMany(targetEntity="Admin\Entity\Access", inversedBy="calendarType")
     * @ORM\OrderBy=({"name"="ASC"})
     * @ORM\JoinTable(name="calendar_type_access",
     *            joinColumns={@ORM\JoinColumn(name="type_id", referencedColumnName="type_id")},
     *            inverseJoinColumns={@ORM\JoinColumn(name="access_id", referencedColumnName="access_id")}
     * )
     * @Annotation\Type("DoctrineORMModule\Form\Element\EntityMultiCheckbox")
     * @Annotation\Options({"target_class":"Admin\Entity\Access"})
     * @Annotation\Attributes({"label":"txt-access"})
     *
     * @var \Admin\Entity\Access[]
     */
    private $access;

    /**
     * @ORM\PreUpdate
     */
    public function removeCachedCssFile()
    {
        if (file_exists($this->getCacheCssFileName())) {
            unlink($this->getCacheCssFileName());
        }
    }

    /**
     * Return a link to the Css Filename.
     *
     * @return string
     */
    public function getCacheCssFileName()
    {
        return __DIR__.'/../../../../../../public'.DIRECTORY_SEPARATOR.'assets'.
        DIRECTORY_SEPARATOR.DEBRANOVA_HOST.DIRECTORY_SEPARATOR.'css/calendar-type-color.css';
    }

    /**
     * Return a normalized CSS name for the type.
     */
    public function parseCssName()
    {
        return 'calendar-type-'.$this->getId();
    }

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->calendar = new Collections\ArrayCollection();
        $this->access = new Collections\ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->type;
    }

    /**
     * @param \Admin\Entity\Access[] $access
     */
    public function setAccess($access)
    {
        $this->access = $access;
    }

    /**
     * @return \Admin\Entity\Access[]
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * @param int $autoPlan
     */
    public function setAutoPlan($autoPlan)
    {
        $this->autoPlan = $autoPlan;
    }

    /**
     * @return int
     */
    public function getAutoPlan()
    {
        return $this->autoPlan;
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
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $colorFont
     */
    public function setColorFont($colorFont)
    {
        $this->colorFont = $colorFont;
    }

    /**
     * @return string
     */
    public function getColorFont()
    {
        return $this->colorFont;
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
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
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
}
