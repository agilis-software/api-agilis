<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';

    protected $fillable = ['name', 'description', 'avatar', 'owner_id'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_user');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
