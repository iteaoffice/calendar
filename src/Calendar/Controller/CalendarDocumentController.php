<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Controller
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

use Calendar\Service\FormServiceAwareInterface;
use Calendar\Service\CalendarService;
use Calendar\Service\FormService;

/**
 *
 */
class CalendarDocumentController extends AbstractActionController implements
    FormServiceAwareInterface,
    ServiceLocatorAwareInterface
{

    /**
     * @var CalendarService;
     */
    protected $calendarService;
    /**
     * @var FormService
     */
    protected $formService;
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Download a document
     *
     * @return int
     */
    public function downloadAction()
    {
        set_time_limit(0);

        $document = $this->getCalendarService()->findEntityById(
            'document',
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        if (is_null($document) || sizeof($document->getObject()) === 0) {
            return $this->notFoundAction();
        }
        /**
         * Due to the BLOB issue, we treat this as an array and we need to capture the first element
         */
        $object = $document->getObject()->first()->getObject();

        $response = $this->getResponse();
        $response->setContent(stream_get_contents($object));

        $response->getHeaders()
            ->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 36000))
            ->addHeaderLine("Cache-Control: max-age=36000, must-revalidate")
            ->addHeaderLine(
                'Content-Disposition',
                'attachment; filename="' . $document->parseFilename() . '.' .
                $document->getContentType()->getExtension() . '"'
            )
            ->addHeaderLine("Pragma: public")
            ->addHeaderLine('Content-Type: ' . $document->getContentType()->getContentType())
            ->addHeaderLine('Content-Length: ' . $document->getSize());

        return $this->response;
    }

    /**
     * @return ViewModel
     */
    public function documentAction()
    {
        $document = $this->getCalendarService()->findEntityById(
            'document',
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        return new ViewModel(array('document' => $document));
    }


    /**
     * @return \Calendar\Service\FormService
     */
    public function getFormService()
    {
        return $this->formService;
    }

    /**
     * @param $formService
     *
     * @return CalendarManagerController
     */
    public function setFormService($formService)
    {
        $this->formService = $formService;

        return $this;
    }

    /**
     * Gateway to the Calendar Service
     *
     * @return CalendarService
     */
    public function getCalendarService()
    {
        return $this->getServiceLocator()->get('calendar_calendar_service');
    }

    /**
     * @param $calendarService
     *
     * @return CalendarManagerController
     */
    public function setCalendarService($calendarService)
    {
        $this->calendarService = $calendarService;

        return $this;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CalendarManagerController|void
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }
}
