<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Service
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Service;

interface FormServiceAwareInterface
{
    /**
     * Get formService.
     *
     * @return FormService.
     */
    public function getFormService();

    /**
     * Set formService.
     *
     * @param FormService the value to set.
     *
     * @return \Calendar\Controller\CalendarAbstractController
     */
    public function setFormService($formService);
}
