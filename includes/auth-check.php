<?php
/**
 * Campus Job Posting System - Auth Check & Session State Helper
 * Handles session verification, role checks, and user authentication state.
 */

require_once __DIR__ . '/data-helper.php';

if (!function_exists('check_authenticated')) {
    /**
     * Check if a user is currently logged in
     */
    function check_authenticated() {
        return is_logged_in();
    }
}

if (!function_exists('get_current_auth_user')) {
    /**
     * Retrieve the currently logged-in user profile array or null
     */
    function get_current_auth_user() {
        return get_logged_user();
    }
}

if (!function_exists('check_user_role')) {
    /**
     * Verify if the active user matches a specific role (student, employer, admin)
     */
    function check_user_role($role) {
        return has_role($role);
    }
}

if (!function_exists('guard_authenticated_page')) {
    /**
     * Guard protected pages and redirect to login if unauthenticated
     */
    function guard_authenticated_page($allowed_roles = []) {
        require_auth($allowed_roles);
    }
}
