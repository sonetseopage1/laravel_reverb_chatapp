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
