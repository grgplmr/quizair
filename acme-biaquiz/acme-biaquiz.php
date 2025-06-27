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

add_action( 'plugins_loaded', [ 'ACME_BIAQuiz', 'instance' ] );
