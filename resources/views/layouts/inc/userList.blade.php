<div class="card">
    <ul class="list-group user-list" style="list-style: none"></ul>
</div>


@push('script')
<script>
    let onlineUsers = [];

    document.addEventListener("DOMContentLoaded", function() {
        if (typeof window.Echo === "undefined") {
            console.error("Echo is not defined! Make sure Vite is loading app.js.");
            return;
        }

        function updateUserList() {
            console.log("Currently Online Users", onlineUsers);
            fetchUsers(); // Refresh user list when online status changes
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

    });

    $(document).ready(function() {
        fetchUsers(); // Fetch users on page load
    });

    function fetchUsers() {
        let selectedUserId = {{ $receiver->id ?? 'null' }};

        $.ajax({
            url: "/users", // The endpoint to fetch all users
            type: "GET",
            dataType: "json",
            success: function(users) {
                let userList = $(".user-list");
                userList.empty(); // Clear previous list

                users.forEach(user => {
                    let isOnline = onlineUsers.some(onlineUser => onlineUser.id == user
                        .id);
                    console.log('status', isOnline);
                    console.log('onlineUser', onlineUsers);
                    console.log('all user', user);
                    let onlineBadge = isOnline ?
                        `<span class="badge bg-success ms-auto">Online</span>` : "";

                    let isSelected = user.id == selectedUserId ?
                        "bg-primary text-white" : "";
                    let userItem = `
                        <li>
                            <a href="/inbox/${user.id}" class="list-group-item d-flex align-items-center ${isSelected}">
                                <div class="i_man">
                                    <img src="https://avatar.iran.liara.run/public/boy?username=${user.id}" class="i_man-image" />
                                </div>
                                <div class="ms-2 flex-grow-1 d-flex align-items-center">${user.name}</div>
                                ${onlineBadge}
                            </a>
                        </li>`;
                    userList.append(userItem);
                });
            },
            error: function(xhr, status, error) {
                console.error("Error fetching users:", error);
            }
        });
    }
</script>
@endpush
