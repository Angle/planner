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

/* FORM REWRITE */
function rewriteFocusMode(path) {
    var focusDate = document.getElementById('focusDateInput').value;

    if (!focusDate) {
        alert('Please specify a valid date to enter Focus Mode');
        return false;
    }

    console.log(focusDate);
    path = path.replace('--FOCUS--', focusDate);
    console.log(path);

    window.location = path;
}