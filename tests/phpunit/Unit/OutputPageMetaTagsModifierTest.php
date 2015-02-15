<?php

namespace SMT\Tests;

use SMT\OutputPageMetaTagsModifier;

/**
 * @covers \SMT\OutputPageMetaTagsModifier
 *
 * @group semantic-meta-tags
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class OutputPageMetaTagsModifierTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$propertyValueContentFinder = $this->getMockBuilder( '\SMT\PropertyValueContentFinder' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SMT\OutputPageMetaTagsModifier',
			new OutputPageMetaTagsModifier( $propertyValueContentFinder )
		);
	}

	public function testTryToModifyOutputPageForInvalidTitle() {

		$propertyValueContentFinder = $this->getMockBuilder( '\SMT\PropertyValueContentFinder' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValueContentFinder->expects( $this->never() )
			->method( 'findContentForProperties' );

		$instance = new OutputPageMetaTagsModifier( $propertyValueContentFinder );
		$instance->setMetaTagsContentPropertySelector( array( 'foo' ) );

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->any() )
			->method( 'isSpecialPage' )
			->will( $this->returnValue( true ) );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->never() )
			->method( 'addMeta' );

		$outputPage->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$instance->modifyOutputPage( $outputPage );
	}

	/**
	 * @dataProvider invalidPropertySelectorProvider
	 */
	public function testTryToModifyOutputPageForInvalidPropertySelector( $propertySelector ) {

		$propertyValueContentFinder = $this->getMockBuilder( '\SMT\PropertyValueContentFinder' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValueContentFinder->expects( $this->never() )
			->method( 'findContentForProperties' );

		$instance = new OutputPageMetaTagsModifier( $propertyValueContentFinder );
		$instance->setMetaTagsContentPropertySelector( $propertySelector );

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->any() )
			->method( 'isSpecialPage' )
			->will( $this->returnValue( false ) );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->never() )
			->method( 'addMeta' );

		$outputPage->expects( $this->never() )
			->method( 'addHeadItem' );

		$outputPage->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$instance->modifyOutputPage( $outputPage );
	}

	/**
	 * @dataProvider validPropertySelectorProvider
	 */
	public function testModifyOutputPageForValidPropertySelector( $propertySelector, $properties, $expected ) {

		$propertyValueContentFinder = $this->getMockBuilder( '\SMT\PropertyValueContentFinder' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValueContentFinder->expects( $this->once() )
			->method( 'findContentForProperties' )
			->with( $this->equalTo( $properties ) )
			->will( $this->returnValue( $expected['content'] ) );

		$instance = new OutputPageMetaTagsModifier( $propertyValueContentFinder );
		$instance->setMetaTagsContentPropertySelector( $propertySelector );

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'isSpecialPage' )
			->will( $this->returnValue( false ) );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->once() )
			->method( 'addMeta' )
			->with(
				$this->equalTo( $expected['tag'] ),
				$this->equalTo( $expected['content'] ) );

		$outputPage->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$instance->modifyOutputPage( $outputPage );
	}

	/**
	 * @dataProvider validOGPropertySelectorProvider
	 */
	public function testModifyOutputPageForValidOGPropertySelector( $propertySelector, $properties, $expected ) {

		$propertyValueContentFinder = $this->getMockBuilder( '\SMT\PropertyValueContentFinder' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValueContentFinder->expects( $this->once() )
			->method( 'findContentForProperties' )
			->with( $this->equalTo( $properties ) )
			->will( $this->returnValue( $expected['content'] ) );

		$instance = new OutputPageMetaTagsModifier( $propertyValueContentFinder );
		$instance->setMetaTagsContentPropertySelector( $propertySelector );

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'isSpecialPage' )
			->will( $this->returnValue( false ) );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->once() )
			->method( 'addHeadItem' )
			->with(
				$this->equalTo( $expected['tag'] ),
				$this->equalTo( $expected['item'] ) );

		$outputPage->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$instance->modifyOutputPage( $outputPage );
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
			array( 'foo' => 'foobar,quin' ),
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

	public function validOGPropertySelectorProvider() {

		$provider = array();

		$provider[] = array(
			array( 'foo:bar' => 'foobar' ),
			array( 'foobar' ),
			array(
				'tag' => 'meta:property:foo:bar',
				'content' => 'Mo,fo',
				'item' => '<!-- Open Graph protocol markup -->' . "\n" . '<meta property="foo:bar" content="Mo,fo" />'
			)
		);

		return $provider;
	}

}
