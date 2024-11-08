<?php

namespace App\Models\Mentor;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'about_me',
        'experience',
        'education',
    ];



    protected function casts(): array
    {
        return [
        'experience' => 'array',
        'education' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
