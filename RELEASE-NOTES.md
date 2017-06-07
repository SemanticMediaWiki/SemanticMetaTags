This file contains the RELEASE-NOTES of the Semantic Meta Tags (a.k.a. SMT) extension.

### 1.4.0

Released on June 7, 2017.

* Requires PHP 5.5 or later
* Requires MediaWiki 1.27 or later
* Requires Semantic MediaWiki 2.4 or later
* `$smtgMetaPropertyPrefixes` to set which prefixes to meta elements should result in rendering as properties rather than names
* Localization updates from https://translatewiki.net

### 1.3.0

Released on July 9, 2016.

* Minor clean-up
* Localization updates from https://translatewiki.net

### 1.2.0

Released on December 19, 2015.

* Requires Semantic MediaWiki 2.2 or later
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
