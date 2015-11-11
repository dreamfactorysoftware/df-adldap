<?php
namespace DreamFactory\Core\ADLdap\Resources;

use DreamFactory\Library\Utility\ArrayUtils;
use DreamFactory\Core\Exceptions\NotFoundException;
use DreamFactory\Core\ADLdap\Services\LDAP;
use DreamFactory\Core\Resources\BaseRestResource;
use DreamFactory\Core\Contracts\RequestHandlerInterface;
use DreamFactory\Core\Utility\ResourcesWrapper;

class ADGroup extends BaseRestResource
{
    /**
     * Name of this resource.
     */
    const RESOURCE_NAME = 'group';

    /**
     * @param \DreamFactory\Core\Contracts\RequestHandlerInterface $parent
     */
    public function setParent(RequestHandlerInterface $parent)
    {
        parent::setParent($parent);
        $this->provider = $this->parent->getDriver();
        $this->parent->authenticateAdminUser();
    }

    protected function handleGET()
    {
        $groupName = $this->resource;
        $resources = [];

        if (empty($groupName)) {
            $groups = $this->provider->search("(&(objectCategory=group)(objectClass=group)(cn=*))");

            if ($groups['count'] === 0) {
                return ResourcesWrapper::cleanResources([]);
            }

            foreach ($groups as $group) {
                if (is_array($group)) {
                    $resources[] = LDAP::cleanData($group);
                }
            }
        } else {
            $group = $this->provider->search(
                "(&(objectCategory=group)(objectClass=group)(cn=" . $groupName . "))"
            );
            if (!ArrayUtils::get($group, 'count')) {
                throw new NotFoundException('Group not found.');
            }
            $group = LDAP::cleanData($group[0]);
            $resources = $group;
        }

        return ResourcesWrapper::cleanResources($resources);
    }
}