<?php

namespace SMT\Tests;

use SMT\HookRegistry;
use Title;

/**
 * @covers \SMT\HookRegistry
 *
 * @group semantic-meta-tags
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class HookRegistryTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$configuration =  array();

		$this->assertInstanceOf(
			'\SMT\HookRegistry',
			new HookRegistry( $configuration )
		);
	}

	public function testRegister() {

		$configuration = array(
			'metaTagsContentPropertySelector' => array()
		);

		$wgHooks = array();

		$instance = new HookRegistry( $configuration );
		$instance->register( $wgHooks );

		$this->assertNotEmpty(
			$wgHooks
		);

		$this->doTestOutputPageParserOutput( $wgHooks );
	}

	public function doTestOutputPageParserOutput( $wgHooks ) {

		$title = Title::newFromText( __METHOD__ );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$parserOutput = $this->getMockBuilder( '\ParserOutput' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertThatHookIsExcutable(
			$wgHooks,
			'OutputPageParserOutput',
			array( &$outputPage, $parserOutput )
		);
	}

	private function assertThatHookIsExcutable( $wgHooks, $hookName, $arguments ) {
		foreach ( $wgHooks[ $hookName ] as $hook ) {
			$this->assertInternalType(
				'boolean',
				call_user_func_array( $hook, $arguments )
			);
		}
	}

}
