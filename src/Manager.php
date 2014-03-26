<?php

/**
 * Authorizable: A flexible authorization component.
 *
 * @package Authorizable
 */
namespace JoshuaJabbour\Authorizable;

use JoshuaJabbour\Authorizable\Rule\Privilege;
use JoshuaJabbour\Authorizable\Rule\Restriction;
use JoshuaJabbour\Authorizable\Rule\Collection as RuleCollection;
use JoshuaJabbour\Authorizable\Rule\Collection\Strategy;
use JoshuaJabbour\Authorizable\Rule\Collection\Strategy\Sequential as SequentialStrategy;
use InvalidArgumentException;
use BadMethodCallException;
use Closure;

class Manager
{
    protected $strategy;

    public function __construct(Strategy $strategy = null)
    {
        $this->rules = new RuleCollection;
        $this->strategy = $strategy ?: new SequentialStrategy;
    }

    protected function check(RuleCollection $rules, array $args = array())
    {
        return $this->strategy->check($rules, $args);
    }

    /**
     * Determine if current user can access the given action and resource
     *
     * @return boolean
     */
    public function can()
    {
        $args = func_get_args();
        $action = array_shift($args);
        $resource = array_shift($args);

        if (is_object($resource)) {
            $resource_object = $resource;
            $resource = get_class($resource_object);
            array_unshift($args, $resource_object);
        }

        return $this->check($this->getRelevantRules($action, $resource), $args);
    }

    /**
     * Determine if current user cannot access the given action and resource
     * Returns negation of can()
     *
     * @return boolean
     */
    public function cannot()
    {
        return ! call_user_func_array([$this, 'can'], func_get_args());
    }

    public function canAny()
    {
        foreach ($actions as $action) {
            $allowed = call_user_func_array([$this, 'can'], func_get_args());
            if ($allowed) {
                return true;
            }
        }
        return false;
    }

    public function canAll()
    {
        foreach ($actions as $action) {
            $allowed = call_user_func_array([$this, 'can'], func_get_args());
            if (! $allowed) {
                return false;
            }
        }
        return true;
    }

    /**
     * Define a privilege rule for the given actions and resources.
     *
     * @param array|string $actions Actions for the rule.
     * @param array|string $resources Resources for the rule.
     * @param Closure|null $condition Optional condition for the rule.
     * @return RuleCollection
     */
    public function allow($actions, $resources, $condition = null)
    {
        return $this->addRules(true, $actions, $resources, $condition);
    }

    /**
     * Define a restriction rule for the given actions and resources.
     *
     * @param array|string $actions Actions for the rule.
     * @param array|string $resources Resources for the rule.
     * @param Closure|null $condition Optional condition for the rule.
     * @return RuleCollection
     */
    public function deny($actions, $resources, $condition = null)
    {
        return $this->addRules(false, $actions, $resources, $condition);
    }

    /**
     * Define a rule for the given actions and resources.
     *
     * @param boolean $behavior True if privilege, false if restriction.
     * @param array|string $actions Actions for the rule.
     * @param array|string $resources Resources for the rule.
     * @param Closure|null $condition Optional condition for the rule.
     * @return RuleCollection
     */
    protected function addRules($behavior, $actions, $resources, $condition = null)
    {
        if (is_string($actions)) {
            $actions = array($actions);
        }

        if (is_string($resources)) {
            $resources = array($resources);
        }

        if (! is_array($actions) || ! is_array($resources)) {
            throw new InvalidArgumentException();
        }

        if ($condition instanceof Closure) {
            $condition = Closure::bind($condition, $this);
        }

        $rules = new RuleCollection;

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $rule = $behavior ? new Privilege($action, $resource, $condition) : new Restriction($action, $resource, $condition);
                $rules->push($rule);
            }
        }

        $this->rules = $this->rules->merge($rules);

        return $rules;
    }

    /**
     * Returns all rules.
     *
     * @return RuleCollection
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Returns all relevant rules based on an action and a resource.
     *
     * @param string|array $action Action to test against the collection.
     * @param string|object $resource Resource to test against the collection.
     * @return RuleCollection
     */
    public function getRelevantRules($action, $resource)
    {
        return $this->rules->getRelevantRules($action, $resource);
    }

    public function setStrategy(Strategy $strategy)
    {
        $this->strategy = $strategy;
        return $this;
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, ['can', 'cannot', 'canAny', 'canAll'])) {
            return call_user_func_array([$this, $name], $arguments);
        }

        throw new BadMethodCallException;
    }
}
