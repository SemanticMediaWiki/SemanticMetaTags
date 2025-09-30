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
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class PropertyValuesContentAggregatorTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$lazySemanticDataLookup = $this->getMockBuilder( '\SMT\LazySemanticDataLookup' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage = $this->getMockBuilder( 'OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SMT\PropertyValuesContentAggregator',
			new PropertyValuesContentAggregator( $lazySemanticDataLookup, $outputPage )
		);
	}

	public function testFindContentForProperty() {
		$properties = [ 'foobar' ];

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubSemanticData' )
			->willReturn( [] );

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->with( DIProperty::newFromUserLabel( 'foobar' ) )
			->willReturn( [ new DIWikiPage( 'Foo', NS_MAIN ) ] );

		$lazySemanticDataLookup = $this->getMockBuilder( '\SMT\LazySemanticDataLookup' )
			->disableOriginalConstructor()
			->getMock();

		$lazySemanticDataLookup->expects( $this->once() )
			->method( 'getSemanticData' )
			->willReturn( $semanticData );

		$outputPage = $this->getMockBuilder( 'OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new PropertyValuesContentAggregator( $lazySemanticDataLookup, $outputPage );

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
			->willReturn( [] );

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->with( DIProperty::newFromUserLabel( 'foobar' ) )
			->willReturn( [
				DIUri::doUnserialize( 'http://username@example.org/foo' ),
				DIUri::doUnserialize( 'http://username@example.org/foo' ),
				new DIWikiPage( 'Foo', NS_MAIN ),
				new DIWikiPage( 'Foo', NS_MAIN ) ] );

		$lazySemanticDataLookup = $this->getMockBuilder( '\SMT\LazySemanticDataLookup' )
			->disableOriginalConstructor()
			->getMock();

		$lazySemanticDataLookup->expects( $this->once() )
			->method( 'getSemanticData' )
			->willReturn( $semanticData );

		$outputPage = $this->getMockBuilder( 'OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new PropertyValuesContentAggregator( $lazySemanticDataLookup, $outputPage );

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
			->with( DIProperty::newFromUserLabel( 'bar' ) )
			->willReturn( [ new DIBlob( 'Foo-with-html-"<>"-escaping-to-happen-somewhere-else' ) ] );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->willReturn( [] );

		$semanticData->expects( $this->once() )
			->method( 'getSubSemanticData' )
			->willReturn( [ $subSemanticData ] );

		$lazySemanticDataLookup = $this->getMockBuilder( '\SMT\LazySemanticDataLookup' )
			->disableOriginalConstructor()
			->getMock();

		$lazySemanticDataLookup->expects( $this->once() )
			->method( 'getSemanticData' )
			->willReturn( $semanticData );

		$outputPage = $this->getMockBuilder( 'OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new PropertyValuesContentAggregator( $lazySemanticDataLookup, $outputPage );

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

		$semanticData->method('getPropertyValues')
			->withConsecutive(
				[ DIProperty::newFromUserLabel('foo') ],
				[ DIProperty::newFromUserLabel('bar') ]
		)
		->willReturnOnConsecutiveCalls(
			$propertyValues[0],
			$propertyValues[2]
		);

		$semanticData->expects( $this->any() )
			->method( 'getSubSemanticData' )
			->willReturn( [] );

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

		$semanticData->expects( $this->once() )
			->method( 'getPropertyValues' )
			->with( DIProperty::newFromUserLabel( 'foo' ) )
			->willReturnOnConsecutiveCalls( $propertyValues[0], $propertyValues[1] );

		$semanticData->expects( $this->any() )
			->method( 'getSubSemanticData' )
			->willReturn( [] );

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
			->willReturn( $semanticData );

		$outputPage = $this->getMockBuilder( 'OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new PropertyValuesContentAggregator( $lazySemanticDataLookup, $outputPage );
		$instance->useFallbackChainForMultipleProperties( $fallbackChainUsageState );

		$this->assertSame(
			$expected,
			$instance->doAggregateFor( $properties )
		);
	}

}
