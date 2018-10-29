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

use Doctrine\ORM\Mapping as ORM;

/**
 * CalendarDocumentObject.
 *
 * @ORM\Table(name="calendar_document_object")
 * @ORM\Entity
 */
class DocumentObject extends AbstractEntity
{
    /**
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="object", type="blob", nullable=true)
     *
     * @var resource
     */
    private $object;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\Document", cascade="persist", inversedBy="object")
     * @ORM\JoinColumn(name="document_id", referencedColumnName="document_id", nullable=false)
     *
     * @var \Calendar\Entity\Document
     */
    private $document;

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
     * @return DocumentObject
     */
    public function setId(int $id): DocumentObject
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return resource
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param string $object
     *
     * @return DocumentObject
     */
    public function setObject($object): DocumentObject
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return Document
     */
    public function getDocument(): ?Document
    {
        return $this->document;
    }

    /**
     * @param Document $document
     *
     * @return DocumentObject
     */
    public function setDocument(Document $document): DocumentObject
    {
        $this->document = $document;

        return $this;
    }
}
