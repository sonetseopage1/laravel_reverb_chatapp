<div class="card">
    <ul class="list-group user-list" style="list-style: none"></ul>
</div>


@push('script')
    <script>
        let onlineUsers = [];
        let leavingUsers = {};

        document.addEventListener("DOMContentLoaded", function() {
            if (typeof window.Echo === "undefined") {
                console.error("Echo is not defined! Make sure Vite is loading app.js.");
                return;
            }

            // Check for any stored users in localStorage
            const storedOnlineUsers = JSON.parse(localStorage.getItem('onlineUsers')) || [];
            onlineUsers = storedOnlineUsers;
            updateUserList(); // Initialize user list from localStorage

            window.Echo.join("online-users")
                .here((users) => {
                    onlineUsers = users;
                    console.log("Currently Users", users);
                    updateUserList();
                    // Save the current users to localStorage
                    localStorage.setItem('onlineUsers', JSON.stringify(onlineUsers));
                })
                .joining((user) => {
                    console.log(user.name + " Joined");
                    onlineUsers.push(user);
                    updateUserList();
                    // Update localStorage
                    localStorage.setItem('onlineUsers', JSON.stringify(onlineUsers));
                })
                .leaving((user) => {
                    console.log(user.name + " left");

                    // Delay the removal of the user to avoid immediate "flip"
                    leavingUsers[user.id] = setTimeout(() => {
                        onlineUsers = onlineUsers.filter((u) => u.id !== user.id);
                        updateUserList();
                        // Remove from localStorage
                        localStorage.setItem('onlineUsers', JSON.stringify(onlineUsers));
                    }, 2000); // Delay for 2 seconds before removing user

                    // Optionally, you can check if the user rejoined before the timeout finishes
                    window.Echo.join("online-users").joining((joinedUser) => {
                        if (joinedUser.id === user.id) {
                            clearTimeout(leavingUsers[user
                            .id]); // Cancel the leave timeout if user rejoins
                            delete leavingUsers[user.id]; // Clean up the leaving users object
                        }
                    });
                });
        });

        function updateUserList() {
            console.log("Currently Online Users", onlineUsers);
            fetchUsers();
        }

        $(document).ready(function() {
            fetchUsers();
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
                        let isOnline = onlineUsers.some(onlineUser => onlineUser.id == user.id);
                        console.log('status', isOnline);
                        console.log('onlineUser', onlineUsers);
                        console.log('all user', user);
                        let onlineBadge = isOnline ?
                            `<span class="badge bg-success ms-auto">Online</span>` : "";

                        let isSelected = user.id == selectedUserId ? "bg-primary text-white" : "";
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
