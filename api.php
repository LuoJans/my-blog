<?php

//删除默认端点
	remove_action( 'rest_api_init', 'create_initial_rest_routes', 0 );

//重写wp-json
	add_filter( 'rest_url_prefix', function() {
	return '__jsonapi';
	});

//注册REST API路由
	add_action( 'rest_api_init', function () {

	register_rest_route( 'v2', '/items', array(
        'methods' => array('GET'),
        'callback' => 'get_items',
    	));
    register_rest_route( 'v2', '/(?P<id>\d+)/like', array(
        'callback' => 'get_like',
        'methods'  => array('POST'),
    ));
});

//文章列表
function get_items($response) {

    $paged = !empty($_GET["paged"]) ? $_GET["paged"] : null;
    $total = !empty($_GET["total"]) ? $_GET["total"] : null;
    $category = !empty($_GET["category"]) ? $_GET["category"] : null;
    $author = !empty($_GET["author"]) ? $_GET["author"] : null;
    $tag = !empty($_GET["tag"]) ? $_GET["tag"] : null;
    $search = !empty($_GET["search"]) ? $_GET["search"] : null;
    $year = !empty($_GET["year"]) ? $_GET["year"] : null;
    $month = !empty($_GET["month"]) ? $_GET["month"] : null;
    $day = !empty($_GET["day"]) ? $_GET["day"] : null;

    // Query Arguments
    $args = array(
        "posts_per_page" => get_option('posts_per_page'),
        "no_found_rows" => true,
        "cat" => $category,
        "tag" => $tag,
        "author" => $author,
        "post_status" => "publish",
        "post_type" => "post",
        "paged" => $paged,
        "s" => $search,
        "year" => $year,
        "monthnum" => $month,
        'orderby' => 'date',
        "day" => $day
    );

    // Run WP_Query()
    $api = new WP_Query( $args );
    while ( $api->have_posts() ) : $api->the_post(); 
    // Build $data JSON object
    $items = array();
    $items[] = array(
        'id'            => get_the_ID(),
        'title'         => get_the_title(),
        'content'       => WP_excerpt('60'),
        'permalink'     => get_permalink(),
        'category'      => get_the_category_list(','),
        //'avatar'      => get_avatar( get_the_author_meta( 'ID' ) ),
        //'comments'    => get_comments_number(),
        'modify'        => date('F',get_the_time('U')).' '.get_the_time('jS Y'),
        'image'         => has_post_thumbnail(),
        'Thumbnail'     => post_list_thumbnail()
    );

    // Get the page
    $nav = ( $total > $paged )  ? ( $paged + 1 ) : '' ;

    // State
    $msg = $items ? 200 : 500;
    
    // Output data
    $response = array(
        'msg'=>$msg,
        'items'=>$items,
        'nav'=> $nav
    );

    // Reset query
    endwhile; wp_reset_postdata();
    return rest_ensure_response($response);
}
//翻页按钮
	function __button(){
    	global $wp_query;
    	if (2 > $GLOBALS["wp_query"]->max_num_pages) {
        return;
    } else {
        $button = '<div class="loading"><button class="button button--primary button--withChrome js-Button" ';
        if (is_category()) $button .= ' data-category="' . get_query_var('cat') . '"';
        if (is_author()) $button .=  ' data-author="' . get_query_var('author') . '"';
        if (is_tag()) $button .=  ' data-tag="' . get_query_var('tag') . '"';
        if (is_search()) $button .=  ' data-search="' . get_query_var('s') . '"';
        if (is_date() ) $button .=  ' data-year="' . get_query_var('year') . '" data-month="' . get_query_var('monthnum') . '" data-day="' . get_query_var('day') . '"';
        $button .= 'data-paged="2" data-total="' . $GLOBALS["wp_query"]->max_num_pages . '">加载更多文章</button></div>';

	return $button;
    }
}