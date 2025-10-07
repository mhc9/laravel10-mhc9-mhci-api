<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'user_permissions';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo(UserRole::class, 'role_id', 'id');
    }
}
