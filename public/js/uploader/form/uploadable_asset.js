window._uploadables = window._uploadables || {'_internal': {'js':{}, 'css':{}, 'bridgeFuncName': 'getUploadable'}};

jQuery(function($) {
    jQuery.loadAssetStatic = function (url, callback, type) {
        if (!isset(window._uploadables['_internal'][type][url])) {
            window._uploadables['_internal'][type][url] = 'init';
        }

        jQuery.ajax({
            url: url,
            async: true,
            dataType: 'text',
            success: function(success) {
                window._uploadables['_internal'][type][url] = 'success';
                callback(success);
            },
            error: function() {
                window._uploadables['_internal'][type][url] = 'error';
            }
        });

        return true;
    };

    jQuery.loadAssetCSS = function(url) {
        jQuery.loadAssetStatic(
            url,
            function(cssData) {
                $("head").append("<style>" + cssData + "</style>");
            },
            'css'
        );
    };

    jQuery.loadAssetJS = function(url, callback) {
        jQuery.loadAssetStatic(url, callback, 'js');
    };

    $('.uploadable-asset-variant-static-injector').each(function() {
        var uploadable = $(this).data();

        uploadable['key'] = [uploadable.asset, uploadable.variant].join('_');
        uploadable['assetSelector'] = '.upload-asset-'+uploadable.asset;
        uploadable['variantSelector'] = uploadable['assetSelector'] + ' .upload-variant-'+uploadable.variant+'.'+uploadable.variantType;

        window._uploadables[uploadable.key] = uploadable;

        uploadable.css && $.loadAssetCSS(uploadable.css);
        uploadable.js && $.loadAssetJS(uploadable.js, function(jsData) {
            var regex = window._uploadables['_internal'].bridgeFuncName + '\\(\\)';
            jsData = jsData.replace(new RegExp(regex,"g"), window._uploadables['_internal'].bridgeFuncName + "('"+uploadable.key+"')");
            $("body").append("<script>" + jsData + "</script>");
        });
    });

    $('input[type="file"]').change(function(e){
        var fileName = e.target.files[0].name;
        $(this).siblings('.custom-file-label').attr('title', fileName).html(fileName);
    });
});

window[window._uploadables['_internal'].bridgeFuncName] = function(uavKey) {
    return window._uploadables[uavKey];
};
