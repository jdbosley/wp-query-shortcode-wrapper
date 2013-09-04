<?php 

function wp_query_shortcode_wrapper($atts, $content = null){
	extract(shortcode_atts(array(
		'limit' => -1,
		'order' => 'ASC',
		'orderby' => 'name',
		'post_type' => 'any',
		'paginate' => false,
		'taxonomy' => '',
		'terms' => '',
		'template' => 'loops/single'
	), $atts));

	// setup default args
	$args = array(
		'order' => $order,
		'orderby' => $orderby,
		'post_type'=> $post_type,
		'post_status'=>'publish',
		'posts_per_page'=> $limit,
	);

	// if a taxonomy exists - add a tax_query
	if($taxonomy){
		if(is_array($terms))
			$terms = explode(',',$terms);
		else
			$terms = $terms;

		$args["tax_query"] = array(
			array(
				'taxonomy' => $taxonomy,
				'field' => 'slug',
				'terms' => $terms
			)
		);
	}

	// if paginate parameter is passed - then paginate!
	if($paginate){
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		$args["paged"] = $paged;
	}

	$posts = new WP_Query($args);

	ob_start();

	if($posts->have_posts()): 

		while($posts->have_posts()): $posts->the_post();
			get_template_part($template);
		endwhile;

		// if we are paginating, display pagination UI
		if($paginate){
			$big = 999999999;
			echo '<div class="post-pagination">
				<p>Page </p>
				'.paginate_links( array(
				'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format' => '?paged=%#%',
				'current' => max( 1, get_query_var('paged') ),
				'total' => $posts->max_num_pages,
				'type' => 'list',
				'prev_next' => false
			)).'</div>';
		}

	endif;
	wp_reset_postdata();

	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}
add_shortcode('wp_query','wp_query_shortcode_wrapper');

?>