<?php

namespace App\Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use http\Client;
use Symfony\Component\Panther\PantherTestCase;

class ControllerTest extends PantherTestCase
{
    public function testSomething()
    {
        $optionNavigateur = [];
        if (false === true) {
            array_push($optionNavigateur, '--headless');
            array_push($optionNavigateur, '--disable-gpu');
            array_push($optionNavigateur, 'window-size=1366,300');
        }
        $options = new ChromeOptions();
        $options->addArguments(
            $optionNavigateur
        );

        $caps = DesiredCapabilities::chrome();
        $caps->setCapability(ChromeOptions::CAPABILITY, $options);
        $client = \Symfony\Component\Panther\Client::createSeleniumClient("http://localhost:4444/wd/hub", $caps);
        $client->request("GET", "http://localhost:8080/");
        $this->assertSame(true, true);
        $client->quit();
    }
}
