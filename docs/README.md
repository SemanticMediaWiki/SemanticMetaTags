## Usage

![image](https://cloud.githubusercontent.com/assets/1245473/16200642/73ddec3a-370e-11e6-938b-4e952077d0c4.png)

In order to generate customized `<meta>` tags, property assignments have
to be added to `$GLOBALS['smtgTagsProperties']` (no assigments = no additional
`<meta>` tags).

`<meta>` tags are mapped (by name) to properties. In case you want to generate
multiple values from different properties to the same `<meta>` tag then separate
those property assigments by comma.

If a tag contains a `og:` it is identified as an [Open Graph][opg] `<meta>` tag
and annotated using the `meta property=""` description.

## Configuration

* `$GLOBALS['smtgTagsProperties']` array of "tag => property" or "tag => callback"
  assignments. If a given property has multiple values (including subobjects)
  on a wiki page, the values are concatenated into a single string separated
  by commas.
	* callback is a function that ought to have the following signature:
	  `function( OutputPage $outputPage ): string`. This is the place to assign
	  MediaWiki system messages, page properties like its title or plain strings to tags
* `$GLOBALS['smtgTagsPropertyFallbackUsage']` in case it is set `true` then the
  first property that returns a valid content for an assigned tag will be used
  exclusively.
* `$GLOBALS['smtgTagsStrings']` can be used to describe static content for an 
  assigned `<meta>` tag.
* Tags specified in `$GLOBALS['smtgTagsBlacklist']` are generally disabled for
  free assignments.
* `$GLOBALS['smtgMetaPropertyPrefixes']` to set which prefixes to meta elements
  should result in rendering as properties rather than names. For example, all
  Open Graph (Facebook) meta tags should render as properties.

### Example settings

#### Simple settings
Best for wikis using templates on all content pages
```php
$GLOBALS['smtgTagsProperties'] = [

	// Standard meta tags
	'keywords' => [
		'Has keywords', 'Has another keyword'
	],
	'description' => 'Has some description',
	'author' => 'Has last editor',

	// Summary card tag
	'twitter:description' => 'Has some description',

	// Open Graph protocol supported tag
	'og:title' => 'Has title'
];

$GLOBALS['smtgTagsStrings'] = [

	// Static content tag
	'some:tag' => 'Content that is static'
];

$GLOBALS['smtgMetaPropertyPrefixes'] = [ 

	// Open Graph prefixes
	'og:',
	'fb:'
];
```

#### Advanced settings
Best for wikis not using templates on all content pages
```php
$GLOBALS['smtgTagsProperties'] = [

	// Standard meta tags
	'keywords' => [
		'Has keywords',
		'Has another keyword',
		function( OutputPage $outputPage ): string {
			// Redirects to the page are collected as keywords.
			$redirects = $outputPage->getContext()->getTitle()->getRedirectsHere();
			$redirect_titles = [];
			foreach ( $redirects as $redirect ) {
				$redirect_titles[] = $redirect->getText();
			}
			return implode( ', ', array_unique( $redirect_titles ));
		}
	],
	'description' => [
		'Has some description',
		function( OutputPage $outputPage ): string {
			/** 
			 * This example generates a fallback description for a page:
			 * - for the main page, the site name from the message "MediaWiki:Pagetitle-view-mainpage",
			 * - for other pages, page title, followed by subtitle from "MediaWiki:Tagline".
			 * The language specified, e.g. 'ru' should match the language of your wiki's language
			 */
			global $wgLanguageCode;
			$title = $outputPage->getContext()->getTitle()->getText();
			$main_page = wfMessage( 'mainpage' )->inLanguage( $wgLanguageCode ?: 'ru' )->escaped();
			$site_name = wfMessage( 'pagetitle-view-mainpage' )->inLanguage( $wgLanguageCode ?: 'ru' )->escaped();
			$subtitle = $title !== $main_page ? wfMessage( 'tagline' )->inLanguage( $wgLanguageCode ?: 'ru' )->escaped() : '';
			return $title === $main_page ? $site_name : $title . '. ' . $subtitle;
	}],
	'author' => 'Has last editor',

	// Summary card tag
	'twitter:description' => 'Has some description',

	// Open Graph protocol supported tag
	'og:title' => 'Has title'
];

$GLOBALS['smtgTagsPropertyFallbackUsage'] = true;

$GLOBALS['smtgTagsStrings'] = [

	// Static content tag
	'some:tag' => 'Content that is static'
];

$GLOBALS['smtgMetaPropertyPrefixes'] = [ 

	// Open Graph prefixes
	'og:',
	'fb:'
];
```
