OC.Share.updateIcon = function(itemType, itemSource) {
    var shares = false;
    var link = false;
    var image = OC.imagePath('core', 'actions/share');
    $.each(OC.Share.itemShares, function(index) {
        if (OC.Share.itemShares[index]) {
            if (index == OC.Share.SHARE_TYPE_LINK) {
                if (OC.Share.itemShares[index] == true) {
                    shares = true;
                    image = OC.imagePath('core', 'actions/public');
                    link = true;
                    return;
                }
            } else if (OC.Share.itemShares[index].length > 0) {
                shares = true;
                image = OC.imagePath('core', 'actions/share');
            }
        }
    });
    if (itemType != 'file' && itemType != 'folder') {
        $('a.share[data-item="'+itemSource+'"]').css('background', 'url('+image+') no-repeat center');
    } else {
        var $tr = $('tr', parent.document).filterAttr('data-id', String(itemSource));
        if ($tr.length > 0) {
            // it might happen that multiple lists exist in the DOM
            // with the same id
            $tr.each(function() {
                OC.Share.markFileAsShared($(this), shares, link);
            });
        }
    }
    if (shares) {
        OC.Share.statuses[itemSource] = OC.Share.statuses[itemSource] || {};
        OC.Share.statuses[itemSource]['link'] = link;
    } else {
        delete OC.Share.statuses[itemSource];
    }
}

$(document).ready(function() {
    $(document).on('ajaxSend',function(elm, xhr) {
        xhr.setRequestHeader('requesttoken', parent.oc_requesttoken);
    });

    $(document).on('click', '#share', function(event) {
        var filename = $('#filename').val();
        var trElmt = $("tr[data-file='"+filename+"']", parent.document);

        event.stopPropagation();
        var itemType = 'file';
        var itemSource = trElmt.data('id');
        var appendTo = $(this).parent().parent();
        var link = true;
        var possiblePermissions = trElmt.data('permissions');
        if ($(this).data('link') !== undefined && $(this).data('link') == true) {
            link = true;
        }
        if (OC.Share.droppedDown) {
            OC.Share.hideDropDown();
        } else {
            OC.Share.showDropDown(itemType, itemSource, appendTo, link, possiblePermissions);
        }
    });
});
