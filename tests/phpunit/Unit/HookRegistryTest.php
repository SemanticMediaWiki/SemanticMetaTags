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
			'metaTagsContentPropertySelector' => array(),
			'metaTagsStaticContentDescriptor' => array(),
			'metaTagsBlacklist' => array(),
			'metaTagsFallbackUseForMultipleProperties' => false
		);

		$instance = new HookRegistry( $configuration );
		$instance->register();

		$this->doTestOutputPageParserOutput( $instance );
	}

	public function doTestOutputPageParserOutput( $instance ) {

		$instance->deregister();
		$instance->register();

		$this->assertTrue(
			$instance->isRegistered( 'OutputPageParserOutput' )
		);

		$title = Title::newFromText( __METHOD__ );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->atLeastOnce() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$parserOutput = $this->getMockBuilder( '\ParserOutput' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertThatHookIsExcutable(
			$instance->getHandlers( 'OutputPageParserOutput' ),
			array( &$outputPage, $parserOutput )
		);
	}

	private function assertThatHookIsExcutable( array $hooks, $arguments ) {
		foreach ( $hooks as $hook ) {

			$this->assertInternalType(
				'boolean',
				call_user_func_array( $hook, $arguments )
			);
		}
	}

}
