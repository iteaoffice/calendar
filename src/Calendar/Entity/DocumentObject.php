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
 * CalendarDocumentObject
 *
 * @ORM\Table(name="calendar_document_object")
 * @ORM\Entity
 */
class DocumentObject
{
    /**
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="object", type="blob", nullable=true)
     * @var resource
     */
    private $object;
    /**
     * @ORM\ManyToOne(targetEntity="Calendar\Entity\Document", cascade="persist", inversedBy="object")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="document_id", referencedColumnName="document_id", nullable=false)
     * })
     * @var \Calendar\Entity\Document
     */
    private $document;

    /**
     * @param \Calendar\Entity\Document $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }

    /**
     * @return \Calendar\Entity\Document
     */
    public function getDocument()
    {
        return $this->document;
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
     * @param resource $object
     */
    public function setObject($object)
    {
        $this->object = $object;
    }

    /**
     * @return resource
     */
    public function getObject()
    {
        return $this->object;
    }
}
