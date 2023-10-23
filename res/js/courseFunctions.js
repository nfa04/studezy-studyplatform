function unsubscribe(btn) {
    new APIRequest("unsubscribe").sendAndTrigger({
        cid: getIDParam()
    });
    btn.value = "Subscribe";
    btn.setAttribute("onclick", "subscribe(this)");
}

function subscribe(btn) {
    new APIRequest("subscribe").sendAndTrigger({
        cid: getIDParam()
    });
    btn.value = "Unsubscribe";
    btn.setAttribute("onclick", "unsubscribe(this)");
}

document.getElementById("notifications_active").addEventListener("click", event => {
    new APIRequest("modify_notification").sendAndTrigger({
        cid: getIDParam(),
        state: event.target.checked
    });
});

document.getElementById("email_active").addEventListener("click", event => {
    new APIRequest("modify_email_notification").sendAndTrigger({
        cid: getIDParam(),
        state: event.target.checked
    });
});

document.getElementById("calendar_subscription").addEventListener("click", event => {
    new APIRequest("modify_course_calendar_subscription").sendAndTrigger({
        cid: getIDParam(),
        state: event.target.checked
    });
});