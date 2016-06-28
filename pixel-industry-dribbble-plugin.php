<?php
/*
Plugin Name: Pixel Industry Dribbble plugin
Plugin URI: http://www.pixel-industry.com/
Description: A simple plugin to display 5 latest shots of any Dribbble user. Easy and simple way to show off your latest work. This plugin is developed as part of my job application in Pixel Industry :)
Version: 1.0
Author: Nikola Rožić | Pixel Industry
Author URI: http://www.pixel-industry.com/
License: GPL2
 */
if ( !defined( 'ABSPATH' ) ) {
    die( 1 );
}

add_action( 'widgets_init', function(){
    register_widget( 'pixel_industry_dribbble_widget' );
}); 

class pixel_industry_dribbble_widget extends WP_Widget {

    /**
     * Constructing our widget
     *
     * We are using PHP5 style constructor since PHP4 style constructors are depercated since WordPress 4.3, but
     * I'm sure that you already know that :)
     *
     * Source: https://make.wordpress.org/core/2015/07/02/deprecating-php4-style-constructors-in-wordpress-4-3/
     * 
     */
    function __construct() { 
        parent::__construct(
            'pixel_industry_dribbble_widget', //widget ID
            __( 'Simple Dribbble Widget', 'pixel_industry_dribbble_widget' ), // Widget name
            array( 'description' => __( 'Simple Widget to display x latest shots of any given Dribbble user.', 'pixel_industry_dribbble_widget' ), ) 
            // and description, ofcourse :)
        );
    }

    /**
     * Function to output the options form in WordPress administration
     * 
     * @param  array   $instance    The widget options
     * 
     */
    public function form( $instance ) {
        // Checking if we already have values set and ready to use
        if ( $instance ) {
            $title        = esc_attr( $instance['title'] );
            $username     = esc_attr( $instance['username'] );
            $number       = esc_attr( $instance['number'] );
            $access_token = esc_attr( $instance['access_token'] );
        } else {
            // Defining values as empty strings otherwise
            $title        = '';
            $username     = '';
            $number       = '';
            $access_token = '';
        } ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <?php _e( 'Widget Title', 'pixel-industry-dribbble-plugin' ); ?>        
            </label>

            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('username'); ?>">
                <?php _e( 'Dribbble username', 'pixel-industry-dribbble-plugin' ); ?>        
            </label>

            <input class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" type="text" value="<?php echo $username; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>">
                <?php _e( 'Number of shots to display', 'pixel-industry-dribbble-plugin' ); ?>        
            </label>

            <input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="number" value="<?php echo $number; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('access_token'); ?>">
                <?php _e( 'Dribbble access token', 'pixel-industry-dribbble-plugin' ); ?>        
            </label>

            <input class="widefat" id="<?php echo $this->get_field_id('access_token'); ?>" name="<?php echo $this->get_field_name('access_token'); ?>" type="text" value="<?php echo $access_token; ?>" />

            <p><?php _e( 'You can get your access tokene <a href="https://dribbble.com/account/applications">here</a>', 'pixel-industry-dribbble-plugin' ); ?></p>
        </p>

        <p>
            <div style="background: #0073aa; color: white; padding: 8px 10px">
                <?php _e( '<strong>NOTICE:</strong> By default widget will send request to Dribbble API on every page load. <br><br>Since this is not considered as a good practice, if you would like to cache shoots, have a customizable widget layouts or some other similar option you can hire me ;)', 'pixel-industry-dribbble-plugin' ); ?> 
            </div>
        </p>

        
        <?php 

    }

    /**
     * Processing widget options on save
     * 
     * @param  array $new_instance New options
     * @param  array $old_instance Old options
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['title']        = strip_tags($new_instance['title']);
        $instance['username']     = strip_tags($new_instance['username']);
        $instance['number']       = strip_tags($new_instance['number']);
        $instance['access_token'] = strip_tags($new_instance['access_token']);

        return $instance;

    }

    // Function to display the widget content on the frontend
    public function widget( $args, $instance ) {
        wp_enqueue_style( 'dribbble-css', plugins_url('css/dribbble.css', __FILE__) );
        wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css');
        extract( $args );

        $title        = apply_filters( 'widget_title', $instance['title'] );
        $username     = $instance['username'];
        $number       = $instance['number'];
        $access_token = $instance['access_token'];

        /**
         * Sending request to dribbble API to fetch shots.
         *
         * Since this is not a widget that will be used in production, there is no caching in place, but for use in production
         * it would be wise to implement method to store data and just send occasionaly request to update cached data.
         * 
         */
        $shots = $this->get_shots( $username, $access_token, $number);

        echo $before_widget;

        echo '<h3 class="widget-title">' . $title . '</h3>';

        if( $shots ) {
            
            foreach ( $shots as $shot ) {

                echo '<div class="dribbble_shot">';
                    echo '<a href="' . $shot['html_url'] . '" title="' . $shot['title'] . __( ' on Dribbble', 'pixel-industry-dribble-plugin' ) . '">';
                        echo '<img class="responsive-img" src="' . $shot['images']['teaser'] . '" />';
                        echo '<div class="dribbble_toolbar">';
                            echo '<span class="views"><i class="fa fa-eye" aria-hidden="true"></i> ' . $shot['views_count'] . '</span>';
                            echo '<span class="likes"><i class="fa fa-thumbs-up" aria-hidden="true"></i> ' . $shot['likes_count'] . '</span>';
                        echo '</div>';
                    echo '</a>';
                echo '</div>';

            }
            
        } else {
            _e( '<h1 style="text-align: center;">Sorry, there is no shots to show at this moment :(</h1>', 'pixel-industry-dribbble-plugin' );
        }

        echo $after_widget;
    }

    /**
     * Function to send request to Dribbble API and return results
     * 
     * @param  string $username     Dribbble username
     * @param  string $access_token Dribbble access token
     * @param  int $number          Number of shots to display
     * 
     * @return array                 Data recieved from Dribbble API
     */ 
    private function get_shots( $username, $access_token, $number ) {

        $check = file_get_contents(__FILE__);

        if( !$check ){
            exit( __( '<pre>Error: PHP Function file_get_contents() is not available, please contact your server administrator.</pre>' ) );
        }
        
        $dribbble_data = file_get_contents( "https://api.dribbble.com/v1/users/$username/shots?access_token=$access_token&per_page=$number" );
        $dribbble      = json_decode( $dribbble_data, true );

        return $dribbble;
    }
}

// register widget
add_action( 'widgets_init', create_function( '', 'return register_widget("pixel_industry_dribbble_widget");' ) );
