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

use Zend\View\Model\ViewModel;
use Zend\Validator\File\FilesSize;

use Calendar\Form\CreateCalendarDocument;

/**
 *
 */
class CalendarDocumentController extends CalendarAbstractController
{
    /**
     * Download a document
     *
     * @return int
     */
    public function downloadAction()
    {
        set_time_limit(0);

        $document = $this->getCalendarService()->findEntityById(
            'Document',
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
     * @return ViewModel
     */
    public function editAction()
    {
        $document = $this->getCalendarService()->findEntityById(
            'document',
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        if (is_null($document)) {
            return $this->notFoundAction();
        }

        $entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );

        $form = new CreateCalendarDocument($entityManager);
        $form->bind($document);
        $form->getInputFilter()->get('file')->setRequired(false);
        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {

            $document = $form->getData();
            /**
             * Remove the file if delete is pressed
             */
            if (isset($data['delete'])) {

                $this->flashMessenger()->setNamespace('success')->addMessage(
                    sprintf(_("txt-calendar-document-%s-successfully-removed"), $document->parseFileName())
                );

                $this->getDocumentService()->removeEntity($document);

                return $this->redirect()->toRoute('zfcadmin/calendar-manager/calendar',
                    array('id' => $document->getCalendar()->getId())
                );
            }

            /**
             * Handle when
             */
            if (!isset($data['cancel'])) {
                $file = $form->get('file')->getValue();

                if (!empty($file['name']) && $file['error'] === 0) {

                    /**
                     * Update the document
                     */
                    $fileSizeValidator = new FilesSize(PHP_INT_MAX);
                    $fileSizeValidator->isValid($file);
                    $document->setSize($fileSizeValidator->size);
                    $document->setContentType($this->getGeneralService()->findContentTypeByContentTypeName($file['type']));

                    /**
                     * Update the object
                     */
                    $documentObject = $document->getObject()->first();
                    $documentObject->setObject(file_get_contents($file['tmp_name']));
                    $this->getCalendarService()->updateEntity($documentObject);
                }

                $this->getCalendarService()->updateEntity($document);

                $this->flashMessenger()->setNamespace('success')->addMessage(
                    sprintf(_("txt-calendar-document-%s-successfully-updated"), $document->parseFileName())
                );
            }

            $this->redirect()->toRoute('zfcadmin/calendar-manager/document/document',
                array('id' => $document->getId())
            );
        } else {
            var_dump($form->getInputFilter()->getMessages());
        }

        return new ViewModel(array(
            'document' => $document,
            'form'     => $form
        ));
    }
}
