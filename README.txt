=== Content XLerator Plugin ===
Contributors: yipeecaiey, jimesten
Requires at least: 4.0
Tested up to: 4.1
Stable tag: 3.6.6
Tags: eInnov8, xmlrpc, ei8t, ei8t.com, Content XLerator, CXL, cxl1.net

Custom integration with cxl1.net
Manage notification and display settings for posts received via xmlrpc.
Custom forms for submitting content to the wp xmlrpc server.

== Description ==

 A wordpress plugin to support integration with the cxl1.net API for content submission, distribution, and playback


== Supported Languages ==

* US English/en_US (default)


== Installation ==

1. Unzip archive to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==

None, yet!


== Changelog ==

****

    VERSION DATE        TYPE    CHANGES
	3.6.6   2015/03/30  UPDATE  Update CXL branding within floodgate
	3.6.5   2015/02/04  BUG-FIX Alter how xmlrpc submissions are processed.
	3.6.4   2014/12/04  BUG-FIX Add unique ids to shortcode video players to prevent double posting errors
	3.6.3   2014/10/31  BUG-FIX Cleanup unneeded debugging text
	3.6.2   2014/10/30  UPDATE  Updated management for the RLimitMem Hack
	3.6.1   2014/10/22  BUG-FIX Updated playlist to fix layout and alignment issues
	3.6.0   2014/10/17  UPDATE  Update [cxl MediaUploader] shortcode to use mobile friendly html5 uploader and the new cxl API
	                            NOTE: Requires WP 4.0 or greater!!
	3.5.9   2014/10/16  UPDATE  Create option to show playlist titles and descriptions on rollover
	3.5.8   2014/10/09  BUG-FIX Updated [cxl shortcode] handling
	3.5.7   2014/10/09  BUG-FIX Updated playlist rendering options
	3.5.5   2014/10/08  BUG-FIX Updated [cxl shortcode] handling
	3.5.4   2014/10/06  BUG-FIX Squash php warning when rendering floodgate
	3.5.3   2014/10/02  UPDATE  Use wp_remote_xxx methods for all remote page loads
	3.5.2   2014/09/22  UPDATE  Rebranded eInnov8->Content XLerator
	3.5.1   2014/09/04  UPDATE  Tested in wp v4
	3.5.0   2014/08/27  UPDATE  Update to access cxl1.net instead of ei8t.com
	                            Update shortcodes to use [cxl...], [ei8t shortcodes] still function
	3.1.8   2014/08/20  UPDATE	Updated Floodgate remote caching to use db for caching
                                Plugin writes to .htaccess file to raise RLimitMem value
    3.1.7   2014/05/30  UPDATE	Improved system performance in db queries
	3.1.6   2014/05/19  UPDATE	Created API Request Caching
	                            WP3.9 compatible
	3.1.5   2014/04/02  BUG-FIX	Removed test code
	3.1.4   2014/04/02  BUG-FIX	Updated handling of text fields in admin screens
	3.1.3   2014/03/27  UPDATE	Rearranged admin screens
	                            Refactored file structure and lots of code!
	                            Fixed issue with admin elements not updating correctly
	3.1.2   2014/03/17  UPDATE	Updated mobile nav elements for floodgate
	3.1.1   2014/03/17  UPDATE	Updated mobile nav for floodgate
	3.1.0   2014/03/14  UPDATE	Mobile responsive design for floodgate
	3.0.2   2014/02/26  UPDATE	Use swfobject to load web recorders
	3.0.1   2014/02/24  UPDATE	Convert media uploader flash->HTML5
	3.0.0   2014/02/15  UPDATE	Add new floodgate interface
	2.6.4   2013/08/15  BUG-FIX	Bugfix for proper playlist display in Firefox
	2.6.3   2013/08/05  UPDATE	Add in playlist autoplay functionality
	2.6.2   2013/07/29  UPDATE	Update Playlist display and admin options
	2.6.1   2013/07/08  UPDATE	Added Playlist functionality
	2.6.0   2013/06/03  UPDATE	Upgrade shortcode rendering to use html5 with jwplayer6
	2.5.7   2013/04/12  UPDATE	Updated tweet submission method
	2.5.6   2013/04/12  UPDATE	Replaced the twitter API connection library
	2.5.5   2013/03/28  UPDATE	Added multiple ei8t destinations for submissions
	                            Added new shortcode definition for the above.
	                    BUG-FIX Resolved issues with xmlrpc user from errors introduced with wp v3.5.1
	2.5.4   2013/01/23  UPDATE	Update methods for calling and using jQuery
	2.5.3   2013/01/23  UPDATE	Skip youtube URLs when autolinking
	                            add in wpdb->prepare statements to prevent mysql errors
	2.5.2   2013/01/16  BUG-FIX	Eliminate false errors for local forms without files uploaded
	2.5.1   2012/12/30  UPDATE	Require captcha for all local forms
	                            Update file upload requirements
	2.5.0   2012/12/13  UPDATE	Rename plugin
	                            Refactor plugin code and files
	                            Separated email notification preferences from standard options
	2.4.5   2012/12/05  BUG-FIX	Updated autolink post update method that was causing an infinite loop
	                            Removed autolink stamp as it is no longer necessary
	2.4.4   2012/12/04  BUG-FIX	Removed use of wp 'alignleft' styling for media alignment
	2.4.3   2012/12/03  UPDATE	Added toggle accessibility of eInnov8 Options tab
	2.4.2   2012/11/29  BUG-FIX	Fixed error inhibiting proper publishing and post type assignments of new posts
	2.4.1   2012/11/28  UPDATE	Update submit forms submit button color
	2.4.0   2012/11/28  UPDATE	Fold back in changes from v2.3.9
	                            updated submission confirmation success/error text color
	                            use wp 'alignleft' style for media alignment
	                            uploaded files now keep original name (updated as necessary for uniqueness)
	2.3.11  2012/11/01  UPDATE	Rollback to version 2.3.8
	2.3.10  2012/10/31  BUG-FIX Updated error check code for false positive
								Added space to preserve opening tag on image write in posts
    2.3.9   2012/10/26  UPDATE  Autolink urls within content received via xmlrpc
    2.3.8   2012/10/23  UPDATE  Updated twitter API library
    2.3.7   2012/10/22  UPDATE  Add default video width to website settings **TAGGED OUT**
                                update email default settings
                                set width for admin textareas
    2.3.6   2012/10/01  UPDATE  Hack to allow ei8 shortcodes to be passed and parsed when syndicated
    2.3.5   2012/09/10  UPDATE  Misc minor refactoring tweaks
    2.3.4   2012/09/06  UPDATE  Added ability to set default alignment for all ei8t media
    2.3.3   2012/08/30  UPDATE  Bugfix: fix video flag to show affiliate info to toggle correctly
    2.3.2   2012/05/02  UPDATE  Updated css to allow for default styling of embedded player
                                Added additional shortcode parsing options for embedded player
    2.3.1   2012/04/08  UPDATE  Removed all references to uploadify for security reasons
    2.3.0   2012/04/01  UPDATE  Added more documentation for recorder/uploader destination folder overrides
    2.2.9   2012/03/31  UPDATE  Added support for [ei8 shortcode] recorder/uploader destination folder overrides
    2.2.8   2012/03/30  UPDATE  Local text submission (i.e. simplesubmit) now get redirected to referrer
    2.2.7   2012/03/22  UPDATE  Changed restrictions to allow Editor access to ei8 preferences page
    2.2.6   2012/03/21  UPDATE  Added styling to all shortcode elements
                                Created ei8-xmlrpc-notifier.css that auto loads
                                Created ei8-xmlrpc-tweet.js that auto loads
                                Added admin visibility into css
                                Updated urls to not use _FILE_, instead hardcoded
                                Updated twitter redirect in case the bootstrap could not be loaded
                                Added expander shortcode options to show/hide content by clicking on the title
                                Added JQuery loader
                                Added Media Uploader css
                                Changed all textarea titles to "Content"
                                Changed Attachment Submit Form to use textarea instead of text box
    2.2.5   2012/01/19  UPDATE  Added support for [ei8 shortcode] conditional alignment
    2.2.4   2012/01/19  UPDATE  Removed width from div wrapper for [ei8 shortcode] to allow centering by user
    2.2.3   2012/01/19  UPDATE  Removed width from div wrapper for [ei8 shortcode] to allow centering by user
    2.2.2   2011/10/30  UPDATE  Fixed conflict with FeedWordPress plugin
    2.2.1   2011/10/30  UPDATE  Remove deprecated php code
                                Remove multiple admin links
                                Add [ei8 shortcode] explanation
    2.2.0   2011/10/29  UPDATE  Add support for [ei8 shortcode] additional features
    2.1.9   2011/10/20  UPDATE  Add support for [ei8 audio shortcodes]
                                Add support for [ei8 shortcodes] additional parameters
                                Add support for [ei8 shortcodes] conditional affiliate link (default off)
    2.1.8   2011/10/04  UPDATE  Add support for [ei8 shortcodes]
                                Update all [[Load ***]] shortcodes to use [ei8 shortcodes]
                                Add support for default value for ei8_xmlrpc_get_option method
    2.1.7   2011/10/04  UPDATE  Add support for custom post_types
    2.1.6   2011/08/22  UPDATE  Bugfix: twitter callback URL updated
    2.1.5   2011/07/26  UPDATE  Bugfix: duplicate function findexts() in contentsave.php
    2.1.4   2011/07/21  UPDATE  Bugfix: fixed admin form submission
    2.1.3   2011/07/19  UPDATE  Bugfix: bug from 2.1.2 that could cause site to crash
    2.1.2   2011/06/14  UPDATE  Require contentsave.php to only process form submissions from the current domain
                                Create 'eInnov8 Options' main menu option in wp admin
                                Put 'xmlrpc preferences' under the einnov8 tab 
                                Allow multiple email addresses for posting notifications
    2.1.1   2011/03/15  UPDATE  Bugfix: resolve authentication conflict with infusionwp plugin
    2.1.0   2011/02/02  UPDATE  Bugfix: form submissions from multisites using folders not working
                                Bugfix: Unneeded admin notifications and updates
    2.0.11  2011/01/25  UPDATE  Allow domains with 4 letter extensions (.info)
    2.0.10  2011/01/18  UPDATE  Add Twitter form option
    2.0.9   2011/01/12  UPDATE  Extended preferences fields
                                Added Twitter post option
                                Added confirmation on form submit
                                Bugfix for dynamic plugin directory names
    2.0.8   2011/01/12  UPDATE  BugFix for wp3 single sites, updated dimensions for TallRecorder
    2.0.7   2010/12/29  UPDATE  Updated short tags to include WideRecorder, TallRecorder, and MediaUploader
    2.0.6   2010/12/29  UPDATE  Compliance changes for wordpress plugins directory submission
    2.0.5   2010/11/09  UPDATE  Compatibility for WP3 multi site networks
    2.0.4   2010/02/05  UPDATE  Added intelligent text box title for 'Simple Submit Form'.
                                Removed width for uploaded (embedded) images to remove distortion
                                Added hspace and vspace for uploaded (embedded) images
    2.0.3   2010/02/05  BUG-FIX Added functionality so 'Attachment Submit Form' creates link to doc instead of embedding
    2.0.2   2010/02/01  BUG-FIX Changed logic to determine upload path.  Some php confs did not show $_SERVER['SCRIPT_FILENAME'] consistently.
    2.0     2010/01/06  UPDATE  Works for WordPress 2.9.1
                                Changed naming of files, functions, tables, and all references from testiboonials to eInnov8 
                                Added in new site options (audio-video-blogs and souped-up-blogs)
    1.2     2009/12/09  UPDATE  consolidated functionality of boonsave.php and floodsave.php into contentsave.php
                                relocated contentsave.php and php_captcha.php to the plugin dir (no more external files referenced)
                                created separate wp user for xmlrpc submissions
                                added optional captcha form to the admin preferences
                                use wpurl now for all urls to avoid errors related to installation location
                                uploaded files are now uploaded to the main wp uploads dir
                                auto setting of enable_xmlrpc variable
    1.1     2009/12/01  UDPATE  Added in keyword replacement for forms in boonsave.php and floodsave.php
    1.0     2009/11/01  NEW     Works for WordPress 2.8



== Upgrade Notice ==

= 3.6.6 =
Update CXL branding within floodgate

= 3.6.5 =
Alter how xmlrpc submissions are processed.

= 3.6.4 =
Add unique ids to shortcode video players to prevent double posting errors

= 3.6.3 =
Cleanup unneeded debugging text

= 3.6.2 =
Updated management for the RLimitMem Hack

= 3.6.1 =
Updated playlist to fix layout and alignment issues

= 3.6.0 =
Update [cxl MediaUploader shortcode] to use mobile friendly html5 uploader and the new cxl API
NOTE: Requires WP 4.0 or greater!!

= 3.5.9 =
Create option to show playlist titles and descriptions on rollover

= 3.5.8 =
Updated [cxl shortcode] handling

= 3.5.7 =
Updated playlist rendering options

= 3.5.5 =
Updated [cxl shortcode] handling

= 3.5.4 =
Squash php warning when rendering floodgate page

= 3.5.3 =
Use wp_remote_xxx methods for all remote page loads

= 3.5.2 =
Rebranded eInnov8->Content XLerator

= 3.5.1 =
Tested in wp v4

= 3.5.0 =
Update to access cxl1.net instead of ei8t.com
Update shortcodes to use [cxl...], [ei8t shortcodes] still function

= 3.1.8 =
Updated Floodgate remote caching to use db for caching
Plugin writes to .htaccess file to raise RLimitMem value

= 3.1.7 =
Improved system performance in db queries

= 3.1.6 =
Created API Request Caching
WP3.9 compatible

= 3.1.5 =
Removed test code

= 3.1.4 =
Updated handling of text fields in admin screens

= 3.1.3 =
Rearranged admin screens
Refactored file structure and lots of code!
Fixed issue with admin elements not updating correctly

= 3.1.2 =
Updated mobile nav elements for floodgate

= 3.1.1 =
Updated mobile nav for floodgate

= 3.1.0 =
Mobile responsive design for floodgate

= 3.0.2 =
Use swfobject to load web recorders

= 3.0.1 =
Convert media uploader flash->HTML5

= 3.0.0 =
Added new floodgate interface

