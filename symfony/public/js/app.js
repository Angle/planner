function openModal() {
    $('body').addClass('open-modal');
}

function closeModal() {
    $('body').removeClass('open-modal');
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