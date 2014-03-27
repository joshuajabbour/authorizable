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
    /**
     * @var Strategy The comparison strategy to use for rule evaluation.
     */
    protected $strategy;

    /**
     * @var RuleCollection Collection of rules to use for evaluation.
     */
    protected $rules;

    /**
     * Constructor
     *
     * @param Strategy $strategy Comparison strategy.
     */
    public function __construct(Strategy $strategy = null)
    {
        $this->setStrategy($strategy);
        $this->rules = new RuleCollection;
    }

    /**
     * Evaluate relevant rules and return result.
     *
     * @param string $action Action to test against the rules.
     * @param string|object $resource Resource to test against the rules.
     * @param ... Additional parameters to pass to the condition.
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
     * Evaluate relevant rules and return opposite of result.
     *
     * @see Authorizable\Manager::can()
     * @return boolean
     */
    public function cannot()
    {
        return ! call_user_func_array([$this, 'can'], func_get_args());
    }

    /**
     * Evaluate relevant rules and return result if any rules match.
     *
     * @see Authorizable\Manager::can()
     * @return boolean
     */
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

    /**
     * Evaluate relevant rules and return result if all rules match.
     *
     * @see Authorizable\Manager::can()
     * @return boolean
     */
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
     * Evaluate relevant rules and return result.
     *
     * @param RuleCollection $rules The rules to evaluate.
     * @param array $args Parameters to pass to the condition.
     * @return boolean
     */
    protected function check(RuleCollection $rules, array $args = array())
    {
        return $this->strategy->check($rules, $args);
    }

    /**
     * Define a privilege for the given actions and resources.
     *
     * @see Authorizable\Manager::addRules()
     * @return RuleCollection
     */
    public function allow($actions, $resources, $condition = null)
    {
        return $this->addRules(true, $actions, $resources, $condition);
    }

    /**
     * Define a restriction for the given actions and resources.
     *
     * @see Authorizable\Manager::addRules()
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
     * Return all rules.
     *
     * @return RuleCollection
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Return all relevant rules based on an action and a resource.
     *
     * @param string|array $action Action to test against the collection.
     * @param string|object $resource Resource to test against the collection.
     * @return RuleCollection
     */
    public function getRelevantRules($action, $resource)
    {
        return $this->rules->getRelevantRules($action, $resource);
    }

    /**
     * Set the comparison strategy to use for rule evaluation.
     *
     * @param Strategy $strategy Comparison strategy.
     * @return Authorizable\Manager
     */
    public function setStrategy(Strategy $strategy = null)
    {
        $this->strategy = $strategy ?: new SequentialStrategy;
        return $this;
    }

    // public function __call($name, $arguments)
    // {
    //     if (in_array($name, ['can', 'cannot', 'canAny', 'canAll'])) {
    //         return call_user_func_array([$this, $name], $arguments);
    //     }

    //     throw new BadMethodCallException;
    // }
}
