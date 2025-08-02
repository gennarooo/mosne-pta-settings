<?php
/**
 * Plugin Name: Post Type Archive Settings
 * Plugin URI: https://mosne.it/
 * Description: Adds editable title and description fields for post type archive pages, with Polylang compatibility.
 * Version: 1.0.0
 * Author: mosne
 * Author URI: https://mosne.it
 * Text Domain: mosne-pta
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load textdomain for translations
function mosne_pta_load_textdomain() {
    load_plugin_textdomain( 'mosne-pta', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'mosne_pta_load_textdomain' );

// Add submenu page under each post type menu
function mosne_pta_add_archive_submenu() {
    $post_types = get_post_types( [ 'show_ui' => true ], 'objects' );
    foreach ( $post_types as $slug => $object ) {
        if ( $object->has_archive ) {
            $cap = isset( $object->cap->edit_posts ) ? $object->cap->edit_posts : 'manage_options';
            add_submenu_page(
                "edit.php?post_type={$slug}",
                __( 'Archive Settings', 'mosne-pta' ),
                __( 'Archive Settings', 'mosne-pta' ),
                $cap,
                "mosne-pta-archive-{$slug}",
                'mosne_pta_render_settings_page'
            );
        }
    }
}
add_action( 'admin_menu', 'mosne_pta_add_archive_submenu' );

// Render the settings page (determines post type from 'page' query var)
function mosne_pta_render_settings_page() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        return;
    }
    if ( ! isset( $_GET['page'] ) || strpos( $_GET['page'], 'mosne-pta-archive-' ) !== 0 ) {
        return;
    }
    $post_type = substr( $_GET['page'], strlen( 'mosne-pta-archive-' ) );

    // Handle save
    if ( isset( $_POST['mosne_pta_nonce'] ) && wp_verify_nonce( $_POST['mosne_pta_nonce'], 'mosne_pta_save_' . $post_type ) ) {
        $cap = get_post_type_object( $post_type )->cap->edit_posts;
        if ( ! current_user_can( $cap ) ) {
            wp_die( esc_html__( 'Insufficient permissions.', 'mosne-pta' ) );
        }
        $lang        = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
        $name_title  = "{$post_type}_archive_title" . ( $lang ? "_{$lang}" : '' );
        $name_desc   = "{$post_type}_archive_description" . ( $lang ? "_{$lang}" : '' );
        $title_value = isset( $_POST['mosne_pta_title'] ) ? sanitize_text_field( wp_unslash( $_POST['mosne_pta_title'] ) ) : '';
        $desc_value  = isset( $_POST['mosne_pta_description'] ) ? wp_kses_post( wp_unslash( $_POST['mosne_pta_description'] ) ) : '';

        update_option( $name_title, $title_value );
        update_option( $name_desc, $desc_value );

        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'mosne-pta' ) . '</p></div>';
    }

    // Get existing values
    $lang        = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
    $name_title  = "{$post_type}_archive_title" . ( $lang ? "_{$lang}" : '' );
    $name_desc   = "{$post_type}_archive_description" . ( $lang ? "_{$lang}" : '' );
    $value_title = get_option( $name_title, '' );
    $value_desc  = get_option( $name_desc, '' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_post_type_object( $post_type )->labels->name ); ?>: <?php esc_html_e( 'Archive Settings', 'mosne-pta' ); ?></h1>
        <form method="post">
            <?php wp_nonce_field( 'mosne_pta_save_' . $post_type, 'mosne_pta_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="mosne_pta_title"><?php esc_html_e( 'Archive Title', 'mosne-pta' ); ?></label></th>
                    <td>
                        <input type="text" id="mosne_pta_title" name="mosne_pta_title" value="<?php echo esc_attr( $value_title ); ?>" class="large-text"  style="font-size:20px;"/>
                    </td>
                </tr>
                <tr>
                    <th><label for="mosne_pta_description"><?php esc_html_e( 'Archive Description', 'mosne-pta' ); ?></label></th>
                    <td>
                        <?php
                        wp_editor(
                            $value_desc,
                            'mosne_pta_description',
                            [
                                'textarea_name' => 'mosne_pta_description',
                                'textarea_rows' => 5,
                                'media_buttons' => false,
                                'teeny'         => true,
                            ]
                        );
                        ?>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Helper functions to get/echo title and description
function mosne_pta_get_archive_title( $post_type = '' ) {
    if ( ! $post_type ) {
        $post_type = get_query_var( 'post_type' ) ?: ( is_post_type_archive() ? get_queried_object()->name : '' );
    }
    $lang   = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
    $option = "{$post_type}_archive_title" . ( $lang ? "_{$lang}" : '' );
    $title  = get_option( $option );
    return $title ? $title : post_type_archive_title( '', false );
}

function mosne_pta_the_archive_title( $post_type = '' ) {
    echo esc_html( mosne_pta_get_archive_title( $post_type ) );
}

function mosne_pta_get_archive_description( $post_type = '' ) {
    if ( ! $post_type ) {
        $post_type = get_query_var( 'post_type' ) ?: ( is_post_type_archive() ? get_queried_object()->name : '' );
    }
    $lang   = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
    $option = "{$post_type}_archive_description" . ( $lang ? "_{$lang}" : '' );
    $desc   = get_option( $option );
    return apply_filters( 'the_content', $desc );
}

function mosne_pta_the_archive_description( $post_type = '' ) {
    echo mosne_pta_get_archive_description( $post_type );
}
