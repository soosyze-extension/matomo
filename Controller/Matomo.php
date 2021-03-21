<?php

namespace SoosyzeExtension\Matomo\Controller;

class Matomo extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/services.php';
    }
}
