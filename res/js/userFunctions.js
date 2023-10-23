const subscribe_btn = document.getElementById("subscribe_btn");
function followUser() {
    var api = new APIRequest("follow");
    api.sendAndTrigger({
        "uid": getIDParam()
    });
    subscribe_btn.onclick = unfollowUser.bind(this);
    subscribe_btn.value = "Unfollow";
}
function unfollowUser() {
    var api = new APIRequest("unfollow");
    api.sendAndTrigger({
        "uid": getIDParam()
    });
    subscribe_btn.onclick = followUser.bind(this);
    subscribe_btn.value = "Follow";
}