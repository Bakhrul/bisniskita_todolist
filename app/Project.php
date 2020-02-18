<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'd_project';
    public $timestamps =false;
    protected $primaryKey = 'p_id';
    
}
