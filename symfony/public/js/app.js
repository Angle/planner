function openModal() {
    var body = $('body');

    if (!body.hasClass('open-modal')) {
        body.addClass('open-modal');
    }
}

function closeModal() {
    var body = $('body');

    if (body.hasClass('open-modal')) {
        body.removeClass('open-modal');
    }
}

function loadModal(url) {
    openModal();
    $('#modal').load( url, function() {
        console.log( "Load was performed: " + url );
    });
}

/* ALERTS */
function dismissAlert(e) {
    $(e).parent().parent().remove();
}