<?php

namespace JoshuaJabbour\Authorizable;

class Privilege extends Rule
{
    public function check()
    {
        return $this->checkCondition(func_get_args());
    }
}
