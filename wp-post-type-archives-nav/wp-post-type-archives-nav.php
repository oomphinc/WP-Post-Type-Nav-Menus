<?php
/*
Plugin Name: Custom Post Types in Nav
Description: Allow custom post types to be specified in nav menus
Author: Ben Doherty @ Oomph, Inc.
Version: 0.0.1
Author URI: http://www.oomphinc.com/thinking/author/bdoherty/
License: GPLv2 or later

		Copyright © 2015 Oomph, Inc. <http://oomphinc.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
class WP_Post_Type_Archives_Nav {
	static function init() {
		$c = get_called_class();

		add_action( 'admin_head-nav-menus.php', array( $c, 'add_meta_boxes' ) );
		add_filter( 'wp_get_nav_menu_items', array( $c, 'archive_menu_filter'), 10, 3 );
	}

	static function add_meta_boxes() {
		$c = get_called_class();

		add_meta_box( 'cpt-archive', __( "Custom Post Type Archives" ), array( $c, 'meta_box' ), 'nav-menus', 'side', 'default' );
	}

	static function meta_box() {
		$post_types = get_post_types( array( 'show_in_nav_menus' => true, 'has_archive' => true ), 'object' );

		if( $post_types ) {
			foreach ( $post_types as &$post_type ) {
				$post_type->classes = array();
				$post_type->type = 'cpt-archive';
				$post_type->object = $post_type->name;
				$post_type->object_id = $post_type->name;
				$post_type->title = $post_type->labels->name . ' ' . __( "Archive" );
			}
			$walker = new Walker_Nav_Menu_Checklist( array() );
		?>
		<div id="cpt-archive" class="posttypediv">
			<div class="tabs-panel tabs-panel-active">
				<ul id="ctp-archive-checklist" class="categorychecklist form-no-clear">
					<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $post_types ), 0, (object) array( 'walker' => $walker ) ); ?>
				</ul>
			</div><!-- /.tabs-panel -->
		</div>
		<p class="button-controls">
			<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-post-type-menu-item" id="submit-cpt-archive" />
				<span class="spinner"></span>
			</span>
		</p>
		<?php
		}
	}

	/**
	 * Render the links for CPT archives
	 */
	static function archive_menu_filter( $items, $menu, $args ) {
		foreach( $items as &$item ) {
			if( $item->type != 'cpt-archive' ) continue;
			$item->url = get_post_type_archive_link( $item->object_id );

			if( get_query_var( 'post_type' ) == $item->object_id ) {
				$item->classes[] = 'current-menu-item';
				$item->current = true;
			}
		}

		return $items;
	}
}
add_action( 'init', array( 'WP_Post_Type_Archives_Nav', 'init' ) );
