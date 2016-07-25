<?php
date_default_timezone_set('Europe/Prague');
ini_set('display_errors', 'on');
ini_set('error_reporting', E_ALL & ~E_STRICT & ~E_DEPRECATED);


require_once('sys.php');
$loader = require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
#$loader->add('', __DIR__.'/src/');


$configurator = new Nette\Configurator;
$configurator->setDebugMode(true);
$configurator->enableDebugger(__DIR__ . DIRECTORY_SEPARATOR . 'log');
$configurator->setTempDirectory(__DIR__ . DIRECTORY_SEPARATOR . 'temp');

\Tracy\Debugger::$email = 'pcirman@pre.cz';
#\Tracy\Debugger::$maxDepth = 10; // default: 3


ini_set('error_reporting', E_ALL & ~E_STRICT & ~E_DEPRECATED); // je nutno znova,m jinak nette debugger ukazuje stricty, pac si to sam prenastavi

$latte = new Latte\Engine;
$latte->setTempDirectory('temp');
//$latte->registerFilter(new LatteFilter);

$engine = new \biibtech\tdc\Engine(__DIR__);
$tour = $engine->getTours()->offsetGet(1);





$latte->render('templates' . DIRECTORY_SEPARATOR . 'dashboard.latte', array('tour' => $tour, 'engine'=>$engine));
#$html = $latte->renderToString('template.latte', $parameters);
