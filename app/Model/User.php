<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @param $valid // method or action
     * @param null $id
     * @return array|null
     */
    public static function rules($valid, $id = null)
    {
        $valid = strtoupper($valid);
        if (!in_array($valid, ['PUT', 'POST', 'CREATE', 'UPDATE', 'DELETE'])) {
            return null;
        }

        $rules = [
            'name' => '{SOMETIMES}required|string|max:255',
            'email' => '{SOMETIMES}required|email:rfc,dns|max:255|unique:users{UNIQUE}',
        ];

        switch ($valid) {
            case 'PUT':
            case 'UPDATE':
                // replace {SOMETIMES} & {UNIQUE}
                $rules['name'] = str_replace('{SOMETIMES}', 'sometimes|', $rules['name']);
                $rules['email'] = str_replace('{SOMETIMES}', 'sometimes|', $rules['email']);
                $rules['email'] = str_replace('{UNIQUE}', ",email,$id", $rules['email']);
                break;
            case 'POST':
            case 'CREATE':
                // remove {SOMETIMES} & {UNIQUE}
                $rules['name'] = str_replace('{SOMETIMES}', '', $rules['name']);
                $rules['email'] = str_replace('{SOMETIMES}', '', $rules['email']);
                $rules['email'] = str_replace('{UNIQUE}', '', $rules['email']);
                break;
            case 'DELETE':
                // remove name validation
                unset($rules['name']);
                // rewrite email validation
                $rules['email'] = 'required|email:rfc,dns|max:255';
                break;
            default:
                $rules = null;
        }

        return $rules;
    }
}
