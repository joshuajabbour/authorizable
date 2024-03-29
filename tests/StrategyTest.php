<?php

namespace JoshuaJabbour\Authorizable\Tests;

use JoshuaJabbour\Authorizable\Rule\Privilege;
use JoshuaJabbour\Authorizable\Rule\Restriction;
use JoshuaJabbour\Authorizable\Rule\Collection as RuleCollection;
use JoshuaJabbour\Authorizable\Rule\Collection\Strategy\Sequential as SequentialStrategy;
use JoshuaJabbour\Authorizable\Rule\Collection\Strategy\Additive as AdditiveStrategy;
use JoshuaJabbour\Authorizable\Rule\Collection\Strategy\Subtractive as SubtractiveStrategy;
use PHPUnit_Framework_TestCase;

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
