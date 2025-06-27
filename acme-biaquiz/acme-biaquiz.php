<?php
/**
 * Plugin Name: ACME BIAQuiz
 * Description: Provides themed BIA quiz training with import/export features.
 * Version: 0.1.0
 * Author: ACME
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Silence is golden!
}

require_once __DIR__ . '/includes/class-acme-biaquiz.php';

/**
 * Create default quiz categories on plugin activation.
 */
function acme_biaquiz_activate() {
    $plugin = ACME_BIAQuiz::instance();
    $plugin->register_post_types();
    $plugin->register_taxonomies();

    $categories = [
        'Aérodynamique et mécanique du vol',
        'Connaissance des aéronefs',
        'Météorologie',
        'Navigation, règlementation et sécurité des vols',
        "Histoire de l'aéronautique et de l'espace",
        'Anglais aéronautique',
    ];

    foreach ( $categories as $category ) {
        if ( ! term_exists( $category, ACME_BIAQuiz::TAX_CATEGORY ) ) {
            wp_insert_term( $category, ACME_BIAQuiz::TAX_CATEGORY );
        }
    }
}

register_activation_hook( __FILE__, 'acme_biaquiz_activate' );

add_action( 'plugins_loaded', [ 'ACME_BIAQuiz', 'instance' ] );
