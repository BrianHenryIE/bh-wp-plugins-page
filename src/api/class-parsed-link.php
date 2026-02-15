<?php
/**
 * Class with utility functions for each action and meta link.
 *
 * @package brianhenryie/bh-wp-plugins-page
 */

namespace BrianHenryIE\WP_Plugins_Page\API;

use DOMDocument;
use DOMElement;
use DOMNamedNodeMap;
use DOMNode;
use DOMNodeList;

/**
 * Uses DOMDocument to extract links from the text, the bare text, and provides utility functions for classifying the
 * text content.
 */
class Parsed_Link {

	/**
	 * The link's original array key.
	 *
	 * @var string|null
	 */
	protected ?string $key = null;

	/**
	 * The parsed HTML. This may be updated from the original.
	 *
	 * @var DOMDocument
	 */
	protected DOMDocument $dom_document;

	/**
	 * The bare text from the HTML.
	 *
	 * @var string
	 */
	protected string $text = '';

	/**
	 * Indicator if the HTML is only one link, or has additional text or links too.
	 *
	 * @var bool
	 */
	protected ?bool $is_only_link = null;

	/**
	 * All the HTML anchor elements found in the string.
	 *
	 * @var array<int,DOMElement> $anchors
	 */
	protected array $anchors = array();

	/**
	 * All URLs found in the HTML string.
	 *
	 * @var array<int,string>
	 */
	protected array $urls = array();

	/**
	 * A representation of the HTML in a plugins.php meta or action link.
	 *
	 * @param int|string $key The original array key.
	 * @param string     $original The HTML string.
	 */
	public function __construct(
		$key,
		protected string $original
	) {
		if ( is_string( $key ) ) {
			$this->key = $key;
		}

		$this->parse_html_string( $this->original );
	}


	/**
	 * Pick out the text, the links, and determine if this link contains only an anchor element.
	 *
	 * @param string $html_string A HTML string we want to analyse.
	 *
	 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	 * phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
	 */
	protected function parse_html_string( string $html_string ): void {

		if ( empty( $html_string ) ) {
			return;
		}

		$dom_document                   = new DOMDocument();
		$previous_internal_errors_value = libxml_use_internal_errors( true );
		$bool_result                    = @$dom_document->loadHTML( $html_string );
		libxml_use_internal_errors( $previous_internal_errors_value );

		if ( false === $bool_result ) {
			return;
		}

		$this->dom_document = $dom_document;

		$this->text = $dom_document->textContent;

		$html_tag = $dom_document->firstElementChild;
		$body_tag = $html_tag?->firstElementChild;

		$body_nodes_count = count( $body_tag->childNodes ?? array() );
		$is_anchor        = 'a' === $body_tag?->firstElementChild?->tagName;

		$this->is_only_link = ( 1 === $body_nodes_count ) && $is_anchor;

		$a_tags     = $dom_document->getElementsByTagName( 'a' );
		$num_a_tags = count( $a_tags );

		for ( $item_index = 0; $item_index < $num_a_tags; $item_index++ ) {

			/**
			 * We know this will not be null because we counted them just above.
			 *
			 * @var DOMElement $anchor_node
			 */
			$anchor_node = $a_tags->item( $item_index );

			$this->anchors[ $item_index ] = $anchor_node;

			if (
				! ( $anchor_node->attributes instanceof DOMNamedNodeMap ) /** @phpstan-ignore instanceof.alwaysTrue */
				|| is_null( $anchor_node->attributes->getNamedItem( 'href' ) )
			) {
				continue;
			}

			$url_string = $anchor_node->attributes->getNamedItem( 'href' )->nodeValue;

			if ( ! empty( $url_string ) ) {
				$this->urls[ $item_index ] = $url_string;
			}
		}

		$script_tags     = $dom_document->getElementsByTagName( 'script' );
		$num_script_tags = count( $script_tags );

		for ( $item_index = 0; $item_index < $num_script_tags; $item_index++ ) {

			/**
			 * We know this will not be null because we counted them just above.
			 *
			 * @var DOMElement $script_node
			 */
			$script_node = $script_tags->item( $item_index );

			// We empty the script tag contents here and wp_kses() will remove the tag itself later.
			$script_node->nodeValue = '';
		}
	}

	/**
	 * Checks all URLs in this link to see are any links to pages inside this site.
	 *
	 * NB: Has internal is not the inverse of has external since some have no link at all.
	 *
	 * @return bool
	 */
	public function has_internal_url(): bool {

		if ( 0 === count( $this->urls ) ) {
			return false;
		}

		foreach ( $this->urls as $url ) {
			if ( ! $this->is_external_url( $url ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks all URLs in this link to see are any linking away from this site.
	 *
	 * @return bool
	 */
	public function has_external_url(): bool {

		if ( 0 === count( $this->urls ) ) {
			return false;
		}

		foreach ( $this->urls as $url ) {
			if ( $this->is_external_url( $url ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks a bare url to see does it contain "http" and a domain other than this site's domain.
	 *
	 * @param string $url The URL to check.
	 * @return bool
	 */
	protected function is_external_url( string $url ): bool {

		$is_external_link = ! is_null( wp_parse_url( $url, PHP_URL_SCHEME ) )
							&& ! stristr( $url, (string) get_site_url() );

		return $is_external_link;
	}

	/**
	 * Checks if the link contains no text at all.
	 *
	 * @return bool
	 */
	public function is_empty(): bool {
		return empty( wp_strip_all_tags( $this->original ) );
	}

	/**
	 * Used to filter to remove upsells and marketing links.
	 * Removes external "pro" and licence links.
	 *
	 * "Donate" links are not removed.
	 *
	 * @return bool True if the link should remain, false to remove.
	 */
	public function is_contains_unwanted_terms(): bool {

		if ( empty( $this->text ) ) {
			return false;
		}

		$definitely_unwanted_terms = array(
			'opt in',
			'opt-in',
			'add on',
			'add-on',
			'free',
			'upgrade',
			'trial',
			'review',
			'rate',
			'Uninstall',
		);

		foreach ( $definitely_unwanted_terms as $term ) {
			if ( stristr( $this->text, $term ) ) {
				return true;
			}
		}

		// These terms are acceptable for internal links, but not for external links.
		$probably_unwanted_terms = array(
			'pro',
			'premium',
			'licence',
			'license',
		);

		foreach ( $probably_unwanted_terms as $term ) {

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( preg_match( '/\b' . $term . '\b/i', $this->text ) && $this->has_external_url() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Run wp_kses to strip unwanted styles etc. from links.
	 * Removes HTML CSS `class` element on Deactivate links.
	 * Returns "View details" links untouched.
	 *
	 * @see wp_kses()
	 *
	 * TODO: Are there CSS classes that need to be removed still? YES!
	 */
	public function get_cleaned_link(): string {

		$allowed_html = array(
			'a' => array(
				'href'       => array(),
				'target'     => array(),
				'class'      => array(),
				'aria-label' => array(),
				'title'      => array(),
				'data-title' => array(),
			),
		);

		switch ( $this->get_type() ) {
			case 'view-details':
				return $this->original;
			case 'deactivate':
				unset( $allowed_html['a']['class'] );
		}

		$unclean = '';
		if ( isset( $this->dom_document ) ) {
			/** @var ?DOMElement $anchor <html> -> <body> -> <a>. */
			$anchor  = $this->dom_document->firstElementChild?->firstElementChild?->firstElementChild;
			$unclean = $this->dom_document->saveHTML( $anchor );
		}

		if ( empty( $unclean ) ) {
			$unclean = $this->original;
		}

		$cleaned_html_string = wp_kses( $unclean, $allowed_html );

		return trim( $cleaned_html_string );
	}

	/**
	 * Some links are special cases.
	 * E.g. "View details" is a special case where we want to keep the internal URL in the middle column.
	 * The link type is used for sorting in the first column (settings first, deactivate last...).
	 * Default type is "links".
	 *
	 * @return string
	 */
	public function get_type(): string {

		$types                = array( 'View details', 'settings', 'log', 'deactivate', 'github' );
		$types['author-link'] = 'By ';
		foreach ( $types as $key => $type ) {
			if ( false !== stristr( $this->text, $type ) ) {
				return is_string( $key ) ? $key : sanitize_title( $type );
			}
		}

		return 'links';
	}

	/**
	 * Looks for GitHub links and replaces them with the GitHub icon.
	 *
	 * Adds the original text as a mouseover hint.
	 *
	 * TODO: Pulling the icon straight from GitHub probably isn't best practice.
	 * TODO: WordPress.org links with WordPress icon?
	 *
	 * Edits the DOMDocument in place.
	 */
	public function replace_text_with_icons(): void {

		// Match github.com / (something other than "sponsors" ) / (anything up to maybe a final / ).
		$match_github_repo_links = '/^https?:\/\/github.com\/(?!sponsors)[^\/]*\/[^\/]*[^\/]?$/i';

		foreach ( $this->anchors as $anchor_node ) {
			if (
				! ( $anchor_node->attributes instanceof DOMNamedNodeMap ) /** @phpstan-ignore instanceof.alwaysTrue */
				|| is_null( $anchor_node->attributes->getNamedItem( 'href' ) )
			) {
				continue;
			}

			$url_string = $anchor_node->attributes->getNamedItem( 'href' )->nodeValue;

			if ( 1 !== preg_match( $match_github_repo_links, $url_string ?? '' ) ) {
				continue;
			}

			$old_text = $anchor_node->nodeValue;

			$anchor_node->setAttribute( 'class', 'bh-wp-plugins-page-github-icon' );
			if ( ! is_null( $old_text ) ) {
				$anchor_node->setAttribute( 'title', $old_text );
			}

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$anchor_node->nodeValue = '';
		}
	}
}
