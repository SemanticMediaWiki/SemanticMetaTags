<?php

namespace SMT\Tests;

use SMT\HookRegistry;
use SMT\Options;
use Title;

/**
 * @covers \SMT\HookRegistry
 * @group semantic-meta-tags
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class HookRegistryTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$options = $this->getMockBuilder( '\SMT\Options' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SMT\HookRegistry',
			new HookRegistry( $store, $options )
		);
	}

	public function testRegister() {

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$configuration = [
			'metaTagsContentPropertySelector' => [],
			'metaTagsStaticContentDescriptor' => [],
			'metaTagsBlacklist' => [],
			'metaTagsFallbackUseForMultipleProperties' => false,
			'metaTagsMetaPropertyPrefixes' => []
		];

		$instance = new HookRegistry(
			$store,
			new Options( $configuration )
		);

		$this->doTestRegisteredOutputPageParserOutputHandler( $instance );
	}

	public function doTestRegisteredOutputPageParserOutputHandler( $instance ) {

		$handler = 'OutputPageParserOutput';

		$title = Title::newFromText( __METHOD__ );

		$webRequest = $this->getMockBuilder( '\WebRequest' )
			->disableOriginalConstructor()
			->getMock();

		$context = $this->getMockBuilder( '\IContextSource' )
			->disableOriginalConstructor()
			->getMock();

		$context->expects( $this->atLeastOnce() )
			->method( 'getRequest' )
			->will( $this->returnValue( $webRequest ) );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->atLeastOnce() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$outputPage->expects( $this->atLeastOnce() )
			->method( 'getContext' )
			->will( $this->returnValue( $context ) );

		$parserOutput = $this->getMockBuilder( '\ParserOutput' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ &$outputPage, $parserOutput ]
		);
	}

	private function assertThatHookIsExcutable( \Closure $handler, $arguments ) {
		$this->assertInternalType(
			'boolean',
			call_user_func_array( $handler, $arguments )
		);
	}

}
