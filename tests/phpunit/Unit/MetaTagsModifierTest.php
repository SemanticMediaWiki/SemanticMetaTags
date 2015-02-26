<?php

namespace SMT\Tests;

use SMT\MetaTagsModifier;
use SMT\OutputPageTagFormatter;

/**
 * @covers \SMT\MetaTagsModifier
 *
 * @group semantic-meta-tags
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class MetaTagsModifierTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$propertyValuesContentFetcher = $this->getMockBuilder( '\SMT\PropertyValuesContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$outputPageTagFormatter = $this->getMockBuilder( '\SMT\OutputPageTagFormatter' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SMT\MetaTagsModifier',
			new MetaTagsModifier( $propertyValuesContentFetcher, $outputPageTagFormatter )
		);
	}

	public function testTryToAddTags() {

		$propertyValuesContentFetcher = $this->getMockBuilder( '\SMT\PropertyValuesContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValuesContentFetcher->expects( $this->never() )
			->method( 'fetchContentForProperties' );

		$outputPageTagFormatter = $this->getMockBuilder( '\SMT\OutputPageTagFormatter' )
			->disableOriginalConstructor()
			->getMock();

		$outputPageTagFormatter->expects( $this->once() )
			->method( 'canUseOutputPage' )
			->will( $this->returnValue( false ) );

		$instance = new MetaTagsModifier(
			$propertyValuesContentFetcher,
			$outputPageTagFormatter
		);

		$instance->setMetaTagsContentPropertySelector( array( 'foo' ) );
		$instance->addMetaTags();
	}

	/**
	 * @dataProvider invalidPropertySelectorProvider
	 */
	public function testTryToModifyOutputPageForInvalidPropertySelector( $propertySelector ) {

		$propertyValuesContentFetcher = $this->getMockBuilder( '\SMT\PropertyValuesContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValuesContentFetcher->expects( $this->never() )
			->method( 'fetchContentForProperties' );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->never() )
			->method( 'addMeta' );

		$outputPage->expects( $this->never() )
			->method( 'addHeadItem' );

		$outputPageTagFormatter = $this->getMockBuilder( '\SMT\OutputPageTagFormatter' )
			->setConstructorArgs( array( $outputPage ) )
			->setMethods( array( 'canUseOutputPage' ) )
			->getMock();

		$outputPageTagFormatter->expects( $this->once() )
			->method( 'canUseOutputPage' )
			->will( $this->returnValue( true ) );

		$instance = new MetaTagsModifier(
			$propertyValuesContentFetcher,
			$outputPageTagFormatter
		);

		$instance->setMetaTagsContentPropertySelector( $propertySelector );
		$instance->addMetaTags();
	}

	/**
	 * @dataProvider validPropertySelectorProvider
	 */
	public function testModifyOutputPageForValidPropertySelector( $propertySelector, $properties, $expected ) {

		$propertyValuesContentFetcher = $this->getMockBuilder( '\SMT\PropertyValuesContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValuesContentFetcher->expects( $this->once() )
			->method( 'fetchContentForProperties' )
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

		$outputPageTagFormatter = $this->getMockBuilder( '\SMT\OutputPageTagFormatter' )
			->setConstructorArgs( array( $outputPage ) )
			->setMethods( array( 'canUseOutputPage' ) )
			->getMock();

		$outputPageTagFormatter->expects( $this->once() )
			->method( 'canUseOutputPage' )
			->will( $this->returnValue( true ) );

		$instance = new MetaTagsModifier(
			$propertyValuesContentFetcher,
			$outputPageTagFormatter
		);

		$instance->setMetaTagsContentPropertySelector( $propertySelector );
		$instance->addMetaTags();
	}

	public function testTryToModifyOutputPageForEmptyStaticContent() {

		$propertyValuesContentFetcher = $this->getMockBuilder( '\SMT\PropertyValuesContentFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$outputPageTagFormatter = $this->getMockBuilder( '\SMT\OutputPageTagFormatter' )
			->disableOriginalConstructor()
			->setMethods( array( 'canUseOutputPage' ) )
			->getMock();

		$outputPageTagFormatter->expects( $this->once() )
			->method( 'canUseOutputPage' )
			->will( $this->returnValue( true ) );

		$outputPageTagFormatter->expects( $this->never() )
			->method( 'addTagContentToOutputPage' );

		$instance = new MetaTagsModifier(
			$propertyValuesContentFetcher,
			$outputPageTagFormatter
		);

		$instance->setMetaTagsStaticContentDescriptor( array( 'foo' => '' ) );
		$instance->addMetaTags();
	}

	/**
	 * @dataProvider staticContentProvider
	 */
	public function testModifyOutputPageForStaticContentDescriptor( $contentDescriptor, $expected ) {

		$propertyValuesContentFetcher = $this->getMockBuilder( '\SMT\PropertyValuesContentFetcher' )
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

		$outputPageTagFormatter = $this->getMockBuilder( '\SMT\OutputPageTagFormatter' )
			->setConstructorArgs( array( $outputPage ) )
			->setMethods( array( 'canUseOutputPage' ) )
			->getMock();

		$outputPageTagFormatter->expects( $this->once() )
			->method( 'canUseOutputPage' )
			->will( $this->returnValue( true ) );

		$instance = new MetaTagsModifier(
			$propertyValuesContentFetcher,
			$outputPageTagFormatter
		);

		$instance->setMetaTagsStaticContentDescriptor( $contentDescriptor );
		$instance->addMetaTags();
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
