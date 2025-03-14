<?php

namespace App\Http\Controllers;

use App\Events\PostCreate;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        $users = User::where('id', '!=', auth()->id())->get();

        return view('message.index', compact('users'));
    }

    public function inbox($id)
    {
        $messages = Message::where('sender', auth()->id())->orWhere('receiver', auth()->id())
            ->latest()
            ->paginate(30);

        $users = User::where('id', '!=', auth()->id())->get();
        $receiver = User::find($id);

        return view('message.chat_box', compact('users', 'messages', 'receiver'));
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
        $receiverId = $request->receiver;

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
}
