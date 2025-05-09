<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'image',
        'visibility',
        'slug',
    ];

    protected function visibility(): Attribute 
    {
        return Attribute::make(
            get: fn ($value) => $value,
        );
    }


    public function user(){
        return $this->belongsTo(User::class);
    }

    public function tags() {
        return $this->belongsToMany(Tag::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class)->whereNull('parent_id')->orderBy('created_at', 'desc');
    }

    public function allComments() {
        return $this->hasMany(Comment::class);
    }

    public function likes() {
        return $this->hasMany(Like::class);
    }

    public function bookmarks() {
        return $this->hasMany(Bookmark::class);
    }

   //scope for public posts
    public function scopePublic($query) {
        return $query->where('visibility', 'published', true);
    }

   //check if liked by a specific user
    public function likedByUser($userId) {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    // Check if bookmarked by a specific user
    public function bookmarkedByUser($userId) {
        return $this->bookmarks()->where('user_id', $userId)->exists();
    }
}
