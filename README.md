# Semantic Meta Tags

[![Build Status](https://secure.travis-ci.org/SemanticMediaWiki/SemanticMetaTags.svg?branch=master)](http://travis-ci.org/SemanticMediaWiki/SemanticMetaTags)
[![Code Coverage](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticMetaTags/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticMetaTags/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticMetaTags/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticMetaTags/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/semantic-meta-tags/version.png)](https://packagist.org/packages/mediawiki/semantic-meta-tags)
[![Packagist download count](https://poser.pugx.org/mediawiki/semantic-meta-tags/d/total.png)](https://packagist.org/packages/mediawiki/semantic-meta-tags)
[![Dependency Status](https://www.versioneye.com/php/mediawiki:semantic-meta-tags/badge.png)](https://www.versioneye.com/php/mediawiki:semantic-meta-tags)

Semantic Meta Tags (a.k.a. SMT) is a [Semantic Mediawiki][smw] extension to enhance
the meta tags of an article with content generated from semantic annotations.

This extension enables to automatically extend the HTML `<meta>` tags of an article
with content generated from selected properties with:
- Standard tags being supported (e.g `meta name="keywords" content=""`)
- [Open Graph][opg] tags are supported as well  (e.g `meta property="og:title"`)

## Requirements

- PHP 5.3.2 or later
- MediaWiki 1.23 or later
- [Semantic MediaWiki][smw] 2.1+

## Installation

The recommended way to install Semantic Meta Tags is by using [Composer][composer] with an entry in MediaWiki's `composer.json`.

```json
{
	"require": {
		"mediawiki/semantic-meta-tags": "~1.0"
	}
}
```
1. From your MediaWiki installation directory, execute
   `composer require mediawiki/semantic-meta-tags:~1.0`
2. Navigate to _Special:Version_ on your wiki and verify that the package
   have been successfully installed.

## Usage

SMT expects that selected tags and property assignments are added to the `egSMTMetaTagsContentPropertySelector` setting. In order for a tag to match different property assignments a comma-separator (`,`) can be used to add more than one property.

A tag that contains a `:` is identified as an [Open Graph][opg] metadata tag and annotated using `meta property="" content=""`.
  
```php
	$GLOBALS['egSMTMetaTagsContentPropertySelector'] = array(

		// Standard meta tags
		'keywords' => 'Has keywords, Has another keyword',
		'descriptions' => 'Has some description',
		'author' => 'Has last editor',

		// Open Graph protocol supported tags
		'og:title' => 'Has title'
	);
```

## Contribution and support

If you want to contribute work to the project please subscribe to the developers mailing list and
have a look at the contribution guideline.

* [File an issue](https://github.com/SemanticMediaWiki/SemanticMetaTags/issues)
* [Submit a pull request](https://github.com/SemanticMediaWiki/SemanticMetaTags/pulls)
* Ask a question on [the mailing list](https://semantic-mediawiki.org/wiki/Mailing_list)
* Ask a question on the #semantic-mediawiki IRC channel on Freenode.

### Tests

This extension provides unit and integration tests that are run by a [continues integration platform][travis]
but can also be executed using `composer phpunit` from the extension base directory.

## License

[GNU General Public License, version 2 or later][gpl-licence].

[smw]: https://github.com/SemanticMediaWiki/SemanticMediaWiki
[contributors]: https://github.com/SemanticMediaWiki/SemanticMetaTags/graphs/contributors
[travis]: https://travis-ci.org/SemanticMediaWiki/SemanticMetaTags
[gpl-licence]: https://www.gnu.org/copyleft/gpl.html
[composer]: https://getcomposer.org/
[opg]: http://ogp.me/
