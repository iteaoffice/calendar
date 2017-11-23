<?php

/**
 * ITEA Office all rights reserved
 *
 * @category   Calendar
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\View\Helper;

use Calendar\Acl\Assertion\Document as CalendarDocumentAssertion;
use Calendar\Entity;

/**
 * Create a link to an project.
 *
 * @category   Calendar
 */
class DocumentLink extends LinkAbstract
{
    /**
     * @var Entity\Document
     */
    protected $document;

    /**
     * @param Entity\Document $document
     * @param string $action
     * @param string $show
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function __invoke(
        Entity\Document $document = null,
        $action = 'view',
        $show = 'text'
    ) {
        $this->setDocument($document);
        $this->setAction($action);
        $this->setShow($show);

        /**LiLik
         * Set the non-standard options needed to give an other link value
         */
        $this->setShowOptions(
            [

                'name' => $this->getDocument()->getDocument(),
            ]
        );

        /*
         * Check the access to the object
         */
        if (!$this->hasAccess(
            $this->getDocument(),
            CalendarDocumentAssertion::class,
            $this->getAction()
        )
        ) {
            return '';
        }

        $this->addRouterParam('id', $this->getDocument()->getId());

        return $this->createLink();
    }

    /**
     * @return Entity\Document
     */
    public function getDocument()
    {
        if (\is_null($this->document)) {
            $this->document = new Entity\Document();
        }

        return $this->document;
    }

    /**
     * @param Entity\Document $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }

    /**
     * Parse te action and fill the correct parameters.
     */
    public function parseAction(): void
    {
        switch ($this->getAction()) {
            case 'document-community':
                $this->setRouter('community/calendar/document/document');
                $this->setText(
                    sprintf(
                        $this->translate("txt-view-calendar-document-%s"),
                        $this->getDocument()->getDocument()
                    )
                );
                break;
            case 'edit-community':
                $this->setRouter('community/calendar/document/edit');
                $this->setText(
                    sprintf(
                        $this->translate("txt-edit-calendar-document-%s"),
                        $this->getDocument()->getDocument()
                    )
                );
                break;
            case 'document-admin':
                $this->setRouter('zfcadmin/calendar-manager/document/document');
                $this->setText(
                    sprintf(
                        $this->translate("txt-view-calendar-document-%s"),
                        $this->getDocument()->getDocument()
                    )
                );
                break;
            case 'edit':
                $this->setRouter('zfcadmin/calendar-manager/document/edit');
                $this->setText(
                    sprintf(
                        $this->translate("txt-edit-calendar-document-%s"),
                        $this->getDocument()->getDocument()
                    )
                );
                break;
            case 'download':
                $this->addRouterParam(
                    'filename',
                    $this->getDocument()->parseFileName()
                );
                $this->setRouter('community/calendar/document/download');
                $this->setText(
                    sprintf(
                        $this->translate("txt-download-calendar-document-%s"),
                        $this->getDocument()->getDocument()
                    )
                );
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf(
                        "%s is an incorrect action for %s",
                        $this->getAction(),
                        __CLASS__
                    )
                );
        }
    }
}
