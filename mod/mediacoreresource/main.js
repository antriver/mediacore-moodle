M.mod_mediacoreresource = M.mod_mediacoreresource || {};

var MCoreResource = M.mod_mediacoreresource;
MCoreResource.chooser = undefined;
MCoreResource.params = mcore_params;


MCoreResource.init = function(Y) {
    // Load the media chooser.js lib only if its not
    // already defined
    if (!'mediacore' in window) {
        this.loadScript(
            this.params['mcore_chooser_js_url']
        );
    }

    // Event listeners
    var addMediaBtnElem = document.getElementById('id_add_media_btn');
    if (addMediaBtnElem) {
        Y.YUI2.util.Event.addListener(addMediaBtnElem, 'click',
                this.launchChooser, this);
    }
};


MCoreResource.launchChooser = function(e, self) {
    if (!self.chooser) {
        var params = {
            'url': self.params['mcore_chooser_url'],
            'launchUrl': self.params['mcore_launch_url'],
            'mode': 'popup'
        };
        self.chooser = mediacore.chooser.init(params);

        self.chooser.on('media', function(media) {
            var mediaIdField = document.getElementById('mcore-media-id');
            mediaIdField.value = media.id;

            var embedUrlField = document.getElementById('mcore-embed-url');
            embedUrlField.value = media.embed_url;

            var thumbUrlField = document.getElementById('mcore-thumb-url');
            thumbUrlField.value = media.thumb_url;

            var metadataField = document.getElementById('mcore-metadata');
            var metadataJson = JSON.stringify(media);
            metadataField.value = metadataJson;

            var iframeElem = document.getElementById('mcore-media-iframe');
            iframeElem.src = media.embed_url;

            var addMediaBtnElem = document.getElementById('id_add_media_btn');
            addMediaBtnElem.value = 'Replace Media';

            this.close();
        });

        // Handle media error
        self.chooser.on('error', function(media) {
            this.close();
            throw err;
        });
    }
    self.chooser.open();
};


MCoreResource.loadScript = function(url) {
    var script = document.createElement('script');
    script.src = url;
    (document.body || document.head || document.documentElement).appendChild(script);
};
