<?php

namespace JoshuaJabbour\Authorizable\Rule\Collection\Strategy;

use JoshuaJabbour\Authorizable\Rule\Collection\Strategy;
use JoshuaJabbour\Authorizable\Rule\Collection as RuleCollection;

class Subtractive extends Strategy
{
    public function apply(RuleCollection $rules, array $args = array())
    {
        // Logic to check rules in an subtractive manner,
        // in which any restriction trumps all other rules.
        return $rules->map(function ($rule) use ($args) {
            return call_user_func_array([$rule, 'check'], $args);
        })->filter(function ($result) {
            return $result === false;
        })->isEmpty();
    }
}
