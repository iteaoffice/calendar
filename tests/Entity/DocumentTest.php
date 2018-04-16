<?php
/**
 * ITEA copyright message placeholder
 *
 * @category    CalendarTest
 * @package     Entity
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

namespace CalendarTest\Entity;

use Calendar\Entity\Document;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Testing\Util\AbstractServiceTest;

class DocumentTest extends AbstractServiceTest
{
    /**
     *
     */
    public function testCanCreateEntity(): void
    {
        $document = new Document();
        $this->assertInstanceOf(Document::class, $document);
    }

    public function testCanHydrateEntity(): void
    {
        $document = new Document();

        $hydrator = new DoctrineObject($this->getEntityManagerMock());
        $this->assertArrayHasKey('id', $hydrator->extract($document));
    }
}
