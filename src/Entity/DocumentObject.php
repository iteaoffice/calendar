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

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="calendar_document_object")
 * @ORM\Entity
 */
class DocumentObject extends AbstractEntity
{
    /**
     * @ORM\Column(name="object_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
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
     * @var Document
     */
    private $document;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): DocumentObject
    {
        $this->id = $id;
        return $this;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object): DocumentObject
    {
        $this->object = $object;
        return $this;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): DocumentObject
    {
        $this->document = $document;
        return $this;
    }
}
