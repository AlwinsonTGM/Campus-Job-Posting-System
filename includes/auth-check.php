<?php
declare(strict_types=1);

/**
 * Campus Job Posting System - Role Routing & Navigation Helpers
 */

require_once __DIR__ . '/data-helper.php';

if (!function_exists('get_role_dashboard_url')) {
    /**
     * Resolve default landing page relative URL for a given role
     */
    function get_role_dashboard_url(?string $role): string {
        return match ($role) {
            'student'  => 'student/dashboard.php',
            'employer' => 'employer/dashboard.php',
            'admin'    => 'admin/reports.php',
            default    => 'index.php'
        };
    }
}

if (!function_exists('redirect_by_role')) {
    /**
     * Redirect active user to their corresponding role dashboard and exit
     */
    function redirect_by_role(?string $role): void {
        $url = get_role_dashboard_url($role);
        if (!headers_sent()) {
            header('Location: ' . $url);
            exit;
        }
        echo '<script>window.location.href = ' . json_encode($url, JSON_HEX_TAG | JSON_HEX_AMP) . ';</script>';
        exit;
    }
}
