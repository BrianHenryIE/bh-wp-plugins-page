<?php

namespace BrianHenryIE\WP_Plugins_Page;

use BrianHenryIE\ColorLogger\ColorLogger;
use lucatume\WPBrowser\TestCase\WPTestCase;
use Psr\Log\LoggerInterface;

class WPUnit_Testcase extends WPTestCase {

	protected LoggerInterface $logger;

	protected function setUp(): void {
		parent::setUp();
		$this->logger = new ColorLogger();
	}
}
