<?php

namespace App\Repositories\User;

use App\Models\User\User;
use App\Repositories\IndexRepository;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends IndexRepository
{
    public Model $model;

    public function __construct(User $duser)
    {
        $this->model = $duser;
    }

    public function createUser(array $data): User
    {
        return User::create($data);
    }

    public function updateUser(array $data, User $duser): User
    {
        $duser->update($data);

        return $duser;
    }
}
