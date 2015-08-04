<?php

namespace SMT\Tests;

use SMT\PropertyValuesContentAggregator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMWDIBlob as DIBlob;
use SMWDIUri as DIUri;

/**
 * @covers \SMT\PropertyValuesContentAggregator
 * @group semantic-meta-tags
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class PropertyValuesContentAggregatorTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$semanticDataFallbackFetcher = $this->getMockBuilder( '\SMT\SemanticDataFallbackFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SMT\PropertyValuesContentAggregator',
			new PropertyValuesContentAggregator( $semanticDataFallbackFetcher )
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

		$semanticDataFallbackFetcher = $this->getMockBuilder( '\SMT\SemanticDataFallbackFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$semanticDataFallbackFetcher->expects( $this->once() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$instance = new PropertyValuesContentAggregator( $semanticDataFallbackFetcher );

		$this->assertSame(
			'Foo',
			$instance->doAggregateFor( $properties )
		);
	}

	public function testAggregatePropertyValueContentWithSameHash() {

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
			->will( $this->returnValue( array(
				DIUri::doUnserialize( 'http://username@example.org/foo' ),
				DIUri::doUnserialize( 'http://username@example.org/foo' ),
				new DIWikiPage( 'Foo', NS_MAIN ),
				new DIWikiPage( 'Foo', NS_MAIN ) ) ) );

		$semanticDataFallbackFetcher = $this->getMockBuilder( '\SMT\SemanticDataFallbackFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$semanticDataFallbackFetcher->expects( $this->once() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$instance = new PropertyValuesContentAggregator( $semanticDataFallbackFetcher );

		$this->assertSame(
			'http://username@example.org/foo,Foo',
			$instance->doAggregateFor( $properties )
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

		$semanticDataFallbackFetcher = $this->getMockBuilder( '\SMT\SemanticDataFallbackFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$semanticDataFallbackFetcher->expects( $this->once() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$instance = new PropertyValuesContentAggregator( $semanticDataFallbackFetcher );

		$this->assertSame(
			'Foo-with-html-"<>"-escaping-to-happen-somewhere-else',
			$instance->doAggregateFor( $properties )
		);
	}

	public function testFindContentForMultiplePropertiesToUseFullContentAggregation() {

		$properties = array( ' foo ', 'bar' );

		$propertyValues = array(
			0 => array(
				DIUri::doUnserialize( 'http://username@example.org/foo' ),
				new DIWikiPage( '"Foo"', NS_MAIN )
			),
			2 => array(
				new DIBlob( 'Mo' ),
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

		$properties = array( ' foo ', 'bar' );

		$propertyValues = array(
			array(
				DIUri::doUnserialize( 'http://username@example.org/foo' ),
				new DIWikiPage( '"Foo"', NS_MAIN )
			),
			array(
				new DIBlob( 'Mo' ),
				new DIBlob( 'fo' )
			)
		);

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->exactly( 1 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIProperty::newFromUserLabel( 'foo' ) ) )
			->will( $this->onConsecutiveCalls( $propertyValues[0], $propertyValues[1] ) );

		$semanticData->expects( $this->any() )
			->method( 'getSubSemanticData' )
			->will( $this->returnValue( array() ) );

		$this->doAssertContentForMultipleProperties(
			true,
			$semanticData,
			$properties,
			'http://username@example.org/foo,"Foo"'
		);
	}

	private function doAssertContentForMultipleProperties( $fallbackChainUsageState, $semanticData, $properties, $expected ) {

		$semanticDataFallbackFetcher = $this->getMockBuilder( '\SMT\SemanticDataFallbackFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$semanticDataFallbackFetcher->expects( $this->atLeastOnce() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$instance = new PropertyValuesContentAggregator( $semanticDataFallbackFetcher );
		$instance->useFallbackChainForMultipleProperties( $fallbackChainUsageState );

		$this->assertSame(
			$expected,
			$instance->doAggregateFor( $properties )
		);
	}

}
