<?php
/*
Plugin Name: Plugin Author Profile Widgets
Plugin URI:
Description: For plugin authors. Show My Plugins, My favorite Plugins, and My Activity from your profiles.wordpress.org profile page.
Version: 0.1
Author: Chris Olbekson
Author URI: http://c3mdigital.com/
License: GPL v2
*/


/*  Copyright 2012  Chris Olbekson  (email : chris@c3mdigital.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * @author Chris Olbekson
 * @package Plugin Author Profile Widgets
 * @version 0.1
 */

include_once( 'inc/simple_html_dom.php' );

class C3M_Plugin_Author_Widgets {
	static $instance;
	const VERSION = '0.1';
	const CRON_HOOK = 'c3m_update_plugins';
	var $my_username;
	var $my_profile;
	var $my_plugins;
	var $my_favorites;
	var $my_activity;
	var $my_about_me;



	function __construct() {
		self::$instance = $this;
		 add_action( 'widgets_init',     'c3m_register'  );
	}

	function get_profile( $my_username ) {
		$my_profile = new simple_html_dom();
		$my_profile->load_file( 'http://profiles.wordpress.org/'.$my_username.'/' );

		var_dump( $my_profile );
		return $my_profile;
	}

	function get_plugins( $my_username ) {
		//$my_profile = new simple_html_dom();
		$my_profile = file_get_html( 'http://profiles.wordpress.org/' . $my_username . '/' );

	//	$my_profile = $this->get_profile( $my_username );
		//foreach ( $my_profile->find( '#main-column .info-group.plugin-theme.main-plugins ul li', 0 ) as $li ) {
		//	$my_plugins[] = $li->innertext;
		$my_plugins = $my_profile->find( 'div[id=main-column]', 0);
	//	}

		return $my_plugins;
	}



	function get_favorites( $my_username ) {

	}

	function get_about_me( $my_username ) {

	}

	function widgets_init() {
		// register_widget( new C3M_MY_Plugins );


	}


}
	$author_widgets = new C3M_Plugin_Author_Widgets();

class C3M_About_Me extends WP_Widget {

	function c3m_about_me() {
		$widget_ops = array( 'classname' => ' ', 'description' => ' ' );
		$this->WP_Widget( ' ', ' ', $widget_ops );

	}

	function widget( $args, $instance ) {

	}

	function update( $new_instance, $old_instance ) {

	}

	function form( $instance ) {

	}


}


class C3M_MY_Favorites extends WP_Widget {

	function c3m_my_favorites() {
		$widget_ops = array ( 'classname'   => ' ',
		                      'description' => ' '
		);
		$this->WP_Widget( ' ', ' ', $widget_ops );

	}

	function widget( $args, $instance ) {

	}

	function update( $new_instance, $old_instance ) {

	}

	function form( $instance ) {

	}

}

class C3M_MY_Activity extends WP_Widget {

	function c3m_my_activity() {
		$widget_ops = array ( 'classname'   => ' ',
		                      'description' => ' '
		);
		$this->WP_Widget( ' ', ' ', $widget_ops );

	}

	function widget( $args, $instance ) {

	}

	function update( $new_instance, $old_instance ) {

	}

	function form( $instance ) {

	}

}

class C3M_MY_Plugins extends WP_Widget {

	function C3M_MY_Plugins() {
		$widget_ops = array ( 'classname'   => 'my_plugins', 'description' => 'Displays a list of your WordPress.org Plugins' );
		$this->WP_Widget( 'my_plugins', 'WordPress.org Plugins', $widget_ops );

	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		global $author_widgets;

		$wp_user = $instance['wp_user'];

			if( false == get_transient( 'c3m_plugin_author_plugins_'.$wp_user.'' ) ) {

				$wp_user = $instance['wp_user'];
				$plugins = $author_widgets->get_plugins( $wp_user );

				set_transient( 'c3m_plugin_author_plugins_' . $wp_user . '', $plugins, 60*60*12 );
			}
			$title      = $instance['title'];
			$wp_user    = $instance['wp_user'];
			$show_stats = $instance['show_stats'];
			$plugins = get_transient( 'c3m_plugin_author_plugins_' . $wp_user . '' );

			/** @var string $before_widget */
			echo $before_widget;

			$title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );
			if ( ! empty( $title ) ) {
				/**
				 * @var string $before_title
				 * @var string $after_title
				 */
				echo $before_title . $title . $after_title; };
				echo '<ul>';
					foreach ( (array) $plugins as $plugin ) {
						echo "<li>$plugin</li>";
					}

				echo '</ul>';
			/** @var string $after_widget */

				echo $after_widget;

	}


	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']          = strip_tags( $new_instance['title']  );
		$instance['wp_user']        = strip_tags( $new_instance['wp_user'] );
		$instance['show_stats']     = !empty( $new_instance['show_stats'] ) ? 1 : 0;

		delete_transient( 'c3m_plugin_author_plugins_' . $instance['wp_user'] . '' );

			return $instance;

	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
				'title'         => '',
				'wp_user'       => '',
				'show_stats'    =>  0,
			));
		foreach( $instance as $field => $value ) {
			if( isset( $new_instance[$field] ) )
				$instance[$field] = 1;
		}

		$title          = strip_tags( $instance['title']);
		$wp_user        = strip_tags( $instance['wp_user']);
		$show_stats     = strip_tags( $instance['show_stats']);
		?>

		<p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"/>
        </p>
		<p>
			<label for="<?php echo $this->get_field_id( 'wp_user' ); ?>"><?php _e( 'WordPress.org username' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'wp_user' ); ?>" name="<?php echo $this->get_field_name( 'wp_user' ); ?>" type="text" value="<?php echo esc_attr( $wp_user ); ?>"/>
			<?php if ( empty ( $wp_user ) ) :    echo '<span class="gist_error_message">Username is required!</span>'; endif; ?>
        </p>
		<br/>
		<p><strong>Optional Values</strong></p>
		<p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['show_stats'], true ) ?> id="<?php echo $this->get_field_id( 'show_stats' ); ?>" name="<?php echo $this->get_field_name( 'show_stats' ); ?>"/>
			<label for="<?php echo $this->get_field_id( 'show_stats' ); ?>"><?php _e( 'Display Download Stats' ); ?></label>
        </p>



	<?php }

 }
function c3m_register() {
register_widget( 'C3M_MY_Plugins' );
}