<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = ['name', 'organization_id', 'start_date', 'finish_date', 'description'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function channels()
    {
        return $this->hasMany(Channel::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function statuses()
    {
        return $this->hasMany(Status::class);
    }
}
