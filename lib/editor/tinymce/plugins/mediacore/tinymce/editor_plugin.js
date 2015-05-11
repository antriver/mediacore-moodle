/**
 * @author MediaCore <info@mediacore.com>
 */

(function() {
    function loadScript(url) {
        var script = document.createElement('script');
        script.src = url;
        (document.body || document.head || document.documentElement).appendChild(script);
    }

    tinymce.PluginManager.requireLangPack('mediacore');

    tinymce.create('tinymce.plugins.MediaCoreChooserPlugin', {
        init : function(ed, pluginUrl) {
            var t = this;
            t.editor = ed;
            t.url = pluginUrl;

            loadScript(ed.getParam('mcore_chooser_js_url'));
            var params = {
                'url': ed.getParam('mcore_chooser_url', undefined),
                'host': ed.getParam('mcore_host_url', undefined),
                'mode': 'popup'
            };

            ed.addCommand('mceMediaCoreChooser', function() {
                if (!window.mediacore) {
                    ed.windowManager.alert(
                        ed.getLang('mediacore.loaderror')
                    );
                    return;
                }
                if (!t.chooser) {
                    t.chooser = mediacore.chooser.init(params);
                    t.chooser.on('media', function(media) {
                        var imgElem = t.editor.dom.createHTML('img', {
                            src:  media.thumb_url,
                            width: 400,
                            height: 225,
                            alt: media.title,
                            title: media.title
                        });
                        var attrs = {
                            'href': media.embed_url,
                            'data-media-id': media.id
                        };
                        var aElem = t.editor.dom.createHTML('a', attrs, imgElem);
                        t.editor.execCommand('mceInsertContent', false, aElem);
                    });
                    t.chooser.on('error', function(err) {
                        throw err;
                    });
                }
                t.chooser.open();
            });

            ed.addButton('mediacore', {
                title : 'mediacore.desc',
                image : t.url + '/img/icon.png',
                cmd : 'mceMediaCoreChooser'});

        },

        getInfo : function() {
            return {
                longname : 'MediaCore Chooser',
                author : 'MediaCore <info@mediacore.com>',
                version : "3.0"
            };
        }

    });

    tinymce.PluginManager.add('mediacore', tinymce.plugins.MediaCoreChooserPlugin);
})();
