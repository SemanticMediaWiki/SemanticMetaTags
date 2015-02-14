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
			'author' => ''
		);

		$configuration = array(
			'metaTagsContentPropertySelector' => $metaTagsContentPropertySelector
		);

		$hookRegistry = new HookRegistry( $configuration );
		$hookRegistry->register( $GLOBALS['wgHooks'] );
	}

	protected function tearDown() {

		UtilityFactory::getInstance()
			->newPageDeleter()
			->doDeletePoolOfPages( $this->subjects );

		parent::tearDown();
	}

	public function testFindMetaTags() {

		$requestContext = new \RequestContext();
		$outputPage = $requestContext->getOutput();

		if ( !method_exists( $outputPage, 'getMetaTags' ) ) {
			$this->markTestSkipped( 'OutputPage::getMetaTags does not exist for the MW version' );
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

		$this->subjects = array( $subject );
	}

}
