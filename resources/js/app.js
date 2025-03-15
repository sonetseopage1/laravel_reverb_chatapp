import "./bootstrap";

let onlineUsers = [];

function updateUserList() {
    console.log("Currently Users", onlineUsers);
}

window.Echo.join("online-users")
    .here((users) => {
        onlineUsers = users;
        console.log("Currently Users", users);
        updateUserList();
    })
    .joining((user) => {
        console.log(user.name + " Joined");
        onlineUsers.push(user);
        updateUserList();
    })
    .leaving((user) => {
        console.log(user.name + " left");
        onlineUsers = onlineUsers.filter((u) => u.id != user.id);
        updateUserList();
    });



