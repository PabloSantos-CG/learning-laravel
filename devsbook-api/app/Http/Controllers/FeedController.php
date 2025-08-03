<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
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

    public function read(Request $request)
    {
        $page = \intval($request->input('page'));
        $perPage = 2;

        // 1. pegar a lista de usuários que EU sigo
        // - userRelations
        $following = UserRelation::where(
            'user_from',
            $this->loggedUser['id']
        )->count();


        // 2. pegar os posts pela data (ordem decrescente)
        // ->orderByDesc()
        $postsOrderedDesc = Post::orderByDesc('created_at')
            ->limit($perPage)->get()->toArray();

        // 3. preencher informações adicionais
        /* (
            de quem é ? 
            quantos likes ?
            eu curti ?
            lista de comentários !
        )
        */
        $postResponse = [];
        foreach ($postsOrderedDesc as $post) {
            $user = User::find($post['id_user']);

            $mounting = [];
            $mounting['post'] = $post;
            $mounting['username'] = $user->name;
            $mounting['comments'] = [];

            $postLikes = Post::where(
                'id_post',
                $post['id']
            )->count();

            $mounting['likes'] = $postLikes;

            $comment = PostComment::where(
                'id_post',
                $post['id']
            )->get();

            if ($comment) $mounting['comments'] = $comment;

            \array_push($postResponse, $mounting);
        }

        return \response()->json($postResponse);
    }
}
