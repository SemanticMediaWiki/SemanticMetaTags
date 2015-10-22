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

		$instance->setMetaTagsContentPropertySelector( array( 'foo' ) );
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
			->setConstructorArgs( array( $outputPage ) )
			->setMethods( array( 'canUseOutputPage' ) )
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
			->setConstructorArgs( array( $outputPage ) )
			->setMethods( array( 'canUseOutputPage' ) )
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
			->setMethods( array( 'canUseOutputPage' ) )
			->getMock();

		$OutputPageHtmlTagsInserter->expects( $this->once() )
			->method( 'canUseOutputPage' )
			->will( $this->returnValue( true ) );

		$OutputPageHtmlTagsInserter->expects( $this->never() )
			->method( 'addTagContentToOutputPage' );

		$instance = new MetaTagsProcessor(
			$propertyValuesContentAggregator
		);

		$instance->setMetaTagsStaticContentDescriptor( array( 'foo' => '' ) );
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
			->setConstructorArgs( array( $outputPage ) )
			->setMethods( array( 'canUseOutputPage' ) )
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

		$provider = array();

		$provider[] = array(
			array()
		);

		$provider[] = array(
			array( 'foo' => '' )
		);

		$provider[] = array(
			array( 'foo' => array() )
		);

		$provider[] = array(
			array( 'foo:bar' => '' )
		);

		return $provider;
	}

	public function validPropertySelectorProvider() {

		$provider = array();

		$provider[] = array(
			array( 'foo' => 'foobar' ),
			array( 'foobar' ),
			array( 'tag' => 'foo', 'content' => 'Mo,fo' )
		);

		$provider[] = array(
			array( 'foo' => array( 'foobar', 'quin' ) ),
			array( 'foobar', 'quin' ),
			array( 'tag' => 'foo', 'content' => 'Mo,fo' )
		);

		$provider[] = array(
			array( 'foo' => ' foobar, quin ' ),
			array( ' foobar', ' quin ' ),
			array( 'tag' => 'foo', 'content' => 'Mo,fo' )
		);

		$provider[] = array(
			array( 'FOO' => 'foobar,quin' ),
			array( 'foobar', 'quin' ),
			array( 'tag' => 'foo', 'content' => 'Mo,fo' )
		);

		$provider[] = array(
			array( 'FO"O' => 'foobar,quin' ),
			array( 'foobar', 'quin' ),
			array( 'tag' => 'fo&quot;o', 'content' => 'Mo,fo' )
		);

		return $provider;
	}

	public function staticContentProvider() {

		$provider = array();

		$provider[] = array(
			array( 'foo' => 'staticDescriptionOfContent' ),
			array( 'tag' => 'foo', 'content' => 'staticDescriptionOfContent' )
		);

		$provider[] = array(
			array( 'FOO' => 'static"Description"OfContent' ),
			array( 'tag' => 'foo', 'content' => 'static&quot;Description&quot;OfContent' )
		);

		$provider[] = array(
			array( 'bar' => '', 'FOO' => 'bar' ),
			array(
				'tag' => 'foo', 'content' => 'bar'
			)
		);

		return $provider;
	}

}
