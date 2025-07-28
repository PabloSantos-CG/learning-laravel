<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;

class UserController extends Controller
{
    private $loggedUser;

    public function __construct(
        private UserService $userService,
    ) {
        $this->middleware('auth:api', ['except' => ['create']]);
        $this->loggedUser = Auth::user();
    }

    public function create(Request $request, AuthService $authService)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $birthdate = $request->input('birthdate');

        if (!$name || !$email || !$password || !$birthdate) {
            return \response()->json([
                'status' => 'error',
                'message' => 'invalid attributes',
            ], 400);
        }

        $userExists = $this->userService->getFirstUser('email', $email);

        if ($userExists) {
            return \response()->json([
                'status' => 'error',
                'message' => 'user exists',
            ], 400);
        }

        $passwordEncripted = \password_hash($password, PASSWORD_BCRYPT);
        $emailIsValid = \filter_var($email, \FILTER_VALIDATE_EMAIL);

        if (!$emailIsValid) {
            return \response()->json([
                'status' => 'error',
                'message' => 'invalid e-mail',
            ], 400);
        }

        $birthdateIsValid = \strtotime($birthdate);

        if (!$birthdateIsValid) {
            return \response()->json([
                'status' => 'error',
                'message' => 'invalid date',
            ], 400);
        }

        $this->userService->createUser([
            'name' => $name,
            'email' => $email,
            'password' => $passwordEncripted,
            'birthdate' => $birthdate,
        ]);

        // user ficou amarrado ao auth, nao está correto
        $token = $authService->authenticate([
            'email' => $email,
            'password' => $password
        ]);

        if (!$token) {
            return \response()->json([
                'status' => 'error',
                'message' => 'authentication failed',
            ], 400);
        }

        return \response()->json([
            'status' => 'success',
            'message' => 'created user',
            'token' => $token,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        // implementar update com for para evitar ifs
        $attributes = [];
        $attributesLabel = ['name', 'email', 'birthdate'];

        foreach ($attributesLabel as $label) {
            $checkingAttribute = $request->input($label);
            if ($checkingAttribute) $attributes[$label] = $checkingAttribute;
        }
        $result = $this->userService->updateUser($id, $attributes);

        if (!$result) {
            return \response()->json([
                'status' => 'error',
                'message' => 'bad request'
            ], 400);
        }

        return \response()->json([
            'status' => 'success',
            'message' => 'updated user'
        ], 200);
    }

    /**
     * adicionar avatar a um usuário específico
     */
    public function updateAvatar(Request $request)
    {
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];

        $image = $request->file('avatar');

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

        $destinationPath = \storage_path('images/avatar');
        $newFileName = \md5(\date('Y-m-d') . '_' . \rand(0, 9999)) . '.jpg';
        $newPath = $destinationPath . \DIRECTORY_SEPARATOR . $newFileName;

        $imageManager = new ImageManager(new Driver());

        $imageManager->read($image->path())->cover(200, 200)->save($newPath);

        $user = User::find($this->loggedUser['id']);
        $user->avatar = $newPath;
        $user->save();

        return \response()->json([
            'status' => 'success',
            'avatar_url' => \url($newPath),
        ], 200);
    }

    public function updateCover(Request $request)
    {
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];

        $image = $request->file('cover');

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

        $destinationPath = \storage_path('images/cover');
        $newFileName = \md5(\date('Y-m-d') . '_' . \rand(0, 9999)) . '.jpg';
        $newPath = $destinationPath . \DIRECTORY_SEPARATOR . $newFileName;

        $imageManager = new ImageManager(new Driver());

        $imageManager->read($image->path())->cover(800, 800)->save($newPath);

        $user = User::find($this->loggedUser['id']);
        $user->cover = $newPath;
        $user->save();

        return \response()->json([
            'status' => 'success',
            'cover_url' => \url($newPath),
        ], 200);
    }
}
