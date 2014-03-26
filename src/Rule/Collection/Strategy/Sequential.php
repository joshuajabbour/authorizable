<?php

namespace JoshuaJabbour\Authorizable\Rule\Collection\Strategy;

use JoshuaJabbour\Authorizable\Rule\Collection\Strategy;
use JoshuaJabbour\Authorizable\Rule\Collection as RuleCollection;

class Sequential extends Strategy
{
    public function apply(RuleCollection $rules, array $args = array())
    {
        // Logic to check rules in a sequential manner.
        // @todo Do all the rules even need to be checked?
        return $rules->reduce(function ($result, $rule) use ($args) {
            return $result && call_user_func_array([$rule, 'check'], $args);
        }, true) || call_user_func_array([$rules->last(), 'check'], $args);
    }
}
