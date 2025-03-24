<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePictureRequest;
use App\Http\Requests\User\GetAllUserRequest;
use App\Http\Requests\User\LoginUserRequest;
use App\Http\Requests\User\PasswordForgotLinkRequest;
use App\Http\Requests\User\PasswordResetAuthenticatedRequest;
use App\Http\Requests\User\PasswordResetUnauthenticatedRequest;
use App\Http\Requests\User\RegisterUserGuestRequest;
use App\Http\Requests\User\RegisterUserRequest;
use App\Http\Requests\User\UpdateEmailUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function addProfilePicture(StorePictureRequest $request): JsonResponse
    {
        try {
            $request->validated();
            $userId = auth()->user()->id;

            if ($request->hasFile('picture')) {
                $file = $request->file('picture');

                $filePath = "public/profile_pictures/{$userId}";

                $files = Storage::allFiles($filePath);
                Storage::delete($files);

                $absoluteFilePath = $file->storeAs($filePath, strtolower(\Str::random(3)) . '-image.' . $file->getClientOriginalExtension());

                $symbolicLink = Storage::url($absoluteFilePath);
            } else {
                $symbolicLink = null;
            }

            $serviceResponse = $this->userService->handleProfilePicture($symbolicLink, $userId);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $serviceResponse['message'],
                'data' => $serviceResponse['model'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function logged()
    {
        try {
            $authUser = auth()->guard()->user();
            return response()->json([
                'message' => 'Usuário autenticado.',
                'user' => $authUser,
                'status' => 200,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(LoginUserRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        try {
            if ($this->userService->login($credentials)) {
                $authUser = auth()->guard()->user();
                return response()->json([
                    'message' => "Bem vindo ao sistema RCE",
                    'auth' => $authUser,
                    'status' => 200,
                    'redirect' => url("{$authUser->level}")
                ], 200);
            } else {
                return response()->json([
                    'message' => "Usuário inválido!",
                    'status' => 403
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'message' => 'Saindo do sistema',
                'status' => 200,
                'redirect' => url('auth')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function registerGuest(RegisterUserGuestRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $serviceResponse = $this->userService->store($data);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $serviceResponse['message'],
                'data' => $serviceResponse['model'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function passwordForgot(PasswordForgotLinkRequest $request)
    {
        try {
            $data = $request->validated();
            $status = Password::sendResetLink($data);

            if ($status !== Password::RESET_LINK_SENT) {
                \Log::error('Guest User, from ip addresses {ip_device} | forgot password, send link to mail {mail} error',
                    [
                        'ip_device' => request()->ip(),
                        'mail' => $data['email'],
                        'status' => $status
                    ]
                );
                return response()->json([
                    'status' => 400,
                    'message' => 'Não foi possível realizar o envio do e-mail. Tente novamente mais tarde',
                ], 400);
            }
            \Log::info('Guest User, from ip addresses {ip_device} | forgot password, send link to mail {mail} success',
                [
                    'ip_device' => request()->ip(),
                    'mail' => $data['email'],
                ]
            );
            return response()->json([
                'status' => 200,
                'message' => 'Foi enviado um e-mail com link de recuperação.',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Guest User, from ip addresses {ip_device} | forgot password, send link to mail {mail} error',
                [
                    'ip_device' => request()->ip(),
                    'message' => $e->getMessage()
                ]
            );

            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function passwordReset(PasswordResetUnauthenticatedRequest $request)
    {
        try {
            $data = $request->validated();

            $status = Password::reset(
                $data, function ($user, $password) {

                $this->userService->updatePassword(Hash::make($password), $user->id);
            });

            if ($status !== Password::PASSWORD_RESET) {
                \Log::error('Guest User, from ip addresses {ip_device} | reset password user, mail {mail} error',
                    [
                        'ip_device' => request()->ip(),
                        'mail' => $data['email'],
                        'status' => $status
                    ]
                );
                return response()->json([
                    'status' => 400,
                    'message' => 'Não foi possível atualizar a senha.',
                ], 400);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Senha atualizada com sucesso, realize o login.',
                'redirect' => url('/auth')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function passwordResetAuthenticated(PasswordResetAuthenticatedRequest $request)
    {
        try {
            $data = $request->validated();
            $serviceResponse = $this->userService->passwordUpdateAuthenticated($data);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $serviceResponse['message'],
                'redirect' => url('auth')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function updateEmail(UpdateEmailUserRequest $request)
    {
        try {
            $data = $request->validated();
            $serviceResponse = $this->userService->updateEmail($data);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $serviceResponse['message'],
                'redirect' => url('auth')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function register(RegisterUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $serviceResponse = $this->userService->store($data);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $serviceResponse['message'],
                'data' => $serviceResponse['model'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getAll(GetAllUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $serviceResponse = $this->userService->getAllWithValidation($data);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $serviceResponse['message'],
                'data' => $serviceResponse['model'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getById($id): JsonResponse
    {
        try {
            $serviceResponse = $this->userService->getById($id);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $serviceResponse['message'],
                'data' => $serviceResponse['model'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();

            $serviceResponse = $this->userService->updateById($data, $id);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $serviceResponse['message'],
                'data' => $serviceResponse['model'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function delete(string $id): JsonResponse
    {
        try {
            $serviceResponse = $this->userService->delete($id);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $serviceResponse['message'],
                'data' => $serviceResponse['model'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
