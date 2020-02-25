<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $table = "d_todolist_attachment";
    protected $primaryKey = "tla_id";

    public $timestamps = true;

    const CREATED_AT = 'tla_created';
    const UPDATED_AT = 'tla_updated';
}
