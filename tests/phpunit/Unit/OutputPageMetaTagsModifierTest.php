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

	/**
	 * @dataProvider invalidPropertySelectorProvider
	 */
	public function testModifyOutputPageForInvalidPropertySelector( $propertySelector ) {

		$propertyValueContentFinder = $this->getMockBuilder( '\SMT\PropertyValueContentFinder' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValueContentFinder->expects( $this->never() )
			->method( 'findContentForProperties' );

		$instance = new OutputPageMetaTagsModifier( $propertyValueContentFinder );
		$instance->setMetaTagsContentPropertySelector( $propertySelector );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->never() )
			->method( 'addMeta' );

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

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->once() )
			->method( 'addMeta' )
			->with(
				$this->equalTo( $expected['tag'] ),
				$this->equalTo( $expected['content'] ) );

		$instance->modifyOutputPage( $outputPage );
	}

	public function invalidPropertySelectorProvider() {

		$provider[] = array(
			array()
		);

		$provider[] = array(
			array( 'foo' => '' )
		);

		return $provider;
	}

	public function validPropertySelectorProvider() {

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

}
