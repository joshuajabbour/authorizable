<?php

namespace JoshuaJabbour\Authorizable;

class Restriction extends Rule
{
    public function check()
    {
        return ! $this->checkCondition(func_get_args());
    }
}
