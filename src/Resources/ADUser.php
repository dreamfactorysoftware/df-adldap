<?php
namespace DreamFactory\Core\ADLdap\Resources;

use DreamFactory\Core\Exceptions\NotFoundException;
use DreamFactory\Core\Resources\BaseRestResource;
use DreamFactory\Core\ADLdap\Contracts\Provider;
use DreamFactory\Core\Contracts\RequestHandlerInterface;
use DreamFactory\Core\Utility\ResourcesWrapper;
use DreamFactory\Library\Utility\ArrayUtils;

class ADUser extends BaseRestResource
{
    /**
     * Name of this resource.
     */
    const RESOURCE_NAME = 'user';

    /** @type Provider */
    protected $provider = null;

    /**
     * @param \DreamFactory\Core\Contracts\RequestHandlerInterface $parent
     */
    public function setParent(RequestHandlerInterface $parent)
    {
        parent::setParent($parent);
        $this->provider = $this->parent->getDriver();
        $this->parent->authenticateAdminUser();
    }

    /**
     * Handles all GET requests.
     *
     * @return array
     * @throws \DreamFactory\Core\Exceptions\NotFoundException
     */
    protected function handleGET()
    {
        $username = $this->resource;
        $resources = [];

        if (empty($username)) {
            $users = $this->provider->search("(&(objectCategory=person)(objectClass=user)(samaccountname=*))");

            if ($users['count'] === 0) {
                return ResourcesWrapper::cleanResources([]);
            }

            foreach ($users as $user) {
                if (is_array($user)) {
                    $adUser = new \DreamFactory\Core\ADLdap\Components\ADUser($user);
                    $resources[] = $adUser->getData();
                }
            }
        } else {
            $user =
                $this->provider->search(
                    "(&(objectCategory=person)(objectClass=user)(samaccountname=" . $username . "))"
                );
            if (!ArrayUtils::get($user, 'count')) {
                throw new NotFoundException('User not found.');
            }
            $adUser = new \DreamFactory\Core\ADLdap\Components\ADUser($user[0]);
            $resources = $adUser->getData();
        }

        return ResourcesWrapper::cleanResources($resources);
    }
}