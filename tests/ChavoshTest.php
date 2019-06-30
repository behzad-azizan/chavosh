<?php


namespace Test;


use Azizan\Chavosh\Chavosh;
use PHPUnit\Framework\TestCase;

class ChavoshTest extends TestCase
{
    public function testCaptcha()
    {
        $captcha = new Chavosh();
        $captcha
            ->build()
            ->saveAs('out.jpg')
        ;

        $this->assertTrue(file_exists(__DIR__.'/../out.jpg'));
    }
}