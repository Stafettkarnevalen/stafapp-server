<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 12/06/2018
 * Time: 21.21
 */

namespace App\Controller;


/**
 * Class Routes
 * - Contains all routes as constants
 *
 * @package App\Controller
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
final class Routes
{
    // AdminUserController HTML calls
    const nav_admin_list_users = 'nav.admin_list_users';
    const nav_admin_create_user = 'nav.admin_create_user';
    const nav_admin_toggle_user = 'nav.admin_toggle_user';
    const nav_admin_edit_user = 'nav.admin_edit_user';
    const nav_admin_delete_user = 'nav.admin_delete_user';
    const nav_admin_simulate_user = 'nav.admin_simulate_user';

    // AdminUserController API calls
    const api_admin_list_users = 'api.admin_list_users';
    const api_admin_toggle_user = 'api.admin_toggle_user';
    const api_admin_create_user = 'api.admin_create_user';
    const api_admin_read_user = 'api.admin_read_user';
    const api_admin_patch_user = 'api.admin_patch_user';
    const api_admin_edit_user = 'api.admin_edit_user';
    const api_admin_delete_user = 'api.admin_delete_user';

    // AuthenticatedUserController HTML calls
    const nav_authuser_merge_profile_data = 'nav.authuser_merge_profile_data';

    // AuthenticatedUserController API calls
    const api_authuser_merge_profile_data = 'api.authuser_merge_profile_data';
}