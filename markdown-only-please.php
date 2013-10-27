<?php
/*
 * Plugin Name: Markdown Only, Please!
 * Plugin URI: https://github.com/keichan34/markdown-only-please
 * Author: Keitaroh Kobayashi
 * Version: 1.0
 * Description: Forces your content to be written in all Markdown.
 * License: MIT
 */

if (!class_exists('Markdown')) {
  require_once (dirname(__FILE__) . '/lib/Michelf/Markdown.php');
}

class MarkdownOnlyPlease {
  function __construct() {
    add_action('init', array($this, 'init'));
  }

  public function init() {
    global $wp_filter;

    // Remove `wpautop`, because Markdown will be doing stuff for us there.
    remove_filter( 'the_content', 'wpautop' );
    remove_filter( 'the_excerpt', 'wpautop' );

    // Add Markdown processing
    add_filter( 'the_content', array($this, 'process_markdown'), 1, 1 );
    add_filter( 'the_excerpt', array($this, 'process_markdown'), 1, 1 );

    // Remove the visual editor
    add_filter( 'user_can_richedit', '__return_false', 99 );
  }

  public function process_markdown( $content ) {
    global $post;
    $found = false;
    $cache_key = 'post-' . $post->ID . '-' . $post->post_modified_gmt;
    $out = wp_cache_get($cache_key, 'MarkdownOnlyPlease', false, $found);
    if (!$found) {
      $out = Markdown::defaultTransform($content);

      // 86400 seconds = 24 hours
      wp_cache_set($cache_key, $out, 'MarkdownOnlyPlease', 86400);
    }

    return $out;
  }
}

new MarkdownOnlyPlease();
