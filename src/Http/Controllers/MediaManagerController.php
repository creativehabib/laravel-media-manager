<?php
namespace Habib\MediaManager\Http\Controllers;

use Habib\MediaManager\Models\MediaFile;
use Habib\MediaManager\Models\MediaFolder;
use Habib\MediaManager\Models\MediaTag;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class MediaManagerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAccess();

        $filters = $request->only(['q','mime','visibility','from','to','folder_id','tag']);

        $files = MediaFile::with('tags')
            ->filter($filters)
            ->latest()
            ->paginate(config('mediamanager.per_page'));

        $folders = MediaFolder::with('children')->whereNull('parent_id')->get();
        $tags    = MediaTag::orderBy('name')->get();

        return view('mediamanager::index', compact('files', 'folders', 'tags', 'filters'));
    }

    public function upload(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'files.*' => 'required|file|max:20480'
        ]);

        $disk = $request->input('disk', config('mediamanager.default_disk'));

        foreach ($request->file('files', []) as $file) {
            $path = $file->store('media/'.now()->format('Y/m/d'), $disk);

            $media = MediaFile::create([
                'name'       => $file->getClientOriginalName(),
                'alt'        => $request->input('alt'),
                'folder_id'  => $request->input('folder_id'),
                'disk'       => $disk,
                'path'       => $path,
                'mime_type'  => $file->getMimeType(),
                'size'       => $file->getSize(),
                'visibility' => $request->input('visibility', 'public'),
                'random_hash'=> md5($file->getClientOriginalName().microtime()),
            ]);

            if ($tags = $request->input('tags')) {
                $tagIds = collect(explode(',', $tags))
                    ->map(fn($t) => trim($t))
                    ->filter()
                    ->map(fn($t) => MediaTag::firstOrCreate(['name' => $t])->id);
                $media->tags()->sync($tagIds);
            }
        }

        return back()->with('success', 'Files uploaded successfully');
    }

    public function destroy(MediaFile $media)
    {
        $this->authorizeAccess();

        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return back()->with('success', 'File deleted');
    }

    protected function authorizeAccess()
    {
        $permission = config('mediamanager.permission');

        if ($permission && Gate::denies($permission)) {
            abort(403);
        }
    }
}
