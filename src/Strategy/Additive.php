<?php

namespace JoshuaJabbour\Authorizable\Strategy;

use JoshuaJabbour\Authorizable\Strategy\Strategy;
use JoshuaJabbour\Authorizable\Rule\Collection as RuleCollection;

class Additive extends Strategy
{
    public function apply(RuleCollection $rules, array $args = array())
    {
        // Logic to check rules in an additive manner,
        // in which any privilege trumps all other rules.
        return ! $rules->map(function ($rule) use ($args) {
            return call_user_func_array([$rule, 'check'], $args);
        })->filter(function ($result) {
            return $result === true;
        })->isEmpty();
    }
}
