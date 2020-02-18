<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = "m_category";
    protected $primaryKey = "c_id";

    public $timestamps = true;

    const CREATED_AT = 'c_created';
    const UPDATED_AT = 'c_updated';

    protected $fillable = ['c_name','c_created','c_updated'];
}
