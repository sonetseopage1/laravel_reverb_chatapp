<?php

namespace App\Http\Controllers;

use App\Events\PostCreate;
use App\Events\Typing;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        return view('message.index');
    }

    public function inbox($id)
    {
        $messages = Message::where(function ($query) use ($id) {
            $query->where('sender', auth()->id())
                ->where('receiver', $id);
        })
            ->orWhere(function ($query) use ($id) {
                $query->where('sender', $id)
                    ->where('receiver', auth()->id());
            })
            ->latest()  // Order by the latest messages
            ->paginate(30);

        $messages = $messages->reverse();
        $receiver = User::find($id);

        return view('message.chat_box', compact('messages', 'receiver'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'body' => 'required',
            'receiver' => 'required', // Ensure receiver is provided
        ]);

        // Start a transaction
        DB::beginTransaction();

        $senderId = auth()->id();
        $receiverId = (int) $request->receiver;

        try {
            $conversation = Conversation::whereJsonContains('participants', $senderId)
                ->whereJsonContains('participants', $receiverId)
                ->first();

            // If no conversation is found, create a new one
            if (!$conversation) {
                $conversation = Conversation::create([
                    'participants' => [$senderId, $receiverId],
                    'messages' => []  // You can initialize messages as an empty array
                ]);
            }

            $post = Message::create([
                'body' => $request->body,
                'sender' => $senderId,
                'receiver' => $receiverId,
                'conversation_id' => $conversation->id,
            ]);

            $currentMessages = $conversation->messages ?? [];
            $currentMessages[] = $post->id;
            $conversation->messages = $currentMessages;
            $conversation->save();

            event(new PostCreate($post));

            DB::commit();

            return response()->json(["success" => true, "message" => $post->body]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["success" => false, "error" => "An error occurred while storing the message."]);
        }
    }

    public function typing(Request $request)
    {
        $senderId = $request->sender;
        $receiverId = $request->receiver;

        // Trigger the Typing event
        event(new Typing($senderId, $receiverId, ));

        return response()->json(['success' => true]);
    }

    // Handle stop typing event
    public function stopTyping(Request $request)
    {
        $senderId = $request->sender;
        $receiverId = null;

        // Optionally broadcast a stop typing event or do other logic
        event(new Typing($senderId, $receiverId, ));

        return response()->json(['success' => true]);
    }

    public function message_inbox($id)
    {
        $messages = Message::where(function ($query) use ($id) {
            $query->where('sender', auth()->id())
                ->where('receiver', $id);
        })
            ->orWhere(function ($query) use ($id) {
                $query->where('sender', $id)
                    ->where('receiver', auth()->id());
            })
            ->latest()  // Order by the latest messages
            ->paginate(30);

        $messages = $messages->reverse();
        $receiver = User::find($id);

        if ($receiver)
            return view('message.componenet.inbox', compact('receiver', 'messages'));
        else
            return 'Data Not Found!';
    }

    public function loadMoreMessages(Request $request)
    {
        $receiverId = $request->receiver_id;
        $page = $request->page;

        $messages = Message::where(function ($query) use ($receiverId) {
            $query->where('sender', auth()->id())->where('receiver', $receiverId);
        })
            ->orWhere(function ($query) use ($receiverId) {
                $query->where('sender', $receiverId)->where('receiver', auth()->id());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page', $page);

        if ($messages->isEmpty()) {
            return response()->json(['html' => '']);
        }

        $messages = $messages->reverse();
        $receiver = User::find($receiverId);

        $view = view('layouts.inc.partial_message', compact('messages', 'receiver'))->render();

        return response()->json(['html' => $view]);
    }

}
