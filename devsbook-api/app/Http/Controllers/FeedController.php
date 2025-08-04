<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\User;
use App\Models\UserRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;

class FeedController extends Controller
{
    private $loggedUser;
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->loggedUser = Auth::user();
    }

    // Código que se repete em 3 locais diferentes em dois controllers (user e feed)
    // (refatorar)
    public function create(Request $request)
    {
        $type = $request->input('type');

        if (!$type) {
            return \response()->json([
                'status' => 'error',
                'message' => 'attribute type not found'
            ], 400);
        }

        $post = new Post();
        $post->id_user = $this->loggedUser['id'];

        switch ($type) {
            case 'text':
                $body = $request->input('body');

                if (!$body) {
                    return \response()->json([
                        'status' => 'error',
                        'message' => 'attribute body not found'
                    ], 400);
                }

                $post->type = $type;
                $post->body = $body;
                $post->save();
                break;

            case 'image':
                $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];

                $image = $request->file('post');

                if (!$image) {
                    return \response()->json([
                        'status' => 'error',
                        'message' => 'image not found',
                    ], 400);
                }

                if (!\in_array($image->getMimeType(), $allowedTypes)) {
                    return \response()->json([
                        'status' => 'error',
                        'message' => 'content not allowed',
                    ], 400);
                }

                $post->type = 'image';

                $destinationPath = \storage_path('images/post');
                $newFileName = \md5(\date('Y-m-d') . '_' . \rand(0, 9999)) . '.jpg';
                $newPath = $destinationPath . \DIRECTORY_SEPARATOR . $newFileName;

                $imageManager = new ImageManager(new Driver());

                $imageManager->read($image->path())->resize(width: 800)->save($newPath);

                $post->body = $newPath;
                $post->save();
                break;

            default:
                return \response()->json([
                    'status' => 'error',
                    'message' => 'type not found',
                ], 404);
        }

        return \response()->json([
            'status' => 'success',
            'message' => 'post added',
        ]);
    }

    // (refatorar)
    public function read(Request $request)
    {
        $page = \intval($request->input('page'));
        $perPage = 2;

        // 1. pegar a lista de usuários que EU sigo
        $followingList = UserRelation::where(
            'user_from',
            $this->loggedUser['id']
        )->get();

        $following = [];

        foreach ($followingList as $userFollowing) {
            $following[] = $userFollowing['user_to'];
        }
        $following[] = $this->loggedUser['id'];


        // 2. pegar os posts pela data (ordem decrescente)
        $postsOrderedDesc = Post::whereIn('id_user', $following)
            ->orderByDesc('created_at')
            ->offset($page * $perPage)
            ->limit($perPage)
            ->get();

        $pageCount = \ceil(count($postsOrderedDesc) / $perPage);

        // 3. preencher informações adicionais
        $postResponse = [];
        foreach ($postsOrderedDesc as $post) {
            $user = User::find($post['id_user']);

            $mounting = [];
            $mounting['post'] = $post;
            $mounting['username'] = $user->name;
            $mounting['comments'] = [];

            $postLikes = PostLike::where(
                'id_post',
                $post['id']
            )->count();

            $mounting['likes'] = $postLikes;

            $comment = PostComment::where(
                'id_post',
                $post['id']
            )->get();

            if ($comment) $mounting['comments'] = $comment;

            $postResponse[] = $mounting;
        }

        $totalFollowing = \count($following);

        return \response()->json([
            'following' => $totalFollowing ? $totalFollowing - 1 : $totalFollowing,
            'post_response' => $postResponse,
            'page' => $page,
            'page_count' => $pageCount,
        ]);
    }

    // código que se repete
    public function userFeed(Request $request, string $id)
    {
        $page = \intval($request->input('page'));
        $perPage = 2;

        if (!User::where('id', $id)->exists()) {
            return \response()->json([
                'status' => 'error',
                'message' => 'user not found',
            ], 404);
        }

        $postsOrderedDesc = Post::where('id_user', $id)
            ->orderByDesc('created_at')
            ->offset($page * $perPage)
            ->limit($perPage)
            ->get();

        $pageCount = \ceil(count($postsOrderedDesc) / $perPage);

        return \response()->json([
            'post_response' => $postsOrderedDesc,
            'page' => $page,
            'page_count' => $pageCount,
        ]);
    }


}
