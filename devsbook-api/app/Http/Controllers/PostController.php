<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    private $loggedUser;
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->loggedUser = Auth::user();
    }

    public function like(Request $request, string $id)
    {
        if (
            !Post::where('id', $id)->exists() ||
            !User::where('id', $this->loggedUser['id'])->exists()
        ) {
            return \response()->json([
                'status' => 'error',
                'message' => 'post or user does not exist'
            ], 400);
        }

        $postExist = PostLike::where([
            'id_post' => $id,
            'id_user' => $this->loggedUser['id']
        ])->first();

        if ($postExist) {
            $postExist->delete();

            return \response()->json([
                'status' => 'success',
                'message' => "disliked in post $id"
            ]);
        }

        $postLike = new PostLike();
        $postLike->id_post = $id;
        $postLike->id_user = $this->loggedUser['id'];
        $postLike->save();

        return \response()->json([
            'status' => 'success',
            'message' => "liked post $id"
        ]);
    }

    public function createComment(Request $request, string $id)
    {
        if (
            !Post::where('id', $id)->exists() ||
            !User::where('id', $this->loggedUser['id'])->exists()
        ) {
            return \response()->json([
                'status' => 'error',
                'message' => 'post or user does not exist'
            ], 400);
        }

        $commentData = $request->input('comment');

        if (!$commentData) {
            return \response()->json([
                'status' => 'error',
                'message' => 'comment property is missing'
            ], 400);
        }

        $postComment = new PostComment();
        $postComment->id_post = $id;
        $postComment->id_user = $this->loggedUser['id'];
        $postComment->body = $commentData;
        $postComment->save();

        return \response()->json([
            'status' => 'success',
            'message' => 'comment created in post',
            'postComment_id' => $postComment->id
        ]);
    }

    public function deleteComment(Request $request, string $id)
    {
        $postComment = PostComment::find($id);

        if (!$postComment) {
            return \response()->json([
                'status' => 'error',
                'message' => 'comment not found',
            ], 404);
        }

        $postComment->delete();

        return \response()->json([
            'status' => 'success',
            'message' => 'comment deleted',
        ]);
    }
}
