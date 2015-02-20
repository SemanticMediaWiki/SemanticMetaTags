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

		$propertyValuesContentFinder = $this->getMockBuilder( '\SMT\PropertyValuesContentFinder' )
			->disableOriginalConstructor()
			->getMock();

		$outputPageTagFormatter = $this->getMockBuilder( '\SMT\OutputPageTagFormatter' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SMT\MetaTagsModifier',
			new MetaTagsModifier( $propertyValuesContentFinder, $outputPageTagFormatter )
		);
	}

	public function testTryToAddTags() {

		$propertyValuesContentFinder = $this->getMockBuilder( '\SMT\PropertyValuesContentFinder' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValuesContentFinder->expects( $this->never() )
			->method( 'findContentForProperties' );

		$outputPageTagFormatter = $this->getMockBuilder( '\SMT\OutputPageTagFormatter' )
			->disableOriginalConstructor()
			->getMock();

		$outputPageTagFormatter->expects( $this->once() )
			->method( 'canUseOutputPage' )
			->will( $this->returnValue( false ) );

		$instance = new MetaTagsModifier(
			$propertyValuesContentFinder,
			$outputPageTagFormatter
		);

		$instance->setMetaTagsContentPropertySelector( array( 'foo' ) );
		$instance->addMetaTags();
	}

	/**
	 * @dataProvider invalidPropertySelectorProvider
	 */
	public function testTryToModifyOutputPageForInvalidPropertySelector( $propertySelector ) {

		$propertyValuesContentFinder = $this->getMockBuilder( '\SMT\PropertyValuesContentFinder' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValuesContentFinder->expects( $this->never() )
			->method( 'findContentForProperties' );

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
			$propertyValuesContentFinder,
			$outputPageTagFormatter
		);

		$instance->setMetaTagsContentPropertySelector( $propertySelector );
		$instance->addMetaTags();
	}

	/**
	 * @dataProvider validPropertySelectorProvider
	 */
	public function testModifyOutputPageForValidPropertySelector( $propertySelector, $properties, $expected ) {

		$propertyValuesContentFinder = $this->getMockBuilder( '\SMT\PropertyValuesContentFinder' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValuesContentFinder->expects( $this->once() )
			->method( 'findContentForProperties' )
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
			$propertyValuesContentFinder,
			$outputPageTagFormatter
		);

		$instance->setMetaTagsContentPropertySelector( $propertySelector );
		$instance->addMetaTags();
	}

	public function testTryToModifyOutputPageForEmptyStaticContent() {

		$propertyValuesContentFinder = $this->getMockBuilder( '\SMT\PropertyValuesContentFinder' )
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
			$propertyValuesContentFinder,
			$outputPageTagFormatter
		);

		$instance->setMetaTagsStaticContentDescriptor( array( 'foo' => '' ) );
		$instance->addMetaTags();
	}

	/**
	 * @dataProvider staticContentProvider
	 */
	public function testModifyOutputPageForStaticContentDescriptor( $contentDescriptor, $expected ) {

		$propertyValuesContentFinder = $this->getMockBuilder( '\SMT\PropertyValuesContentFinder' )
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
			$propertyValuesContentFinder,
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

		return $provider;
	}

}
