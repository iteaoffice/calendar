<?php

/**
 * Jield copyright message placeholder.
 *
 * @category    Admin
 *
 * @author      Johan van der Heide <info@jield.nl>
 * @copyright   Copyright (c) 2004-2015 Jield (http://jield.nl)
 */

declare(strict_types=1);

namespace Calendar\View\Helper;

use Calendar\Acl\Assertion;
use Calendar\Entity\Document;
use function sprintf;

/**
 * Class DocumentLink
 *
 * @package Calendar\View\Helper
 */
final class DocumentLink extends AbstractLink
{
    public function __invoke(Document $document, string $action = 'view', string $show = 'name'): string
    {
        $this->reset();

        if (!$this->hasAccess($document, Assertion\Document::class, $action)) {
            return '';
        }

        $this->extractLinkContentFromEntity($document, ['document']);
        $this->extractRouterParams($document, ['id']);

        if (null !== $document) {
            $this->addShowOption('name', $document->parseFileName());
        }

        $this->parseAction($action, $document);

        return $this->createLink($show);
    }

    private function parseAction(string $action, Document $document): void
    {
        $this->action = $action;

        switch ($action) {
            case 'document-community':
                $this->setRouter('community/calendar/document/document');
                $this->setText(
                    sprintf(
                        $this->translate("txt-view-calendar-document-%s"),
                        $document
                    )
                );
                break;
            case 'edit-community':
                $this->setRouter('community/calendar/document/edit');
                $this->setText(
                    sprintf(
                        $this->translate("txt-edit-calendar-document-%s"),
                        $document
                    )
                );
                break;
            case 'document-admin':
                $this->setRouter('zfcadmin/calendar/document/document');
                $this->setText(
                    sprintf(
                        $this->translate("txt-view-calendar-document-%s"),
                        $document
                    )
                );
                break;
            case 'edit':
                $this->setRouter('zfcadmin/calendar/document/edit');
                $this->setText(
                    sprintf(
                        $this->translate("txt-edit-calendar-document-%s"),
                        $document
                    )
                );
                break;
            case 'download':
                $this->addRouteParam('filename', $document->parseFileName());
                $this->setRouter('community/calendar/document/download');
                $this->setText(
                    sprintf(
                        $this->translate("txt-download-calendar-document-%s"),
                        $document
                    )
                );
                break;
        }
    }
}
