<?php

// app/Http/Controllers/MarkerCommentController.php


namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController as BaseController;

use App\Models\Marker;
use App\Models\User;
use App\Models\MarkerComment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class MarkerCommentController extends BaseController
{
    // Create a new comment for a marker
    public function store(Request $request, $markerId)
    {
        $input = $request->all();
        $validator = Validator::make($input,[
            'user_id' => 'required|exists:users,id',
            'comment' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }


        $marker = Marker::findOrFail($markerId);

        $comment = $marker->comments()->create([
            'user_id' =>$input['user_id'],
            'comment' => $input['comment'],
            'marker_id'=>$markerId
        ]);

        return $this->sendResponse($comment,'Marker  Comment Saved');
    }


    public function showComments($markerId)
    {
        // Find the marker with the given ID
      //  $marker = Marker::findOrFail($markerId);
      $comments =  MarkerComment::with('user')->get();

        // Retrieve comments for the marker along with user information
       // $comments = $marker->comments()->with('user')->get();

        // Return a JSON response with the comments and HTTP status code 200 (OK)
        return $this->sendResponse($comments,'Marker  Comment retrieved successfully');
    }
    // Update a comment for a marker
    public function update(Request $request, $markerId, $commentId)
    {
        $input = $request->all();
       // dd( $input );
        $validator = Validator::make($input,[
           // 'user_id' => 'required',
            'comment' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $marker = Marker::findOrFail($markerId);
        $comment = $marker->comments()->findOrFail($commentId);

        $comment->update([
            'comment' => $request->comment,
        ]);

        return $this->sendResponse($comment,'Marker  Comment Update successfully');
    }

    // Delete a comment for a marker
    public function destroy($markerId, $commentId)
    {

        $marker = Marker::findOrFail($markerId);
         $getcomment=MarkerComment::where('id',$commentId)->first();
             if($getcomment==""){
                return $this->sendResponse("",'Comment Not Found');

            }
        $comment = $marker->comments()->findOrFail($commentId);


        $comment->delete();

        return $this->sendResponse("",'Marker  Comment Remove successfully');
    }
    public function users()
    {
        $users= User::get();
        return $this->sendResponse($users,'users retrieved successfully');
    }
}
