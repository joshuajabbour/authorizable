<?php

namespace JoshuaJabbour\Authorizable\Rule\Collection;

use JoshuaJabbour\Authorizable\Rule\Collection as RuleCollection;

abstract class Strategy
{
    abstract public function apply(RuleCollection $rules, array $args = array());

    public function check(RuleCollection $rules, array $args = array())
    {
        if ($rules->isEmpty()) {
            return false;
        }

        return $this->apply($rules, $args);
    }
}
