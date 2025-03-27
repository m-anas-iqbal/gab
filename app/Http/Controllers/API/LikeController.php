<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class LikeController extends BaseController
{
    public function likePost(Request $request, Post $post)
{
    $user = Auth::user();

    $like = Like::where('user_id', $user->id)
                    ->where('post_id', $post->id)
                    ->first();
    if (!$like) {
        $like = new Like();
        $like->user_id = $user->id;
        $like->post_id = $post->id;
        $like->save();
    }
    return $this->sendResponse($like, 'Post liked.');

}

public function unlikePost(Request $request, Post $post)
{
    $user = auth()->user();

    $like = Like::where('user_id', $user->id)
                    ->where('post_id', $post->id)
                    ->first();

    if ($like) {
        $like->delete();
    }
    return $this->sendResponse([], 'Post unliked.');

}
}
