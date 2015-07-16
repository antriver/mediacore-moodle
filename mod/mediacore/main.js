M.mod_mediacore = {};

M.mod_mediacore.chooser = undefined;

M.mod_mediacore.init = function(Y) {
    var addMediaBtnElem = document.getElementById('id_mcore-add-media-btn');
    if (addMediaBtnElem) {
        Y.YUI2.util.Event.addListener(
            addMediaBtnElem, 'click', M.mod_mediacore.handleClick);
    }
};

M.mod_mediacore.handleClick = function(e) {
    M.mod_mediacore.launchChooser();
};

M.mod_mediacore.launchChooser = function() {
    if (!M.mod_mediacore.chooser) {
        var params = {
            'url': mcore_vars['mcore_chooser_url'],
            'launchUrl': mcore_vars['mcore_launch_url'],
            'mode': 'popup'
        };
        M.mod_mediacore.chooser = mediacore.chooser.init(params);

        // Handle media add
        // TODO cleanup unused db fields
        M.mod_mediacore.chooser.on('media', function(media) {
            var mediaIdField = document.getElementById('mcore-media-id');
            var linkUrlField = document.getElementById('mcore-link-url');
            var embedUrlField = document.getElementById('mcore-embed-url');
            var metadataField = document.getElementById('mcore-metadata');

            mediaIdField.value = media.id;
            linkUrlField.value = media.embed_url.replace('embed_player', 'embed_link');
            embedUrlField.value = media.embed_url;
            var metadataJson = JSON.stringify(media);
            metadataField.value = metadataJson;

            var thumbPreview = document.getElementById('mcore-media-thumb');
            var iframeElem = document.getElementById('mcore-media-iframe');
            iframe.sec = media.embed_url;
            iframe.style.display = 'block';
            thumbPreview.style.display = 'none';

            var addMediaBtnElem = document.getElementById('id_mcore-add-media-btn');
            addMediaBtnElem.value = 'Replace Media'; //TODO i18n

            this.close();
        });

        // Handle media error
        M.mod_mediacore.chooser.on('error', function(media) {
            this.close();
            throw err;
        });
    }
    M.mod_mediacore.chooser.open();
};
