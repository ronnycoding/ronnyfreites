=== WP Engine Smart Search ===
Tags: search
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 0.2.53
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Author: WP Engine
Contributors: wpengine toughcrab24 konrad-glowacki ciaranshan1 dimitrios-wpengine ericosullivanwp RBrady98 richard-wpengine mindctrl

A WordPress plugin to enhance the default WordPress search features for wp-graphql, and REST search methods.

== Description ==

WP Engine Smart Search is an enhanced search solution for WordPress. WordPress default search struggles to surface relevant content
from searches, this plugin enables WordPress to query vastly more relevant and refined content. The plugin enables a
variety of configuration options and is compatible with builtins and a variety of popular plugins such as, Custom Post Type UI,
Advanced Custom Fields (ACF).


== Installation ==

This plugin and its credentials will be automatically installed and configured on your WordPress instance after purchasing WP Engine Smart Search on your WP Engine Plan and assigning a license to the environment in the user portal.

This plugin can be installed directly from your WordPress site.

* Log in to your WordPress site and navigate to **Plugins &rarr; Add New**.
* Type "Smart Search" into the Search box.
* Locate the WP Engine Smart Search plugin in the list of search results and click **Install Now**.
* Once installed, click the Activate button.

It can also be installed manually using a zip file.

* Download the WP Engine Smart Search plugin from WordPress.org.
* Log in to your WordPress site and navigate to **Plugins &rarr; Add New**.
* Click the **Upload Plugin** button.
* Click the **Choose File** button, select the zip file you downloaded in step 1, then click the **Install Now** button.
* Click the **Activate Plugin** button.

Configuring the Plugin once activated

* Navigate to the WP Engine Smart Search settings page in WP Admin
* Enter your URL and Access Token on the WP Engine Smart Search settings page
* Click save

**NOTE:** credentials for this plugin can only be obtained by purchasing this as an add-on as a part of your existing WP Engine Plan.
Please see [wpengine.com/smart-search](https://wpengine.com/smart-search/) for details.

== Changelog ==
= 0.2.53 =
* **Added:** Standard security headers for HTTP rest calls.
* **Added:** Enable Hybrid/Semantic Search for multisite.

= 0.2.52 =
* **Added:** Support for WordPress 6.6.

= 0.2.51 =
* **Fixed:** Fixed typos.

= 0.2.50 =
* **Updated:** Renamed Sync page to Index Data.
* **Updated:** Left menu items reordered - Index Data is default page now.
* **Added:** Settings page reworked. AI-Powered Hybrid Search page was merged into Settings pages.

= 0.2.49 =
* **Fixed:** WP Engine Smart Search not running for admin searches.
* **Fixed:** All sites are indexed in multisite.

= 0.2.48 =
* **Added:** Basic support for Polylang plugin.
* **Fixed:** Corrected inaccurate text messages.

= 0.2.47 =
* **Added:** UI re-skin.

= 0.2.46 =
* **Added:** Support for taxonomy filtering.

= 0.2.45 =
* **Added:** Make clear data indexing needs to be completed.
* **Added:** Add meta in requests when multisite.

= 0.2.44 =
* **Added:** Strip html tags from post_content.
* **Added:** Minimum multisite support. Only Network admins can sync all multisite content.
* **Added:** When multisite, Search Config and AI-Powered Hybrid Search pages were removed from network admin.

= 0.2.43 =
* **Fixed:** Filtering issues when excluding post types.
* **Added:** Support for WordPress 6.5.

= 0.2.42 =
* **Fixed:** Filtering issues when more than two terms present.

= 0.2.41 =
* **Fixed:**  When selected fields are empty semantic search shouldn't return results.
* **Fixed:**  When all selected fields are unchecked for a selected post type search shouldn't return results for this post type.


= 0.2.40 =
* **Fixed:**  Update AI config page name and fixed typos.

= 0.2.39 =
* **Fixed:**  Using multiple wp-graphql queries causing issues in cursor pagination.
* **Fixed:**  When no fields are selected full text search should return 0 results.

= 0.2.38 =
* **Fixed:**  Allow pages to be queried by default.
* **Fixed:**  Only allow valid fields to be selectable for Semantic Search.

= 0.2.37 =
* **Fixed:**  New error message now appears when system initialization as opposed an authentication error.

= 0.2.36 =
* **Added:**  WPGraphQL cursor pagination support.

= 0.2.35 =
* **Added:** filter `wpe_smartsearch/acf/excluded_field_names`, for excluding ACF field names from being indexed.

= 0.2.34 =
* **Added:** AI Powered Search feature.

= 0.2.33 =
* **Updated:** The id prefix filter will now add the id prefix to indexed documents
* **Updated:** The id prefix filter will now add the prefix as a search filter to isolate WordPress doucments for the current site
* **Added:** New filter hook to allow users to exclude post types from WP Engine Smart Search

= 0.2.32 =
* **Updated:** WordPress compatibility: 6.4
* **Updated:** Security updates
* **Added:** Non-logged in admin calls now use WP Engine Smart Search

= 0.2.31 =
* **Fixed:** Pagination issues when page size is set to `-1`

= 0.2.30 =
* **Fixed:** Total found posts number was incorrect after search

= 0.2.29 =
* **Fixed:** UI not rendering on WP Admin

= 0.2.28 =
* **Updated:** Revert to old plugin file name to prevent deactivation

= 0.2.27 =
* **Fixed:** UI issue on v0.2.26

= 0.2.26 =
* **Updated:** Plugin rebranded to WP Engine Smart Search
* **Added:** filter `wpe_smartsearch/extra_search_config_fields`, for filtering the search config fields.
* **Added:** support for orderby queries.

= 0.2.25 =
* **Added:** Added filter `wpe_smartsearch/extra_fields`, for filtering the index fields before indexing content.
* **Fixed:** Show error message when 401/404 on settimgs page
* **Fixed:** ACF not needed user type fields filtered out during index. Including user_pass field
* **Updated:** WordPress compatibility: 6.3

= 0.2.24 =
* **Fixed:** wpe_smartsearch/id_prefix filter now shows errors on the sync page if the returned data is invalid from the filter
* **Fixed:** Reduce number of fields indexed by ACF

= 0.2.23 =
* **Fixed:** Prevented unnecessary real-time index requests which resulted in error messages

= 0.2.22 =
* **Fixed:** Breaking change for php7.4 users
* **Added:** Search meta data is now sent during search requests

= 0.2.21 =
* **Fixed:** Issue when unsupported fields were being indexed
* **Added:** Filter for adding prefix
* **Updated:** Cleanup old endpoints code
* **Updated:** Remove metadata calls

= 0.2.20 =
* **Fixed:** Issue when errors where not displaying properly

= 0.2.19 =
* **Updated:** Remove ACM support from README

= 0.2.18 =
* **Added:** Add excluded posts support
* **Updated:** Improve admin messages
* **Updated:** Security updates

= 0.2.17 =
* **Fixed:** Issue where post type revisions were being indexed.
* **Fixed:** Issue where unpublished data was being indexed.

= 0.2.16 =
* **Fixed:** Issue where search config could not be saved.

= 0.2.15 =
* **Updated:** Plugin now uses the new index API, This change also streamlines how data is synchronized from WordPress.
* **Updated:** Search config now uses the WordPress field names for post types
* **Added:** Support for custom taxonomies

= 0.2.14 =
* **Fixed:** sync issues with unsupported ACF subfields

= 0.2.13 =
* **Fixed:** Remove ACF keys with empty string
* **Updated:** Use new find API for searches

= 0.2.12 =
* **Fixed:** ACF issue with nested content

= 0.2.11 =
* **Fixed:** ACF field issue with empty values on fields

= 0.2.10 =
* **Added:** Extended support for ACF types

All ACF types will now be indexed and searchable except for the following, these fields are excluded:
* image
* file
* google_map
* password
* gallery

**NOTE:** To take advantage of this new feature, please delete and re sync your data.

= 0.2.8 =
* **Fixed:** Issue where assets syncing were taking too long.

= 0.2.4 =
* **Updated:** Update version headers.

= 0.2.3 =
* **Fixed:** Success toast now pops up when sync is complete.

= 0.2.2 =
* **Added:** WordPress HTML and REST search now work with WP Engine Smart Search.

= 0.2.1 =
* **Fixed:** Issue when searching multiple terms.

= 0.2.0 =
* **Notice:** Upgrading to this version requires re-syncing data.

= 0.1.52 =
* **Fixed:** Issue where ACF fields were being omitted on initial sync.

= 0.1.51 =
* **Added:** Feature to allow more complex searching.

= 0.1.50 =
* **Fixed:** Issue where weight sliders were not working.

= 0.1.49 =
* **Updated:** Update version headers.

= 0.1.48 =
* **Fixed:** Issue where post slugs were casing sync issues.

= 0.1.47 =
* **Updated:** Update version headers.

= 0.1.46 =
* **Fixed:** Issue where permalinks were casing sync issues.

= 0.1.45 =
* **Fixed:** Issue where parent posts were causing failed syncs.

= 0.1.44 =
* **Fixed:** Issue where ACM date types were causing sync issues

= 0.1.43 =
* **Updated:** Readme docs
* **Fixed:** Allow hyphens in model identifiers

= 0.1.42 =
* **Updated:** Update version headers

= 0.1.41 =
* **Updated:** Update version headers

= 0.1.40 =
* **Added:** Support for offset pagination

= 0.1.39 =
* **Fixed:** issue where field weights were not being respected in search results

= 0.1.38 =
* **Updated:** Version headers

= 0.1.37 =
* **Reverted** unintended changes to sync.

= 0.1.36 =
* **Removed** search unused capabilities check.

= 0.1.35 =
* **Fixed** Issue with nested ACF fields on CPT's.

= 0.1.34 =
* **Fixed** WP Engine Smart Search not working when ACF objects are attached to CPT's.

== Changelog ==
= 0.1.33 =
* **Fixed** ACF issue with empty field groups during sync.

= 0.1.32 =
* **Fixed** Failed sync's due to null ACF field groups.
* **Fixed** Correctly order posts and pages when syncing data.

= 0.1.31 =
* **Fixed** Sync issues with parent terms.

= 0.1.30 =
* **Fixed** Sync issues with removed and re-added ACM fields.

= 0.1.29 =
* **Fixed** Empty ACM repeatable fields causing sync issues.
* **Fixed** Post featured images would cause sync to fail.

= 0.1.28 =
* **Fixed** ACM repeatable fields causing sync issues.
* **Fixed** Front-end missing wpApiSettings object.

= 0.1.27 =
* **Fixed** Sync issue when post types had no author.
* **Fixed** Sync issue when ACF fields contained dates as strings.

= 0.1.26 =
* **Added** UI configuration for fuziness and stemming toggle.

= 0.1.25 =
* **Fixed** ACM models can now be searched correctly.
* **Fixed** Posts with no authors will be synchronized correctly.

= 0.1.24 =
* **Updated** WP Engine Smart Search minimum PHP version is now 7.4

= 0.1.23 =
* **Fixed** Posts with empty `post_name` with not be synchronized

= 0.1.22 =
* **Fixed:** Simple Feature Request plugin breaking WP Engine Smart Search sync

= 0.1.21 =

* **Fixed:** Auto drafts will no longer be automatically synchronized
* **Fixed:** User delete events are now correctly handled
* **Fixed:** Tag descriptions can now be synchronized as longtext

= 0.1.20 =
* **Updated:** Version headers

= 0.1.19 =
* **Updated:** Version headers

= 0.1.18 =
* **Fixed:** Admin error notices correctly instruct users to sync when data sync issues occur

= 0.1.17 =
* **Fixed:** ACF group names search config where they were unable to be searched
* **Fixed:** Fuzzy queries unable to search where numbers are involved


= 0.1.16 =
* **Added:** Fuzzy configuration UI

= 0.1.15 =
* **Added:** Enable fuzzy search by default

= 0.1.14 =
* **Added:** Support for ACM's email field

= 0.1.13 =
* **Fixed:** Breaking pagination in WP Admin views
* **Added:** Clear sync progress & locks when plugin is deactivated

= 0.1.12 =
* **Added:** Add button to delete search data
* **Fixed:** Sync button progress bar improvement

= 0.1.11 =
* **Fixed:** Sync button progress is reset when multiple tabs try to sync

= 0.1.10 =
* **Fixed:** Progress bar animation
* **Fixed:** Sync items correctly syncing

= 0.1.9 =
* **Added:** Sync lock to prevent more than one sync executing at a time
* **Fixed:** Progress calculation on sync progress bar
* **Fixed:** Sync can now progress when ACM is not installed

= 0.1.8 =
* **Added:** New sync button to sync content via plugin
* **Added:** Plugin Icon and Banner
* **Added:** Toast confirmations when saving settings
* **Fixed:** Importing posts via ACM
* **Fixed:** Styling issues on WP Engine Smart Search Settings

= 0.1.7 =
* Added toast confirmations on settings changes
* Show info to user about syncing data when plugin is activated
* Settings based scripts are now cached by the browser on WP Admin
* Search configuration regenerated on content changes
* Added validation to settings form

= 0.1.6 =
* Search fields now correctly search through content models
* Remove slug as an option from search config
* Url setting will correctly default to an empty string

= 0.1.5 =
* Added new settings page
* Added Search Config page

= 0.1.4 =
* Update WP CLI command prefix to `wp as`

= 0.1.1 =
* Prepare for release

= 0.1.0 =
* Add support for ACM repeater fields
* Improve error messages in wp-admin
* Sync CPT excerpt field

