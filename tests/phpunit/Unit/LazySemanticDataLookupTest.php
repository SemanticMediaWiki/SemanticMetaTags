<?php

namespace SMT\Tests;

use SMT\LazySemanticDataLookup;
use SMW\DIWikiPage;

/**
 * @covers \SMT\LazySemanticDataLookup
 * @group semantic-meta-tags
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class LazySemanticDataLookupTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->assertInstanceOf(
			'\SMT\LazySemanticDataLookup',
			new LazySemanticDataLookup( $parserData, $store )
		);
	}

	public function testGetSemanticDataFromParserOutput() {
		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'isEmpty' )
			->willReturn( false );

		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$parserData->expects( $this->once() )
			->method( 'getSemanticData' )
			->willReturn( $semanticData );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->never() )
			->method( 'getSemanticData' );

		$instance = new LazySemanticDataLookup( $parserData, $store );
		$instance->getSemanticData();

		// Internally cached
		$instance->getSemanticData();
	}

	public function testGetSemanticDataFromStore() {
		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->at( 0 ) )
			->method( 'isEmpty' )
			->willReturn( true );

		$semanticData->expects( $this->at( 1 ) )
			->method( 'isEmpty' )
			->willReturn( false );

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( new DIWikiPage( 'Foo', NS_MAIN ) );

		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$parserData->expects( $this->atLeastOnce() )
			->method( 'getSemanticData' )
			->willReturn( $semanticData );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getSemanticData' );

		$instance = new LazySemanticDataLookup( $parserData, $store );
		$instance->getSemanticData();

		// Internally cached
		$instance->getSemanticData();
	}

}
