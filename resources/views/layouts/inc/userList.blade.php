<div class="card">
    <ul class="list-group user-list" style="list-style: none"></ul>
</div>


@push('script')
    <script>
        let onlineUsers = [];
        let leavingUsers = {};
        let selectedUser = null;

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
                    }, 0); // Delay for 2 seconds before removing user

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
                        let selectedClass = selectedUser === user.id ? 'bg-primary text-white' : '';
                        let userItem = `
                    <li>
                        <span class="list-group-item d-flex align-items-center user_page pointer ${selectedClass}" data-row-id="${user.id}">
                            <div class="i_man">
                                <img src="https://avatar.iran.liara.run/public/boy?username=${user.id}" class="i_man-image" />
                            </div>
                            <div class="ms-2 flex-grow-1 d-flex align-items-center">${user.name}</div>
                            ${onlineBadge}
                        </span>
                    </li>`;
                        userList.append(userItem);
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching users:", error);
                }
            });
        }

        $(document).on('click', '.user_page', function(event) {
            event.preventDefault();
            var user_id = $(this).data('row-id');
            selectedUser = user_id;
            console.log('object', selectedUser);

            // Remove the 'bg-primary text-white' classes from all user items
            $('.user_page').removeClass('bg-primary text-white');

            // Add 'bg-primary text-white' classes to the clicked user
            $(this).addClass('bg-primary text-white');


            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="token"]').attr('value')
                }
            });
            $.ajax({
                url: "{{ route('message_inbox', ['id' => ':id']) }}".replace(':id',
                    user_id),
                type: 'GET',
                success: function(res) {
                    $("#inbox").empty();
                    $("#inbox").append(res);


                    initializeNewScripts(user_id);
                },
                error: function(data) {
                    console.error(data.responseText);
                }
            });
        });

        function initializeNewScripts(rcvr_id) {
            const userId = {{ auth()->user()->id }};
            const receiverId = rcvr_id;
            const chatBox = document.getElementById("chat-box");
            const notification = document.getElementById("notification");
            const messageForm = document.getElementById("messageForm");
            const messageInput = document.getElementById("messageInput");

            function scrollToBottom() {
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            messageForm.addEventListener("submit", function(e) {
                e.preventDefault();

                const button = messageForm.querySelector('button');
                button.disabled = true;
                button.innerHTML = '<span class="loading_ind px-3"></span>';

                let formData = new FormData(messageForm);

                fetch("{{ route('messages.store') }}", {
                        method: "POST",
                        body: formData,
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute(
                                "content")
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            button.disabled = false;
                            button.innerHTML = 'Send';

                            const lastMessage = notification.lastElementChild;
                            const isReceiver = lastMessage && lastMessage.classList.contains('Chat_item_r');

                            if (isReceiver) {
                                lastMessage.querySelector('.Chat_msgs').insertAdjacentHTML('beforeend', `
            <div class="msg">
                <div class="msg-content">${data.message}</div>
            </div>`);
                            } else {
                                notification.insertAdjacentHTML('beforeend', `
            <li class="Chat_item Chat_item_r">
                <div class="Chat_msgs">
                    <div class="msg">
                        <div class="msg-content">${data.message}</div>
                    </div>
                </div>
            </li>`);
                            }

                            messageInput.value = "";
                            scrollToBottom();
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        button.disabled = false;
                        button.innerHTML = 'Send';
                    });
            });



            window.Echo.channel("messages." + userId).listen(".create", (e) => {
                console.log('object', e);

                if (e.sender == receiverId) {
                    document.getElementById('typing-indicator').style.display = 'none';

                    const lastMessage = notification.lastElementChild;
                    const isSender = lastMessage && lastMessage.classList.contains('Chat_item_l');

                    if (isSender) {
                        lastMessage.querySelector('.Chat_msgs').insertAdjacentHTML('beforeend', `
            <div class="msg">
                <div class="msg-content">${e.message}</div>
            </div>`);
                    } else {
                        notification.insertAdjacentHTML('beforeend', `
            <li class="Chat_item Chat_item_l">
                <div class="i_man">
                    <img src="https://avatar.iran.liara.run/public/boy?username=${receiverId}" class="i_man-image" />
                </div>
                <div class="Chat_msgs">
                    <div class="msg">
                        <div class="msg-content">
                            ${e.message}
                        </div>
                    </div>
                </div>
            </li>`);
                    }
                }

                scrollToBottom();
            });

            scrollToBottom();


            window.Echo.channel('messagestype.' + receiverId).listen('.typing', (e) => {
                if (event.receiver == null) {
                    document.getElementById('typing-indicator').style.display = 'none';
                }
                if (e.receiver == userId) {
                    document.getElementById('typing-indicator').style.display = 'block';
                }
            });

            const typingTimeout = 2000; // 2 seconds
            let typingTimer;

            messageInput.addEventListener("keydown", function() {
                typing();
            });

            messageInput.addEventListener("blur", function() {
                clearTimeout(typingTimer);
                stopTyping(); // Immediately stop typing when input loses focus
            });

            function typing() {
                const senderId = {{ auth()->user()->id }};
                const receiverId = rcvr_id;

                // Send AJAX request to indicate that the user is typing
                fetch("{{ route('messages.typing') }}", {
                        method: "POST",
                        body: JSON.stringify({
                            sender: senderId,
                            receiver: receiverId,
                        }),
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute(
                                "content"),
                        },
                    })
                    .then(response => response.json())
                    .catch(error => console.error("Error:", error));
            }

            function stopTyping() {
                const senderId = {{ auth()->user()->id }};
                const receiverId = rcvr_id;

                // Send AJAX request to indicate that the user has stopped typing
                fetch("{{ route('messages.stopTyping') }}", {
                        method: "POST",
                        body: JSON.stringify({
                            sender: senderId,
                            receiver: receiverId,
                        }),
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute(
                                "content"),
                        },
                    })
                    .then(response => response.json())
                    .catch(error => console.error("Error:", error));
            }

            $(document).ready(function() {
                let page = 1;
                let loading = false;

                $("#chat-box").on("scroll", function() {
                    if ($(this).scrollTop() === 0 && !loading) {
                        loading = true;
                        page++;
                        loadMoreMessages(page);
                    }
                });

                function loadMoreMessages(page) {
                    $.ajax({
                        url: "{{ route('chat.loadMore') }}",
                        type: "GET",
                        data: {
                            page: page,
                            receiver_id: selectedUser,
                        },
                        beforeSend: function() {
                            $("#chat-box").prepend('<div id="loading-indicator">Loading...</div>');
                        },
                        success: function(response) {
                            $("#loading-indicator").remove();
                            if (response.html) {
                                let oldScrollHeight = $("#chat-box")[0].scrollHeight;
                                $("#notification").prepend(response.html);
                                let newScrollHeight = $("#chat-box")[0].scrollHeight;
                                $("#chat-box").scrollTop(newScrollHeight - oldScrollHeight);
                            }
                            loading = false;
                        },
                        error: function() {
                            $("#loading-indicator").remove();
                            loading = false;
                        },
                    });
                }
            });

        }
    </script>
@endpush
