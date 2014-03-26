<?php

namespace JoshuaJabbour\Authorizable\Tests;

use PHPUnit_Framework_TestCase;
use JoshuaJabbour\Authorizable\Rule\Rule;
use JoshuaJabbour\Authorizable\Rule\Privilege;
use JoshuaJabbour\Authorizable\Rule\Restriction;
use JoshuaJabbour\Authorizable\Rule\Collection as RuleCollection;
use JoshuaJabbour\Authorizable\Strategy\Strategy;
use JoshuaJabbour\Authorizable\Strategy\Sequential as SequentialStrategy;
use JoshuaJabbour\Authorizable\Strategy\Additive as AdditiveStrategy;
use JoshuaJabbour\Authorizable\Strategy\Subtractive as SubtractiveStrategy;

class StrategyTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->sequential = new SequentialStrategy;
        $this->additive = new AdditiveStrategy;
        $this->subtractive = new SubtractiveStrategy;
        $this->rules = new RuleCollection(array(
            new Restriction('create', 'User'),
            new Privilege('create', 'User'),
            new Privilege('read', 'User'),
            new Privilege('update', 'User'),
            new Restriction('update', 'User'),
            new Restriction('delete', 'User'),
        ));
    }

    public function testCanCheckEmptyRuleCollection()
    {
        $noRules = new RuleCollection;
        $this->assertFalse($this->sequential->check($noRules));
        $this->assertFalse($this->additive->check($noRules));
        $this->assertFalse($this->subtractive->check($noRules));
    }

    public function testCanCheckRulesUsingSequentialStrategy()
    {
        $this->assertTrue($this->sequential->check($this->rules->getRelevantRules('create', 'User')));
        $this->assertFalse($this->sequential->check($this->rules->getRelevantRules('update', 'User')));
    }

    public function testCanCheckRulesUsingAdditiveStrategy()
    {
        $this->assertTrue($this->additive->check($this->rules->getRelevantRules('create', 'User')));
        $this->assertTrue($this->additive->check($this->rules->getRelevantRules('read', 'User')));
        $this->assertTrue($this->additive->check($this->rules->getRelevantRules('update', 'User')));
        $this->assertFalse($this->additive->check($this->rules->getRelevantRules('delete', 'User')));
    }

    public function testCanCheckRulesUsingSubtractiveStrategy()
    {
        $this->assertFalse($this->subtractive->check($this->rules->getRelevantRules('create', 'User')));
        $this->assertTrue($this->subtractive->check($this->rules->getRelevantRules('read', 'User')));
        $this->assertFalse($this->subtractive->check($this->rules->getRelevantRules('update', 'User')));
        $this->assertFalse($this->subtractive->check($this->rules->getRelevantRules('delete', 'User')));
    }
}
