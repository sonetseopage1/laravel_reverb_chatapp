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
       @include('layouts.inc.partial_message')
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


