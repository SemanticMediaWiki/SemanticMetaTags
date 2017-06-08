<?php

namespace SMT\Tests;

use SMT\MetaTagsProcessor;
use SMT\OutputPageHtmlTagsInserter;

/**
 * @covers \SMT\MetaTagsProcessor
 * @group semantic-meta-tags
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class MetaTagsProcessorTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$propertyValuesContentAggregator = $this->getMockBuilder( '\SMT\PropertyValuesContentAggregator' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SMT\MetaTagsProcessor',
			new MetaTagsProcessor( $propertyValuesContentAggregator )
		);
	}

	public function testTryToAddTags() {

		$propertyValuesContentAggregator = $this->getMockBuilder( '\SMT\PropertyValuesContentAggregator' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValuesContentAggregator->expects( $this->never() )
			->method( 'doAggregateFor' );

		$OutputPageHtmlTagsInserter = $this->getMockBuilder( '\SMT\OutputPageHtmlTagsInserter' )
			->disableOriginalConstructor()
			->getMock();

		$OutputPageHtmlTagsInserter->expects( $this->once() )
			->method( 'canUseOutputPage' )
			->will( $this->returnValue( false ) );

		$instance = new MetaTagsProcessor(
			$propertyValuesContentAggregator
		);

		$instance->setMetaTagsContentPropertySelector( [ 'foo' ] );
		$instance->addMetaTags( $OutputPageHtmlTagsInserter );
	}

	/**
	 * @dataProvider invalidPropertySelectorProvider
	 */
	public function testTryToModifyOutputPageForInvalidPropertySelector( $propertySelector ) {

		$propertyValuesContentAggregator = $this->getMockBuilder( '\SMT\PropertyValuesContentAggregator' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValuesContentAggregator->expects( $this->never() )
			->method( 'doAggregateFor' );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->never() )
			->method( 'addMeta' );

		$outputPage->expects( $this->never() )
			->method( 'addHeadItem' );

		$OutputPageHtmlTagsInserter = $this->getMockBuilder( '\SMT\OutputPageHtmlTagsInserter' )
			->setConstructorArgs( [ $outputPage ] )
			->setMethods( [ 'canUseOutputPage' ] )
			->getMock();

		$OutputPageHtmlTagsInserter->expects( $this->once() )
			->method( 'canUseOutputPage' )
			->will( $this->returnValue( true ) );

		$instance = new MetaTagsProcessor(
			$propertyValuesContentAggregator
		);

		$instance->setMetaTagsContentPropertySelector( $propertySelector );
		$instance->addMetaTags( $OutputPageHtmlTagsInserter );
	}

	/**
	 * @dataProvider validPropertySelectorProvider
	 */
	public function testModifyOutputPageForValidPropertySelector( $propertySelector, $properties, $expected ) {

		$propertyValuesContentAggregator = $this->getMockBuilder( '\SMT\PropertyValuesContentAggregator' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValuesContentAggregator->expects( $this->once() )
			->method( 'doAggregateFor' )
			->with( $this->equalTo( $properties ) )
			->will( $this->returnValue( $expected['content'] ) );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->once() )
			->method( 'addMeta' )
			->with(
				$this->equalTo( $expected['tag'] ),
				$this->equalTo( $expected['content'] ) );

		$OutputPageHtmlTagsInserter = $this->getMockBuilder( '\SMT\OutputPageHtmlTagsInserter' )
			->setConstructorArgs( [ $outputPage ] )
			->setMethods( [ 'canUseOutputPage' ] )
			->getMock();

		$OutputPageHtmlTagsInserter->expects( $this->once() )
			->method( 'canUseOutputPage' )
			->will( $this->returnValue( true ) );

		$instance = new MetaTagsProcessor(
			$propertyValuesContentAggregator
		);

		$instance->setMetaTagsContentPropertySelector( $propertySelector );
		$instance->addMetaTags( $OutputPageHtmlTagsInserter );
	}

	public function testTryToModifyOutputPageForEmptyStaticContent() {

		$propertyValuesContentAggregator = $this->getMockBuilder( '\SMT\PropertyValuesContentAggregator' )
			->disableOriginalConstructor()
			->getMock();

		$OutputPageHtmlTagsInserter = $this->getMockBuilder( '\SMT\OutputPageHtmlTagsInserter' )
			->disableOriginalConstructor()
			->setMethods( [ 'canUseOutputPage' ] )
			->getMock();

		$OutputPageHtmlTagsInserter->expects( $this->once() )
			->method( 'canUseOutputPage' )
			->will( $this->returnValue( true ) );

		$OutputPageHtmlTagsInserter->expects( $this->never() )
			->method( 'addTagContentToOutputPage' );

		$instance = new MetaTagsProcessor(
			$propertyValuesContentAggregator
		);

		$instance->setMetaTagsStaticContentDescriptor( [ 'foo' => '' ] );
		$instance->addMetaTags( $OutputPageHtmlTagsInserter );
	}

	/**
	 * @dataProvider staticContentProvider
	 */
	public function testModifyOutputPageForStaticContentDescriptor( $contentDescriptor, $expected ) {

		$propertyValuesContentAggregator = $this->getMockBuilder( '\SMT\PropertyValuesContentAggregator' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->once() )
			->method( 'addMeta' )
			->with(
				$this->equalTo( $expected['tag'] ),
				$this->equalTo( $expected['content'] ) );

		$OutputPageHtmlTagsInserter = $this->getMockBuilder( '\SMT\OutputPageHtmlTagsInserter' )
			->setConstructorArgs( [ $outputPage ] )
			->setMethods( [ 'canUseOutputPage' ] )
			->getMock();

		$OutputPageHtmlTagsInserter->expects( $this->once() )
			->method( 'canUseOutputPage' )
			->will( $this->returnValue( true ) );

		$instance = new MetaTagsProcessor(
			$propertyValuesContentAggregator
		);

		$instance->setMetaTagsStaticContentDescriptor( $contentDescriptor );
		$instance->addMetaTags( $OutputPageHtmlTagsInserter );
	}

	public function invalidPropertySelectorProvider() {

		$provider = [];

		$provider[] = [
			[]
		];

		$provider[] = [
			[ 'foo' => '' ]
		];

		$provider[] = [
			[ 'foo' => [] ]
		];

		$provider[] = [
			[ 'foo:bar' => '' ]
		];

		return $provider;
	}

	public function validPropertySelectorProvider() {

		$provider = [];

		$provider[] = [
			[ 'foo' => 'foobar' ],
			[ 'foobar' ],
			[ 'tag' => 'foo', 'content' => 'Mo,fo' ]
		];

		$provider[] = [
			[ 'foo' => [ 'foobar', 'quin' ] ],
			[ 'foobar', 'quin' ],
			[ 'tag' => 'foo', 'content' => 'Mo,fo' ]
		];

		$provider[] = [
			[ 'foo' => ' foobar, quin ' ],
			[ ' foobar', ' quin ' ],
			[ 'tag' => 'foo', 'content' => 'Mo,fo' ]
		];

		$provider[] = [
			[ 'FOO' => 'foobar,quin' ],
			[ 'foobar', 'quin' ],
			[ 'tag' => 'foo', 'content' => 'Mo,fo' ]
		];

		$provider[] = [
			[ 'FO"O' => 'foobar,quin' ],
			[ 'foobar', 'quin' ],
			[ 'tag' => 'fo&quot;o', 'content' => 'Mo,fo' ]
		];

		return $provider;
	}

	public function staticContentProvider() {

		$provider = [];

		$provider[] = [
			[ 'foo' => 'staticDescriptionOfContent' ],
			[ 'tag' => 'foo', 'content' => 'staticDescriptionOfContent' ]
		];

		$provider[] = [
			[ 'FOO' => 'static"Description"OfContent' ],
			[ 'tag' => 'foo', 'content' => 'static&quot;Description&quot;OfContent' ]
		];

		$provider[] = [
			[ 'bar' => '', 'FOO' => 'bar' ],
			[
				'tag' => 'foo', 'content' => 'bar'
			]
		];

		return $provider;
	}

}
