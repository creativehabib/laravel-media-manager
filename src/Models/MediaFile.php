<?php
namespace Habib\MediaManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'folder_id',
        'disk',
        'path',
        'mime_type',
        'width',
        'height',
        'size',
        'visibility',
        'alt',
        'is_favorite',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
    ];

    protected $appends = ['url'];

    public function folder()
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function tags()
    {
        return $this->belongsToMany(MediaTag::class, 'media_file_tag');
    }

    public function getUrlAttribute()
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    // 🔍 Search + Filter Scope
    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['q'] ?? null, fn($q, $search) =>
            $q->where(function ($q2) use ($search) {
                $q2->where('name', 'like', "%$search%")
                    ->orWhere('alt', 'like', "%$search%");
            })
            )
            ->when($filters['mime'] ?? null, fn($q, $mime) =>
            $q->where('mime_type', 'like', "$mime%")
            )
            ->when($filters['visibility'] ?? null, fn($q, $visibility) =>
            $q->where('visibility', $visibility)
            )
            ->when($filters['from'] ?? null, fn($q, $from) =>
            $q->whereDate('created_at', '>=', $from)
            )
            ->when($filters['to'] ?? null, fn($q, $to) =>
            $q->whereDate('created_at', '<=', $to)
            )
            ->when($filters['folder_id'] ?? null, fn($q, $folderId) =>
            $q->where('folder_id', $folderId)
            )
            ->when($filters['tag'] ?? null, fn($q, $tag) =>
            $q->whereHas('tags', fn($tq) => $tq->where('name', $tag))
            );
    }
}
