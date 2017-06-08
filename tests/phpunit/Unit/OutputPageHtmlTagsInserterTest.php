<?php

namespace SMT\Tests;

use SMT\OutputPageHtmlTagsInserter;

/**
 * @covers \SMT\OutputPageHtmlTagsInserter
 * @group semantic-meta-tags
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class OutputPageHtmlTagsInserterTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SMT\OutputPageHtmlTagsInserter',
			new OutputPageHtmlTagsInserter( $outputPage )
		);
	}

	public function testTryToUseOutputPageForSpecialPage() {

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

		$outputPage->expects( $this->atLeastOnce() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$instance = new OutputPageHtmlTagsInserter( $outputPage );

		$this->assertFalse(
			$instance->canUseOutputPage()
		);
	}

	public function testTryToUseOutputPageForNonViewAction() {

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

		$outputPage->expects( $this->atLeastOnce() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$instance = new OutputPageHtmlTagsInserter( $outputPage );
		$instance->setActionName( 'foo' );

		$this->assertFalse(
			$instance->canUseOutputPage()
		);
	}

	public function testTryToAddContentForBlacklistedTag() {

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->never() )
			->method( 'addMeta' );

		$instance = new OutputPageHtmlTagsInserter( $outputPage );
		$instance->setMetaTagsBlacklist( [ 'foo' ] );

		$instance->addTagContentToOutputPage( 'FOO', 'bar' );
	}

	/**
	 * @dataProvider nonOgTagProvider
	 */
	public function testAddTagForNonOgContent( $tag, $content, $expected ) {

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->once() )
			->method( 'addMeta' )
			->with(
				$this->equalTo( $expected['tag'] ),
				$this->equalTo( $expected['content'] ) );

		$instance = new OutputPageHtmlTagsInserter( $outputPage );
		$instance->addTagContentToOutputPage( $tag, $content );
	}

	/**
	 * @dataProvider propertyTagProvider
	 */
	public function testAddTagOnMetaPropertyPrefixContent( $prefixes, $tag, $content, $expected ) {

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->once() )
			->method( 'addHeadItem' )
			->with(
				$this->equalTo( $expected['tag'] ),
				$this->stringContains( $expected['item'] ) );

		$instance = new OutputPageHtmlTagsInserter( $outputPage );

		$instance->setMetaPropertyPrefixes(
			$prefixes
		);

		$instance->addTagContentToOutputPage( $tag, $content );
	}

	public function nonOgTagProvider() {

		$provider = [];

		$provider[] = [
			'foo',
			'foobar',
			[ 'tag' => 'foo', 'content' => 'foobar' ]
		];

		$provider[] = [
			'FOO',
			'"foobar"',
			[ 'tag' => 'foo', 'content' => '&quot;foobar&quot;' ]
		];

		$provider[] = [
			' foo ',
			' foobar ',
			[ 'tag' => 'foo', 'content' => ' foobar ' ]
		];

		$provider[] = [
			'FO"O',
			'foobar',
			[ 'tag' => 'fo&quot;o', 'content' => 'foobar' ]
		];

		$provider[] = [
			'twitter:card',
			'FOO',
			[ 'tag' => 'twitter:card', 'content' => 'FOO' ]
		];

		return $provider;
	}

	public function propertyTagProvider() {

		$provider = [];

		$provider[] = [
			[
				'og:'
			],
			'og:bar',
			'foobar',
			[
				'tag' => 'meta:property:og:bar',
				'item' => '<!-- Semantic MetaTags -->' . "\n" . '<meta property="og:bar" content="foobar"'
			]
		];

		return $provider;
	}

}
