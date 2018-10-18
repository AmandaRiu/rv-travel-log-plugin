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

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*
 * Create the markup to display the custom fields.
 */
function custom_meta_box_markup( $object ){

    wp_nonce_field( basename( __FILE__ ), "meta-box-nonce" );    
    ?>  
        <div>
                <label for="meta-box-park-name">Park Name:</label>
                <br/>
                <input name="meta-box-park-name" type="text" value="<?php echo get_post_meta($object->ID, "meta-box-park-name", true); ?>" style="width: 100%;">
                <br/>

                <label for="meta-box-start">Start Date:</label>
                <br/>
                <input name="meta-box-start" type="Date" value="<?php echo get_post_meta($object->ID, "meta-box-start", true); ?>">
                <br/>

                <label for="meta-box-end">End Date:</label>
                <br/>
                <input name="meta-box-end" type="Date" value="<?php echo get_post_meta($object->ID, "meta-box-end", true); ?>">
                <br/>

                <label for="meta-box-park-cost">Park Cost: </label>
                <br/>
                <input name="meta-box-park-cost" type="number" min="0.01" step="0.01" max="50000.00" value="<?php echo get_post_meta($object->ID, "meta-box-park-cost", true); ?>">            
                <br/>

                <label for="meta-box-site-number">Site Number: </label>
                <br/>
                <input name="meta-box-site-number" type="text" value="<?php echo get_post_meta($object->ID, "meta-box-site-number", true); ?>">
                <br/>
        </div>
    <?php
}

function add_custom_meta_box() {
        add_meta_box(
                "demo-meta-box", 
                "RV Travel Log", 
                "custom_meta_box_markup", 
                "post", 
                "side", 
                "high", 
                null
        );
}

add_action( "add_meta_boxes", "add_custom_meta_box" );

function save_custom_meta_box( $post_id, $post, $update ) {
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

        if ( isset( $_POST["meta-box-start"] ) ) {
                $meta_box_start_value = $_POST["meta-box-start"];
        }
        update_post_meta( $post_id, "meta-box-start", $meta_box_start_value );

        if ( isset( $_POST["meta-box-end"] ) ) {
                $meta_box_end_value = $_POST["meta-box-end"];
        }
        update_post_meta( $post_id, "meta-box-end", $meta_box_end_value );

        if ( isset( $_POST["meta-box-park-name"] ) ) {
                $meta_box_park_name_value = $_POST["meta-box-park-name"];
        }
        update_post_meta( $post_id, "meta-box-park-name", $meta_box_park_name_value );

        if ( isset( $_POST["meta-box-park-cost"] ) ) {
                $meta_box_park_cost_value = $_POST["meta-box-park-cost"];
        }
        update_post_meta( $post_id, "meta-box-park-cost", $meta_box_park_cost_value );

        if ( isset( $_POST["meta-box-site-number"] ) ) {
                $meta_box_site_number_value = $_POST["meta-box-site-number"];
        }
        update_post_meta( $post_id, "meta-box-site-number", $meta_box_site_number_value );
}

add_action( "save_post", "save_custom_meta_box", 10, 3 );

function get_display_custom_meta_markup( $post_id ) {
        // fetch post metadata
        $stored_start_date = get_post_meta( $post_id, 'meta-box-start' );
        $stored_end_date = get_post_meta( $post_id, 'meta-box-end' );
        $stored_park_name = get_post_meta( $post_id, 'meta-box-park-name' );
        $stored_park_cost = get_post_meta( $post_id, 'meta-box-park-cost' );
        $stored_site_number = get_post_meta( $post_id, 'meta-box-site-number' );

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

function append_custom_footer( $content ) {
        return $content .= '<br/>' . get_display_custom_meta_markup( get_the_ID() );
}

add_filter( 'the_content', 'append_custom_footer' );
?>
