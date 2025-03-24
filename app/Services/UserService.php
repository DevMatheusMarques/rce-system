<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Traits\LogTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserService
{
    use LogTrait;
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getById(string $id): array
    {
        try {
            $userData = $this->userRepository->getById($id);
            return [
                'success' => true,
                'message' => 'Usuário encontrado.',
                'model' => $userData
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    private function calculateRegistration(): string
    {
        $lastUSer = $this->userRepository->lastUser();
        if (!$lastUSer) {
            $registration = 1;
        } else {
            $registration = intval($lastUSer->registration) + 1;
        }
        return str_pad($registration, 5, '0', STR_PAD_LEFT);
    }

    public function store(array $data): array
    {
        try {
            $data['password'] = bcrypt($data['password'] ?? 'password');
            if (!Auth::check()) {
                $data['first_access'] = false;
                $data['status'] = 'inactive';
            }

            if (isset($data['registration'])) {
                $data['registration'] = str_pad($data['registration'], 5, '0', STR_PAD_LEFT);
                if ($this->userRepository->existsByAttribute('registration', $data['registration'])) {
                    throw new Exception("Já existe uma matrícula para {$data['registration']}");
                }
            } else {
                $data['registration'] = $this->calculateRegistration();

            }

            $successData = $this->userRepository->store($data);

            $this->logInfo('User {id} | create user {userId} success',
                [
                    'id' => auth()->user()->id ?? 'User Guest from ip: ' . request()->ip(),
                    'userId' => $successData['id'],
                ]
            );

            return [
                'success' => true,
                'message' => 'Usuário registrado com sucesso.',
                'model' => $successData
            ];

        } catch (Exception $e) {
            $this->logError('User {id} | create user error | {messageError}',
                [
                    'id' => auth()->user()->id,
                    'messageError' => $e->getMessage(),
                ]
            );
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    public function updateById(array $data, int $id): array
    {
        try {
            $userData = $this->userRepository->getById($id);

            $authUserData = auth()->guard()->user();
            if ($authUserData->level == 'operator') {
                if ($authUserData->id !== $id) {
                    throw new Exception('Você só pode editar a si mesmo.');
                }
            }

            if ($authUserData['level'] == 'manager' && $authUserData['id'] !== $id) {
                if ($userData['level'] == 'admin' || $userData['level'] == 'manager') {
                    throw new Exception('Um usuário \'master\' só pode editar a si mesmo ou um nível a baixo (operador). ');
                }
            }

            if ($authUserData->id === $userData->id) {
                foreach (['status', 'level'] as $key) {
                    unset($data[$key]);
                }
            }

            if (isset($data['registration'])) {
                $data['registration'] = str_pad($data['registration'], 5, '0', STR_PAD_LEFT);

                if ($this->userRepository->existsByAttribute('registration', $data['registration'])) {
                    throw new Exception("Já existe uma matrícula para {$data['registration']}");
                }
            }

            $result = $this->userRepository->updateById($data, $id);

            if (!$result) {
                throw new Exception('Falha na atualização.');
            }
            $this->logInfo('User {id} | update user {userId} success',
                [
                    'id' => auth()->user()->id,
                    'userId' => $id,
                ]
            );

            return [
                'success' => true,
                'message' => 'Usuário alterado com sucesso',
                'model' => $this->userRepository->getById($id)
            ];

        } catch (Exception $e) {
            $this->logError('User {id} | update user {userId} error | {messageError}',
                [
                    'id' => auth()->user()->id,
                    'messageError' => $e->getMessage(),
                    'userId' => $id,
                ]
            );
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    public function handleProfilePicture(?string $profilePicturePath, string $id): array
    {
        try {
            $result = $this->userRepository->updateById(['profile_picture_path' => $profilePicturePath], $id);

            if (!$result) {
                throw new Exception('Falha na atualização.');
            }
            $this->logInfo('User {id} | update profile picture user {userId} success',
                [
                    'id' => auth()->user()->id,
                    'userId' => $id,
                ]
            );

            return [
                'success' => true,
                'message' => 'Imagem de perfil atualizada',
                'model' => $this->userRepository->getById($id)
            ];

        } catch (Exception $e) {
            $this->logError('User {id} | update profile picture user {userId} error | {messageError}',
                [
                    'id' => auth()->user()->id,
                    'messageError' => $e->getMessage(),
                    'userId' => $id,
                ]
            );
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    public function updatePassword(string $password, string $id): array
    {
        try {
            $this->userRepository->getById($id);

            $result = $this->userRepository->updateById(['password' => $password], $id);

            if (!$result) {
                throw new Exception('Houve uma falha ao atualizar a senha.');
            }
            $this->logInfo('Guest User, from ip addresses {ip_device} | update password user {userId} success',
                [
                    'ip_device' => request()->ip(),
                    'userId' => $id,
                ]
            );

            return [
                'success' => true,
                'message' => 'Senha atualizada com sucesso.',
                'model' => $this->userRepository->getById($id)
            ];

        } catch (Exception $e) {
            $this->logError('Guest User, from ip addresses {ip_device} | update password user {userId} error',
                [
                    'ip_device' => request()->ip(),
                    'messageError' => $e->getMessage(),
                    'userId' => $id,
                ]
            );
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    public function delete(string $id): array
    {
        try {
            $userData = $this->userRepository->getById($id);
            $authUserData = auth()->guard()->user();

            if ($authUserData['id'] == $id) {
                throw new Exception('Você não pode excluir o seu próprio usuário.');
            }
            if ($authUserData['level'] == 'manager' && $userData['level'] == 'admin') {
                throw new Exception('Um usuário \'master\' não pode excluir um \'admin\'');
            }

            $result = $this->userRepository->delete($id);

            if (!$result) {
                throw new Exception('Falha ao excluir produto.');
            }

            $this->logInfo('User {id} | delete user {user} success',
                [
                    'id' => auth()->user()->id,
                    'user' => $userData,
                ]
            );

            return [
                'success' => true,
                'message' => 'Usuário excluido com sucesso.',
                'model' => null
            ];

        } catch (Exception $e) {
            $this->logError('User {id} | delete user {userId} error | {messageError}',
                [
                    'id' => auth()->user()->id,
                    'userId' => $id,
                    'messageError' => $e->getMessage(),
                ]
            );
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    public function getAllWithValidation(array $data): array
    {
        try {
            $userData = $this->userRepository->getAllWithValidation($data);
            if ($userData->total() > 0) {
                return [
                    'success' => true,
                    'message' => 'Registros encontrados',
                    'model' => $userData
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Nenhum registro encontrado',
                    'model' => []
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    public function login(array $credentials): bool
    {
        $credentials['status'] = 'active';
        if (Auth::guard()->attempt($credentials)) {

            $this->logInfo('User {id} | logged on app success using device ip: {ip}',
                [
                    'id' => auth()->user()->id,
                    'ip' => request()->ip(),
                ]
            );

            return true;
        }
        $this->logError('Failed access attempt by device ip: {ip} | email {email}',
            [
                'ip' => request()->ip(),
                'email' => $credentials['email']
            ]
        );
        return false;
    }

    public function passwordUpdateAuthenticated(array $data): array
    {
        $user = auth()->user();
        try {

            $credentials = [
                'email' => $user->email,
                'password' => $data['current_password'],
            ];
            if (!auth()->guard()->validate($credentials)) {
                throw new Exception('Senha atual incorreta.');
            }
            $updateData = ['password' => Hash::make($data['password'])];

            if ($user->first_access) {
                $updateData['first_access'] = false;
            }

            $result = $this->userRepository->updateById($updateData, $user->id);
            if (!$result) {
                throw new Exception('Houve um erro ao atualizar a senha.');
            }

            Auth::guard('web')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            $this->logInfo('User {id} | update your password success',
                [
                    'id' => $user->id,
                ]
            );
            return [
                'success' => true,
                'message' => 'Senha atualizada com sucesso, você será desconectado',
            ];
        } catch (Exception $e) {
            $this->logError('User {id} | update your password failed | {messageError}',
                [
                    'id' => $user->id,
                    'messageError' => $e->getMessage(),
                ]
            );
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    public function updateEmail(array $data): array
    {
        try {

            $credentials = [
                'email' => auth()->user()->email,
                'password' => $data['current_password'],
            ];
            if (!auth()->guard()->validate($credentials)) {
                throw new Exception('Senha atual incorreta.');
            }

            $result = $this->userRepository->updateById(
                ['email' => $data['email']],
                auth()->guard()->user()->id
            );

            if (!$result) {
                throw new Exception('Houve um erro ao atualizar o email.');
            }

            $this->logInfo('User {id} | update your email success',
                [
                    'id' => auth()->user()->id,
                ]
            );

            Auth::guard('web')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return [
                'success' => true,
                'message' => 'Email atualizado com sucesso, você será desconectado',
            ];
        } catch (Exception $e) {
            $this->logError('User {id} | update your email failed | {messageError}',
                [
                    'id' => auth()->user()->id,
                    'messageError' => $e->getMessage(),
                ]
            );
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }
}
