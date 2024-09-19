<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';

    protected $fillable = ['name', 'description', 'avatar'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_user');
    }
}
