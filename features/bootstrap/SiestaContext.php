<?php

use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

require("user.php");

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

class SiestaContext extends BehatContext {

    /**
     * @Given /^I have an instance of "([^"]*)" that extends Siesta$/
     */
    public function iHaveAClass($klass)
    {
        $this->klass = new $klass([
                "id" => 0,
                "name" => "Will McKenzie",
                "email" => "will@komododigital.co.uk"
            ]);
    }

    /**
     * @When /^I query the methods$/
     */
    public function iQueryTheMethods()
    {
        $f = new ReflectionClass('User');
        $this->output = [];
        foreach ($f->getMethods() as $method) {
            $this->output[] = $method->name;
        }
    }

    /**
     * @Then /^it should have these methods:$/
     */
    public function itShouldHaveAMethod(PyStringNode $methods)
    {
        $expected = explode("\n",$methods);

        foreach ($expected as $method) {
            if (!in_array($method,$this->output))
                throw new Exception("Class does not contain method: " + $method);

        }
    }

    /**
     * @When /^I call static method "([^"]*)"$/
     */
    public function iCallStatic($method)
    {
        $this->output = User::$method();
    }

    /**
     * @When /^I call static method "([^"]*)" with arguments:$/
     */
    public function iCallStaticWithArguments($method,$arguments)
    {
        $arguments = str_replace("'","\"",$arguments);
        $arguments = json_decode($arguments,true);
        $this->output = call_user_func_array(["User",$method],$arguments);
    }

    /**
     * @When /^I call instance method "([^"]*)"$/
     */
    public function iCallInstance($method)
    {
        $this->output = $this->klass->$method();
    }

    /**
     * @When /^I call instance method "([^"]*)" with arguments:$/
     */
    public function iCallInstanceWithArguments($method,$arguments)
    {
        $arguments = str_replace("'","\"",$arguments);
        $arguments = json_decode($arguments,true);
        $this->output = call_user_func_array([$this->klass,$method],$arguments);
    }

    /**
     * @Then /^the response should be an? "([^"]*)"$/
     */
    public function theResponseShouldBeA($type)
    {
        if(!gettype($this->output) == $type)
            throw new Exception("Response is not of format: " + $type);
    }

    /**
     * @Then /^the length should be "([^"]*)"$/
     */
    public function theLengthShouldBe($length)
    {
        $length = (int)$length;
        assertEquals(count($this->output), $length);
    }

    /**
     * @Then /^the items should be instances of "([^"]*)"$/
     */
    public function itemsShouldBeInstancesOf($klass)
    {
        foreach ($this->output as $item) {
            assertEquals(get_class($item),$klass);
        }

    }

    /**
     * @Then /^the item should be an instance of "([^"]*)"$/
     */
    public function itemShouldBeInstanceOf($klass)
    {
        assertEquals(get_class($this->output),$klass);
    }

    /**
     * @Then /^the results' "([^"]*)" properties should equal:$/
     */
    public function propertiesShouldEqual($prop,PyStringNode $values)
    {
        $values = explode("\n",$values);

        for ($i = 0; $i < count($this->output); $i++) {
            assertEquals($values[$i],$this->output[$i]->$prop);
        }
    }

    /**
     * @Then /^the item's "([^"]*)" property should equal "([^"]*)"$/
     */
    public function propertyShouldEqual($prop,$value)
    {
        assertEquals($value,$this->output->$prop);
    }

    /**
     * @Then /^the item's "([^"]*)" key should equal "([^"]*)"$/
     */
    public function keyShouldEqual($prop,$value)
    {
        assertEquals($value,$this->output[$prop]);
    }

    /**
     * @When /^I set the "([^"]*)" property to "([^"]*)"$/
     */
    public function setPropertyTo($prop,$value)
    {
        $this->klass->$prop = $value;
    }
}
