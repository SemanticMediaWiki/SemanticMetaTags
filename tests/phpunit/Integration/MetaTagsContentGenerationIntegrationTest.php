<?php

namespace SMT\Tests\Integration;

use SMW\Tests\MwDBaseUnitTestCase;
use SMW\Tests\Utils\UtilityFactory;
use SMT\HookRegistry;
use SMW\DIWikiPage;
use Title;

/**
 * @group semantic-meta-tags
 * @group medium
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class MetaTagsContentGenerationIntegrationTest extends MwDBaseUnitTestCase {

	private $pageCreator;
	private $subjects = array();

	protected function setUp() {
		parent::setUp();

		$this->pageCreator = UtilityFactory::getInstance()->newpageCreator();

		$metaTagsContentPropertySelector = array(
			'keywords' => 'SMT keywords',
			'descriptions' => '',
			'og:title' => 'SMT title'
		);

		$configuration = array(
			'metaTagsContentPropertySelector' => $metaTagsContentPropertySelector
		);

		$hookRegistry = new HookRegistry( $configuration );
		$hookRegistry->register();

		// Deregister all hooks to ensure that only the one that is ought to be
		// tested is tested
		$hookRegistry->deregister();
		$hookRegistry->register();
	}

	protected function tearDown() {

		UtilityFactory::getInstance()
			->newPageDeleter()
			->doDeletePoolOfPages( $this->subjects );

		parent::tearDown();
	}

	public function testAddStandardMetaTag() {

		$requestContext = new \RequestContext();
		$outputPage = $requestContext->getOutput();

		if ( !method_exists( $outputPage, 'getMetaTags' ) ) {
			$this->markTestSkipped( 'OutputPage::getMetaTags does not exist for this MW version' );
		}

		$subject = DIWikiPage::newFromTitle( Title::newFromText( __METHOD__ ) );
		$requestContext->setTitle( $subject->getTitle() );

		$this->pageCreator
			->createPage( $subject->getTitle() )
			->doEdit( '[[SMT keywords::KeywordMetaTags]]' );

		$parserOutput = $this->pageCreator->getEditInfo()->output;

		$outputPage->addParserOutputMetadata( $parserOutput );

		$this->assertEquals(
			array( array( 'keywords', 'KeywordMetaTags' ) ),
			$outputPage->getMetaTags()
		);

		$this->assertFalse(
			$outputPage->hasHeadItem( 'meta:property:og:title' )
		);

		$this->subjects = array( $subject );
	}

	public function testAddOpenGraphMetaTag() {

		$requestContext = new \RequestContext();
		$outputPage = $requestContext->getOutput();

		if ( !method_exists( $outputPage, 'addParserOutputMetadata' ) ) {
			$this->markTestSkipped( 'OutputPage::addParserOutputMetadata does not exist for this MW version' );
		}

		$subject = DIWikiPage::newFromTitle( Title::newFromText( __METHOD__ ) );
		$requestContext->setTitle( $subject->getTitle() );

		$this->pageCreator
			->createPage( $subject->getTitle() )
			->doEdit( '[[SMT title::OGTitleMetaTags]]' );

		$parserOutput = $this->pageCreator->getEditInfo()->output;

		$outputPage->addParserOutputMetadata( $parserOutput );

		$this->assertTrue(
			$outputPage->hasHeadItem( 'meta:property:og:title' )
		);

		$this->subjects = array( $subject );
	}

}
