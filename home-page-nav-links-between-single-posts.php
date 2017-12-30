<?php

/**
 * Plugin Name: Home Page Nav Links between Single Posts
 * Description: Makes nav links on single post pages to previous and next posts point to next-recent posts from ALL custom post types, not only within the same post type, and only does this when the single post pages are accessed from the home page, not custom post type archive page.
 * Author:      Hoday
 * Version:     0.0.0
 */

// this is necessary to make next/prev links on the single post pages point to all post types.
// it works by modifying the sql query through a regex map to include all post types in the WHERE clause.
function get_adj_post_where_callback($where_clause) {
	global $wp_query;
	
	$old_where_clause = $where_clause;
	// now look at wp_query variables to determine if this a single page nav link for the home page
	if ($wp_query->is_single && (array_key_exists('all_post_types', $wp_query->query) && $wp_query->query_vars['all_post_types'] == 1)) {
		$result = preg_match("/(.*) p.post_type = '(post|envira|tribe_events)' (.*)/", $where_clause, $matches);	
		$where_clause = $matches[1]." (p.post_type = 'post' OR p.post_type = 'envira' OR p.post_type = 'tribe_events') ".$matches[3];
	}
	return $where_clause;
}	
add_filter( 'get_next_post_where', 'get_adj_post_where_callback' );
add_filter( 'get_previous_post_where', 'get_adj_post_where_callback' );

// add the variable "all_post_types" to the query variable array
// so that wordpress will be able to parse value of "all_post_types" into this variable when it sees it in a url
function query_vars_callback_add_queryvar($qvars) {
  $qvars[] = 'all_post_types';
  return $qvars;
}
add_filter('query_vars', 'query_vars_callback_add_queryvar' );


// makes wordpress able to recognize "all-all_post_types-types" variable in url query
// and make it add it to the query variable array
function init_callback_add_custom_rewrite_rule() {
  //add_rewrite_rule('^nutrition/([^/]*)/([^/]*)/?','index.php?page_id=12&all_post_types=$matches[1]&$matches[2]','top');
  add_rewrite_tag('%all_post_types%', '([^&]+)');
}
add_action('init', 'init_callback_add_custom_rewrite_rule', 10, 0);



// this is needed to add a query variable to permalinks to both posts and custom post type posts
// so that wordpress will know whether we are on single page accessed from the home page
// or custom post type archive.
function append_query_string( $url, $post ) {
	
	global $wp_query;
	
	if ( is_home() || (array_key_exists('all_post_types', $wp_query->query) && $wp_query->query['all_post_types'] == 1)) {
		$url = add_query_arg( 'all_post_types', '1', $url );
	}
	return $url;
}
add_filter( 'post_type_link', 'append_query_string', 10, 2 );
add_filter( 'post_link', 'append_query_string', 10, 2 );


