function floodgate_response(status,title,msg) {
    var response    = "";
    if ('true'==status) {
        if (title == "")    title = successDefaultTitle;
        if (msg == "")      msg = successDefaultMsg;
        response = showSuccess;
    } else {
        if (title == "")    title = errorDefaultTitle;
        if (msg == "")      msg = errorDefaultMsg;
        response = showError;
    }
    response        = response.replace("%title%", title);
    response        = response.replace("%msg%", msg);

    return response;
}
function floodgate_response_hide() {
    jQuery("#ei8-confirmation").delay(7000).fadeOut(3000);
}