<?php
/**
 * RV Travel Log
 *
 * @author      Amanda Riu
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: RV Travel Log
 * Description: Collects additional metadata during a post that is used to create a travel log.
 * Version:     0.0.1
 * Author:      Amanda Riu
 * Author URI:
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

 // If this file is called directly, abort.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

register_activation_hook( __FILE__, 'riu_rv_log_plugin_activated' );
/**
 * Called when this plugin is activated. Use this to set up this plugin — for example, 
 * creating some default settings in the options table.
 */
function riu_rv_log_plugin_activated() {
    error_log( "RV Travel Log plugin has been activated!" );       
}

register_deactivation_hook( __FILE__, 'riu_rv_log_plugin_deactivated' );
/**
 * Called when this plugin is deactivated. Use this to clear any temporary data stores by this plugin.
 */
function riu_rv_log_plugin_deactivated() {
    error_log( "RV Travel Log plugin has been deactivated!" );
}

//add_action( 'all', 'riu_rv_log_debug' );
/**
 * Will print every action! Really not all that useful.
 */
function riu_rv_log_debug() {
    error_log( current_action() );
}

/*
 * Create the markup to display the custom fields.
 */
function riu_rv_log_custom_meta_box_markup( $object ) {

    wp_nonce_field( basename( __FILE__ ), "meta-box-nonce" );    
    ?>  
        <div>
                <label for="riu-rv-log-park-name">Park Name:</label>
                <br/>
                <input name="riu-rv-log-park-name" type="text" value="<?php echo get_post_meta($object->ID, "riu-rv-log-park-name", true); ?>" style="width: 100%;">
                <br/>

                <label for="riu-rv-log-start-date">Start Date:</label>
                <br/>
                <input name="riu-rv-log-start-date" type="Date" value="<?php echo get_post_meta($object->ID, "riu-rv-log-start-date", true); ?>">
                <br/>

                <label for="riu-rv-log-end-date">End Date:</label>
                <br/>
                <input name="riu-rv-log-end-date" type="Date" value="<?php echo get_post_meta($object->ID, "riu-rv-log-end-date", true); ?>">
                <br/>

                <label for="riu-rv-log-park-cost">Park Cost: </label>
                <br/>
                <input name="riu-rv-log-park-cost" type="number" min="0.01" step="0.01" max="50000.00" value="<?php echo get_post_meta($object->ID, "riu-rv-log-park-cost", true); ?>">            
                <br/>

                <label for="riu-rv-log-site-number">Site Number: </label>
                <br/>
                <input name="riu-rv-log-site-number" type="text" value="<?php echo get_post_meta($object->ID, "riu-rv-log-site-number", true); ?>">
                <br/>
        </div>
    <?php
}

add_action( "add_meta_boxes", "riu_rv_log_add_custom_meta_box" );
/**
 * 
 */
function riu_rv_log_add_custom_meta_box() {
    add_meta_box(
        "demo-meta-box", 
        "RV Travel Log", 
        "riu_rv_log_custom_meta_box_markup", 
        "post", 
        "side", 
        "high", 
        null
    );
}

add_action( "save_post", "riu_rv_log_save_custom_meta_box", 10, 3 );
/**
 * Save plugin metadata when the post is saved or published.
 */
function riu_rv_log_save_custom_meta_box( $post_id, $post, $update ) {
    if ( ! isset( $_POST["meta-box-nonce"] ) || !wp_verify_nonce( $_POST["meta-box-nonce"], basename( __FILE__ ) ) ) {
        return $post_id;
    }

    if ( !current_user_can( "edit_post", $post_id ) ) {
        return $post_id;
    }

    if ( defined( "DOING_AUTOSAVE" ) && DOING_AUTOSAVE ) {
        return $post_id;
    }

    $slug = "post";
    if ( $slug != $post->post_type ) {
        return $post_id;
    }

    $meta_box_start_value = "";
    $meta_box_end_value = "";
    $meta_box_park_name_value = "";
    $meta_box_park_cost_value = "";
    $meta_box_site_number_value = "";

    if ( isset( $_POST["riu-rv-log-start-date"] ) ) {
        $meta_box_start_value = $_POST["riu-rv-log-start-date"];
    }
    update_post_meta( $post_id, "riu-rv-log-start-date", $meta_box_start_value );

    if ( isset( $_POST["riu-rv-log-end-date"] ) ) {
        $meta_box_end_value = $_POST["riu-rv-log-end-date"];
    }
    update_post_meta( $post_id, "riu-rv-log-end-date", $meta_box_end_value );

    if ( isset( $_POST["riu-rv-log-park-name"] ) ) {
        $meta_box_park_name_value = $_POST["riu-rv-log-park-name"];
    }
    update_post_meta( $post_id, "riu-rv-log-park-name", $meta_box_park_name_value );

    if ( isset( $_POST["riu-rv-log-park-cost"] ) ) {
        $meta_box_park_cost_value = $_POST["riu-rv-log-park-cost"];
    }
    update_post_meta( $post_id, "riu-rv-log-park-cost", $meta_box_park_cost_value );

    if ( isset( $_POST["riu-rv-log-site-number"] ) ) {
        $meta_box_site_number_value = $_POST["riu-rv-log-site-number"];
    }
    update_post_meta( $post_id, "riu-rv-log-site-number", $meta_box_site_number_value );
}

add_filter( 'the_content', 'riu_rv_log_append_custom_footer' );
/**
 * Parse and display the plugin metadata in the post view.
 */
function riu_rv_log_append_custom_footer( $content ) {
    return $content .= '<br/>' . get_display_custom_meta_markup( get_the_ID() );
}

/**
 * Creates the markup for displaying the RV Travel log data when the user is viewing
 * the post.
 */
function get_display_custom_meta_markup( $post_id ) {
    // fetch post metadata
    $stored_start_date = get_post_meta( $post_id, 'riu-rv-log-start-date' );
    $stored_end_date = get_post_meta( $post_id, 'riu-rv-log-end-date' );
    $stored_park_name = get_post_meta( $post_id, 'riu-rv-log-park-name' );
    $stored_park_cost = get_post_meta( $post_id, 'riu-rv-log-park-cost' );
    $stored_site_number = get_post_meta( $post_id, 'riu-rv-log-site-number' );

    if ( !empty( $stored_park_name ) ) {
        // return formatted view
        $return = "<div class='park-info'>";
        $return .= '<h4>Park Info</h4>';
        $return .= '<strong>Park name</strong>: ' . $stored_park_name[0] . '<br/>';

        $formatted_start_date = date('m/d/Y', strtotime( $stored_start_date[0] ) );
        $formatted_end_date = date('m/d/Y', strtotime( $stored_end_date[0] ) );
        if ( !empty( $stored_start_date ) ) $return .= '<strong>Start date</strong>: ' . $formatted_start_date . '<br/>';
        if ( !empty( $stored_end_date ) ) $return .= '<strong>End date</strong>: ' . $formatted_end_date . '<br/>';
        
        if ( !empty( $stored_site_number ) ) $return .= '<strong>Site</strong>: ' . $stored_site_number[0] . '<br/>';
        
        $formatted_cost = money_format('$%i', $stored_park_cost[0]);
        if ( !empty( $stored_park_cost ) ) $return .= '<strong>Cost</strong>: ' . $formatted_cost . '<br/>';
        $return .= '</div>';
    }

    return $return;
}


/**
 * 
 * 
 * SHORTCODES
 * 
 */

add_action('init', 'riu_rv_log_shortcodes_init');
 /**
  * Add shortcode
  */
function riu_rv_log_shortcodes_init() {
    function riu_rv_log_shortcode($atts = [], $content = null) {

        // Get a list of posts with rv metadata
        $args = array(
            'post_type' => 'post',
            'meta_key' => 'riu-rv-log-start-date',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'riu-rv-log-park-name',
                    'compare' => 'EXISTS',
                ),
            ),
        );
        $query = new WP_Query($args);
        $return = '<div class="park-info">';
        $return .= '<table style="width:100%">';
        $return .= '<tr>';
        $return .= '<th>Campground Name</th>';
        $return .= '<th>Start Date</th>';
        $return .= '<th>End Date</th>';
        $return .= '<th>Cost</th>';
        $return .= '<th>Site Number</th>';
        $return .= '</tr>';
        while ( $query->have_posts() ): $query->the_post(); global $post;
            // Print to the screen
            $return .= get_display_rv_table_markup($post->ID);
        endwhile; wp_reset_postdata();
        $return .= '</table>';
        echo $return;
        // always return
        return $content;
    }

    add_shortcode('riu_rv_log', 'riu_rv_log_shortcode');
}

function get_display_rv_table_markup($post_id) {
    // fetch post metadata
    $stored_start_date = get_post_meta( $post_id, 'riu-rv-log-start-date' );
    $stored_end_date = get_post_meta( $post_id, 'riu-rv-log-end-date' );
    $stored_park_name = get_post_meta( $post_id, 'riu-rv-log-park-name' );
    $stored_park_cost = get_post_meta( $post_id, 'riu-rv-log-park-cost' );
    $stored_site_number = get_post_meta( $post_id, 'riu-rv-log-site-number' );

    $return = '<tr>';
    if ( !empty( $stored_park_name ) ) {
        // return formatted view
        $return .= '<td style="width:40%">' . $stored_park_name[0] . '</td>';
        $return .= '<td>' . $stored_start_date[0] . '</td>';
        $return .= '<td>' . $stored_end_date[0] . '</td>';
        $return .= '<td>' . $stored_park_cost[0] . '</td>';
        $return .= '<td>' . $stored_site_number[0] . '</td>';
    }
    $return .= '</tr>';

    return $return;
}
?>
