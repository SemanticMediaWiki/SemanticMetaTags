This file contains the RELEASE-NOTES of the **Semantic Meta Tags** (a.k.a. SMT) extension.

### Semantic Compound Queries 2.0.0

Released on January 29, 2019.

* Minimum requirement for Semantic MediaWiki changed to version 3.0 and later
* #39 Adds support for extension registration via "extension.json"  
  → Now you have to use `wfLoadExtension( 'SemanticMetaTags' );` in the "LocalSettings.php" file to invoke the extension
* Localization updates from https://translatewiki.net

### 1.5.0

Released on October 9, 2018.

* Minimum requirement for
  * PHP changed to version 5.6 and later
  * Semantic MediaWiki changed to version 2.5 and later
* Minor clean-up
* Localization updates from https://translatewiki.net

### 1.4.0

Released on June 7, 2017.

* Minimum requirement for
  * PHP changed to version 5.5 and later
  * MediaWiki changed to version 1.27 and later
  * Semantic MediaWiki changed to version 2.4 and later
* `$smtgMetaPropertyPrefixes` to set which prefixes to meta elements should result in rendering as properties rather than names
* Localization updates from https://translatewiki.net

### 1.3.0

Released on July 9, 2016.

* Minor clean-up
* Localization updates from https://translatewiki.net

### 1.2.0

Released on December 19, 2015.

* Minimum requirement for Semantic MediaWiki changed to version 2.2 and later
* Minor clean-up
* Changed value aggregation so that DI values for the same property only appear once
* Localization updates from https://translatewiki.net

### 1.1.0

Released on June 2, 2015.

* Minor clean-up
* Localization updates from https://translatewiki.net

### 1.0.0

Released on February 28, 2015.

* Initial release
* Requires PHP 5.3 or later
* Requires MediaWiki 1.23 or later
* Requires Semantic MediaWiki 2.1 or later
* `$smtgTagsProperties` to set which meta elements should be enabled
* `$smtgTagsPropertyFallbackUsage` to set whether the first property that returns
   a valid content for an assigned meta element will be used exclusively
* `$smtgTagsStrings` to describe static content for an assigned meta element
* `$smtgTagsBlacklist` to generally disable certain meta elements for free assignments
