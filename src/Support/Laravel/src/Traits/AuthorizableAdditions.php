<?php

namespace JoshuaJabbour\Authorizable\Laravel\Traits;

use JoshuaJabbour\Authorizable\Manager as AuthorizableManager;
use App;

trait AuthorizableAdditions
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

    public function setAuthorizableManager(AuthorizableManager $authorizable_manager)
    {
        $this->authorizable_manager = $authorizable_manager;

        return $this;
    }
}
