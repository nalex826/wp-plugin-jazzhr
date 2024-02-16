<?php
/*
Plugin Name: JazzHR API
Description: Allow access to JazzHR API
Version: 1.0
Author: Alex Nguyen
License: GPLv3
*/

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once dirname(__FILE__) . '/inc/jazzhr.class.php';

// Define the shortcode function
function jazzhr_shortcode_function($atts)
{
    // Instantiate the JazzHr class
    $jazzhr = new JazzHr();

    // Call the method to connect to JazzHR API and retrieve jobs
    $jobs = $jazzhr->connect_jobs();

    // Check if jobs are retrieved successfully
    if ($jobs) {
        // Display the jobs
        $output = '<ul>';
        foreach ($jobs as $job) {
            $output .= '<li>' . esc_html($job['title']) . '</li>';
        }
        $output .= '</ul>';
    } else {
        // If no jobs retrieved, display a message
        $output = 'No jobs available at the moment.';
    }

    return $output;
}

// Register the shortcode
add_shortcode('jazzhr_jobs', 'jazzhr_shortcode_function');

// Initialize JazzHR Admin Panel
if (is_admin()) {
    // require_once ABSPATH . '/wp-includes/pluggable.php';
    require_once dirname(__FILE__) . '/inc/jazzhradmin.class.php';

    // Initialize JazzHR Admin
    $jazzAdmin = new JazzHrAdmin();

    // Delete cache if action is delete and settings are not updated
    if (! empty($_GET['action']) && 'delete' == $_GET['action'] && empty($_GET['settings-updated'])) {
        $jazzAdmin->delete_cache();
    }
}
