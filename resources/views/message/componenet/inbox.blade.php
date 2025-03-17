<div class="card-header">
    <span class="d-flex align-items-center">
        <div class="i_man">
            <img src="https://avatar.iran.liara.run/public/boy?username={{ $receiver->id }}" class="i_man-image" />
        </div>
        <div>
            &nbsp; &nbsp;{{ $receiver->name }}
        </div>
    </span>
</div>
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
                            <img src="https://avatar.iran.liara.run/public/boy?username={{ $receiver->id }}"
                                class="i_man-image" />
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
        <span class="loading_ind">{{ $receiver->name }} is typing</span>
    </div>


    <form id="messageForm" class="mb-3">
        @csrf
        <input type="hidden" name="receiver" value="{{ $receiver->id }}">

        <div class="mt-2">
            <label>Write Message</label>
            <div class="d-flex gap-3">
                <input type="text" class="form-control" name="body" id="messageInput" required />
                <div class="wrapper">
                    <button class="sendButton btn btn-primary" type="submit">Send</button>
                </div>
            </div>
        </div>
    </form>
</div>


