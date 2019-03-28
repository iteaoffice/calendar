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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use General\Entity\ContentType;
use Zend\Form\Annotation;

/**
 * CalendarDocument.
 *
 * @ORM\Table(name="calendar_document")
 * @ORM\Entity
 */
class Document extends AbstractEntity
{
    /**
     * @ORM\Column(name="document_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     *
     * @var \DateTime
     */
    private $dateCreated;
    /**
     * @ORM\Column(name="document", type="string", nullable=false)
     *
     * @var string
     */
    private $document;
    /**
     * @ORM\Column(name="size", type="integer", nullable=true)
     *
     * @var integer
     */
    private $size;
    /**
     * @ORM\Column(name="date_updated", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     *
     * @var \DateTime
     */
    private $dateUpdated;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\Calendar", cascade="persist", inversedBy="document")
     * @ORM\JoinColumn(name="calendar_id", referencedColumnName="calendar_id", nullable=false)
     *
     * @var \Calendar\Entity\Calendar
     */
    private $calendar;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", cascade="persist", inversedBy="calendarDocument")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     *
     * @var \Contact\Entity\Contact
     */
    private $contact;
    /**
     * @ORM\ManyToOne(targetEntity="General\Entity\ContentType", cascade={"persist"}, inversedBy="calendarDocument")
     * @ORM\JoinColumn(name="contenttype_id", referencedColumnName="contenttype_id", nullable=false)
     *
     * @var \General\Entity\ContentType
     */
    private $contentType;
    /**
     * @ORM\OneToMany(targetEntity="Calendar\Entity\DocumentObject", cascade={"persist","remove"}, mappedBy="document",
     * fetch="EXTRA_LAZY")
     * @Annotation\Exclude()
     *
     * @var \Calendar\Entity\DocumentObject[]|ArrayCollection
     */
    private $object;

    /**
     * Document constructor.
     *
     */
    public function __construct()
    {
        $this->size = 0;
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

    public function parseFileName(): string
    {
        /**
         * When we don't know the extension, leave out the dot at the end of the to prevent that the document
         * is not in the zip
         */
        if ($this->contentType->getId() === ContentType::TYPE_UNKNOWN) {
            return $this->document;
        }

        if (\strpos($this->document, $this->contentType->getExtension()) !== false) {
            return $this->document;
        }

        return sprintf(
            '%s.%s',
            $this->document,
            $this->contentType->getExtension()
        );
    }

    public function getContentType(): ?ContentType
    {
        return $this->contentType;
    }

    /**
     * @param ContentType $contentType
     *
     * @return Document
     */
    public function setContentType(ContentType $contentType): Document
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->document;
    }

    /**
     * @return string
     */
    public function getDocument(): ?string
    {
        return $this->document;
    }

    /**
     * @param string $document
     *
     * @return Document
     */
    public function setDocument(string $document): Document
    {
        $this->document = $document;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Document
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated(): ?\DateTime
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return Document
     */
    public function setDateCreated(\DateTime $dateCreated): Document
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     *
     * @return Document
     */
    public function setSize(int $size): Document
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdated(): ?\DateTime
    {
        return $this->dateUpdated;
    }

    /**
     * @param \DateTime $dateUpdated
     *
     * @return Document
     */
    public function setDateUpdated(\DateTime $dateUpdated): Document
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    /**
     * @return Calendar|null
     */
    public function getCalendar(): ?Calendar
    {
        return $this->calendar;
    }

    /**
     * @param Calendar $calendar
     *
     * @return Document
     */
    public function setCalendar($calendar): Document
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * @return \Contact\Entity\Contact|null
     */
    public function getContact(): ?\Contact\Entity\Contact
    {
        return $this->contact;
    }

    /**
     * @param \Contact\Entity\Contact $contact
     *
     * @return Document
     */
    public function setContact(\Contact\Entity\Contact $contact): Document
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return DocumentObject[]|ArrayCollection
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param DocumentObject[]|ArrayCollection $object
     *
     * @return Document
     */
    public function setObject($object): Document
    {
        $this->object = $object;

        return $this;
    }
}
