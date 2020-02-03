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
use General\ValueObject\Link\Link;
use General\View\Helper\AbstractLink;

/**
 * Class DocumentLink
 *
 * @package Calendar\View\Helper
 */
final class DocumentLink extends AbstractLink
{
    public function __invoke(
        Document $document,
        string $action = 'view',
        string $show = 'name'
    ): string {
        if (!$this->hasAccess($document, Assertion\Document::class, $action)) {
            return '';
        }

        $routeParams = [];
        $showOptions = [];

        $routeParams['id'] = $document->getId();
        $routeParams['filename'] = $document->parseFileName();

        $showOptions['name'] = $document->getDocument();


        switch ($action) {
            case 'document-community':
                $linkParams = [
                    'icon' => 'far fa-file',
                    'route' => 'community/calendar/document/document',
                    'text' => $showOptions[$show] ?? $document->getDocument()
                ];

                break;
            case 'edit-community':
                $linkParams = [
                    'icon' => 'far fa-edit',
                    'route' => 'community/calendar/document/edit',
                    'text' => $showOptions[$show] ?? $this->translator->translate('txt-edit-document')
                ];

                break;
            case 'document-admin':
                $linkParams = [
                    'icon' => 'far fa-file',
                    'route' => 'zfcadmin/calendar/document/document',
                    'text' => $showOptions[$show] ?? $document->getDocument()
                ];

                break;
            case 'edit':
                $linkParams = [
                    'icon' => 'far fa-edit',
                    'route' => 'zfcadmin/calendar/document/edit',
                    'text' => $showOptions[$show] ?? $this->translator->translate('txt-edit-document')
                ];

                break;
            case 'download':
                $linkParams = [
                    'icon' => 'fas fa-download',
                    'route' => 'community/calendar/document/download',
                    'text' => $showOptions[$show] ?? $this->translator->translate('txt-download-document')
                ];
                break;
        }


        $linkParams['action'] = $action;
        $linkParams['show'] = $show;
        $linkParams['routeParams'] = $routeParams;

        return $this->parse(Link::fromArray($linkParams));
    }
}
