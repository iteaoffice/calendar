<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Calendar\Acl\Assertion;

use Admin\Entity\Access;
use Calendar\Entity\Contact as ContactEntity;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;

final class Contact extends AbstractAssertion
{
    public function assert(
        Acl $acl,
        RoleInterface $role = null,
        ResourceInterface $contact = null,
        $privilege = null
    ): bool {
        $this->setPrivilege($privilege);
        $id = $this->getId();

        if (! $contact instanceof ContactEntity && null !== $id) {
            $contact = $this->calendarService->find(ContactEntity::class, $id);
        }

        if (null === $contact) {
            return true;
        }

        if ($this->calendarService->calendarHasContact($contact->getCalendar(), $this->contact)) {
            return true;
        }

        return $this->rolesHaveAccess(Access::ACCESS_OFFICE);
    }
}
