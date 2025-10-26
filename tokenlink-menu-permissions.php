<?php
/*
Plugin Name: TokenLink Menu Permissions
Plugin URI: https://github.com/jcbenton/tokenlink-menu-permissions
Description: Adds per-menu-item visibility controls (everyone / logged-in / logged-out + roles) for WordPress menus.
Version: 1.0.3
Author: Jerry Benton
Author URI: https://www.mailborder.com
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

/* Primary Comments */
/* Constants */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TL_MENU_PERMISSIONS_META_STATUS', '_tl_menu_visibility_status' );
define( 'TL_MENU_PERMISSIONS_META_ROLES',  '_tl_menu_visibility_roles' );

/* Primary Comments */
/* Utility: Get editable roles */
function tlmp_get_roles_map() {
    $roles = function_exists( 'get_editable_roles' ) ? get_editable_roles() : wp_roles()->roles;
    $map   = array();
    foreach ( $roles as $role_key => $role_data ) {
        $name = isset( $role_data['name'] ) ? $role_data['name'] : $role_key;
        $map[ $role_key ] = $name;
    }
    return $map;
}

/* Primary Comments */
/* Admin UI: Add custom fields to menu items */
add_action( 'wp_nav_menu_item_custom_fields', 'tlmp_add_custom_fields', 10, 4 );
function tlmp_add_custom_fields( $item_id, $item, $depth, $args ) {
    $status = get_post_meta( $item_id, TL_MENU_PERMISSIONS_META_STATUS, true ) ?: 'everyone';
    $roles  = get_post_meta( $item_id, TL_MENU_PERMISSIONS_META_ROLES, true );
    $roles  = is_array( $roles ) ? $roles : array();
    $roles_map = tlmp_get_roles_map();
    ?>
    <div class="field-tlmp-visibility description-wide" style="margin:12px 0;">
        <p><strong><?php esc_html_e( 'TokenLink Menu Permissions', 'tokenlink-menu-permissions' ); ?></strong></p>

        <p class="description">
            <label for="edit-menu-item-tlmp-status-<?php echo esc_attr( $item_id ); ?>">
                <?php esc_html_e( 'Show to', 'tokenlink-menu-permissions' ); ?>:
                <select class="tlmp-visibility-select"
                        id="edit-menu-item-tlmp-status-<?php echo esc_attr( $item_id ); ?>"
                        name="tlmp_status[<?php echo esc_attr( $item_id ); ?>]">
                    <option value="everyone"   <?php selected( $status, 'everyone' ); ?>><?php esc_html_e( 'Everyone', 'tokenlink-menu-permissions' ); ?></option>
                    <option value="logged_in"  <?php selected( $status, 'logged_in' ); ?>><?php esc_html_e( 'Logged-in users only', 'tokenlink-menu-permissions' ); ?></option>
                    <option value="logged_out" <?php selected( $status, 'logged_out' ); ?>><?php esc_html_e( 'Logged-out users only', 'tokenlink-menu-permissions' ); ?></option>
                </select>
            </label>
        </p>

        <div class="tlmp-roles" style="padding:8px 10px; border:1px solid #ccd0d4; border-radius:4px; background:#fff;">
            <em><?php esc_html_e( 'If "Logged-in users only" is selected, choose roles allowed to see this item.', 'tokenlink-menu-permissions' ); ?></em><br/>
            <?php foreach ( $roles_map as $role_key => $role_label ) : ?>
                <label style="display:inline-block; margin-right:10px; margin-bottom:6px;">
                    <input type="checkbox"
                           class="tlmp-role-checkbox"
                           name="tlmp_roles[<?php echo esc_attr( $item_id ); ?>][]"
                           value="<?php echo esc_attr( $role_key ); ?>"
                           <?php checked( in_array( $role_key, $roles, true ) ); ?> />
                    <?php echo esc_html( $role_label ); ?>
                </label>
            <?php endforeach; ?>
        </div>

        <?php wp_nonce_field( 'tlmp_menu_permissions_save', 'tlmp_menu_permissions_nonce' ); ?>

    </div>
    <?php
}

/* Primary Comments */
/* Save menu item metadata with nonce verification */
add_action( 'wp_update_nav_menu_item', 'tlmp_save_menu_item', 10, 3 );
function tlmp_save_menu_item( $menu_id, $menu_item_db_id, $args ) {

    /* Verify nonce before processing any form data */
    if (
        ! isset( $_POST['tlmp_menu_permissions_nonce'] ) ||
        ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tlmp_menu_permissions_nonce'] ) ), 'tlmp_menu_permissions_save' )
    ) {
        return; /* Nonce missing or invalid — do not process */
    }

    /* Sanitize and save visibility status */
    $status = isset( $_POST['tlmp_status'][ $menu_item_db_id ] )
        ? sanitize_text_field( wp_unslash( $_POST['tlmp_status'][ $menu_item_db_id ] ) )
        : 'everyone';

    if ( ! in_array( $status, array( 'everyone', 'logged_in', 'logged_out' ), true ) ) {
        $status = 'everyone';
    }

    update_post_meta( $menu_item_db_id, TL_MENU_PERMISSIONS_META_STATUS, $status );

    /* Sanitize and save roles */
    $roles = array();
    if ( isset( $_POST['tlmp_roles'][ $menu_item_db_id ] ) && is_array( $_POST['tlmp_roles'][ $menu_item_db_id ] ) ) {
        $roles = array_map( 'sanitize_text_field', wp_unslash( $_POST['tlmp_roles'][ $menu_item_db_id ] ) );
        $roles = array_values( array_unique( $roles ) );
    }

    update_post_meta( $menu_item_db_id, TL_MENU_PERMISSIONS_META_ROLES, $roles );
}

/* Primary Comments */
/* Filter menu items on output */
add_filter( 'wp_nav_menu_objects', 'tlmp_filter_menu_items', 10, 2 );
function tlmp_filter_menu_items( $items, $args ) {
    if ( empty( $items ) ) return $items;

    $is_logged_in = is_user_logged_in();
    $user_roles = $is_logged_in ? (array) wp_get_current_user()->roles : array();
    $remove_ids = array();

    foreach ( $items as $item ) {
        $status = get_post_meta( $item->ID, TL_MENU_PERMISSIONS_META_STATUS, true ) ?: 'everyone';
        if ( $status === 'everyone' ) continue;

        if ( $status === 'logged_out' && $is_logged_in ) {
            $remove_ids[$item->ID] = true; continue;
        }

        if ( $status === 'logged_in' && ! $is_logged_in ) {
            $remove_ids[$item->ID] = true; continue;
        }

        if ( $status === 'logged_in' ) {
            $roles = get_post_meta( $item->ID, TL_MENU_PERMISSIONS_META_ROLES, true );
            $roles = is_array( $roles ) ? $roles : array();
            if ( ! empty( $roles ) ) {
                $allowed = array_intersect( $roles, $user_roles );
                if ( empty( $allowed ) ) {
                    $remove_ids[$item->ID] = true;
                    continue;
                }
            }
        }
    }

    /* Hide children of removed parents */
    if ( ! empty( $remove_ids ) ) {
        $changed = true;
        while ( $changed ) {
            $changed = false;
            foreach ( $items as $it ) {
                if ( isset( $remove_ids[ $it->ID ] ) ) continue;
                $parent = (int) $it->menu_item_parent;
                if ( $parent && isset( $remove_ids[ $parent ] ) ) {
                    $remove_ids[ $it->ID ] = true;
                    $changed = true;
                }
            }
        }
    }

    foreach ( $items as $key => $it ) {
        if ( isset( $remove_ids[ $it->ID ] ) ) unset( $items[$key] );
    }

    return array_values( $items );
}

/* Primary Comments */
/* Admin JS: disable role checkboxes unless 'logged_in' is selected */
add_action( 'admin_print_footer_scripts-nav-menus.php', 'tlmp_admin_js' );
function tlmp_admin_js() {
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateRoleCheckboxState(container) {
        const select = container.querySelector('.tlmp-visibility-select');
        const checkboxes = container.querySelectorAll('.tlmp-role-checkbox');
        const disable = select && select.value !== 'logged_in';
        checkboxes.forEach(cb => { cb.disabled = disable; });
        container.style.opacity = disable ? '0.6' : '1.0';
    }

    document.querySelectorAll('.field-tlmp-visibility').forEach(container => {
        updateRoleCheckboxState(container);
        const select = container.querySelector('.tlmp-visibility-select');
        if (select) {
            select.addEventListener('change', () => updateRoleCheckboxState(container));
        }
    });
});
</script>
<?php
}
