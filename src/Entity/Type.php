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
 * CalendarType.
 *
 * @ORM\Table(name="calendar_type")
 * @ORM\Entity(repositoryClass="Calendar\Repository\Type")
 * @ORM\HasLifecycleCallbacks
 */
class Type extends AbstractEntity
{
    /**
     * @ORM\Column(name="type_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Type("\Zend\Form\Element\Hidden")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="type", type="string", nullable=false, unique=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-calendar-type-type-label","help-block": "txt-calendar-type-type-help-block"})
     * @Annotation\Attributes({"placeholder":"txt-calendar-type-type-placeholder"})
     *
     * @var string
     */
    private $type;
    /**
     * @ORM\Column(name="color", type="string", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Color")
     * @Annotation\Options({"label":"txt-calendar-type-background-color-label","help-block": "txt-calendar-type-background-color-help-block"})
     *
     * @var string
     */
    private $color;
    /**
     * @ORM\Column(name="color_font", type="string", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Color")
     * @Annotation\Options({"label":"txt-calendar-font-color-label","help-block": "txt-calendar-font-color-help-block"})
     *
     * @var string
     */
    private $colorFont;
    /**
     * @ORM\OneToMany(targetEntity="\Calendar\Entity\Calendar", cascade={"persist"}, mappedBy="type")
     * @Annotation\Exclude()
     *
     * @var \Calendar\Entity\Calendar[]|Collections\ArrayCollection
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
     * @Annotation\Options({
     *      "target_class":"Admin\Entity\Access",
     *      "find_method":{
     *          "name":"findBy",
     *          "params": {
     *              "criteria":{},
     *              "orderBy":{
     *                  "access":"ASC"}
     *              }
     *          }
     *      }
     * )
     * @Annotation\Attributes({"label":"txt-calendar-type-access-label","help-block":"txt-calendar-type-access-help-block"})
     *
     * @var \Admin\Entity\Access[]
     */
    private $access;

    public function __construct()
    {
        $this->calendar = new Collections\ArrayCollection();
        $this->access = new Collections\ArrayCollection();
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
        return (string)$this->type;
    }

    /**
     * @ORM\PreUpdate
     */
    public function removeCachedCssFile(): void
    {
        if (file_exists($this->getCacheCssFileName())) {
            unlink($this->getCacheCssFileName());
        }
    }

    public function getCacheCssFileName(): string
    {
        return __DIR__ . '/../../../../../public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR
            . (\defined('ITEAOFFICE_HOST') ? ITEAOFFICE_HOST : 'test') . DIRECTORY_SEPARATOR
            . 'css/calendar-type-color.css';
    }

    /**
     * Return a normalized CSS name for the type.
     */
    public function parseCssName(): string
    {
        return 'calendar-type-' . $this->getId();
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Type
     */
    public function setId($id): Type
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Type
     */
    public function setType(string $type): Type
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @param string $color
     *
     * @return Type
     */
    public function setColor(string $color): Type
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @return string
     */
    public function getColorFont(): ?string
    {
        return $this->colorFont;
    }

    /**
     * @param string $colorFont
     *
     * @return Type
     */
    public function setColorFont(string $colorFont): Type
    {
        $this->colorFont = $colorFont;
        return $this;
    }

    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * @param Calendar[]|Collections\ArrayCollection $calendar
     *
     * @return Type
     */
    public function setCalendar($calendar): Type
    {
        $this->calendar = $calendar;
        return $this;
    }

    /**
     * @return \Admin\Entity\Access[]|Collections\ArrayCollection
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * @param \Admin\Entity\Access[] $access
     *
     * @return Type
     */
    public function setAccess($access): Type
    {
        $this->access = $access;
        return $this;
    }
}
