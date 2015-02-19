<?php

namespace SMT\Tests;

use SMT\OutputPageTagFormatter;

/**
 * @covers \SMT\OutputPageTagFormatter
 *
 * @group semantic-meta-tags
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class OutputPageTagFormatterTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SMT\OutputPageTagFormatter',
			new OutputPageTagFormatter( $outputPage )
		);
	}

	public function testTryToUseOutputPage() {

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

		$instance = new OutputPageTagFormatter( $outputPage );

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

		$instance = new OutputPageTagFormatter( $outputPage );
		$instance->setMetaTagsBlacklist( array( 'foo' ) );

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

		$instance = new OutputPageTagFormatter( $outputPage );
		$instance->addTagContentToOutputPage( $tag, $content );
	}

	/**
	 * @dataProvider ogTagProvider
	 */
	public function testAddTagForOgContent( $tag, $content, $expected ) {

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->once() )
			->method( 'addHeadItem' )
			->with(
				$this->equalTo( $expected['tag'] ),
				$this->equalTo( $expected['item'] ) );

		$instance = new OutputPageTagFormatter( $outputPage );
		$instance->addTagContentToOutputPage( $tag, $content );
	}

	public function nonOgTagProvider() {

		$provider = array();

		$provider[] = array(
			'foo',
			'foobar',
			array( 'tag' => 'foo', 'content' => 'foobar' )
		);

		$provider[] = array(
			'FOO',
			'"foobar"',
			array( 'tag' => 'foo', 'content' => '&quot;foobar&quot;' )
		);

		$provider[] = array(
			' foo ',
			' foobar ',
			array( 'tag' => 'foo', 'content' => ' foobar ' )
		);

		$provider[] = array(
			'FO"O',
			'foobar',
			array( 'tag' => 'fo&quot;o', 'content' => 'foobar' )
		);

		$provider[] = array(
			'twitter:card',
			'FOO',
			array( 'tag' => 'twitter:card', 'content' => 'FOO' )
		);

		return $provider;
	}

	public function ogTagProvider() {

		$provider = array();

		$provider[] = array(
			'og:bar',
			'foobar',
			array(
				'tag' => 'meta:property:og:bar',
				'item' => '<!-- Open Graph protocol markup -->' . "\n" . '<meta property="og:bar" content="foobar" />'
			)
		);

		return $provider;
	}

}
