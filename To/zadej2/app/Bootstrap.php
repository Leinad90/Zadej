<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;
use Nette\Utils\FileSystem;
use Tracy\Debugger;


class Bootstrap
{
	public static function boot(): Configurator
	{
		$configurator = new Configurator;
		$appDir = dirname(__DIR__);

		$configurator->enableTracy($appDir . '/log', 'daniel.hejduk@gmail.com');

		$configurator->setTimeZone('Europe/Prague');
		$configurator->setTempDirectory($appDir . '/temp');

		$configurator->createRobotLoader()
			->addDirectory(__DIR__)
			->register();

		$configurator->addConfig($appDir . '/config/common.neon');
		$configurator->addConfig($appDir . '/config/local.neon');

		return $configurator;
	}
}
