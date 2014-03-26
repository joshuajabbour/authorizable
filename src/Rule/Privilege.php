<?php

namespace JoshuaJabbour\Authorizable\Rule;

use JoshuaJabbour\Authorizable\Rule\Rule;

class Privilege extends Rule
{
    public function check()
    {
        return $this->checkCondition(func_get_args());
    }
}
