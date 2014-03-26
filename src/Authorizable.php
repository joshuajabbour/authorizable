<?php
/**
 * Authorizable: A flexible authorization component for PHP.
 *
 * @package Authorizable
 */
namespace JoshuaJabbour\Authorizable;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use BadMethodCallException;
use Closure;

class Authorizable
{
    protected $check_sequentially = true;

    public function __construct()
    {
        $this->rules = new Collection;
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

        $allowed = false;

        $rules = $this->getRulesFor($action, $resource);

        if (! $rules->isEmpty()) {
            foreach ($rules as $rule) {
                if ($this->check_sequentially) {
                    // Logic to check rules in sequential order.
                    $allowed = $rules->reduce(function ($result, $rule) use ($args) {
                        return $result && call_user_func_array([$rule, 'check'], $args);
                    }, true) || call_user_func_array([$rules->last(), 'check'], $args);
                } else {
                    // Logic to check rules in additive manner.
                    $allowed = ! $rules->map(function ($rule) use ($args) {
                        return call_user_func_array([$rule, 'check'], $args);
                    })->filter(function ($result) {
                        return $result == true;
                    })->isEmpty();
                }
            }
        }

        return $allowed;
    }

    public function getRulesFor($action, $resource)
    {
        return $this->rules->filter(function ($rule) use ($action, $resource) {
            return $rule->getAction() == $action && $rule->getResource() == $resource;
        });
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
     * @return void
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
     * @return void
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
     * @return void
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

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $rule = $behavior ? new Privilege($action, $resource, $condition) : new Restriction($action, $resource, $condition);
                $this->rules->push($rule);
            }
        }
    }

    /**
     * Returns the current rule set.
     *
     * @return Collection
     */
    public function getRules()
    {
        return $this->rules;
    }

    public function setCheckToSequential()
    {
        $this->check_sequentially = true;
        return $this;
    }

    public function setCheckToAdditive()
    {
        $this->check_sequentially = false;
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
