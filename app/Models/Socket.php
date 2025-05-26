<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Socket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'socket_id',
        'location',
        'address',
    ];

    /**
     * Get the user that owns the socket.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the laad sessies for this socket.
     */
    public function laadSessies()
    {
        return $this->hasMany(LaadSessie::class, 'socket_id', 'socket_id');
    }
} 