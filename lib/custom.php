<?php

namespace MLA\Tuileries\Custom;

/**
 * Custom functions for the theme should go here. Use namespacing like:
 *
 * use MLA\Tuileries\Custom; (at the top of the file)
 *
 * Custom\function_name(); (in your PHP code)
 */

/**
 * Output markup listing group admins. Modelled after bp_group_list_admins(),
 * but instead of outputting <ul>s and <li>s, it outputs a comma-separated
 * prose list like this: "Jonathan Reeve, Richard Stallman, Emma Goldman"
 *
 * @param object $group Optional. Group object. Default: current
 *        group in loop.
 * @return string
 */
function group_list_admins( $group = false ) {
	global $groups_template;

	if ( empty( $group ) ) {
		$group =& $groups_template->group;
	}

	// fetch group admins if 'populate_extras' flag is false
	if ( empty( $group->args['populate_extras'] ) ) {
		$query = new BP_Group_Member_Query( array(
			'group_id'   => $group->id,
			'group_role' => 'admin',
			'type'       => 'first_joined',
		) );

		if ( ! empty( $query->results ) ) {
			$group->admins = $query->results;
		}
	}

	if ( ! empty( $group->admins ) ) {
		if ( count ( $group->admins ) == 1 ):
			$admin = $group->admins[0]; ?>
			<a href="<?php echo bp_core_get_user_domain( $admin->user_id, $admin->user_nicename, $admin->user_login ) ?>"><?php echo bp_core_get_user_displayname( $admin->user_id ); ?></a>
		<?php elseif ( count ( $group->admins ) > 1 ):
			$admin_list_html = array();
			foreach( (array) $group->admins as $admin ) {
				$admin_html = sprintf( '<a href="%s">%s</a>',
					bp_core_get_user_domain( $admin->user_id, $admin->user_nicename, $admin->user_login ),
					bp_core_get_user_displayname( $admin->user_id ) );
				$admin_list_html[] = $admin_html;
			}
			$admins = implode( ', ', $admin_list_html );
			echo $admins;
		endif;
	} else { ?>
		<span class="activity"><?php _e( 'No Admins', 'buddypress' ) ?></span>
	<?php }
}
