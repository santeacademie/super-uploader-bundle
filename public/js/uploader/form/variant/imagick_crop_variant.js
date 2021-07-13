var uploadCrops = uploadCrops || {};

jQuery(function($) {
    var _uploadable = getUploadable();

    $(_uploadable.variantSelector).find('.croppie').each(function() {
        uploadCrops[$(this).attr('id')] = ICV_initCroppieOnVariant($(this));
    });

    $(document).on('change', [
        _uploadable.assetSelector + ' .upload-asset-input',
        _uploadable.variantSelector + ' .upload-variant-input',
        ].join(','), function () {
            ICV_bindCroppieFromInput(this, $(_uploadable.variantSelector).find('.croppie'));
        }
    );
});


function ICV_bindCroppieFromInput(input, $holder) {
    if (!$holder.length) {
        return;
    }

    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $holder.addClass('ready');
            $holder.removeClass('hidden');
            $holder.siblings('.image-wrapper').addClass('hidden');
            $holder.croppie('bind', {
                url: e.target.result,
            }).then(function () {
                // pass
            });
        };

        reader.readAsDataURL(input.files[0]);
    } else {
        alert("Sorry - you're browser doesn't support the FileReader API");
    }
}

function ICV_initCroppieOnVariant($holder) {
    var output = $holder.attr('data-output');
    var width = parseInt($holder.attr('data-width'));
    var height = parseInt($holder.attr('data-height'));

    var path = $holder.attr('data-variantFile');
    var zoom = parseFloat($holder.attr('data-zoom'));
    var topLeftX = parseFloat($holder.attr('data-topLeftX'));
    var topLeftY = parseFloat($holder.attr('data-topLeftY'));
    var bottomRightX = parseFloat($holder.attr('data-bottomRightX'));
    var bottomRightY = parseFloat($holder.attr('data-bottomRightY'));
    var points = [topLeftX, topLeftY, bottomRightX, bottomRightY];

    var boundary_width = 300;
    var boundary_height = 300;

    var viewport_width = boundary_width;
    var viewport_height = boundary_height;

    if (width > height) {
        viewport_height = (height * boundary_height) / width;
    } else {
        viewport_width = (width * viewport_width) / height;
    }

    var crop = $holder.croppie({
        quality: 1,
        viewport: {
            width: viewport_width,
            height: viewport_height
        },
        boundary: {
            width: boundary_width,
            height: boundary_height,
        },
        exif: false,
        enforceBoundary: false
    });

    if (path !== undefined && path !== '') {

        $holder.croppie('bind', {
            url: path,
            minZoom: 1,
            setZoom: zoom,
            points: points,
        }).then(function () {
            // pass
        });
    }

    $holder.on('update.croppie', function (ev, data) {
        $('#' + output + '_zoom').val(data.zoom);
        $('#' + output + '_topLeftX').val(data.points[0]);
        $('#' + output + '_topLeftY').val(data.points[1]);
        $('#' + output + '_bottomRightX').val(data.points[2]);
        $('#' + output + '_bottomRightY').val(data.points[3]);

        if (data.zoom < 0.1) {
            $holder.croppie('setZoom', 0.1);
        }
    })

    return crop;
}