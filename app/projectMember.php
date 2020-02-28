<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class projectMember extends Model
{
    protected $table = 'd_project_member';
    public $timestamps= false;

    public function project()
    {
        return $this->belongsTo('App\projectMember', 'mp_project', 'p_id');

    }

    public function user()
    {
        return $this->belongsTo('App\User', 'mp_user', 'us_id');
    }

}
