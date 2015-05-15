<?php

/**
 * BuddyPress - Deposits Loop
 *
 * Querystring is set via AJAX in _inc/ajax.php - bp_dtheme_object_filter()
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

?>

<?php do_action( 'bp_before_group_deposits_loop' ); ?>

<?php

if ( is_user_logged_in() ) {
	echo '<a href="/deposits/item/new/" class="bp-deposits-deposit button" title="Deposit an Item">Deposit an Item</a>';
}

// Fill this string with the list of activity types
// you want to see when the filter is set to "everything."
// An easy way to get this list is to check out the html source
// and get all the values of the <option>s.

$my_querystring = sprintf( 'facets[group_facet][]=%s', urlencode( bp_get_current_group_name() ) );

// If the ajax string is empty, that usually means that
// it's the first page of the "everything" filter.
$querystring = '';

if ( empty( $querystring ) ) {
	$querystring = $my_querystring;
}

// Handle subsequent pages of the "Everything" filter
if ( 'page' == substr( $querystring, 0, 4 ) && strlen( $querystring ) < 8 ) {
	$querystring = $my_querystring . '&' . $querystring;
}
?> 

<?php if ( humcore_has_deposits( $querystring ) ) : ?>

	<?php if ( 1 == 1 || empty( $_POST['page'] ) ) : ?>

		<ul id="deposits-stream" class="deposits-list item-list">

	<?php endif; ?>

	<?php while ( humcore_deposits() ) : humcore_the_deposit(); ?>

		<?php locate_template( array( 'deposits/entry.php' ), true, false ); ?>

	<?php endwhile; ?>

	<?php if ( 1 == 2 && bp_activity_has_more_items() ) : ?>

		<li class="load-more">
			<a href="#more"><?php _e( 'Load More', 'humcore_domain' ); ?></a>
		</li>

	<?php endif; ?>

	<?php if ( 1 == 1 || empty( $_POST['page'] ) ) : ?>

		</ul>

	<?php endif; ?>

		<div class="pagination">
			<div class="pag-count"><?php humcore_deposit_pagination_count(); ?></div>
			<div class="pagination-links"><?php humcore_deposit_pagination_links(); ?></div>
		</div>

<?php else : ?>

	<div id="message" class="info">
		<p><?php _e( 'No deposited items have been associated with this group.', 'humcore_domain' ); ?></p>
	</div>

<?php endif; ?>

<?php do_action( 'bp_after_deposits_loop' ); ?>

<form action="" name="deposits-loop-form" id="deposits-loop-form" method="post">

	<?php wp_nonce_field( 'deposits_filter', '_wpnonce_deposits_filter' ); ?>

</form>