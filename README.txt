=== eInnov8 WP XML-RPC Notifier ===
Contributors: yipeecaiey
Requires at least: 2.7
Tested up to: 3.0.4
Stable tag: 2.0.10
Tags: eInnov8, xmlrpc

Manage notification and display settings for posts received via xmlrpc.
Custom forms for submitting content to the wp xmlrpc server.

== Description ==

    * Set default status for new posts to draft or published
    * Set custom email address for notifications when posts are received (optional)
    * Manage email notification content when posts are received.
    * Short tags for custom form inclusion within posts 
    * Optional captcha authentication for form submissions


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

= 2.0.7 =
Allow new short tags: [[Load WideRecorder]], [[Load TallRecorder]], and [[Load MediaUploader]]

= 2.0.6 =
This version will now support automatic upgrades

= 2.0.5 =
Converts the plugin for compatibility with WP3 multi site networks
