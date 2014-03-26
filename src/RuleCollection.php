<?php

namespace JoshuaJabbour\Authorizable;

use Illuminate\Support\Collection;

class RuleCollection extends Collection
{
    /**
     * Returns all relevant rules based on an action and a resource.
     *
     * @param string|array $action Action to test against the collection.
     * @param string|object $resource Resource to test against the collection.
     * @return RuleCollection
     */
    public function getRelevantRules($action, $resource)
    {
        return $this->filter(function ($rule) use ($action, $resource) {
            return $rule->isRelevant($action, $resource);
        });
    }
}
