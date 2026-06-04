<?php

namespace SMT;

use SMW\Services\ServicesFactory as ApplicationFactory;

/**
 * @license GPL-2.0-or-later
 * @since 7.0
 *
 * @author mwjames
 */
class Setup {

	/**
	 * @since 7.0
	 */
	public static function onExtensionFunction() {
		$configuration = [
			'metaTagsContentPropertySelector' => $GLOBALS['smtgTagsProperties'],
			'metaTagsStaticContentDescriptor' => $GLOBALS['smtgTagsStrings'],
			'metaTagsBlacklist' => $GLOBALS['smtgTagsBlacklist'],
			'metaTagsFallbackUseForMultipleProperties' => $GLOBALS['smtgTagsPropertyFallbackUsage'],
			'metaTagsMetaPropertyPrefixes' => $GLOBALS['smtgMetaPropertyPrefixes']
		];

		$hookRegistry = new HookRegistry(
			ApplicationFactory::getInstance()->getStore(),
			new Options( $configuration )
		);

		$hookRegistry->register();
	}

}
