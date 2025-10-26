=== TokenLink Menu Permissions ===
Contributors: mailborder
Donate link: https://donate.stripe.com/14AdRa6XJ1Xn8yT8KObfO00
Tags: menu, permissions, visibility, roles
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Adds per-menu-item visibility controls (everyone / logged-in / logged-out + roles) for WordPress menus.

== Description ==

**TokenLink Menu Permissions** is a lightweight, zero-bloat plugin that lets you control which users can see specific menu items in WordPress.

Each menu item in *Appearance → Menus* gains new visibility options:
- **Everyone** — visible to all visitors.
- **Logged-in users only** — visible only when a user is logged in.
- **Logged-out users only** — visible only to guests.
- Optional: Restrict visibility to one or more specific **user roles** (Administrator, Editor, Subscriber, etc.).

If a parent menu item is hidden, all its child menu items are automatically hidden too.

**Key Features**
- Adds visibility options directly to menu item settings.
- Supports per-item role-based visibility.
- Grays out role checkboxes when not applicable.
- Works with all standard themes and WordPress menus.
- No external dependencies or extra database tables.
- 100% compatible with caching and custom roles.

Built for developers and site owners who want precise menu control without the heavy overhead of typical public plugins.

== Installation ==

1. Upload the `tokenlink-menu-permissions` folder to `/wp-content/plugins/`.
2. Activate the plugin through the *Plugins* menu in WordPress.
3. Go to *Appearance → Menus*.
4. Edit a menu item and set its **visibility options** at the bottom of the item panel.
5. Save your menu.

== Frequently Asked Questions ==

= Does this plugin modify or replace existing menus? =
No. It only hides menu items that the current visitor should not see.

= What happens to child menu items if the parent is hidden? =
Child items are automatically hidden to prevent orphaned links.

= Can I restrict menu items by capability instead of role? =
Not currently. Role-based control is simpler and more predictable for most use cases.

= Does this affect custom walker menus or mega menu plugins? =
It works with any menu that relies on the standard `wp_nav_menu()` API.

= Is JavaScript required? =
Only for the admin screen to gray-out role checkboxes. The front end logic is entirely PHP-based.

== Screenshots ==

1. Visibility options added to each menu item in the Menus editor.
2. Role checkboxes active only when "Logged-in users only" is selected.

== Changelog ==

= 1.0.2 =
* Code fix for Wordpress standards. 

= 1.0.1 =
* Initial public release.
* Added admin UI role toggle JavaScript.
* Improved role handling and parent-child filtering.

== Upgrade Notice ==

= 1.0.2 =
No user action required for this upgrade. 

== Credits ==

Developed by **Jerry Benton**  
Website: [https://www.mailborder.com](https://www.mailborder.com)  
GitHub: [https://github.com/jcbenton/tokenlink-menu-permissions](https://github.com/jcbenton/tokenlink-menu-permissions)

== License ==

This plugin is free software, distributed under the terms of the GNU General Public License v3 or later.  
See [https://www.gnu.org/licenses/gpl-3.0.html](https://www.gnu.org/licenses/gpl-3.0.html) for full license text.