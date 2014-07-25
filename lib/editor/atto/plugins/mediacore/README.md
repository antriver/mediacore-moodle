MediaCore Chooser plugin for the Atto Editor
============================================

Requirements
============

- Moodle 2.7
- MediaCore Filter (filter_mediacore)


Description
===========

Provides a rich integration with MediaCore, allowing you to launch the "MediaCore Chooser" from a custom button added to every Atto editor.


Installation
============

1. Copy the plugin directory into the /lib/editor/atto/plugins/ directory of your Moodle installation.
2. Go through the standard Moodle plugin installation process.
3. Do to http://your.moodle/admin/filters.php and enable the MediaCore media filter plugin (active=ON)
4. Go to http://your.moodle/admin/settings.php?section=editorsettingsatto and add a new line containing 'mediacore = mediacore' (without the quotes). Put this line in the location that you want the plugin button to appear in the editor. See http://docs.moodle.org/27/en/Text_editor#Toolbar_settings on how to fine tune Atto's toolbar to your liking.
