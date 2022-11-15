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

		wp_cache_set( $cache_key, $results );
	}
	
	$data = array();
	
	if ( ! empty( $results ) && ! is_null( $results ) ) {
		foreach ( $results as $result ) {

				$data[] = $result[$select];
		}
	}
    
	return $data;
}

function anony_query_related_children( $object_jd, $rel_id , $separate_table = false){
    global $wpdb;

	$cache_key = 'anony_get_related_children_'.$object_jd;
	
	return anony_get_related_objects( $cache_key, 'child_object_id', 'parent_object_id' ,$object_jd, $rel_id, $separate_table );
}

function anony_query_related_parent( $object_jd, $rel_id, $separate_table = false  ){
    global $wpdb;

	$cache_key = 'anony_get_related_parent_'.$object_jd;
	
	return anony_get_related_objects( $cache_key, 'parent_object_id', 'child_object_id' ,$object_jd ,$rel_id, $separate_table);
}