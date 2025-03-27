<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Comment;
use App\Models\Reply;
use Validator;
class ReplyController extends BaseController
{
    // Get all replies for a comment
    public function index($comment_id)
    {
        $replies = Reply::where('comment_id', $comment_id)->with('user')->get();
        return $this->sendResponse($replies, 'Replies retrieved successfully.');
    }

    // Create a new reply for a comment
    public function store(Request $request, $comment_id)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'body' => 'required|string|max:255'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $user = auth()->user(); // Assuming you're using authentication
        $comment = Comment::find($comment_id);

        if (!$comment) {
            return $this->sendError('Comment not found.');
        }

        $reply = Reply::create([
            'comment_id' => $comment_id,
            'body' => $request->body,
            'user_id' => $user->id // Assign the authenticated user's id
        ]);
      //  return response()->json(['reply' => $reply], 201);
        return $this->sendResponse($reply, 'Reply created successfully.');
    }

    // Get a specific reply
    public function show($comment_id, $reply_id)
    {
        $reply = Reply::where('id', $reply_id)->where('comment_id', $comment_id)->with('user')->first();

        if (is_null($reply)) {
            return $this->sendError('Reply not found.');
        }

        return $this->sendResponse($reply, 'Reply retrieved successfully.');
    }

    // Update a reply
    public function update(Request $request, $comment_id, $reply_id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'body' => 'required|string|max:255'
        ]);

        $reply = Reply::where('id', $reply_id)->where('comment_id', $comment_id)->first();

        if (is_null($reply)) {
            return $this->sendError('Reply not found.');
        }

        $reply->update(['body' => $request->body]);

        return $this->sendResponse($reply, 'Reply updated successfully.');
    }

    // Delete a reply
    public function destroy($comment_id, $reply_id)
    {
        $reply = Reply::where('id', $reply_id)->where('comment_id', $comment_id)->first();

        if (!$reply) {
            return $this->sendError('Comment not found.');
        }

        $reply->delete();

        return $this->sendResponse([], 'Reply deleted successfully.');
    }
}
