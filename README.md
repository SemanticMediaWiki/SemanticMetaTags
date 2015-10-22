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
with content generated from selected properties to create:

- Standard tags (e.g `meta name="keywords"`) as well as
- [Summary card][tw] and [Open Graph][opg] protocol tags (e.g `meta property="og:title"`)

## Requirements

- PHP 5.3.2 or later
- MediaWiki 1.23 or later
- [Semantic MediaWiki][smw] 2.2 or later

## Installation

The recommended way to install Semantic Meta Tags is by using [Composer][composer] with an entry in MediaWiki's `composer.json`.

```json
{
	"require": {
		"mediawiki/semantic-meta-tags": "~1.2"
	}
}
```
1. From your MediaWiki installation directory, execute
   `composer require mediawiki/semantic-meta-tags:~1.2`
2. Navigate to _Special:Version_ on your wiki and verify that the package
   have been successfully installed.

## Usage

In order to generate customized `<meta>` tags, property assignments have
to be added to `$GLOBALS['smtgTagsProperties']` (no assigments = no additional
`<meta>` tags).

`<meta>` tags are mapped (by name) to properties. In case you want to generate
multiple values from different properties to the same `<meta>` tag then separate
those property assigments by comma.

If a tag contains a `og:` it is identified as an [Open Graph][opg] `<meta>` tag
and annotated using the `meta property=""` description.

### Output example

![image](https://cloud.githubusercontent.com/assets/1245473/7828511/b9cf5a2a-0434-11e5-8aa6-33ee8189f44b.png)

## Configuration

- `$GLOBALS['smtgTagsProperties']` array of tag, property assignments. If a given
  property has multiple values (including subobjects) on a wiki page, the values
  are concatenated into a single string separated by commas.
- `$GLOBALS['smtgTagsPropertyFallbackUsage']` in case it is set `true` then the
  first property that returns a valid content for an assigned tag will be used
  exclusively.
- `$GLOBALS['smtgTagsStrings']` can be used to describe static content for an assigned `<meta>` tag
- Tags specified in `$GLOBALS['smtgTagsBlacklist']` are generally disabled for free assignments

### Example settings

```php
$GLOBALS['smtgTagsProperties'] = array(

	// Standard meta tags
	'keywords' => array( 'Has keywords', 'Has another keyword' ),
	'description' => 'Has some description',
	'author' => 'Has last editor',

	// Summary card tag
	'twitter:description' => 'Has some description',

	// Open Graph protocol supported tag
	'og:title' => 'Has title'
);

$GLOBALS['smtgTagsStrings'] = array(

	// Static content tag
	'some:tag' => 'Content that is static'
);
```

## Contribution and support

If you want to contribute work to the project please subscribe to the developers mailing list and
have a look at the contribution guideline.

* [File an issue](https://github.com/SemanticMediaWiki/SemanticMetaTags/issues)
* [Submit a pull request](https://github.com/SemanticMediaWiki/SemanticMetaTags/pulls)
* Ask a question on [the mailing list](https://semantic-mediawiki.org/wiki/Mailing_list)
* Ask a question on the #semantic-mediawiki IRC channel on Freenode.

## Tests

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
[tw]: https://dev.twitter.com/cards/types/summary
