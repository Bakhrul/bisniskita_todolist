<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    protected $table = 'd_todolist';
    protected $primaryKey = 'tl_id';
    public $timestamps = false;

    function project()
    {
        return $this->belongsTo('App\Project','tl_project','p_id');
    }

}
