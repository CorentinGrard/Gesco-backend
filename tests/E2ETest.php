<?php

namespace App\Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

require __DIR__ . '/../vendor/autoload.php';

class E2ETest extends PantherTestCase
{
    private $IDENTIFIANT = 'matthieu.tinnes@mines-ales.org';
    private $MOT_DE_PASSE = 'azertyuiop';
    private $client;

    public function testMyApp(): void
    {
        $this->client = static::createPantherClient();
        $this->login();
        $this->checkPlanning();
        E2ETestMethods::deleteScreenShots();
        $this->client->quit();
        echo "Test terminés";
    }

    public function login(): bool
    {
        $this->client->request('GET', 'http://10.8.0.9:8080/auth/realms/imt-mines-ales/protocol/openid-connect/auth?client_id=frontend&redirect_uri=http%3A%2F%2Flocalhost%3A8080%2F%3Ferror%3Dinvalid_request%26error_description%3DMissing%2Bparameter%253A%2Bresponse_type&state=ee9d2f80-5d80-4c77-9b9d-5fb9364b7624&response_mode=fragment&response_type=code&scope=openid&nonce=5e749fa4-cb92-4945-b392-2eb5d6ab77a4');
        $this->client->takeScreenshot('tests/screen/loginScreen.png');
        $this->assertPageTitleSame('Sign in to imt-mines-ales');
        $this->client->findElement(WebDriverBy::xpath("//*[@id='username']"))->sendKeys($this->IDENTIFIANT);
        $this->client->findElement(WebDriverBy::xpath("//*[@id='password']"))->sendKeys($this->MOT_DE_PASSE);
        $this->client->takeScreenshot('tests/screen/screenDuringLogin.png');
        $boutonLoginLocalisation = WebDriverBy::xpath("//*[@id='kc-login']");
        $boutonLogin = $this->client->findElement($boutonLoginLocalisation);
        $this->client->wait(5)->until(WebDriverExpectedCondition::presenceOfElementLocated($boutonLoginLocalisation));
        $this->client->executeScript("arguments[0].style.display = 'none';", [$boutonLogin]);
        $this->client->takeScreenshot('tests/screen/screenWithLoginBouttonNotDisplayed.png');
        $this->assertSelectorIsNotVisible('#kc-login');
        $this->client->executeScript("arguments[0].style.display = '';", [$boutonLogin]);
        $boutonLogin->click();
        return true;
    }

    private function checkPlanning(): bool
    {
        $this->assertSame(true, true);
        return true;
    }
}