<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'd_project';
    public $timestamps =false;
    protected $primaryKey = 'p_id';

    protected $fillable = ['*'];

     function todo()
    {
        return $this->hasMany('App\Todo','tl_project','p_id');
    }

    function role()
    {
        return $this->hasMany('App\projectMember','mp_project','p_id');
    }

}
