<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UserRequest;
use App\Models\User\User;
use App\Repositories\User\UserRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(): JsonResponse
    {
        $first = request('first', false);
        $rows = request('rows', false);
        $orderBy = request('sortField', 'id');
        $ascending = request('sortOrder', 1);
        $filters = json_decode(request('filters', '{}'), true);
        $columns = request()->has('columns') ? json_decode(request('columns')) : array_keys($filters);

        return response()->json(
            $this->repository->index($first, $rows, $orderBy, $ascending, $filters, $columns)
        );
    }

    public function show(User $user): JsonResponse
    {
        $columns = request()->has('columns') ? json_decode(request('columns')) : ['id'];
        $user->loadAliasScopes($columns);

        return response()->json($user->only($columns));
    }

    public function store(UserRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = $this->repository->createUser($request->validated());
            DB::commit();

            return response()->json(
                $user,
                Response::HTTP_CREATED
            );
        } catch (Exception $ex) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong',
            ], 422);
        }
    }

    public function update(UserRequest $request, User $user): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = $this->repository->updateUser($request->validated(), $user);
            DB::commit();

            return response()->json(
                [
                    'user' => $user,
                ],
                200
            );
        } catch (Exception $ex) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong',
            ], 422);
        }
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(
            ['message' => 'Object deleted'],
            204
        );
    }
}
