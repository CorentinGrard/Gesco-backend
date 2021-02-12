<?php

namespace App\Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Symfony\Component\Panther\Client;

class E2ETestMethods
{
    public static function deleteScreenShots()
    {
        $folder_path = __DIR__ . "/screen";
        $files = glob($folder_path . '/*');
        foreach ($files as $file) {
            if (is_file($file))
                unlink($file);
        }
    }
}

