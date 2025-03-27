<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use Validator;
class CommentController extends BaseController
{
    // Get all comments for a post
    public function index($post_id)
    {
        $comments = Comment::where('post_id', $post_id)->with('replies.user','user')->get();
        return $this->sendResponse($comments, 'Comments retrieved successfully.');
    }

    // Create a new comment for a post
    public function store(Request $request, $post_id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'body' => 'required|string|max:255'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $user = auth()->user(); // Assuming you're using authentication
        $comment = Comment::create([
            'post_id' => $post_id,
            'body' => $request->body,
            'user_id' => $user->id // Assign the authenticated user's id
        ]);
        return $this->sendResponse($comment, 'Comment created successfully.');
    }

    // Get a specific comment
    public function show($post_id, $comment_id)
    {
        $comment = Comment::where('id', $comment_id)->where('post_id', $post_id)->with('replies.user','user')->first();


        if (is_null($comment)) {
            return $this->sendError('Comment not found.');
        }

        return $this->sendResponse($comment, 'Comment retrieved successfully.');
    }

    // Update a comment
    public function update(Request $request, $post_id, $comment_id)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'body' => 'required|string|max:255'
        ]);
        $comment = Comment::where('id', $comment_id)->where('post_id', $post_id)->first();

        if (!$comment) {
            return $this->sendError('Comment not found.');
        }

        $comment->update(['body' => $input['body']]);

        return $this->sendResponse($comment, 'Comment updated successfully.');

    }

    // Delete a comment
    public function destroy($post_id, $comment_id)
    {
        $comment = Comment::where('id', $comment_id)->where('post_id', $post_id)->first();

        if (!$comment) {
            return $this->sendError('Comment not found.');
        }

        $comment->delete();

        return $this->sendResponse([], 'Comment deleted successfully.');
    }
}
