=== Content Repeater - Custom Posts Simplified ===
Contributors: db0112358
Tags: custom post types, custom fields, template, masonry, isotope, slick slider, testimonials, coupons, products, flipboxes, portals, portfolio, before & after, twentytwenty
Requires at least: 4.4.0
Tested up to: 5.6.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

I use this tool to quickly set up custom content types and display them in interesting ways.

== Description ==

Content Repeater is designed to quickly set up custom content types. It simplifies the process of managing and displaying custom posts. Typically, there are at least three steps for displaying custom post content on the frontend:

1. Register a custom post type specifying all its options.
2. Create custom fields (field groups)
3. Create a post type template (single-{post_type}.php) in the theme folder

Content Repeater reduces step #1 to a single name-entry action, and merges steps #2 and #3 into one. Custom fields are assigned right in the Template Editor, and you don't have to add any files to your theme. Displaying custom content is as simple as inserting a shortcode.

**Features**

- Set up custom content types with a single action
- Quickly add slug for single post frontend view
- Quickly add categories when needed
- Content type icon selector
- Template Editor with syntax highlighting
- Separate Template Editor for the single post view (when slug is defined)
- field types: Post Title, Post URL, Post ID, Featured Image, Site URL, Plugin URL, Text (single line), Textarea, Images / Gallery, Color, Date, Icon, Dropdown, Checkboxes
- Default values for fields
- Conditional shortcodes (e.g. [if field="Sale Price"]...[/if])
- Custom classes per post type
- Custom CSS per post type (with syntax highlighting)
- Custom Javascript per post type (with syntax highlighting)
- Adding external CSS and Javascript files per post type
- Drag & drop post ordering
- Post duplication
- Publish & Add New
- Available Repeaters: Ajax Reload, Masonry, Isotope, Slick Slider, Single Row
- Pre-built Templates: Testimonials, Coupons, Products (with Magnifier), Flipboxes, Entry Portals, Portfolios (with Gallery), Before & Afters (using TwentyTwenty)
- Export / import plugin settings
- Bulk rename custom fields per post type


== Installation ==

1. Install
2. Activate
3. Create content types
4. Create a Template or use a pre-built one
5. Add content
6. Insert Repeaters into posts or pages


== Screenshots ==

1. Content types
2. Template
3. Template syntax
4. Template shortcodes inserter / generator
5. Repeaters
6. Fields


== Changelog ==

= 1.0.0 =
Release Date - 7 March 2019
= 1.0.1 =
fixed images field sorting issue
= 1.0.2 =
add options to slider field: nolink, fullheight
= 1.0.3 =
minor localization changes, slider field improvements
= 1.0.4 =
fix empty fields not saving
= 1.0.5 =
field inserter improvements
= 1.0.6 =
fix post sorting issue
fix isotope selected class
= 1.0.7 =
improve icon field
= 1.0.8 =
fix opacity issue for IE11
= 1.0.9 =
fix jQuery syntax for IE11
= 1.0.10 =
fix Testimonials template loading
= 1.0.11 =
fix meta boxes
= 1.0.12 =
add post__in argument to shortcodes
= 1.0.13 =
isotope: add child class
= 1.0.14 =
fix error including files
= 1.0.15 =
fix javascript error
= 1.0.16 =
add 'post_content' field
= 1.0.17 =
field renaming to 'post_content'
= 1.0.18 =
hide 'post_content' on single page
= 1.1.0 =
fixes and improvements
= 1.1.1 =
fix meta boxes missing when no meta in Template
= 1.1.2 =
remove id="crid-..."
= 1.1.3 =
disable Resize Observer by default
= 1.1.4 =
add category__not_in parameter to shortcodes
= 1.1.5 =
fix bug in ajax reload
= 1.1.6 =
sync
= 1.1.7 =
disable CSS controls on template
= 1.1.8 =
remove Add Repeater button from editors
= 1.1.9 =
updated readme.txt
= 1.1.10 =
remove site_url label from editor
= 1.1.11 =
fix dashicons picker
= 1.1.12 =
fix dashicons picker again
= 1.1.13 =
FontAwesome CSS fix 