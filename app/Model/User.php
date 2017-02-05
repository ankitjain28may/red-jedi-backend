<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'login', 'userId', 'weeklyCommits', 'totalCommits'
    ];
}
