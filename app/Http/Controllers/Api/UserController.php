<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserImportRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\User as UserResource;
use App\Imports\UsersImport;
use App\Model\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $users = User::query();

        if ($query_name = empty($query['name']) ? '' : $query['name']) {
            $users = $users->where('name', 'like', "%$query_name%");
        }
        if ($query_email = empty($query['email']) ? '' : $query['email']) {
            $users = $users->where('email', 'like', "%$query_email%");
        }

        return UserResource::collection($users->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserRequest $request
     * @return JsonResponse
     */
    public function store(UserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt(env('DEFAULT_PASSWORD')),
        ]);

        if ($user) {
            return response()->json([
                'message' => 'Create User Success.'.' default password is \''.env('DEFAULT_PASSWORD').'\'',
                'data' => new UserResource($user)
            ], 201);
        }

        return response()->json([
            'message'=>'Create User Fail.',
        ], 422);
    }

    /**
     * Display the specified resource.
     *
     * @param $user
     * @return UserResource|JsonResponse
     */
    public function show($user)
    {
        if ($data = User::find($user)) {
            return new UserResource($data);
        }

        return response()->json([
            'message'=>'Get User Not Found.',
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserRequest $request
     * @param $user
     * @return JsonResponse
     */
    public function update(UserRequest $request, $user)
    {
        $user = User::find($user);

        if (empty($user)) {
            return response()->json([
                'message'=>'User Not Found.',
            ], 404);
        }

        $validated = $request->validated();
        $user->fill($validated);
        $result = $user->save();

        if ($result) {
            return response()->json([
                'message' => 'Update User Success.',
                'data' => new UserResource($user)
            ], 200);
        }

        return response()->json([
            'message'=>'Update User Fail.',
        ], 422);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $user
     * @return JsonResponse
     */
    public function destroy($user)
    {
        $user = User::find($user);

        if (empty($user)) {
            return response()->json([
                'message'=>'User Not Found.',
            ], 404);
        }

        $result = $user->delete();

        if ($result) {
            return response()->json([
                'message' => 'Delete User Success.',
            ], 200);
        }

        return response()->json([
            'message'=>'Delete User Fail.',
        ], 422);
    }

    /**
     * import excel/csv files and create/update/delete User according to action
     *
     * @param UserImportRequest $request
     * @return JsonResponse
     */
    public function import(UserImportRequest $request)
    {
        $validated = $request->validated();

        Excel::import(new UsersImport, $validated['file']);

        return response()->json([
            'message' => 'Import \''.$validated['file']->getClientOriginalName().'\' Success.',
        ], 200);
    }
}
