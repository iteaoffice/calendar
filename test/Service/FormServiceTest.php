<?php
/**
 * ITEA copyright message placeholder
 *
 * @category    CalendarTest
 * @package     Entity
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

namespace CalendarTest\Service;

use Calendar\Entity\Calendar;
use Calendar\Entity\Document;
use Calendar\InputFilter\CalendarFilter;
use Calendar\InputFilter\DocumentFilter;
use Calendar\Service\FormService;
use Testing\Util\AbstractServiceTest;
use Zend\Form\Form;

class FormServiceTest extends AbstractServiceTest
{
    /**
     *
     */
    public function testCanCreateService(): void
    {
        $formService = new FormService($this->serviceManager, $this->getEntityManagerMock());
        $this->assertInstanceOf(FormService::class, $formService);
    }

    public function testCanPrepareFormForCalendar(): void
    {
        $inputFilter = new CalendarFilter();
        $this->serviceManager->setService(CalendarFilter::class, $inputFilter);

        $formService = new FormService($this->serviceManager, $this->getEntityManagerMock(Calendar::class));
        $prepare = $formService->prepare(new Calendar(), []);

        $this->assertInstanceOf(Form::class, $prepare);
    }

    public function testCanPrepareFormForDocument(): void
    {
        $inputFilter = new DocumentFilter();
        $this->serviceManager->setService(DocumentFilter::class, $inputFilter);

        $formService = new FormService($this->serviceManager, $this->getEntityManagerMock(Document::class));
        $prepare = $formService->prepare(new Document(), []);

        $this->assertInstanceOf(Form::class, $prepare);
    }

}
