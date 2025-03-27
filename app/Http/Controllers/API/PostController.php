<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Post;
use Validator;
use App\Models\Like;
use App\Helpers\Helper;
use \App\Models\User;
use \App\Models\GroupMember;
use App\Models\Comment;
class PostController extends BaseController
{


    public function index(Request $request)
    {

        $authUserId =$request->user_id ?$request->user_id : null;

        $post = Post::withCount(['likes', 'comments'])
            ->with('user')
            ->get()
            ->each(function ($post) use ($authUserId) {
                $post->liked = $authUserId ? $post->likes->contains('user_id', $authUserId) : false;
            });
        //  $post=Post::withCount(['likes', 'comments'])->with('user')->get();
        return $this->sendResponse($post, 'Posts retrieved successfully.');
    }
    /**

 * @OA\Post(
 *      path="/api/posts",
 *      operationId="createPost",
 *      tags={"Posts"},
 *      summary="Create a new post",
 *      security={{"sanctum": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="title",
 *                     type="string",
 *                 ),
 *                 @OA\Property(
 *                     property="content",
 *                     type="string",
 *                 ),
 *                 @OA\Property(
 *                     property="image",
 *                     type="string",
 *                     format="binary",
 *                 ),
 *             )
 *         )
 *     ),
*   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *)
     **/
    public function store(Request $request)
    {
        // $input = $request->all();
        // $validator = Validator::make($input, [
        //     'title' => 'required|string|max:255',
        //     'content' => 'required|string',
        //     'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048' // Adjust file types and size as needed
        // ]);
        // if($validator->fails()){
        //     return $this->sendError('Validation Error.', $validator->errors());
        // }

        // if ($request->hasFile('image')) {
        //     $image = $request->file('image');
        //     $imageName = time() . '.' . $image->getClientOriginalExtension();
        //     $image->move(public_path('images'), $imageName);

        //     // Generate the full URL
        //     $imageUrl = url('images/' . $imageName);
        // } else {
        //     $imageUrl = null; // If no image is uploaded
        // }

                $input = $request->all();
//dd($input);
        // Define validation rules based on post_type
        $validationRules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ];

        if ($input['post_type'] == 'video') {
            $validationRules['video'] = 'required|mimes:mp4,avi,flv|max:20480'; // Adjust file types and size as needed
        } else {
            $validationRules['image'] = 'image|mimes:jpeg,png,jpg,gif|max:2048'; // Adjust file types and size as needed
        }

        $validator = Validator::make($input, $validationRules);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $imageUrl="";
        $videoUrl="";
        if ($input['post_type'] == 'video' && $request->hasFile('video')) {
            $video = $request->file('video');
            $videoName = time() . '.' . $video->getClientOriginalExtension();
            $video->move(public_path('videos'), $videoName);

            // Generate the full URL
            $videoUrl = url('videos/' . $videoName);
        } elseif ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);

            // Generate the full URL
            $imageUrl = url('images/' . $imageName);
        } else {
            $videoUrl = $imageUrl = null; // If no video or image is uploaded
        }
        $post = Post::create([
        'group_id' => $request->group_id ?? null,
        'content' => $request->content,
            'title' => $request->title,
            'user_id' => $request->user_id,
            'image' => $imageUrl, // Add this line to include the image filename in the database
            'video_url'=>  $videoUrl,
            'post_type'=>$request->post_type,
        ]);
        if (isset($request->group_id)&&$request->group_id !="") {
            $userIds = GroupMember::where('group_id', $request->group_id)->pluck('user_id')->toArray();
            $userIds[] =$user->organization->admin_id;
        }
        // Fetch users with valid FCM tokens
        // if (isset($request->group_id)&&$request->group_id !="") {
        //     $userIds = GroupMember::where('group_id', $request->group_id)->where('group_id', $request->group_id)->pluck('user_id');
        //     $users->whereIn('id', $userIds);
        // }
        // $users =$users->get();
        // dd($userIds);
        Helper::sendFcmNotification($userIds, "demo test", "demo test", $icon = "demo test");
        return $this->sendResponse($post, 'Post created successfully.');
    }
     /**
    * @OA\Get(
        *      path="/api/posts/{post}",
        *      operationId="ShowPost",
        *      tags={"Posts"},
        *      summary="post Detail",
        *      security={{"sanctum": {}}},
        *
            *   *  @OA\Parameter(
                *      name="post",
                *      in="path",
                *      required=true,
                *      @OA\Schema(
                *           type="integer"
                *      )
                *   ),
             *   @OA\Response(
            *      response=201,
            *       description="Success",
            *      @OA\MediaType(
            *           mediaType="application/json",
            *      )
            *   ),
       *   @OA\Response(
            *      response=401,
            *       description="Unauthenticated"
            *   ),
            *   @OA\Response(
            *      response=400,
            *      description="Bad Request"
            *   ),
            *   @OA\Response(
            *      response=404,
            *      description="not found"
            *   ),
            *      @OA\Response(
            *          response=403,
            *          description="Forbidden"
            *      )
            *)
            **/
    public function show(Request $request,$id)
    {
        $authUserId =$request->user_id ?$request->user_id : null;
        $Post = Post::withCount(['likes', 'comments'])
            ->with('user')
            ->find($id);

        $Post['liked'] = $authUserId && $Post ? $Post->likes->contains('user_id', $authUserId) : false;

        if (is_null($Post)) {
            return $this->sendError('Post not found.');
        }
      //  $Post['likescount'] = Like::where('post_id', $id)->count();
       // $Post['commentscount']  = Comment::where('post_id', $id)->count();
        return $this->sendResponse($Post, 'Post retrieved successfully.');
    }
/**
    * @OA\Put(
        *      path="/api/posts/{post}",
        *      operationId="Update Post",
        *      tags={"Posts"},
        *      summary="Update post",
        *      security={{"sanctum": {}}},
           *      @OA\Parameter(
 *          name="post",
 *          in="path",
 *          description="ID of the post",
 *          required=true,
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),
                 *      @OA\Parameter(
 *          name="post",
 *          in="path",
 *          description="ID of the post",
 *          required=true,
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),

           *      @OA\Parameter(
 *          name="post",
 *          in="path",
 *          description="ID of the post",
 *          required=true,
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),

           *      @OA\Parameter(
 *          name="post",
 *          in="path",
 *          description="ID of the post",
 *          required=true,
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),

       *   @OA\Response(
            *      response=401,
            *       description="Unauthenticated"
            *   ),
            *   @OA\Response(
            *      response=400,
            *      description="Bad Request"
            *   ),
            *   @OA\Response(
            *      response=404,
            *      description="not found"
            *   ),
            *      @OA\Response(
            *          response=403,
            *          description="Forbidden"
            *      )
            *)
            **/
    public function update(Request $request,$id)
    {
        //dd($id);
        $input = $request->all();
                // Define validation rules based on post_type
        $validationRules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ];

        if ($input['post_type'] == 'video') {
            $validationRules['video'] = 'nullable|mimes:mp4,avi,flv|max:20480'; // Adjust file types and size as needed
        } else {
            $validationRules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'; // Adjust file types and size as needed
        }

        $validator = Validator::make($input, $validationRules);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
       // dd( $input );
        // $validator = Validator::make($input, [
        //     'title' => 'required|string|max:255',
        //     'content' => 'required|string',
        //     'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048' // Add image validation rule
        // ]);

        // if($validator->fails()){
        //     return $this->sendError('Validation Error.', $validator->errors());
        // }
        // if ($request->hasFile('image')) {
        //     $image = $request->file('image');
        //     $imageName = time() . '.' . $image->getClientOriginalExtension();
        //     $image->move(public_path('images'), $imageName);
        //     $imageUrl = url('images/' . $imageName);
        //     $post->image = $imageUrl;
        // }
        $post = Post::findOrFail($id); // Assuming $id contains the ID of the post to be updated
        if ($input['post_type'] == 'video' && $request->hasFile('video')) {
            // Delete old image if any
            if ($post->image_url) {
                Storage::delete($post->image_url);
            }

            $video = $request->file('video');
            $videoName = time() . '.' . $video->getClientOriginalExtension();
            $video->move(public_path('videos'), $videoName);

            // Generate the full URL
            $videoUrl = url('videos/' . $videoName);
            $post->video_url = $videoUrl;


        }else if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);

            // Generate the full URL
            $imageUrl = url('images/' . $imageName);
            $post->image = $imageUrl;
          }
        $post->post_type =  $request->post_type;
        $post->title = $request->title;
        $post->content = $request->content;
        $post->save();

        return $this->sendResponse($post, 'Post updated successfully.');

    }

    public function destroy(Post $post)
    {
        $post->delete();

        return $this->sendResponse([], 'Post deleted successfully.');
    }

}
