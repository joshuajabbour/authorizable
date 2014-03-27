<?php

namespace JoshuaJabbour\Authorizable\Laravel\Exceptions;

use App;
use Exception;

class AccessDenied extends Exception
{
    protected $action;
    protected $resource;
    protected $resource_type;
    protected $route;
    protected $request;
    protected $default_message = 'You are not authorized to access this page.';

    public function __construct(array $data = array(), $message = '', $code = 0, Exception $previous = null)
    {
        $this->action = array_get($data, 'action', null);
        $this->setResource(array_get($data, 'resource', null), array_get($data, 'resource_type', null));
        $this->route = array_get($data, 'route', null);
        $this->request = array_get($data, 'request', null);

        // Try to set the default message. If the app doesn't have a translation
        // service provider, this falls back to the default message as set above.
        try {
            $translator = App::make('translator');
            // Get the default message from the config.
            $default_message = App::make('config')->get('authorizable::messages.access_denied.default', 'messages.access_denied.default');
            // See if the specified message is a language key. If not, assume it's a raw message.
            if ($translator->has('authorizable::' . $default_message)) {
                // Translate the specified message.
                $default_message = $translator->get('authorizable::' . $default_message, ['action' => strtolower($this->action), 'resource' => strtolower($this->resource_type)]);
            }
            $this->setDefaultMessage($default_message);
        } catch (Exception $exception) {
            // Do nothing, as the default message is already set.
        }

        $this->setMessage($message);

        parent::__construct($this->message, $code, $previous);
    }

    public function __toString()
    {
        return $this->message ?: $this->default_message;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getResourceType()
    {
        return $this->resource_type;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setResource($resource = null, $resource_type = null)
    {
        if (is_string($resource)) {
            $this->resource = $resource;
            $this->resource_type = $resource_type ?: last(explode('\\', $resource));
        } elseif (is_object($resource) || is_array($resource)) {
            $this->resource = $resource;
            $this->resource_type = $resource_type ?: class_basename($resource);
        }
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setMessage($value = '')
    {
        $this->message = $value ?: $this->default_message;
    }

    public function getDefaultMessage()
    {
        return $this->default_message;
    }

    public function setDefaultMessage($value = '')
    {
        $this->default_message = $value;
        $this->message = $this->message ?: $this->default_message;
    }
}
