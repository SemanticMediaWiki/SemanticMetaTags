<?php

namespace SMT\Tests;

use SMT\MetaTagsProcessor;

/**
 * @covers \SMT\MetaTagsProcessor
 * @group semantic-meta-tags
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class MetaTagsProcessorTest extends \PHPUnit\Framework\TestCase {

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
			->willReturn( false );

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
			->onlyMethods( [ 'canUseOutputPage' ] )
			->getMock();

		$OutputPageHtmlTagsInserter->expects( $this->once() )
			->method( 'canUseOutputPage' )
			->willReturn( true );

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
			->with( $properties )
			->willReturn( $expected['content'] );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->once() )
			->method( 'addMeta' )
			->with(
				$expected['tag'],
				$expected['content'] );

		$OutputPageHtmlTagsInserter = $this->getMockBuilder( '\SMT\OutputPageHtmlTagsInserter' )
			->setConstructorArgs( [ $outputPage ] )
			->onlyMethods( [ 'canUseOutputPage' ] )
			->getMock();

		$OutputPageHtmlTagsInserter->expects( $this->once() )
			->method( 'canUseOutputPage' )
			->willReturn( true );

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

		$outputPageHtmlTagsInserter = $this->getMockBuilder( '\SMT\OutputPageHtmlTagsInserter' )
			->disableOriginalConstructor()
			->onlyMethods( [ 'canUseOutputPage', 'addTagContentToOutputPage' ] )
			->getMock();

		$outputPageHtmlTagsInserter->expects( $this->once() )
			->method( 'canUseOutputPage' )
			->willReturn( true );

		$outputPageHtmlTagsInserter->expects( $this->never() )
			->method( 'addTagContentToOutputPage' );

		$instance = new MetaTagsProcessor(
			$propertyValuesContentAggregator
		);

		$instance->setMetaTagsStaticContentDescriptor( [ 'foo' => '' ] );
		$instance->addMetaTags( $outputPageHtmlTagsInserter );
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
				$expected['tag'],
				$expected['content'] );

		$OutputPageHtmlTagsInserter = $this->getMockBuilder( '\SMT\OutputPageHtmlTagsInserter' )
			->setConstructorArgs( [ $outputPage ] )
			->onlyMethods( [ 'canUseOutputPage' ] )
			->getMock();

		$OutputPageHtmlTagsInserter->expects( $this->once() )
			->method( 'canUseOutputPage' )
			->willReturn( true );

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
