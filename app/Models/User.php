<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'profile_picture',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            $user->generateDefaultProfilePicture();
        });
    }

    /**
     * Generate a default profile picture using initials.
     */
    public function generateDefaultProfilePicture()
    {
        try {
            // Create the profile_pictures directory if it doesn't exist
            if (!Storage::disk('public')->exists('profile_pictures')) {
                Storage::disk('public')->makeDirectory('profile_pictures');
            }

            // Get the first letter of the username for initials
            $initials = strtoupper(substr($this->username, 0, 1));
            $filename = "{$this->username}_{$this->id}.svg";
            $path = "profile_pictures/{$filename}";
            
            // Create a simple SVG image with initials
            $svg = '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
                <rect width="200" height="200" fill="#'.substr(md5($this->username), 0, 6).'"/>
                <text x="100" y="100" font-size="80" fill="white" text-anchor="middle" dominant-baseline="middle">'.$initials.'</text>
            </svg>';
            
            // Save the SVG to storage
            Storage::disk('public')->put($path, $svg);
            
            // Update the user's profile picture path
            $this->profile_picture = $path;
            $this->save();

        } catch (\Exception $e) {
            \Log::error('Failed to generate profile picture: ' . $e->getMessage());
        }
    }

    /**
     * Get the URL for the user's profile picture.
     */
    public function getProfilePictureUrlAttribute()
    {
        if (!$this->profile_picture) {
            return null;
        }
        
        return Storage::disk('public')->url($this->profile_picture);
    }
}
