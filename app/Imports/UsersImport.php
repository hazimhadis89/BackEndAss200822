<?php

namespace App\Imports;

use App\Model\User;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    use Importable;

    public function model(array $row)
    {
        $action = strtoupper($row['action']);

        if (!isset($action)) {
            return null;
        }

        $user = User::where('email', $row['email'])->first();

        if ($rules = User::rules($action, optional($user)->id)) {
            $validator = Validator::make($row, $rules);
            $validator->validate();

            $validated = $validator->validated();
            switch ($action) {
                case 'CREATE':
                    $validated['password'] = bcrypt(env('DEFAULT_PASSWORD'));
                    User::create($validated);
                    break;
                case 'UPDATE':
                    $user->update($validated);
                    break;
                case 'DELETE':
                    $user->delete();
                    break;
                default:
                    return null;
            }
        } else {
            return null;
        }
    }
}
