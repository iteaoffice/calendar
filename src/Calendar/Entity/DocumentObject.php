<?php
/**
 * ITEA copyright message placeholder.
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Calendar\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;

/**
 * CalendarDocumentObject.
 *
 * @ORM\Table(name="calendar_document_object")
 * @ORM\Entity
 */
class DocumentObject extends EntityAbstract
{
    /**
     * @ORM\Column(name="object_id", length=10, type="integer", nullable=false)
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
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="document_id", referencedColumnName="document_id", nullable=false)
     * })
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
     * Set input filter.
     *
     * @param InputFilterInterface $inputFilter
     *
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
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

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
