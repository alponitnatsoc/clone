<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$classMap = array(
    'FPDF_' => __DIR__.'/../vendor/royopa/fpdf-symfony2/lib/FPDF/FPDF.php',
    'FPDI_' => __DIR__.'/../vendor/royopa/fpdf-symfony2/lib/FPDF/FPDI.php'
);
$loader->addClassMap($classMap);  

return $loader;
