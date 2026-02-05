<?php
namespace Habib\MediaManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MediaFolder extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'user_id', 'parent_id', 'color'];

    protected static function booted(): void
    {
        static::addGlobalScope('media_folder_owner', function ($query) {
            if (app()->runningInConsole()) {
                return;
            }

            $userId = Auth::id();

            if (! $userId) {
                $query->whereRaw('1 = 0');
                return;
            }

            $query->where('user_id', $userId);
        });

        static::creating(function (self $mediaFolder) {
            if (! $mediaFolder->user_id && Auth::id()) {
                $mediaFolder->user_id = Auth::id();
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(MediaFile::class, 'folder_id');
    }
}
