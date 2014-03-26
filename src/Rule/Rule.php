<?php

namespace JoshuaJabbour\Authorizable\Rule;

use Closure;

abstract class Rule
{
    /**
     * @var string Action for the rule.
     */
    protected $action;

    /**
     * @var string Resource for the rule.
     */
    protected $resource;

    /**
     * @var Closure Optional condition for the rule.
     */
    protected $condition;

    /**
     * @constant string Wildcard to match any action or resource.
     */
    const WILDCARD = 'all';

    /**
     * Define a rule for the given action and resource.
     *
     * @param string $action Action for the rule.
     * @param string $resource Resource for the rule.
     * @param Closure|null $condition Optional condition for the rule.
     */
    public function __construct($action, $resource, $condition = null)
    {
        $this->action = $action;
        $this->resource = $resource;
        if ($condition instanceof Closure) {
            $this->condition = $condition;
        }
    }

    /**
     * Check the condition for the rule.
     *
     * @return boolean
     */
    abstract public function check();

    /**
     * Determine if the rule is relevant based on an action and a resource.
     *
     * @param string|array $action Action to test against the rule.
     * @param string|object $resource Resource to test against the rule.
     * @return boolean
     */
    public function isRelevant($action, $resource)
    {
        return $this->matchesAction($action) && $this->matchesResource($resource);
    }

    /**
     * Determine if the rule matches an action.
     *
     * @param string|array $action Action to test against the rule.
     * @return boolean
     */
    public function matchesAction($action)
    {
        return in_array($this->action, (array) $action);
    }

    /**
     * Determine if the rule matches a resource.
     *
     * @param string|object $resource Resource to test against the rule.
     * @return boolean
     */
    public function matchesResource($resource)
    {
        if (is_string($this->resource) && $this->resource == static::WILDCARD) {
            return true;
        } elseif (gettype($this->resource) == gettype($resource)) {
            return $this->resource == $resource;
        } elseif (is_object($this->resource) && is_string($resource)) {
            return get_class($this->resource) === $resource;
        } elseif (is_string($this->resource) && is_object($resource)) {
            return $this->resource === get_class($resource);
        } else {
            return false;
        }
    }

    /**
     * Return the action for the rule.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Return the resource for the rule.
     *
     * @return string|object
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Return the condition for the rule.
     *
     * @return Closure|null
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Invoke the condition for the rule.
     *
     * @param array $args
     * @return boolean|null
     */
    protected function checkCondition(array $args = array())
    {
        return is_callable($this->condition) ? call_user_func_array($this->condition, $args) : true;
    }

    public function __invoke()
    {
        return call_user_func_array([$this, 'check'], func_get_args());
    }
}
