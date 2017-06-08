<?php

namespace SMT\Tests\Integration;

use SMW\Tests\MwDBaseUnitTestCase;
use SMW\Tests\Utils\UtilityFactory;
use SMT\HookRegistry;
use SMT\Options;
use SMW\DIWikiPage;

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
	private $pageDeleter;
	private $subjects = [];

	protected function setUp() {
		parent::setUp();

		$this->pageCreator = UtilityFactory::getInstance()->newpageCreator();
		$this->pageDeleter = UtilityFactory::getInstance()->newPageDeleter();

		$metaTagsBlacklist = [
			'robots'
		];

		$metaTagsContentPropertySelector = [
			'KEYwoRDS' => [ 'SMT keywords', 'SMT other keywords' ],
			'robots' => 'SMT keywords, SMT other keywords',
			'description' => '',
			'twitter:description' => 'SMT description',
			'og:title' => 'SMT title'
		];

		$metaTagsStaticContentDescriptor = [
			'static:TAG' => 'withStatic<Content>'
		];

		$configuration = [
			'metaTagsContentPropertySelector' => $metaTagsContentPropertySelector,
			'metaTagsStaticContentDescriptor' => $metaTagsStaticContentDescriptor,
			'metaTagsBlacklist' => $metaTagsBlacklist,
			'metaTagsFallbackUseForMultipleProperties' => false,
			'metaTagsMetaPropertyPrefixes' => [ 'og:' ]
		];

		$hookRegistry = new HookRegistry(
			$this->getStore(),
			new Options( $configuration )
		);

		$hookRegistry->register();
	}

	protected function tearDown() {
		$this->pageDeleter->doDeletePoolOfPages(
			$this->subjects
		);

		parent::tearDown();
	}

	public function testAddStandardMetaTag() {

		$requestContext = new \RequestContext();
		$outputPage = $requestContext->getOutput();

		if ( !method_exists( $outputPage, 'getMetaTags' ) ) {
			$this->markTestSkipped( 'OutputPage::getMetaTags does not exist for this MW version' );
		}

		$subject = new DIWikiPage( __METHOD__, NS_MAIN );
		$requestContext->setTitle( $subject->getTitle() );

		$this->pageCreator
			->createPage( $subject->getTitle() )
			->doEdit(
				'[[SMT keywords::KeywordMetaTag]]' .
				'[[SMT other keywords::AnotherKeywordMetaTag]]' .
				'[[SMT description::Example description]]' );

		$parserOutput = $this->pageCreator->getEditInfo()->output;

		$outputPage->addParserOutputMetadata( $parserOutput );

		$expected = [
			[ 'keywords', 'KeywordMetaTag,AnotherKeywordMetaTag' ],
			[ 'twitter:description', 'Example description' ],
			[ 'static:tag', 'withStatic&lt;Content&gt;' ]
		];

		$this->assertEquals(
			$expected,
			$outputPage->getMetaTags()
		);

		$this->assertFalse(
			$outputPage->hasHeadItem( 'meta:property:og:title' )
		);

		$this->subjects[] = $subject;
	}

	public function testAddOpenGraphMetaTag() {

		$requestContext = new \RequestContext();
		$outputPage = $requestContext->getOutput();

		if ( !method_exists( $outputPage, 'addParserOutputMetadata' ) ) {
			$this->markTestSkipped( 'OutputPage::addParserOutputMetadata does not exist for this MW version' );
		}

		$subject = new DIWikiPage( __METHOD__, NS_MAIN );
		$requestContext->setTitle( $subject->getTitle() );

		$this->pageCreator
			->createPage( $subject->getTitle() )
			->doEdit( '[[SMT title::OGTitleMetaTags]]' );

		// Force the FallbackSemanticDataFetcher to indirectly use the Store
		$outputPage->addParserOutputMetadata( new \ParserOutput() );

		$this->assertTrue(
			$outputPage->hasHeadItem( 'meta:property:og:title' )
		);

		$this->subjects[] = $subject;
	}

}
