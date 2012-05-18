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

	function get_profile_data( $widget, $wp_user ) {
		$my_profile = file_get_html( 'http://profiles.wordpress.org/'.$wp_user.'/' );

		if ( $my_profile !== FALSE ) {

			$my_about_me    = $my_profile->find( 'p.item-meta-about', 0 )->innertext;
			$my_activity    = $my_profile->find( 'ul#activity-list', 0 )->innertext;
			$key = 0;
			foreach ( $my_profile->find( 'div[class="main-plugins]' ) as $div ) {

				$plugin[$key] = $div->innertext; $key++;
			}
			$profile_data = array(
			//	'plugins'   => $my_profile->find('div.info-group.plugin-theme.main-plugins', 0)->children(1),
			//	'favorites' => $my_profile->find('div.info-group.plugin-theme.main-plugins', 0)->childeren(2),
				'plugins'   => $plugin[0],
				'favorites' => $plugin[1],
				'activity'  => $my_activity,
				'about_me'  => $my_about_me,
			);

			$my_profile->clear();

			switch ( $widget ) :
				case 'my_plugins' :
					return '<ul>'. $profile_data['plugins']. '</ul>';
					break;
				case 'my_favorites' :
					$data = $profile_data['favorites'];
					$data = preg_replace( '#\<h4\>(.+?)\<\/h4\>#s', '', $data );
					$data = preg_replace( '#\<br\>(.+?)\<\/\>#s', '', $data );
					$s = array ( '<h3>', '</h3>' );
					$r = array ( '<p>', '</p>' );
					$data = str_ireplace( $s, $r, $data );
					return $data;
					break;
				case 'about_me' :
					return '<p>' .$profile_data['about_me']. '</p>';
				case 'activity' :
					return '<ul>'. $profile_data['activity']. '</ul>';
					break;
			endswitch;


		}

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

}
	$author_widgets = new C3M_Plugin_Author_Widgets();


class C3M_MY_Profile_Data extends WP_Widget {

	function C3M_MY_Profile_Data() {
		$widget_ops = array ( 'classname'   => 'my_plugins', 'description' => 'Displays a list of your WordPress.org Plugins' );
		$this->WP_Widget( 'my_plugins', 'WordPress.org Plugins', $widget_ops );

	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		global $author_widgets;
		$widget = $instance['widget'];


			if ( false == get_transient( 'c3m_plugin_'.$widget.'_' ) ) {
				$title = $instance['title'];
				$wp_user = $instance['wp_user'];
				$widget = $instance['widget'];
				$plugin_data = $author_widgets->get_profile_data( $widget, $wp_user );

				set_transient( 'c3m_plugin_'.$widget.'_', $plugin_data, 60*60*12 );
			}

				$title = $instance['title'];
				$wp_user = $instance['wp_user'];
				$widget = $instance['widget'];

				$plugin_data = get_transient( 'c3m_plugin_'.$widget.'_' );

				/**
			    * @var string $before_widget defined by theme @see register_sidebar()
			    */
				echo $before_widget;

				/**
				 * @var string $before_title defined by theme @see register_sidebar()
				 * @var string $after_title
				 */
				echo $before_title . $title . $after_title;
				echo  $plugin_data;
				echo '<style type="text/css">.star-rating{background: url('. WP_PLUGIN_URL . '/plugin-author-profile-widgets/images/rating-stars-small-blue.png)}</style>';
				/**
			    * @var string $after_widget defined by theme @see register_sidebar()
			    */
				echo $after_widget;

	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']      = strip_tags( $new_instance['title']  );
		$instance['wp_user']    = strip_tags( $new_instance['wp_user'] );
		$instance['widget']     = strip_tags( $new_instance['widget'] );

		delete_transient( 'c3m_plugin_'.$instance['widget'].'_' );

			return $instance;

	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
									'title'     => '',
									'wp_user'   => '',
									'widget'    => '',
			) );
		foreach( $instance as $field => $value ) {
			if( isset( $new_instance[$field] ) )
				$instance[$field] = 1;
		}
		$widgets_avail = array( 'my_plugins' => 'My Plugins', 'my_favorites' => 'My Favorites', 'about_me' => 'My About Me', 'my_activity' => 'My Activity' );
		$title          = strip_tags( $instance['title']);
		$wp_user        = strip_tags( $instance['wp_user']);
		$widget         = strip_tags( $instance['widget']);
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
		<p>
			<label for="<?php echo $this->get_field_id( 'widget' ); ?>"><?php _e( 'Choose Widget' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'widget' ); ?>" name="<?php echo $this->get_field_name( 'widget' ); ?>" class="widefat">
                <?php foreach ( $widgets_avail as $key => $value ) : ?>
					<option value="<?php echo $key; ?>" <?php  selected( $key, $widget ); ?>><?php echo $value; ?></option>
				<?php endforeach; ?>
            </select>
		</p>




	<?php }

 }
function c3m_register() {
register_widget( 'C3M_MY_Profile_Data' );
}