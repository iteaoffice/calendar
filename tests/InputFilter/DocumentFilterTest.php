<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Contact
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

namespace ContactTest\Service;

use Calendar\InputFilter\DocumentFilter;
use Testing\Util\AbstractInputFilterTest;

/**
 * Class ContactFilterTest
 *
 * @package ContactTest\Service
 */
class DocumentFilterTest extends AbstractInputFilterTest
{
    public function testCanCreateDocumentFilter(): void
    {
        $documentFilter = new DocumentFilter();

        $this->assertNotNull($documentFilter->get('calendar_entity_document'));
        $this->assertNotNull($documentFilter->get('calendar_entity_document')->get('document'));
        $this->assertNotNull($documentFilter->get('calendar_entity_document')->get('contact'));
        $this->assertNotNull($documentFilter->get('calendar_entity_document')->get('file'));
    }
}