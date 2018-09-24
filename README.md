# Company Manager #
**Contributors:** [WPdrift](https://profiles.wordpress.org/WPdrift), [upnrunn](https://profiles.wordpress.org/upnrunn), [kishores](https://profiles.wordpress.org/kishores), [shamimmoeen](https://profiles.wordpress.org/shamimmoeen)  
**Tags:** wp-job-manager, wp-job-manager-company, wp-job-manager-company-listings, company-listings  
**Requires at least:** 4.4  
**Tested up to:** 4.9.8  
**Stable tag:** 1.0.5  
**Requires PHP:** 5.4  
**License:** GPLv3  
**License URI:** https://www.gnu.org/licenses/gpl-3.0.html  

Company Listings for WP Job Manager.

## Description ##

Company Listing is a lightweight plugin for adding Company Listings functionality to your WordPress site. Being shortcode based, it can work with any theme (given a bit of CSS styling) and is really simple to setup. Shortcodes allow you to easily output individual companies in various formats, lists of companies, a company submission form and even an company dashboard which logged in users can use to view, edit and delete their listings.

### Features ###

* Outputs a list of all companies that have submitted job
* Lists all jobs under the company profile
* Moreover, it will be a custom post type, so can be easily searched or can make a directory for the company. And much more.

<a href="https://wpdrift.com/company-listings-for-wp-job-manager/" target="_blank">Read more about Company Listings for WP Job Manager</a>.

### Requirements ###

Company Listings for WP Job Manager plugin needs the following plugins to be installed:

* <a href="https://wordpress.org/plugins/wp-job-manager/" target="_blank">WP Job Manager</a>

### Support Policy ###

We will happily patch any confirmed bugs with this plugin, however, we will not offer support for:

1. Customisations of this plugin or any plugins it relies upon
2. Conflicts with "premium" themes from ThemeForest and similar marketplaces (due to bad practice and not being readily available to test)
3. CSS Styling (this is customisation work)

If you need help with customisation you will need to find and hire a developer capable of making the changes.

## Installation ##

### Automatic installation ###

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t even need to leave your web browser. To do an automatic install, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

In the search field type “Screening Questions For WP Job Manager” and click Search Plugins. Once you’ve found the plugin you can view details about it such as the point release, rating, and description. Most importantly, of course, you can install it by clicking Install Now.

### Manual installation ###

The manual installation method involves downloading the plugin and uploading it to your web server via your favorite FTP application.

* Download the plugin file to your computer and unzip it
* Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation’s <code>wp-content/plugins/</code> directory.
* Activate the plugin from the Plugins menu within the WordPress admin

## Frequently Asked Questions ##

## Screenshots ##

1. Company Listings Setup - 1
2. Company Listings Setup - 2
3. Company Listings Setup - 3
4. List of Companies in the backend
5. Company post custom meta fields
6. View Single Company
7. Submit company form
8. List of Companies in the frontend

## Changelog ##

### 1.0.5 - 2018-09-25 ###
* Remove fields from submit-job-form and move to submit-company-form
* Update submit-company-form
* Add missing company datas to single company post page
* Remove unnecessary scripts
* Add option to filter companies by featured in list table
* Remove linkedin import feature
* Remove option to make the company name field to text field
* Add filters to modify company select field
* Load select2 scripts only when needed
* Fix - preview company then press edit erases the company data
* Fix design issues on company preview page
* Set default user role to 'company' when creating user from the submit company form
* Fix company permalink issue
* Update submitted company email template
* Remove custom fields from wp-admin related to company from job post type
* Updated pot file

### 1.0.4 ###
* Add filters to modify the columns of companies list table
* Add supports the feature 'author' for post type 'company_listings'

### 1.0.3 ###
* Fix scroll to bottom issue on plugin settings page
* Add filter to modify company slug, filter available 'company_listing_post_slug'
* Add option to enable company name field type text
* Add option to show only self companies

### 1.0.2 ###
* Update readme.txt and added README.md files
* Improve the company submission system
* Fix company featured image attach issue
* Turn the company name field into searchable field
* Remove 'Add Company' link from the company listings table
* Fix localization issue and updated pot file
* Remove company autohide features
* Load minimized version of frontend.css if SCRIPT_DEBUG is enabled
* Update select2 plugin
* Make username field required
* Fix issues on plugin settings page
* Fix wrong constant name issue
* Add missing jquery-tiptip library
* Remove company expire related functionality
* Generate unique slug for company when creating company

### 1.0.1 ###
* New - Ability to bookmark company when WP Job Manager - Bookmarks plugin is enabled.
* Fix - Installer fail to run on the new install.
* Fix - Various notices/warnings.

### 1.0.0 ###
* First release.

## Upgrade Notice ##

### 1.0.5 ###
This is a major update. Make a full site backup before updating this plugin.
