<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Repo extends Model
{


    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'repos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'fullName', 'description', 'stars', 'forks', 'repoId', 'language', 'weeklyCommits', 'totalWeeklyCommits', 'userId'
    ];
}
