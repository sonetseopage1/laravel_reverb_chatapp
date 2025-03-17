<?php

namespace App\Http\Controllers;

use App\Events\VideoCallSignal;
use Illuminate\Http\Request;

class VideoCallController extends Controller
{
    public function call()
    {
        return view('video_call.call_screen');
    }
    public function signal(Request $request)
    {
        event(new VideoCallSignal($request->input('data'), $request->input('receiverId')));
        return response()->json(['message' => 'Signal sent']);
    }
}
