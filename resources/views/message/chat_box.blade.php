@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                @include('layouts.inc.userList')
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ $receiver->name }}</div>
                    <div class="card-body">
                        <div class="Chat__wrapper" id="chat-box">
                            <ul class="Chat" id="notification">
                                @php
                                    $prevSender = null;
                                    $messagesGrouped = [];
                                @endphp

                                @foreach ($messages as $message)
                                    @if ($message->receiver == auth()->id())
                                        @if ($prevSender == 'auth')
                                            @php
                                                // Add message to the last grouped messages
                                                $messagesGrouped[count($messagesGrouped) - 1]['messages'][] = $message;
                                            @endphp
                                        @else
                                            @php
                                                // Start a new group for the authenticated user
                                                $messagesGrouped[] = ['sender' => 'auth', 'messages' => [$message]];
                                            @endphp
                                        @endif
                                        @php
                                            $prevSender = 'auth';
                                        @endphp
                                    @else
                                        @if ($prevSender == 'receiver')
                                            @php
                                                // Add message to the last grouped messages
                                                $messagesGrouped[count($messagesGrouped) - 1]['messages'][] = $message;
                                            @endphp
                                        @else
                                            @php
                                                // Start a new group for the receiver
                                                $messagesGrouped[] = ['sender' => 'receiver', 'messages' => [$message]];
                                            @endphp
                                        @endif
                                        @php
                                            $prevSender = 'receiver';
                                        @endphp
                                    @endif
                                @endforeach

                                @foreach ($messagesGrouped as $group)
                                    @if ($group['sender'] == 'auth')
                                        <li class="Chat_item Chat_item_l">
                                            <div class="i_man">
                                                <img src="https://avatar.iran.liara.run/public/boy?username={{ $receiver->id }}" class="i_man-image" />
                                            </div>
                                            <div class="Chat_msgs">
                                                @foreach ($group['messages'] as $message)
                                                    <div class="msg">
                                                        <div class="msg-content">{{ $message->body }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </li>
                                    @else
                                        <li class="Chat_item Chat_item_r">
                                            <div class="Chat_msgs">
                                                @foreach ($group['messages'] as $message)
                                                    <div class="msg">
                                                        <div class="msg-content">{{ $message->body }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div id="typing-indicator" style="display: none;">
                            <span class="loading">{{ $receiver->name }} is typing</span>
                        </div>


                        <form id="messageForm" class="mb-3">
                            @csrf
                            <div class="mt-2">
                                <label>Write Message</label>
                                <input type="text" class="form-control" name="body" id="messageInput" required />
                            </div>
                            <input type="hidden" name="receiver" value="{{ $receiver->id }}">
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary">Send</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section('script')
        <script type="module">
            const userId = {{ auth()->user()->id }};
            const receiverId = {{ $receiver->id }};
            const chatBox = document.getElementById("chat-box");
            const notification = document.getElementById("notification");
            const messageForm = document.getElementById("messageForm");
            const messageInput = document.getElementById("messageInput");

            function scrollToBottom() {
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            messageForm.addEventListener("submit", function(e) {
                e.preventDefault();

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
                    .catch(error => console.error("Error:", error));
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
                const receiverId = {{ $receiver->id }};

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
                const receiverId = {{ $receiver->id }};

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
        </script>

        <script>
            $(document).ready(function() {
                let selectedUserId = {{ $receiver->id ?? 'null' }};

                fetchUsers(); // Fetch users on page load

                function fetchUsers() {
                    $.ajax({
                        url: "/users", // The endpoint to fetch users
                        type: "GET",
                        dataType: "json",
                        success: function(users) {
                            let userList = $(".user-list");
                            userList.empty(); // Clear previous list

                            users.forEach(user => {
                                let isSelected = user.id == selectedUserId ?
                                    "bg-primary text-white" : "";
                                let userItem = `<li>
                                    <a href="/inbox/${user.id}" class="list-group-item d-flex align-items-center ${isSelected}">
                                        <div class="i_man">
                                            <img src="https://avatar.iran.liara.run/public/boy?username=${user.id}" class="i_man-image" />
                                        </div>
                                        <div class="ms-2 flex-grow-1 d-flex align-items-center">${user.name}</div>
                                    </a>
                                </li>
                                `;
                                userList.append(userItem);
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error("Error fetching users:", error);
                        }
                    });
                }
            });
        </script>
    @endsection
