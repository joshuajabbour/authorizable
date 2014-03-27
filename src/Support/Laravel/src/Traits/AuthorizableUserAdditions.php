<?php

namespace JoshuaJabbour\Authorizable\Laravel\Traits;

trait AuthorizableUserAdditions
{
    use AuthorizableAdditions;

    public function __call($method, $args)
    {
        if (in_array($method, ['can', 'cannot', 'canAny', 'canAll'])) {
            $authorizable = $this->getAuthorizableManager();

            if (is_callable([$authorizable, $method])) {
                // Set this user object as the temporary user for the request.
                $authorizable = $authorizable->setTemporaryUser($this);

                return call_user_func_array([$authorizable, $method], $args);
            }
        }

        parent::__call($method, $args);
    }
}
