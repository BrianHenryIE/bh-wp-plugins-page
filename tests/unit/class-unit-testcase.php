<?php

namespace BrianHenryIE\WP_Plugins_Page;

use BrianHenryIE\ColorLogger\ColorLogger;
use Codeception\Test\Unit;
use Override;
use Psr\Log\LoggerInterface;
use WP_Mock;

class Unit_Testcase extends Unit {

	protected LoggerInterface $logger;

	#[Override]
	protected function setup(): void {
		WP_Mock::setUp();

		$this->logger = new ColorLogger();
	}

	#[Override]
	protected function tearDown(): void {
		parent::_tearDown();
		WP_Mock::tearDown();
		\Patchwork\restoreAll();
	}
}
