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
     * @Annotation\Type("\Laminas\Form\Element\Hidden")
     *
     * @var int
     */
    private $id;
    /**
     * @ORM\Column(name="type", type="string", nullable=false, unique=true)
     * @Annotation\Type("\Laminas\Form\Element\Text")
     * @Annotation\Options({"label":"txt-calendar-type-type-label","help-block": "txt-calendar-type-type-help-block"})
     * @Annotation\Attributes({"placeholder":"txt-calendar-type-type-placeholder"})
     *
     * @var string
     */
    private $type;
    /**
     * @ORM\Column(name="color", type="string", nullable=true)
     * @Annotation\Type("\Laminas\Form\Element\Color")
     * @Annotation\Options({"label":"txt-calendar-type-background-color-label","help-block": "txt-calendar-type-background-color-help-block"})
     *
     * @var string
     */
    private $color;
    /**
     * @ORM\Column(name="color_font", type="string", nullable=true)
     * @Annotation\Type("\Laminas\Form\Element\Color")
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

    public function parseCssName(): string
    {
        return 'calendar-type-' . $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Type
    {
        $this->id = $id;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): Type
    {
        $this->type = $type;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): Type
    {
        $this->color = $color;
        return $this;
    }

    public function getColorFont(): ?string
    {
        return $this->colorFont;
    }

    public function setColorFont(?string $colorFont): Type
    {
        $this->colorFont = $colorFont;
        return $this;
    }

    public function getCalendar()
    {
        return $this->calendar;
    }

    public function setCalendar($calendar): Type
    {
        $this->calendar = $calendar;
        return $this;
    }

    public function getAccess()
    {
        return $this->access;
    }

    public function setAccess($access): Type
    {
        $this->access = $access;
        return $this;
    }
}
