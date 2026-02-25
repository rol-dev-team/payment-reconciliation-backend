<?php

    namespace App\Models;

    use App\Enums\UserRole;
    use Illuminate\Database\Eloquent\Model;

    class Role extends Model
    {
        protected $fillable = ['name'];

        protected $casts = [
            'name' => UserRole::class, // Automatically converts string to Enum
        ];

        public function users()
        {
            return $this->hasMany(User::class);
        }
    }