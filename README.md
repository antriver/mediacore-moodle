```
     __  _____________   _______   __________  ____  ______
    /  |/  / ____/ __ \ /  _/   | / ____/ __ \/ __ \/ ____/
   / /|_/ / __/ / / / / / // /| |/ /   / / / / /_/ / __/
  / /  / / /___/ /_/ /_/ // ___ / /___/ /_/ / _, _/ /___
 /_/  /_/_____/_____//___/_/  |_\____/\____/_/ |_/_____/

```

A set of [Moodle](http://moodle.org) plugins that integrate with
[MediaCore](http://mediacore.com).

Designed to work with Moodle 2.3+. Tested and compatible up to Moodle v2.6.

## Overview ##
These plugins provide a rich set of Moodle-MediaCore integrations using LMS-LTI
to connect with your MediaCore site without having to leave Moodle.

## MediaCore Moodle Plugins ##

**Local** `(local/mediacore)`

* Provides LTI integration libraries and configuration setup that is used
by the other plugins.

**Repository** `(repository/mediacore)`

* Provides a custom 'repository' file picker as part of Moodle's 
built-in media integration.

**Filter** `(filter/mediacore)`

* Transforms the video URLs generated by the
TinyMCE and Repository plugins into MediaCore's embedded media player.

**TinyMce** `(lib/editor/tinymce/plugins/mediacore)`

* Provides a rich
integration with MediaCore, allowing you to launch the "MediaCore Chooser" from
a custom button added to every TinyMCE editor.

**Atto Editor** `(lib/editor/atto/plugins/mediacore)`

* Provides a rich
integration with MediaCore, allowing you to launch the "MediaCore Chooser" from
a custom button added to every Atto editor.

## Plugin Installation/Upgrade ##

Upgrading the MediaCore plugin is done by copying the following folders into
the correct Moodle directories.

* Copy `local/mediacore` into `path/to/your/moodle/local/`
* Copy `repository/mediacore` into `path/to/your/moodle/repository/`
* Copy `filters/mediacore` into `path/to/your/moodle/filters/`

#### Moodle 2.3: ####
* Copy `lib/editor/tinymce/mediacore` **into** `path/to/your/moodle/lib/editor/tinymce/tiny_mce/{version}/plugins/`

#### Moodle 2.4: ####
* Copy `lib/editor/tinymce/plugins/mediacore` **into**
  `path/to/your/moodle/lib/editor/tinymce/plugins/`
  
#### Moodle 2.7+: ####
* Copy `lib/editor/atto/plugins/mediacore` **into**
  `path/to/your/moodle/lib/editor/atto/plugins/`


To finalize the installation you will need to navigate to: `Site administration
-> Notifications` and click "Check for available updates". Click "Upgrade
Moodle database now" to complete this step.

You may be asked to enter any new configuration settings as well.

It's always a good idea to purge the Moodle caches after this step as well :)

## Base Plugins Configuration ##

### Local MediaCore package config: ###

To hook your MediaCore site into Moodle you must navigate to: `Site
administration -> Plugins -> Local plugins -> MediaCore package config` and enter:

* The `hostname` of your MediaCore site (i.e. demo.mediacore.tv).
* The `scheme` you wish to launch the Chooser and view embeds from (i.e. HTTP or HTTPS)
* Whether you want to use `lti_authentication` (i.e. True or False)
* The name of your `lti consumer key` (this must match a valid LTI consumer in
  your MediaCore site)
* The secret of your `lti shared secret` (this also must match the secret in the
  LTI consumer above)

### Repository configuration setup: ###

You will also need to enable the repository. it may be turned on by navigating
to: `Site administration -> Plugins -> Repositories -> Manage Repositories` and
selecting `Enabled and visible` from drop down menu next to "MediaCore
search".

### Filter configuration setup: ###

In order for videos to display in Moodle, you must enable the MediaCore Filter.
This can be turned on by navigating to: `Site administration -> Plugins ->
Filters -> Manage Filters` and selecting `On` from drop down menu in the
`active` column next to 'MediaCore media filter'.

## Text Editor Plugins Configuration ##

### TinyMCE Rich text editor plugin config: ###

#### Moodle 2.3: ####

1. Move the `editor_plugin.js` file from `/lib/editor/tinymce/plugins/mediacore/tinymce` to `lib/editor/tinymce/tiny_mce/{version}/plugins/mediacore/` and **rename** it to `editor_plugin_src.js`.
2. Then, delete the `/lib/editor/tinymce/plugins/mediacore/tinymce` directory.
3. Now we need to let Moodle know about the MediaCore TinyMCE plugin. Open:
    `/path/to/moodle/lib/editor/tinymce/lib.php`

4. At the bottom of the `get_init_params` function, just above `return $params`, add the following lines of code:

    ~~~~~~~
    //for mediacore
    if (class_exists('mediacore_client', true /* autoload */)) {
        $mcore_client = new mediacore_client();
        $params = $mcore_client->configure_tinymce_lib_params($filters, $params);
    }
    ~~~~~~~

#### Moodle 2.4+: ####

* Once the `lib/editor/tinymce/plugins/mediacore` directory is in place, plugin configuration is automatic.

### Atto Rich text editor plugin config: ###

#### Moodle 2.7+: ####

* Go to `http://your.moodle/admin/settings.php?section=editorsettingsatto` and add a new line containing `mediacore = mediacore`. Put this line in the location that you want the plugin button to appear in the editor. See [Text Editor Toolbar Settings](http://docs.moodle.org/27/en/Text_editor#Toolbar_settings) on how to fine tune Atto's toolbar to your liking.

## Troubleshooting Plugins  ##

To do a clean install we recommend you remove any old versions
of the MediaCore plugin from your Moodle install. This is done by removing the
following directories, if they exist:

* `path/to/moodle/local/mediacore`
* `path/to/moodle/repository/mediacore`
* `path/to/moodle/filters/mediacore`

#### Moodle 2.3: ####
* `path/to/moodle/lib/editor/tinymce/tiny_mce/{version}/plugins/mediacore`

#### Moodle 2.4+: ####
* `path/to/moodle/lib/editor/tinymce/plugins/mediacore`

#### Moodle 2.7+: ####
* `path/to/moodle/lib/editor/atto/plugins/mediacore`

You will also need to Navigate to: `Site administration ->
Plugins -> Plugins Overview` and **uninstall** the following MediaCore plugin code
from the Moodle database:

* `filter_mediacore`
* `atto_mediacore`
* `tinymce_mediacore`
* `repository_mediacore`
* `local_mediacore` 

** Note: any previous Moodle/MediaCore settings will be removed when you delete
  or upgrade the MediaCore plugin **

Once any old versions have been removed, you can retry the installation steps above.

## Support Links ##

* [Moodle Support Documentation](http://support.mediacore.com/customer/portal/articles/search?q=moodle)

## About MediaCore ##

Video is transforming education, and MediaCore helps hundreds of educational institutions around the world embrace it. The MediaCore Video Platform puts powerful video learning and sharing tools into the hands of every student and professor — empowering them to easily capture, manage and share video privately and securely across all platforms and mobile devices. 

MediaCore has received a wide range of awards, most recently being recognized by Fast Company as one of the top ten most innovative companies in digital video.
