<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\Acl\Assertion;

use Admin\Entity\Access;
use Calendar\Entity\Document as DocumentEntity;
use Interop\Container\ContainerInterface;
use Project\Acl\Assertion\Project;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

final class Document extends AbstractAssertion
{
    /**
     * @var Project
     */
    private $projectAssertion;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->projectAssertion = $container->get(Project::class);
    }

    public function assert(
        Acl $acl,
        RoleInterface $role = null,
        ResourceInterface $document = null,
        $privilege = null
    ): bool {
        $this->setPrivilege($privilege);
        $id = $this->getId();

        if (!$document instanceof DocumentEntity && null !== $id) {
            $document = $this->calendarService->find(DocumentEntity::class, $id);
        }

        if (null === $document) {
            return true;
        }

        switch ($this->getPrivilege()) {
            case 'document-community':
            case 'download':
                if ($this->hasPermission($document->getCalendar(), 'view')) {
                    return true;
                }

                return $this->calendarService->isPublic($document->getCalendar());
            case 'edit-community':
                if ($this->hasPermission($document->getCalendar(), 'edit')) {
                    return true;
                }

                /*
                 * The project leader also has rights to invite users
                 */
                if (null !== $document->getCalendar()->getProjectCalendar()
                    && $this->projectAssertion->assert(
                        $acl,
                        $role,
                        $document->getCalendar()->getProjectCalendar()->getProject(),
                        'edit-community'
                    )
                ) {
                    return true;
                }


                return $this->rolesHaveAccess(Access::ACCESS_OFFICE);
            case 'document-admin':
                return $this->rolesHaveAccess(Access::ACCESS_OFFICE);
        }

        return false;
    }
}
