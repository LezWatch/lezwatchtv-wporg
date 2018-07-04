<?php
/*

 * Plugin File: Widgets
 * This file contains all the widget code for LWTV
 * @since 1.2.0
	
	Copyright 2017-18 LezWatch.TV (email: webmaster@lezwatchtv.com)

	This file is part of LezWatch.TV, a plugin for WordPress.

	LezWatch.TV is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 2 of the License, or
	(at your option) any later version.

	LezWatch.TV is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with WordPress.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
 * class LWTV_Last_Death_Widget
 *
 * Widget to display last queer death
 *
 * @since 1.0
 */
class LWTV_Last_Death_Widget extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 */
	protected $defaults;

	/**
	 * Constructor.
	 *
	 * Set the default widget options and create widget.
	 */
	function __construct() {

		$this->defaults = array(
			'title' => __( 'Last Queer Death', 'bury-your-queers' ),
		);

		$widget_ops = array(
			'classname'   => 'dead-character deadwidget',
			'description' => __( 'Displays time since the last queer female or trans character death on television.', 'bury-your-queers' ),
		);

		$control_ops = array(
			'id_base' => 'byq-dead-char',
		);

		parent::__construct( 'byq-dead-char', __( 'LWTV - Last Death', 'bury-your-queers' ), $widget_ops, $control_ops );
	}

	/**
	 * Echo the widget content.
	 *
	 * @param array $args Display arguments
	 * @param array $instance The settings for the particular instance of the widget
	 */
	function widget( $args, $instance ) {

		extract( $args );
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		echo LezWatch_TV::last_death();

		echo $args['after_widget'];
	}

	/**
	 * Update a particular instance.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {
		$new_instance['title'] = strip_tags( $new_instance['title'] );
		return $new_instance;
	}

	/**
	 * Echo the settings update form.
	 *
	 * @param array $instance Current settings
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'bury-your-queers' ); ?>: </label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>
		<?php
	}
}

/*
 * class LWTV_Of_The_Day_Widget
 *
 * Widget to display ... Of The Day
 *
 * @since 1.3
 */
class LWTV_Of_The_Day_Widget extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 */
	protected $defaults;
	protected $valid_types;

	/**
	 * Constructor.
	 *
	 * Set the default widget options and create widget.
	 */
	function __construct() {

		$this->defaults   = array(
			'title' => __( 'Of The Day', 'bury-your-queers' ),
			'type'  => 'character',
		);
		$this->valid_types = array( 'character', 'show', 'death' );

		$widget_ops = array(
			'classname'   => 'widget-lwtv-of-the-day',
			'description' => __( 'Displays the character, show, or death of the day.', 'bury-your-queers' ),
		);

		$control_ops = array(
			'id_base' => 'lwtv-of-the-day',
		);

		parent::__construct( 'lwtv-of-the-day', __( 'LWTV - Of The Day', 'bury-your-queers' ), $widget_ops, $control_ops );
	}

	/**
	 * Echo the widget content.
	 *
	 * @param array $args Display arguments
	 * @param array $instance The settings for the particular instance of the widget
	 */
	function widget( $args, $instance ) {

		extract( $args );
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$type = ( ! empty( $instance['type'] ) )? $instance['type'] : 'character' ;

		echo '<center>' . LezWatch_TV::of_the_day( $type ) . '</center>';

		echo $args['after_widget'];
	}

	/**
	 * Update a particular instance.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {
		$new_instance['title'] = wp_strip_all_tags( $new_instance['title'] );

		if ( !in_array( $new_instance['type'], $this->valid_types ) ) {
			$new_instance['type'] = 'character';
		}
		$new_instance['type'] = sanitize_html_class( $new_instance['type'], 'character' );

		return $new_instance;
	}

	/**
	 * Echo the settings update form.
	 *
	 * @param array $instance Current settings
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'bury-your-queers' ); ?>: </label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>"><?php _e( 'Type', 'bury-your-queers' ); ?>: </label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'type' ) ); ?>" class="widefat">
				<?php foreach( $this->valid_types as $type ) { ?>
					<option <?php selected( $instance['type'], $type ); ?> value="<?php echo $type; ?>"><?php echo ucfirst( $type ); ?></option>
				<?php } ?>
			</select>
		</p>
		<?php
	}
}

/*
 * class LWTV_On_This_Day_Widget
 *
 * Widget to display On This Day...
 *
 * @since 1.0
 */
class LWTV_On_This_Day_Widget extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 */
	protected $defaults;

	/**
	 * Constructor.
	 *
	 * Set the default widget options and create widget.
	 */
	function __construct() {

		$this->defaults = array(
			'title' => __( 'On This Day', 'bury-your-queers' ),
			'date'  => '',
		);

		$widget_ops = array(
			'classname'   => 'dead-on-this-day deadwidget',
			'description' => __( 'Displays any queer female or trans TV character who died on this day in years past.', 'bury-your-queers' ),
		);

		$control_ops = array(
			'id_base' => 'byq-on-this-day',
		);

		parent::__construct( 'byq-on-this-day', __( 'LWTV - On This Day', 'bury-your-queers' ), $widget_ops, $control_ops );
	}

	/**
	 * Echo the widget content.
	 *
	 * @param array $args Display arguments
	 * @param array $instance The settings for the particular instance of the widget
	 */
	function widget( $args, $instance ) {

		extract( $args );
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$date = ( ! empty( $instance['date'] ) )? $instance['date'] : 'today' ;

		echo LezWatch_TV::died_on_this_day( $date );

		echo $args['after_widget'];
	}

	/**
	 * Update a particular instance.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {
		$new_instance['title'] = wp_strip_all_tags( $new_instance['title'] );

		$new_instance['date'] = substr( $new_instance['date'], 0, 5);
		$month = substr( $new_instance['date'], 0, 2);
		$day = substr( $new_instance['date'], 3, 2);
		if ( checkdate( $month, $day, date("Y") ) == false ) $new_instance['date'] = '';
		$new_instance['date']  = wp_strip_all_tags( $new_instance['date'] );

		return $new_instance;
	}

	/**
	 * Echo the settings update form.
	 *
	 * @param array $instance Current settings
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'bury-your-queers' ); ?>: </label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'date' ) ); ?>"><?php _e( 'Date (Optional)', 'bury-your-queers' ); ?>: </label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'date' ) ); ?>" class="datepicker" value="<?php echo esc_attr( $instance['date'] ); ?>" class="widefat" />
			<br><em><?php _e( 'If left blank, the date used will be the current day.', 'bury-your-queers' ); ?></em>
		</p>
		<?php
	}
}

/*
 * class LWTV_Statistics_Widget
 *
 * Widget to display death statistics
 *
 * @since 1.2.0
 */
class LWTV_Statistics_Widget extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 */
	protected $defaults;

	/**
	 * Constructor.
	 *
	 * Set the default widget options and create widget.
	 */
	function __construct() {

		$this->defaults = array(
			'title' => __( 'Queer Fatality Statistics', 'bury-your-queers' ),
			'type'  => '',
		);

		$widget_ops = array(
			'classname'   => 'dead-stats deadwidget',
			'description' => __( 'Displays the percentage of how many queer female or trans TV characters who have died, and/or how many shows have death.', 'bury-your-queers' ),
		);

		$control_ops = array(
			'id_base' => 'byq-statistics',
		);

		parent::__construct( 'byq-statistics', __( 'LWTV - Statistics', 'bury-your-queers' ), $widget_ops, $control_ops );
	}

	/**
	 * Echo the widget content.
	 *
	 * @param array $args Display arguments
	 * @param array $instance The settings for the particular instance of the widget
	 */
	function widget( $args, $instance ) {

		extract( $args );
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$type = ( ! empty( $instance['type'] ) )? $instance['type'] : 'all' ;

		echo LezWatch_TV::statistics( $type );

		echo $args['after_widget'];
	}

	/**
	 * Update a particular instance.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {
		$new_instance['title'] = strip_tags( $new_instance['title'] );
		$new_instance['type']  = strip_tags( $new_instance['type'] );
		return $new_instance;
	}

	/**
	 * Echo the settings update form.
	 *
	 * @param array $instance Current settings
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		
		$stat_types = array( 'characters', 'shows' );
		
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'bury-your-queers' ); ?>: </label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>"><?php _e( 'Type', 'bury-your-queers' ); ?>: </label>

		<select id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" class="widefat" style="width:100%;">
			<option value="" selected>All</option>
			<?php foreach( $stat_types as $type) { ?>
				<option <?php selected( $instance[ 'type' ], $type ); ?> value="<?php echo $type; ?>"><?php echo ucfirst( $type ); ?></option>
			<?php } ?>
		</select>
		</p>
		<?php
	}
}

/*
 * class LWTV_This_Year_Widget
 *
 * Widget to display This Year data
 *
 * @since 1.3
 */
class LWTV_This_Year_Widget extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 */
	protected $defaults;

	/**
	 * Constructor.
	 *
	 * Set the default widget options and create widget.
	 */
	function __construct() {

		$this->defaults = array(
			'title' => __( 'In This Year', 'bury-your-queers' ),
			'year'  => date( 'Y' ),
		);

		$widget_ops = array(
			'classname'   => 'in-this-year thisyearwidget',
			'description' => __( 'Displays a review of queer female and trans representation on TV for a given year.', 'bury-your-queers' ),
		);

		$control_ops = array(
			'id_base' => 'byq-in-this-year',
		);

		parent::__construct( 'byq-in-this-year', __( 'LWTV - In This Year', 'bury-your-queers' ), $widget_ops, $control_ops );
	}

	/**
	 * Echo the widget content.
	 *
	 * @param array $args Display arguments
	 * @param array $instance The settings for the particular instance of the widget
	 */
	function widget( $args, $instance ) {

		extract( $args );
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$year = ( ! empty( $instance['year'] ) )? $instance['year'] : date( 'Y' ) ;

		echo LezWatch_TV::this_year( $year );

		echo $args['after_widget'];
	}

	/**
	 * Update a particular instance.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {
		$new_instance['title'] = wp_strip_all_tags( $new_instance['title'] );
		$new_instance['year'] = ( preg_match( '/^[0-9]{4}$/', $new_instance['year'] ) )? $new_instance['year'] : date( 'Y' );
		return $new_instance;
	}

	/**
	 * Echo the settings update form.
	 *
	 * @param array $instance Current settings
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		// Get the first year
		$request  = wp_remote_get( LezWatch_TV::$apiurl . '/stats/first-year/' );
		$response = wp_remote_retrieve_body( $request );
		$response = json_decode($response, true);
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'bury-your-queers' ); ?>: </label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'date' ) ); ?>"><?php _e( 'Year', 'bury-your-queers' ); ?>: </label>
			<select id="<?php echo $this->get_field_id( 'year' ); ?>" name="<?php echo $this->get_field_name( 'year' ); ?>" class="widefat" style="width:100%;">
				<?php for( $year = $response['first']; $year <= date( 'Y' ); ++$year ) {
					echo '<option ' . selected( $instance[ 'year' ], $year ) .' value="' . $year . '">' . $year . '</option>';
				} ?>
			</select>
		</p>
		<?php
	}
}