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

        return view('posts.index', compact('users'));
    }

    public function inbox($id)
    {
        $messages = Message::where('sender', auth()->id())->orWhere('receiver', auth()->id())
            ->latest()
            ->paginate(5);

        $users = User::where('id', '!=', auth()->id())->get();
        $receiver = User::find($id);

        return view('posts.chat_box', compact('users', 'messages', 'receiver'));
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
                'sender' => auth()->id(),
                'receiver' => $request->receiver,
                'conversation_id' => $conversation->id,
            ]);

            $currentMessages = $conversation->messages ?? [];
            $currentMessages[] = $post->id;
            $conversation->messages = $currentMessages;
            $conversation->save();

            event(new PostCreate($post));

            DB::commit();

            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
            return redirect()->back()->with('error', 'An error occurred while storing the message.');
        }
    }
}
