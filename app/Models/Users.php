<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Users extends Authenticatable
{
    use Notifiable;

    protected $table = 'tbl_users';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'name',
        'email',
        'no_telp',
        'alamat',
        'sandi',
        'role',
    ];

    protected $hidden = [
        'sandi',
    ];

    // kasih tahu Laravel kalau password field = sandi
    public function getAuthPassword()
    {
        return $this->sandi;
    }
}
