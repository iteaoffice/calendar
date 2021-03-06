<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Calendar\Controller;

use Calendar\Entity\Document;
use Calendar\Entity\DocumentObject;
use Calendar\Form\CreateCalendarDocument;
use Calendar\Service\CalendarService;
use Doctrine\ORM\EntityManager;
use General\Service\GeneralService;
use Laminas\Http\Response;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Validator\File\FilesSize;
use Laminas\Validator\File\MimeType;
use Laminas\View\Model\ViewModel;

/**
 * @method FlashMessenger flashMessenger()
 */
final class DocumentController extends AbstractActionController
{
    private CalendarService $calendarService;
    private GeneralService $generalService;
    private EntityManager $entityManager;
    private TranslatorInterface $translator;

    public function __construct(
        CalendarService $calendarService,
        GeneralService $generalService,
        EntityManager $entityManager,
        TranslatorInterface $translator
    ) {
        $this->calendarService = $calendarService;
        $this->generalService = $generalService;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    public function downloadAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /**
         * @var Document $document
         */
        $document = $this->calendarService->find(Document::class, (int)$this->params('id'));

        if (null === $document || \count($document->getObject()) === 0) {
            return $response->setStatusCode(Response::STATUS_CODE_404);
        }

        /*
         * Due to the BLOB issue, we treat this as an array and we need to capture the first element
         */
        $object = $document->getObject()->first()->getObject();

        $response->setContent(stream_get_contents($object));
        $response->getHeaders()
            ->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 36000))
            ->addHeaderLine('Cache-Control: max-age=36000, must-revalidate')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $document->parseFileName() . '"')
            ->addHeaderLine('Pragma: public')->addHeaderLine(
                'Content-Type: ' . $document->getContentType()
                    ->getContentType()
            )->addHeaderLine('Content-Length: ' . $document->getSize());

        return $response;
    }

    public function documentAction(): ViewModel
    {
        $document = $this->calendarService->find(Document::class, (int)$this->params('id'));

        if (null === $document) {
            return $this->notFoundAction();
        }

        return new ViewModel(['document' => $document]);
    }

    public function editAction()
    {
        /** @var Document $document */
        $document = $this->calendarService->find(Document::class, (int)$this->params('id'));
        if (null === $document) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );
        $form = new CreateCalendarDocument($this->entityManager);
        $form->bind($document);
        $form->getInputFilter()->get('file')->setRequired(false);
        $form->setData($data);
        if ($this->getRequest()->isPost() && $form->isValid()) {
            /*
             * @var Document
             */
            $document = $form->getData();
            /*
             * Remove the file if delete is pressed
             */
            if (isset($data['delete'])) {
                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate('txt-calendar-document-%s-successfully-removed'),
                        $document->parseFileName()
                    )
                );
                $this->calendarService->delete($document);

                return $this->redirect()
                    ->toRoute(
                        'community/calendar/calendar',
                        ['id' => $document->getCalendar()->getId()],
                        ['fragment' => 'documents']
                    );
            }
            /*
             * Handle when
             */
            if (! isset($data['cancel'])) {
                $file = $form->get('file')->getValue();
                if (! empty($file['name']) && $file['error'] === 0) {
                    /** If no name is given, take the name of the file */
                    if (empty($data['document'])) {
                        $document->setDocument($file['name']);
                    }

                    /*
                     * Update the document
                     */
                    $fileSizeValidator = new FilesSize(PHP_INT_MAX);
                    $fileSizeValidator->isValid($file);
                    $document->setSize($fileSizeValidator->size);

                    $fileTypeValidator = new MimeType();
                    $fileTypeValidator->isValid($file);
                    $document->setContentType(
                        $this->generalService->findContentTypeByContentTypeName($fileTypeValidator->type)
                    );

                    /**
                     * Update the object
                     *
                     * @var DocumentObject $documentObject
                     */
                    $documentObject = $document->getObject()->first();
                    $documentObject->setObject(file_get_contents($file['tmp_name']));
                    $this->calendarService->save($documentObject);
                }
                $this->calendarService->save($document);
                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate('txt-calendar-document-%s-successfully-updated'),
                        $document->parseFileName()
                    )
                );
            }

            return $this->redirect()->toRoute('community/calendar/document/document', ['id' => $document->getId()]);
        }

        return new ViewModel(
            [
                'document' => $document,
                'form' => $form,
            ]
        );
    }
}
