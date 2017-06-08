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

		$lazySemanticDataLookup = $this->getMockBuilder( '\SMT\LazySemanticDataLookup' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SMT\PropertyValuesContentAggregator',
			new PropertyValuesContentAggregator( $lazySemanticDataLookup )
		);
	}

	public function testFindContentForProperty() {

		$properties = [ 'foobar' ];

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubSemanticData' )
			->will( $this->returnValue( [] ) );

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIProperty::newFromUserLabel( 'foobar' ) ) )
			->will( $this->returnValue( [ new DIWikiPage( 'Foo', NS_MAIN ) ] ) );

		$lazySemanticDataLookup = $this->getMockBuilder( '\SMT\LazySemanticDataLookup' )
			->disableOriginalConstructor()
			->getMock();

		$lazySemanticDataLookup->expects( $this->once() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$instance = new PropertyValuesContentAggregator( $lazySemanticDataLookup );

		$this->assertSame(
			'Foo',
			$instance->doAggregateFor( $properties )
		);
	}

	public function testAggregatePropertyValueContentWithSameHash() {

		$properties = [ 'foobar' ];

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubSemanticData' )
			->will( $this->returnValue( [] ) );

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIProperty::newFromUserLabel( 'foobar' ) ) )
			->will( $this->returnValue( [
				DIUri::doUnserialize( 'http://username@example.org/foo' ),
				DIUri::doUnserialize( 'http://username@example.org/foo' ),
				new DIWikiPage( 'Foo', NS_MAIN ),
				new DIWikiPage( 'Foo', NS_MAIN ) ] ) );

		$lazySemanticDataLookup = $this->getMockBuilder( '\SMT\LazySemanticDataLookup' )
			->disableOriginalConstructor()
			->getMock();

		$lazySemanticDataLookup->expects( $this->once() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$instance = new PropertyValuesContentAggregator( $lazySemanticDataLookup );

		$this->assertSame(
			'http://username@example.org/foo,Foo',
			$instance->doAggregateFor( $properties )
		);
	}

	public function testFindContentForSubobjectProperty() {

		$properties = [ 'bar' ];

		$subSemanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$subSemanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIProperty::newFromUserLabel( 'bar' ) ) )
			->will( $this->returnValue( [ new DIBlob( 'Foo-with-html-"<>"-escaping-to-happen-somewhere-else' ) ] ) );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->will( $this->returnValue( [] ) );

		$semanticData->expects( $this->once() )
			->method( 'getSubSemanticData' )
			->will( $this->returnValue( [ $subSemanticData ] ) );

		$lazySemanticDataLookup = $this->getMockBuilder( '\SMT\LazySemanticDataLookup' )
			->disableOriginalConstructor()
			->getMock();

		$lazySemanticDataLookup->expects( $this->once() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$instance = new PropertyValuesContentAggregator( $lazySemanticDataLookup );

		$this->assertSame(
			'Foo-with-html-"<>"-escaping-to-happen-somewhere-else',
			$instance->doAggregateFor( $properties )
		);
	}

	public function testFindContentForMultiplePropertiesToUseFullContentAggregation() {

		$properties = [ ' foo ', 'bar' ];

		$propertyValues = [
			0 => [
				DIUri::doUnserialize( 'http://username@example.org/foo' ),
				new DIWikiPage( '"Foo"', NS_MAIN )
			],
			2 => [
				new DIBlob( 'Mo' ),
				new DIBlob( 'Mo' ),
				new DIBlob( 'fo' )
			]
		];

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
			->will( $this->returnValue( [] ) );

		$this->doAssertContentForMultipleProperties(
			false,
			$semanticData,
			$properties,
			'http://username@example.org/foo,"Foo",Mo,fo'
		);
	}

	public function testFindContentForMultiplePropertiesToUseFallbackChain() {

		$properties = [ ' foo ', 'bar' ];

		$propertyValues = [
			[
				DIUri::doUnserialize( 'http://username@example.org/foo' ),
				new DIWikiPage( '"Foo"', NS_MAIN )
			],
			[
				new DIBlob( 'Mo' ),
				new DIBlob( 'fo' )
			]
		];

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->exactly( 1 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIProperty::newFromUserLabel( 'foo' ) ) )
			->will( $this->onConsecutiveCalls( $propertyValues[0], $propertyValues[1] ) );

		$semanticData->expects( $this->any() )
			->method( 'getSubSemanticData' )
			->will( $this->returnValue( [] ) );

		$this->doAssertContentForMultipleProperties(
			true,
			$semanticData,
			$properties,
			'http://username@example.org/foo,"Foo"'
		);
	}

	private function doAssertContentForMultipleProperties( $fallbackChainUsageState, $semanticData, $properties, $expected ) {

		$lazySemanticDataLookup = $this->getMockBuilder( '\SMT\LazySemanticDataLookup' )
			->disableOriginalConstructor()
			->getMock();

		$lazySemanticDataLookup->expects( $this->atLeastOnce() )
			->method( 'getSemanticData' )
			->will( $this->returnValue( $semanticData ) );

		$instance = new PropertyValuesContentAggregator( $lazySemanticDataLookup );
		$instance->useFallbackChainForMultipleProperties( $fallbackChainUsageState );

		$this->assertSame(
			$expected,
			$instance->doAggregateFor( $properties )
		);
	}

}
