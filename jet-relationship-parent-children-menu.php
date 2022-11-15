<?php
function anony_get_related_objects( $cache_key, $select, $where ,$object_id, $rel_id, $separate_table = false ){
    global $wpdb;

    $table_suffix = ( $separate_table ) ? $rel_id : 'default';
    
    $results = wp_cache_get( $cache_key );
	
	if ( false === $results ) {

		$results = $wpdb->get_results(
			$wpdb->prepare("
				SELECT {$select} 
				FROM 
					{$wpdb->prefix}jet_rel_{$table_suffix} t
				WHERE 
					t.rel_id = %d
				AND t.{$where} = %d",
				$rel_id,
				$object_id
			),
			ARRAY_A
		);
        
        if ( $results && ! empty( $results ) && !is_null( $results ) ) 
        {
            wp_cache_set( $cache_key, $results );
        }
		
	}
	
	$data = array();
	
	if ( $results && ! empty( $results ) && !is_null( $results ) ) {
		foreach ( $results as $result ) {
                $obj= new stdClass();
                $obj->child_object_id = $result[$select];
				$data[] = $obj;
		}
	}
    
	return $data;
}

function anony_query_related_children( $object_jd, $rel_id, $separate_table = false ){
    global $wpdb;

	$cache_key = 'anony_get_related_children_'.$object_jd;
	
	return anony_get_related_objects( $cache_key, 'child_object_id', 'parent_object_id' ,$object_jd, $rel_id, $separate_table);
}

function anony_query_related_parent( $object_jd, $rel_id, $separate_table = false  ){
    global $wpdb;

	$cache_key = 'anony_get_related_parent_'.$object_jd;
	
	return anony_get_related_objects( $cache_key, 'parent_object_id', 'child_object_id' ,$object_jd ,$rel_id, $separate_table);
}

function anony_get_content_tree( $contents )
{
	$temp = array();
	
	foreach( $contents as $content )
	{
		
		$content_to_contents = anony_query_related_children($content->child_object_id, 17, true);
		if( !empty( $content_to_contents ) && is_array( $content_to_contents ) )
		{
			$title     = get_the_title( $content->child_object_id  );
			$permalink = get_the_permalink( $content->child_object_id  );
			
			$temp[$content->child_object_id] = array(
				'object_id'        => $content->child_object_id,
				'object_title'     => $title,
				'has_children'      => true, 
				'object_permalink' => $permalink,
				'children'         => anony_get_content_tree( $content_to_contents ) 
			);
		}else{
			$title     = get_the_title( $content->child_object_id  );
			$permalink = get_the_permalink( $content->child_object_id  );
			$temp[$content->child_object_id] = array( 'has_children' => false );
			
			$temp[$content->child_object_id]['children'] = array( 
				'object_id'        => $content->child_object_id,
				'object_title'     => $title,
				'object_permalink' => $permalink,
			);
			
		}
	}
	
	return $temp;
}

function anony_walker( $multi_dimensional_array ){
	$output = '';
	if( !empty( $multi_dimensional_array )  )
	{
		foreach($multi_dimensional_array as $object_id => $object_data)
		{

			if ( !$object_data[ 'has_children' ] ){
				$output .= sprintf( '<li id="anony-walker-item-%1$s" class="anony-walker-item"><a href="%2$s">%3$s</a></li>' ,esc_attr ($object_id), esc_url($object_data[ 'children' ][ 'object_permalink' ]), $object_data[ 'children' ][ 'object_title' ] );
			}else{
				
				$output .= sprintf( '<li id="anony-walker-item-%1$s" class="anony-walker-item"><a href="%2$s">%3$s</a>' ,esc_attr ($object_id), esc_url($object_data[ 'object_permalink' ]), $object_data[ 'object_title' ] );
				$output .= '<ul>';
				$output .= anony_walker( $object_data[ 'children' ] );
				$output .= '</ul></li>';
			}
		}
		
	}else{
		$output = 'No elements to render';
	}
	
	return $output;
}
add_shortcode( 'anony-book-contents', function(){
    
    if( !is_singular('book') ) return;
    global $post;
    
    
	$book_to_contents = anony_query_related_children($post->ID, 12, true);

	$output = '';
	if( !empty( $book_to_contents ) && is_array( $book_to_contents ) )
	{
		$output = '<ul>';
		$output .= anony_walker( anony_get_content_tree( $book_to_contents ) );
		$output .= '</ul>';
	}
	
	return $output ;
} );