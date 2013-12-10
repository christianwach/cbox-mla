<?php

/* MLA edits to BP literals */

define ( 'BP_FRIENDS_SLUG', 'contacts' );

// Change "en_US" to your locale
define( 'BPLANG', 'en_US' );
if ( file_exists( WP_LANG_DIR . '/buddypress-' . BPLANG . '.mo' ) ) {
	load_textdomain( 'buddypress', WP_LANG_DIR . '/buddypress-' . BPLANG . '.mo' );
}


// [COMMUNITY STRUCTURE] Add private/public filter to group lists

function mla_group_directory_status_filter() {
	$str  = '<li class="last filter-status" style="margin-left: 1em; float: left;"><label for="groups-filter-by">Visibility:</label>';
	$str .= '<select id="groups-filter-by">';
	$str .= '<option value="all">All</option>';
	$str .= '<option value="public">Public</option>';
	$str .= '<option value="private">Private</option>';
	if (is_admin() || is_super_admin()) {
		$str .= '<option value="hidden">Hidden</option>';
	} 
	$str .= '</select></li>';
	echo $str;
}
function mla_group_directory_type_filter() { 
	$str  = '<li class="last filter-type" style="margin-left: 1em; float: left;"><label for="groups-filter-by-type">Type:</label>';
	$str .= '<select id="groups-filter-by-type">';
	$str .= '<option value="all">All</option>';
	$str .= '<option value="committees">Committees</option>';
	$str .= '<option value="divisions">Divisions</option>';
	$str .= '<option value="discussion_groups">Discussion Groups</option>';
	$str .= '<option value="Other">Other</option>';
	$str .= '</select></li>';
	echo $str;
} 
add_action( 'bp_groups_directory_group_types', 'mla_group_directory_status_filter');
add_action( 'bp_groups_directory_group_types', 'mla_group_directory_type_filter');


class BP_Groups_Status_Filter {
	protected $status;
	protected $group_ids = array();

	function __construct( ) {

		$this->status = $_COOKIE['bp-groups-status'];
		//print_r('<p>Status: '.$this->status.'</p>'); //debugging. Remove this later. 
		$this->setup_group_ids();

		add_filter( 'bp_groups_get_paged_groups_sql', array( &$this, 'filter_sql' ) );
		add_filter( 'bp_groups_get_total_groups_sql', array( &$this, 'filter_sql' ) );
	}

	function setup_group_ids() {
		global $wpdb, $bp;
		$sql = $wpdb->prepare( "SELECT id FROM {$bp->groups->table_name} WHERE status = %s", $this->status);
		$this->group_ids = wp_parse_id_list( $wpdb->get_col( $sql ) );
	}

	function get_group_ids() {
		return $this->group_ids;
	}

	function filter_sql( $sql ) {
		$group_ids = $this->get_group_ids();
		if ( empty( $group_ids ) ) {
			return $sql;
		}

		$sql_a = explode( 'WHERE', $sql );
		$new_sql = $sql_a[0] . 'WHERE g.id IN (' . implode( ',', $group_ids ) . ') AND ' . $sql_a[1];

		//print_r("New SQL is: ".$new_sql); // debugging
		return $new_sql;
	}

	function remove_filters() {
		remove_filter( 'bp_groups_get_paged_groups_sql', array( &$this, 'filter_sql' ) );
		remove_filter( 'bp_groups_get_total_groups_sql', array( &$this, 'filter_sql' ) );
	}
}

class BP_Groups_Type_Filter extends BP_Groups_Status_Filter { 

	protected $status_type;
	protected $group_ids = array();

	function __construct() { 
		$this->status_type = $_COOKIE['bp-groups-type']; 
		//print_r('<p>Type: '.$this->status_type.'</p>'); //debugging. Remove this later. 
		$this->setup_group_ids();

		add_filter( 'bp_groups_get_paged_groups_sql', array( &$this, 'filter_sql' ) );
		add_filter( 'bp_groups_get_total_groups_sql', array( &$this, 'filter_sql' ) );
	} 
	function setup_group_ids() {
		global $wpdb, $bp;
		$sql_stub = "SELECT group_id FROM {$bp->groups->table_name}_groupmeta WHERE meta_key = 'mla_oid' AND LEFT(meta_value, 1) =  %s"; 
		switch ( $this->status_type ) { 
			case "committees": 
				$sql = $wpdb->prepare($sql_stub, 'M');
				//echo "<p>Original SQL: " . $sql . "</p>"; 
				break; 
			case "divisions": 
				$sql = $wpdb->prepare($sql_stub, 'D');
				//echo "<p>Original SQL: " . $sql . "</p>"; 
				break; 
			case "discussion_groups": 
				$sql = $wpdb->prepare($sql_stub, 'G');
				//echo "<p>Original SQL: " . $sql . "</p>"; 
				break; 
			case "other": 
				echo "Hello world!"; 
				$sql = $wpdb->prepare("SELECT group_id FROM {$bp->groups->table_name}_groupmeta WHERE meta_key = 'mla_oid' AND LEFT(meta_value, 1) NOT IN ('G','M','D')"); //this has been empty on the mysql commandline 
				echo "<p>Original SQL: " . $sql . "</p>"; 
				break; 
		} 
		$this->group_ids = wp_parse_id_list( $wpdb->get_col( $sql ) );
	}
} 

$status_filter = '';
function add_status_filter() {
	global $status_filter;
	if($_COOKIE['bp-group-status']!='all')	$status_filter = new BP_Groups_Status_Filter();
}

function remove_status_filter() {
	global $status_filter;
	if($_COOKIE['bp-group-status']!='all') $status_filter->remove_filters();
}
$type_filter = ''; 
function add_type_filter() { 
	global $type_filter; 
	if($_COOKIE['bp-group-type']!='all') $type_filter = new BP_Groups_Type_Filter();
} 
function remove_type_filter() {
	global $type_filter;
	if($_COOKIE['bp-group-type']!='all') $type_filter->remove_filters();
} 
add_action('bp_before_groups_loop','add_status_filter');
add_action('bp_after_groups_loop','remove_status_filter');
add_action('bp_before_groups_loop','add_type_filter');
add_action('bp_after_groups_loop','remove_type_filter');

function status_filter_js() {
	if( wp_script_is( 'jquery', 'done' ) ) { ?>
	<script type="text/javascript">
		jq('li.filter-status select').val(jq.cookie('bp-groups-status'));
	jq('li.filter-status select').change( function() {

		if ( jq('.item-list-tabs li.selected').length )
			var el = jq('.item-list-tabs li.selected');
		else
			var el = jq(this);

		var css_id = el.attr('id').split('-');
		var object = css_id[0];
		var scope = css_id[1];
		var status = jq(this).val();
		var filter = jq('select#groups-order-by').val();
		var search_terms = '';

		jq.cookie('bp-groups-status',status,{ path: '/' });

		if ( jq('.dir-search input').length )
			search_terms = jq('.dir-search input').val();

		bp_filter_request( object, filter, scope, 'div.' + object, search_terms, 1, jq.cookie('bp-' + object + '-extras') );

		return false;

	});
	</script>
<?php }
}
function type_filter_js() {
	if( wp_script_is( 'jquery', 'done' ) ) { ?>
	<script type="text/javascript">
		jq('li.filter-type select').val(jq.cookie('bp-groups-type'));
	jq('li.filter-type select').change( function() {

		if ( jq('.item-list-tabs li.selected').length )
			var el = jq('.item-list-tabs li.selected');
		else
			var el = jq(this);

		var css_id = el.attr('id').split('-');
		var object = css_id[0];
		var scope = css_id[1];
		var status = jq(this).val();
		var filter = jq('select#groups-order-by-type').val();
		var search_terms = '';

		jq.cookie('bp-groups-type',status,{ path: '/' });

		if ( jq('.dir-search input').length )
			search_terms = jq('.dir-search input').val();

		bp_filter_request( object, filter, scope, 'div.' + object, search_terms, 1, jq.cookie('bp-' + object + '-extras') );

		return false;

	});
	</script>
<?php }
}
add_action('wp_footer', 'status_filter_js');
add_action('wp_footer', 'type_filter_js');


?>
