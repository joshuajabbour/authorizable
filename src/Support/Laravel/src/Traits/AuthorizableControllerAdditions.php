<?php

namespace JoshuaJabbour\Authorizable\Laravel\Traits;

use JoshuaJabbour\Authorizable\Manager as AuthorizableManager;
use JoshuaJabbour\Authorizable\Laravel\Exceptions\AccessDenied;
use App;

trait AuthorizableControllerAdditions
{
    protected $authorizable_manager;

    protected $authorizable_user;

    protected $is_authorized = false;

    /**
     * Declare which controller methods are authorizable.
     *
     * This allows for calling the authorize method without needing to
     * know if the method is actually authorizable (e.g. authorizing in
     * a parent class, but declaring authorizable methods in the child).
     *
     * @return array
     */
    abstract protected function getAuthorizableMethods();

    /**
     * Authorize a resource.
     *
     * @see AuthorizableManager::can()
     * @param string $action Action to test against the rules.
     * @param string|object $resource Resource to test against the rules.
     * @param ... Additional parameters to pass to the condition.
     * @return boolean
     */
    public function authorize()
    {
        if (in_array(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'], $this->getAuthorizableMethods())) {
            $args = func_get_args();

            $this->is_authorized = call_user_func_array([$this->getAuthorizableManager(), 'can'], $args);

            if (! $this->is_authorized) {
                $router = App::make('router');

                throw new AccessDenied(array(
                    'action' => $args[0],
                    'resource' => $args[1],
                    'route' => $router->getCurrentRoute(),
                    'request' => $router->getCurrentRequest(),
                ));
            }
        }
    }

    public function getAuthorizableManager()
    {
        if (is_null($this->authorizable_manager)) {
            $this->authorizable_manager = App::make('authorizable');
        }

        return $this->authorizable_manager;
    }

    public function setAuthorizableManager(AuthorizableManager $authorizable_manager)
    {
        $this->authorizable_manager = $authorizable_manager;

        return $this;
    }

    public function getAuthorizableUser()
    {
        if (is_null($this->authorizable_user)) {
            $this->authorizable_user = $this->getAuthorizableManager()->getUser();
        }

        return $this->authorizable_user;
    }

    public function setAuthorizableUser($user)
    {
        $this->getAuthorizableManager()->setPrimaryUser($user);

        $this->authorizable_user = $user;

        return $this;
    }
}
