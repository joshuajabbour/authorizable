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
use ErrorException;
use Closure;

class Manager
{
    /**
     * @var mixed Primary user for the application.
     */
    protected $primaryUser;

    /**
     * @var mixed Temporary user to use for the rule evaluation request.
     *
     * This user only remains active for a single evaluation request.
     */
    protected $temporaryUser;

    /**
     * @var Strategy Comparison strategy to use for rule evaluation.
     */
    protected $strategy;

    /**
     * @var RuleCollection Collection of rules to use for evaluation.
     */
    protected $rules;

    /**
     * Constructor
     *
     * @param mixed $user Primary user for the application.
     * @param Strategy $strategy Comparison strategy.
     */
    public function __construct($user = null, Strategy $strategy = null)
    {
        $this->setPrimaryUser($user);
        $this->setStrategy($strategy);
        $this->rules = new RuleCollection;
    }

    /**
     * Evaluate relevant rules and return result.
     *
     * @see Authorizable\Manager::evaluate()
     * @return boolean
     */
    public function can($action, $resource)
    {
        $authorized = call_user_func_array([$this, 'evaluate'], func_get_args());

        $this->temporaryUser = null;

        return $authorized;
    }

    /**
     * Evaluate relevant rules and return opposite of result.
     *
     * @see Authorizable\Manager::evaluate()
     * @return boolean
     */
    public function cannot($action, $resource)
    {
        $authorized = ! call_user_func_array([$this, 'evaluate'], func_get_args());

        $this->temporaryUser = null;

        return $authorized;
    }

    /**
     * Evaluate relevant rules and return result if any rules match.
     *
     * @see Authorizable\Manager::evaluate()
     * @return boolean
     */
    public function canAny(array $actions, $resource)
    {
        $args = func_get_args();

        foreach ($actions as $action) {
            $args[0] = $action;

            if ($allowed = call_user_func_array([$this, 'evaluate'], $args)) {
                $this->temporaryUser = null;

                return true;
            }
        }

        $this->temporaryUser = null;

        return false;
    }

    /**
     * Evaluate relevant rules and return result if all rules match.
     *
     * @see Authorizable\Manager::evaluate()
     * @return boolean
     */
    public function canAll(array $actions, $resource)
    {
        $args = func_get_args();

        foreach ($actions as $action) {
            $args[0] = $action;

            if (! $allowed = call_user_func_array([$this, 'evaluate'], $args)) {
                $this->temporaryUser = null;

                return false;
            }
        }

        $this->temporaryUser = null;

        return true;
    }

    /**
     * Evaluate relevant rules and return result.
     *
     * @param string $action Action to test against the rules.
     * @param string|object $resource Resource to test against the rules.
     * @param mixed ... Additional parameters to pass to the condition.
     * @return boolean
     */
    protected function evaluate($action, $resource)
    {
        if (! is_string($action)) {
            throw new InvalidArgumentException('Action must be a string; ' . gettype($action) . ' was provided.');
        }

        if (! (is_string($resource) || is_object($resource))) {
            throw new InvalidArgumentException('Resource must be a string or an object; ' . gettype($resource) . ' was provided.');
        }

        // Pass all args except action to the rule condition check.
        $args = array_slice(func_get_args(), 1);

        if (is_object($resource)) {
            $resource = get_class($args[0]);
        }

        return $this->check($this->getRelevantRules($action, $resource), $args);
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
     * Return the active user.
     *
     * If a temporary user was set for the request, it will be returned.
     * Otherwise, the primary user for the application will be returned.
     *
     * @see Authorizable\Manager::setPrimaryUser()
     * @see Authorizable\Manager::setTemporaryUser()
     * @return mixed|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Return the primary user for the application.
     *
     * @return mixed|null
     */
    public function getPrimaryUser()
    {
        return $this->primaryUser;
    }

    /**
     * Set the primary user for the application.
     *
     * @param mixed $user User to set.
     * @return Authorizable\Manager
     */
    public function setPrimaryUser($user)
    {
        $this->primaryUser = $user;
        return $this;
    }

    /**
     * Return the temporary user for the request.
     *
     * @return mixed|null
     */
    public function getTemporaryUser()
    {
        return $this->temporaryUser;
    }

    /**
     * Set a temporary user for the request.
     *
     * @param mixed $user User to set.
     * @return Authorizable\Manager
     */
    public function setTemporaryUser($user)
    {
        $this->temporaryUser = $user;
        return $this;
    }

    /**
     * Set a temporary user for the request.
     *
     * Alias for Authorizable\Manager::setTemporaryUser().
     *
     * @param mixed $user User to set.
     * @return Authorizable\Manager
     */
    public function user($user)
    {
        return $this->setTemporaryUser($user);
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

    public function __get($property)
    {
        if ($property == 'user') {
            return $this->temporaryUser ?: $this->primaryUser;
        }

        throw new ErrorException("Property {$property} does not exist.");

    }
}
