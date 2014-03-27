<?php

namespace JoshuaJabbour\Authorizable\Laravel\Traits;

use JoshuaJabbour\Authorizable\Manager as AuthorizableManager;
use App;

trait AuthorizableUserAdditions
{
    /**
     * The current authorizable manager instance.
     *
     * @var Authorizable\Manager
     */
    protected $authorizable_manager;

    public function getAuthorizableManager()
    {
        if (is_null($this->authorizable_manager)) {
            $this->setAuthorizableManager(App::make('authorizable'));
        }

        return $this->authorizable_manager;
    }

    public function setAuthorizableManager(AuthorizableManager $authorizable)
    {
        $this->authorizable_manager = $authorizable;
        return $this;
    }

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
