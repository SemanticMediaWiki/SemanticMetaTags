<?php

namespace SMT\Tests;

use SMT\PropertyValueContentFinder;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMWDIBlob as DIBlob;
use SMWDIUri as DIUri;

/**
 * @covers \SMT\PropertyValueContentFinder
 *
 * @group semantic-meta-tags
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class PropertyValueContentFinderTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$fallbackSemanticDataFetcher = $this->getMockBuilder( '\SMT\FallbackSemanticDataFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SMT\PropertyValueContentFinder',
			new PropertyValueContentFinder( $fallbackSemanticDataFetcher )
		);
	}

	public function testFindContentForProperty() {

		$properties = array( 'foobar' );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubSemanticData' )
			->will( $this->returnValue( array() ) );

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIProperty::newFromUserLabel( 'foobar' ) ) )
			->will( $this->returnValue( array( new DIWikiPage( 'Foo', NS_MAIN ) ) ) );

		$fallbackSemanticDataFetcher = $this->getMockBuilder( '\SMT\FallbackSemanticDataFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$fallbackSemanticDataFetcher->expects( $this->once() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$instance = new PropertyValueContentFinder( $fallbackSemanticDataFetcher );

		$this->assertSame(
			'Foo',
			$instance->findContentForProperties( $properties )
		);
	}

	public function testFindContentForSubobjectProperty() {

		$properties = array( 'bar' );

		$subSemanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$subSemanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIProperty::newFromUserLabel( 'bar' ) ) )
			->will( $this->returnValue( array( new DIBlob( 'Foo-with-html-"<>"-escaping-to-happen-somewhere-else' ) ) ) );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->will( $this->returnValue( array() ) );

		$semanticData->expects( $this->once() )
			->method( 'getSubSemanticData' )
			->will( $this->returnValue( array( $subSemanticData ) ) );

		$fallbackSemanticDataFetcher = $this->getMockBuilder( '\SMT\FallbackSemanticDataFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$fallbackSemanticDataFetcher->expects( $this->once() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$instance = new PropertyValueContentFinder( $fallbackSemanticDataFetcher );

		$this->assertSame(
			'Foo-with-html-"<>"-escaping-to-happen-somewhere-else',
			$instance->findContentForProperties( $properties )
		);
	}

	public function testFindContentForMultiplePropertiesToUseFullContentConcatenation() {

		$properties = array( ' foo ', 'bar' );

		$propertyValues = array(
			0 => array(
				new DIUri( 'http', 'username@example.org/foo', '', '' ),
				new DIWikiPage( '"Foo"', NS_MAIN )
			),
			2 => array(
				new DIBlob( 'Mo' ),
				new DIBlob( 'fo' )
			)
		);

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->at( 0 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIProperty::newFromUserLabel( 'foo' ) ) )
			->will( $this->returnValue( $propertyValues[0] ) );

		$semanticData->expects( $this->at( 2 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIProperty::newFromUserLabel( 'bar' ) ) )
			->will( $this->returnValue( $propertyValues[2] ) );

		$semanticData->expects( $this->any() )
			->method( 'getSubSemanticData' )
			->will( $this->returnValue( array() ) );

		$this->doAssertContentForMultipleProperties(
			false,
			$semanticData,
			$properties,
			'http://username@example.org/foo,"Foo",Mo,fo'
		);
	}

	public function testFindContentForMultiplePropertiesToUseFallbackChain() {

		$properties = array( ' foo ', 'bar', 'bar' );

		$propertyValues = array(
			0 => array(),
			2 => array(
				new DIBlob( 'Mo' ),
				new DIBlob( 'fo' )
			)
		);

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->at( 0 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIProperty::newFromUserLabel( 'foo' ) ) )
			->will( $this->returnValue( $propertyValues[0] ) );

		$semanticData->expects( $this->at( 2 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIProperty::newFromUserLabel( 'bar' ) ) )
			->will( $this->returnValue( $propertyValues[2] ) );

		$semanticData->expects( $this->any() )
			->method( 'getSubSemanticData' )
			->will( $this->returnValue( array() ) );

		$this->doAssertContentForMultipleProperties(
			true,
			$semanticData,
			$properties,
			'Mo,fo'
		);
	}

	private function doAssertContentForMultipleProperties( $fallbackChainUsageState, $semanticData, $properties, $expected ) {

		$fallbackSemanticDataFetcher = $this->getMockBuilder( '\SMT\FallbackSemanticDataFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$fallbackSemanticDataFetcher->expects( $this->atLeastOnce() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$instance = new PropertyValueContentFinder( $fallbackSemanticDataFetcher );
		$instance->useFallbackChainForMultipleProperties( $fallbackChainUsageState );

		$this->assertSame(
			$expected,
			$instance->findContentForProperties( $properties )
		);
	}

}
