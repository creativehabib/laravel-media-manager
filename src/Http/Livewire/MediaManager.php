<?php

namespace Habib\MediaManager\Http\Livewire;

use Habib\MediaManager\Models\MediaFile;
use Habib\MediaManager\Models\MediaFolder;
use Habib\MediaManager\Models\MediaTag;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image as ImageManager;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class MediaManager extends Component
{
    use WithFileUploads, WithPagination;

    protected $listeners = [
        'media-manager-opened' => 'onOpened',
        'media-manager-insert' => 'onInsert',
        'media-insert' => 'handleMediaInsert',
    ];
    public $showMoveToTrashModal = false;
    public $skipTrash = false;

    public $showEmptyTrashModal = false;
    public $showDeletePermanentModal = false;
    public $pendingDeleteId = null;
    protected $paginationTheme = 'tailwind';

    public $perPage;

    // LOCAL uploads (Livewire temp files)
    public $uploads = [];

    // Filters / state
    public $q = '';
    public $mime = '';
    public $visibility = '';
    public $from;
    public $to;
    public ?int $folder_id = null;
    public $tag;
    public $viewMode = 'grid';
    public $sort = 'name-asc';

    public $selectedDisk;
    public $tagsInput;

    // Selected file for preview + actions
    public $selectedId;
    public ?MediaFile $selected = null; // ✅ সাইড প্যানেলের জন্য প্রপার্টি যোগ করা হয়েছে

    // URL upload modal
    public $showUrlModal = false;
    public $urlInput;

    // All media / Trash / Recent / Favorites
    public string $scope = 'all'; // all | trash | recent | favorites

    // ALT text modal
    public $showAltModal = false;
    public $altTextInput = '';

    // Right-click context menu state
    public $contextMenu = [
        'show'   => false,
        'x'      => 0,
        'y'      => 0,
        'fileId' => null,
    ];

    public $showFolderModal = false;
    public $newFolderName   = '';
    protected $queryString = [
        'q',
        'mime',
        'visibility',
        'from',
        'to',
        'folder_id',
        'tag',
        'viewMode',
        'sort',
        'scope',
    ];

    // Image crop
    public bool $showCropModal = false;
    public ?int $cropFileId = null;
    public bool $showPreview = false;
    public ?MediaFile $previewFile = null;

    public bool $cropOptimize = true;
    public ?int $cropMaxWidth = null;
    public ?int $cropMaxHeight = null;
    public int $cropQuality = 80;
    public string $cropFormat = 'keep';

    public function mount()
    {
        $this->selectedDisk = config('mediamanager.default_disk', 'public');

        $this->resetPerPage();

        if (! in_array($this->scope, ['all', 'trash', 'recent', 'favorites'])) {
            $this->scope = 'all';
        }
    }

    /* ========= LOCAL upload (auto) ========= */

    public function updatedUploads()
    {
        if (empty($this->uploads)) {
            return;
        }

        $this->validate([
            'uploads.*' => 'required|file|max:20480',
        ]);

        foreach ($this->uploads as $file) {
            $path = $file->store(
                'media/' . now()->format('Y/m/d'),
                $this->selectedDisk
            );

            $mime = $file->getMimeType();
            $size = $file->getSize();
            $width = null;
            $height = null;

            // if image then calculate dimension
            if(Str::startsWith($mime, 'image/')) {
                $image = ImageManager::read($file->getRealPath());
                $width = $image->width();
                $height = $image->height();
            }

            $media = MediaFile::create([
                'name'       => $file->getClientOriginalName(),
                'folder_id'  => $this->folder_id,
                'disk'       => $this->selectedDisk,
                'path'       => $path,
                'mime_type'  => $file->getMimeType(),
                'size'       => $file->getSize(),
                'visibility' => $this->visibility ?: 'public',
                'width'      => $width,
                'height'     => $height,
            ]);

            if ($this->tagsInput) {
                $tagIds = collect(explode(',', $this->tagsInput))
                    ->map(fn ($t) => trim($t))
                    ->filter()
                    ->map(fn ($t) => MediaTag::firstOrCreate(['name' => $t])->id);

                $media->tags()->sync($tagIds);
            }

            // প্রিভিউতে দেখানোর জন্য
            $this->selectedId = $media->id;
        }

        $this->reset('uploads');
        $this->resetPage();
        $this->toast('Upload successfully!', 'success');
    }

    /* ========= Upload from URL ========= */

    public function openUrlModal()
    {
        $this->resetErrorBag('urlInput');
        $this->urlInput     = '';
        $this->showUrlModal = true;
    }

    public function closeUrlModal()
    {
        $this->showUrlModal = false;
    }

    public function uploadFromUrl()
    {
        $this->validate([
            'urlInput' => 'required|url',
        ], [
            'urlInput.required' => 'Please enter an URL.',
            'urlInput.url'      => 'Invalid URL.',
        ]);

        $url = $this->urlInput;

        try {
            $contents = @file_get_contents($url);

            if ($contents === false) {
                $this->addError('urlInput', 'Unable to download file from URL.');
                return;
            }

            // ফাইল নাম বের করি
            $parsed = parse_url($url);
            $path   = $parsed['path'] ?? 'file';
            $name   = basename($path) ?: 'file-' . time();

            $storePath = 'media/' . now()->format('Y/m/d') . '/' . uniqid() . '-' . $name;

            Storage::disk($this->selectedDisk)->put($storePath, $contents);

            $size  = strlen($contents);
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->buffer($contents) ?: 'application/octet-stream';
            $width = null;
            $height = null;


            if(Str::startsWith($mime, 'image/')) {
                $fullPath = Storage::disk($this->selectedDisk)->path($storePath);
                $image = ImageManager::read($fullPath);
                $width = $image->width();
                $height = $image->height();
            }

            $media = MediaFile::create([
                'name'       => $name,
                'folder_id'  => $this->folder_id,
                'disk'       => $this->selectedDisk,
                'path'       => $storePath,
                'mime_type'  => $mime,
                'size'       => $size,
                'visibility' => $this->visibility ?: 'public',
                'width'      => $width,
                'height'     => $height,
            ]);

            if ($this->tagsInput) {
                $tagIds = collect(explode(',', $this->tagsInput))
                    ->map(fn ($t) => trim($t))
                    ->filter()
                    ->map(fn ($t) => MediaTag::firstOrCreate(['name' => $t])->id);

                $media->tags()->sync($tagIds);
            }

            $this->selectedId   = $media->id;
            $this->showUrlModal = false;
            $this->urlInput     = null;
            $this->resetPage();
            $this->toast('Upload from url successfully!', 'success');
        } catch (\Throwable $e) {
            $this->addError('urlInput', 'Error while downloading: ' . $e->getMessage());
        }
    }

    public function loadMore()
    {
        $this->perPage += config('mediamanager.default_per_page', 24);
    }
    public function onOpened()
    {
        // দরকার হলে reset selection ইত্যাদি
    }

    public function onInsert($payload)
    {
        if (! $this->selectedId) return;

        $file = MediaFile::find($this->selectedId);
        if (! $file) return;

        $this->dispatch('media-manager-selected', [
            'fieldId' => $payload['fieldId'] ?? null,
            'url'     => $file->url,
        ]);
    }

    public function insertSelected()
    {
        if (!$this->selectedId) {
            return;
        }

        $file = MediaFile::withTrashed()->find($this->selectedId);
        if (! $file) {
            return;
        }

        // যেই ফাইল সিলেক্ট হয়েছে – তার তথ্য পাঠাচ্ছি
        $this->dispatch(
            'media-selected',
            id: $file->id,
            url: $file->url,
            name: $file->name,
            mime: $file->mime_type,
        );
    }
    #[On('media-insert')]
    public function handleMediaInsert(): void
    {
        $this->insertSelected();
    }

    /* ========= Actions ========= */

    /**
     * Livewire State & Selected File (Preview Panel) কে রিফ্রেশ করে
     */
    protected function refreshState()
    {
        // Livewire-কে বলে যে পুরো কম্পোনেন্টের স্টেট রিফ্রেশ করতে হবে
        $this->dispatch('$refresh');
    }

    public function makeCopy()
    {
        if (! $this->selectedId) {
            return;
        }

        $file = MediaFile::withTrashed()->find($this->selectedId);
        if (! $file) {
            return;
        }

        $disk = $file->disk;
        $ext  = pathinfo($file->path, PATHINFO_EXTENSION);
        $base = pathinfo($file->path, PATHINFO_FILENAME);

        $newPath = 'media/copies/' . $base . '_copy_' . uniqid() . ($ext ? ".{$ext}" : '');

        Storage::disk($disk)->copy($file->path, $newPath);

        $copy = MediaFile::create([
            'name'       => $file->name . ' (copy)',
            'folder_id'  => $file->folder_id,
            'disk'       => $disk,
            'path'       => $newPath,
            'mime_type'  => $file->mime_type,
            'size'       => $file->size,
            'visibility' => $file->visibility,
        ]);

        $this->selectedId = $copy->id;
        $this->resetPage();
        $this->toast('File duplicate successfully.');
        $this->refreshState(); // ✅
    }

    /**
     * Move to trash just open trash modal
     */
    public function moveToTrash()
    {
        if (! $this->selectedId) {
            return;
        }

        $this->skipTrash = false;
        $this->showMoveToTrashModal = true;

        $this->closeContextMenu(); // ✅
    }

    public function closeMoveToTrashModal()
    {
        $this->showMoveToTrashModal = false;
    }

    public function confirmMoveToTrash()
    {
        if (! $this->selectedId) {
            return;
        }
        $file = MediaFile::withTrashed()->find($this->selectedId);
        if (! $file) {
            return;
        }

        if($this->skipTrash) {
            $this->deleteMedia($file->id);
            $this->toast('File permanently deleted.');
        } else {
            $file->delete();
            $this->toast('File moved to trash successfully.');
        }
        $this->selectedId = null;
        $this->resetPage();
        $this->resetPerPage();

        $this->showMoveToTrashModal = false;
        $this->refreshState(); // ✅
    }

    /**
     * Add to favorite (toggle)
     */
    public function addToFavorite()
    {
        if (! $this->selectedId) {
            return;
        }

        $file = MediaFile::withTrashed()->find($this->selectedId);
        if (! $file) {
            return;
        }

        // toggle
        $file->is_favorite = ! $file->is_favorite;
        $file->save();

        // 🔔 Toast message
        $message = $file->is_favorite
            ? 'Favorite-এ যোগ করা হয়েছে।'
            : 'Favorite থেকে সরানো হয়েছে।';

        $this->toast($message);
        if ($this->scope === 'favorites' && ! $file->is_favorite) {
            $this->selectedId = null;
            $this->resetPage();
        }

        $this->closeContextMenu();
        $this->refreshState(); // ✅
    }

    /* ======= IMAGE CROP =========== */
    public function openCropModal(int $fileId): void
    {
        $file = MediaFile::withTrashed()->find($fileId);

        if (! $file || ! Str::startsWith($file->mime_type, 'image/')) {
            return;
        }

        $this->cropFileId    = $fileId;
        $this->showCropModal = true;

        // 🔥 crop অপশনগুলোর ডিফল্ট ভ্যালু
        $this->cropOptimize  = true;
        $this->cropQuality   = 80;
        $this->cropMaxWidth  = null;
        $this->cropMaxHeight = null;
        $this->cropFormat    = 'keep';

        $this->closeContextMenu();
        $this->dispatch('init-cropper', id: $this->getId());
    }

    public function closeCropModal(): void
    {
        $this->showCropModal = false;
        $this->cropFileId = null;
        $this->cropMaxWidth  = null;
        $this->cropMaxHeight = null;
        $this->cropQuality   = 80;
        $this->cropFormat   = 'keep';
    }

    /**
     * @param array $crop
     * @return void
     */
    public function saveCroppedImage(array $crop): void
    {
        if (! $this->cropFileId) {
            return;
        }

        $file = MediaFile::withTrashed()->find($this->cropFileId);

        if (! $file || ! Str::startsWith($file->mime_type, 'image/')) {
            return;
        }

        $x      = (int) round($crop['x'] ?? 0);
        $y      = (int) round($crop['y'] ?? 0);
        $width  = (int) round($crop['width'] ?? 0);
        $height = (int) round($crop['height'] ?? 0);

        // সেফটি চেক
        if ($width <= 0 || $height <= 0) {
            $this->toast('Invalid crop dimensions.', 'error');
            return;
        }

        $disk     = $file->disk ?? 'public';
        $path     = $file->path;
        $fullPath = Storage::disk($disk)->path($path);

        // Intervention Image ব্যবহার করে ফাইল লোড
        $image = ImageManager::read($fullPath);

        // ✅ সেফটি: crop area যেন ইমেজের বাইরে না যায়
        $imgW = $image->width();
        $imgH = $image->height();

        if ($x < 0) {
            $width += $x;
            $x = 0;
        }
        if ($y < 0) {
            $height += $y;
            $y = 0;
        }
        if ($x + $width > $imgW) {
            $width = $imgW - $x;
        }
        if ($y + $height > $imgH) {
            $height = $imgH - $y;
        }
        if ($width <= 0 || $height <= 0) {
            $this->toast('Invalid crop area.', 'error');
            return;
        }
        // মূল ক্রপিং অপারেশন: x, y, width, height অনুযায়ী ক্রপ
        $image->crop($width, $height, $x, $y);

        // ---------- OPTIONAL RESIZE + COMPRESS ----------
        if ($this->cropOptimize) {
            $quality = max(10, min(100, $this->cropQuality ?: 80));

            // 🔍 প্রয়োজনে রিসাইজ (max width/height অনুযায়ী)
            $maxW = $this->cropMaxWidth ?: null;
            $maxH = $this->cropMaxHeight ?: null;

            if ($maxW || $maxH) {
                $currW = $image->width();
                $currH = $image->height();
                $scale = 1.0;

                if ($maxW && $currW > $maxW) {
                    $scale = min($scale, $maxW / $currW);
                }
                if ($maxH && $currH > $maxH) {
                    $scale = min($scale, $maxH / $currH);
                }

                if ($scale < 1) {
                    $newW = (int) round($currW * $scale);
                    $newH = (int) round($currH * $scale);
                    $image->resize($newW, $newH);
                }
            }

            $format = $this->cropFormat; // keep | webp | jpeg

            if ($format === 'webp') {
                // 👉 WebP তে কনভার্ট
                $encoder = new WebpEncoder(quality: $quality);
                $binary  = $image->encode($encoder);

                $newPath = preg_replace('/\.\w+$/', '.webp', $path) ?: ($path . '.webp');

                Storage::disk($disk)->put($newPath, (string) $binary);

                if ($newPath !== $path && Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);
                }

                $path     = $newPath;
                $fullPath = Storage::disk($disk)->path($path);

                $file->path      = $newPath;
                $file->mime_type = 'image/webp';

            } elseif ($format === 'jpeg') {
                // 👉 JPEG তে কনভার্ট
                $encoder = new JpegEncoder(quality: $quality);
                $binary  = $image->encode($encoder);

                $newPath = preg_replace('/\.\w+$/', '.jpg', $path) ?: ($path . '.jpg');

                Storage::disk($disk)->put($newPath, (string) $binary);

                if ($newPath !== $path && Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);
                }

                $path     = $newPath;
                $fullPath = Storage::disk($disk)->path($path);

                $file->path      = $newPath;
                $file->mime_type = 'image/jpeg';

            } elseif ($format === 'png') {
                // 👉 PNG তে কনভার্ট
                // নোট: PNG সাধারণত lossless, তাই এখানে quality হিসেবে compression level ঠিক করছি না,
                // শুধু PNG encoder দিয়ে encode করছি।
                $encoder = new PngEncoder();
                $binary  = $image->encode($encoder);

                $newPath = preg_replace('/\.\w+$/', '.png', $path) ?: ($path . '.png');

                Storage::disk($disk)->put($newPath, (string) $binary);

                if ($newPath !== $path && Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);
                }

                $path     = $newPath;
                $fullPath = Storage::disk($disk)->path($path);

                $file->path      = $newPath;
                $file->mime_type = 'image/png';

            } else {
                // 👉 ফরম্যাট same রেখে শুধু compress
                if (in_array($file->mime_type, ['image/jpeg', 'image/jpg'])) {
                    $encoder = new JpegEncoder(quality: $quality);
                    $binary  = $image->encode($encoder);
                    Storage::disk($disk)->put($path, (string) $binary);
                } elseif ($file->mime_type === 'image/webp') {
                    $encoder = new WebpEncoder(quality: $quality);
                    $binary  = $image->encode($encoder);
                    Storage::disk($disk)->put($path, (string) $binary);
                } elseif ($file->mime_type === 'image/png') {
                    // PNG এর জন্য lossless re-encode
                    $encoder = new PngEncoder();
                    $binary  = $image->encode($encoder);
                    Storage::disk($disk)->put($path, (string) $binary);
                } else {
                    // অন্য ফরম্যাট হলে normal save
                    $image->save($fullPath);
                }
            }
        } else {
            // শুধু crop, কোনো extra optimize নেই
            $image->save($fullPath);
        }

        // ---------- Database update ----------
        clearstatcache();
        $file->size   = filesize($fullPath);
        $file->width  = $image->width();
        $file->height = $image->height();
        $file->save();

        $this->showCropModal = false;
        $this->cropFileId    = null;
        $this->resetPage();
        $this->refreshState();
        $this->toast('Image cropped & optimized successfully.', 'success');
    }

    public function openPreview(?int $id = null): void
    {
        $id = $id ?: $this->selectedId;

        $file = MediaFile::withTrashed()->find($id);
        if (! $file || !str_starts_with($file->mime_type, 'image/')) {
            return;
        }

        $this->selectedId   = $id;
        $this->previewFile  = $file;
        $this->showPreview  = true;

        $this->closeContextMenu();
    }

    // Close Preview
    public function closePreview(): void
    {
        $this->showPreview = false;
        $this->previewFile = null;
    }

    /* ========= ALT TEXT MODAL ========= */

    public function openAltTextModal()
    {
        if (! $this->selectedId) {
            return;
        }

        $file = MediaFile::withTrashed()->find($this->selectedId);
        if (! $file) {
            return;
        }

        $this->altTextInput = $file->alt ?? '';
        $this->showAltModal = true;
        $this->resetErrorBag('altTextInput');

        $this->closeContextMenu();
    }

    public function closeAltTextModal()
    {
        $this->showAltModal = false;
    }

    public function saveAltText()
    {
        $this->validate([
            'altTextInput' => 'nullable|string|max:255',
        ], [
            'altTextInput.max' => 'Alt text may not be greater than 255 characters.',
        ]);

        if (! $this->selectedId) {
            return;
        }

        $file = MediaFile::withTrashed()->find($this->selectedId);
        if (! $file) {
            return;
        }

        $file->alt = $this->altTextInput;
        $file->save();

        $this->toast('File alt text saved successfully.');

        $this->showAltModal = false;
        $this->refreshState(); // ✅
    }

    /* ========= Copy link / indirect link ========= */

    public function copyLink()
    {
        if (! $this->selectedId) {
            return;
        }

        $file = MediaFile::withTrashed()->find($this->selectedId);
        if (! $file) {
            return;
        }

        $this->dispatch('media-copy-link', url: $file->url);

        $this->toast('File link copy successfully.');
        $this->closeContextMenu(); // ✅
    }

    public function copyIndirectLink()
    {
        if (! $this->selectedId) {
            return;
        }

        $file = MediaFile::withTrashed()->find($this->selectedId);
        if (! $file) {
            return;
        }

        $indirect = Route::has('mediamanager.indirect')
            ? route('mediamanager.indirect', $file->id)
            : $file->url;

        $this->dispatch('media-copy-link', url: $indirect);
        $this->toast('File indirect link copy successfully.');

        $this->closeContextMenu(); // ✅
    }

    public function download()
    {
        if (! $this->selectedId) {
            return;
        }

        $file = MediaFile::withTrashed()->find($this->selectedId);
        if (! $file) {
            return;
        }

        // Livewire থেকে ব্রাউজারে ইভেন্ট পাঠালাম
        $this->dispatch('media-download', url: $file->url);

        // কনটেক্সট মেনু খুলে থাকলে বন্ধ করে দিই
        $this->closeContextMenu();
    }

    public function share()
    {
        if (! $this->selectedId) {
            return;
        }

        // future এ share modal / navigator.share ইত্যাদি

        $this->closeContextMenu(); // ✅
    }

    /* ========= misc UI helpers ========= */

    public function setFolder(?int $folderId)
    {
        $this->folder_id = $folderId;
        $this->resetPage();
    }

    public function setViewMode(string $mode)
    {
        $this->viewMode = $mode;
    }

    public function setSort(string $sort)
    {
        $this->sort = $sort;
        $this->resetPage();
    }

    protected function toast(string $message, string $type = 'success'): void
    {
        $this->dispatch('media-toast', message: $message, type: $type);
    }

    public function refreshList()
    {
        $this->reset([
            'q',
            'mime',
            'visibility',
            'from',
            'to',
            'folder_id',
            'tag'
        ]);
        $this->scope = 'all';
        $this->resetPerPage();
        $this->resetPage();
        $this->selectedId = null;
    }

    public function selectMedia(int $id)
    {
        if ($this->selectedId === $id) {
            $this->selectedId = null;
            $this->dispatch('media-unselected');
        } else {
            $this->selectedId = $id;

            $this->dispatch('media-selected', id: $id);
        }
    }

    public function setScope(string $scope)
    {
        if (! in_array($scope, ['all', 'trash', 'recent', 'favorites'])) {
            $scope = 'all';
        }

        $this->scope      = $scope;
        $this->selectedId = null;
        $this->resetPage();
        $this->resetPerPage();
    }

    /* ========= CREATE FOLDER MODAL ========= */

    public function openFolderModal()
    {
        $this->resetErrorBag('newFolderName');
        $this->newFolderName   = '';
        $this->showFolderModal = true;
    }

    public function closeFolderModal()
    {
        $this->showFolderModal = false;
    }

    public function createFolder()
    {
        $this->validate([
            'newFolderName' => 'required|string|max:191',
        ], [
            'newFolderName.required' => 'Folder name is required.',
        ]);

        MediaFolder::create([
            'name'      => $this->newFolderName,
            'parent_id' => $this->folder_id, // current folder এর নিচে তৈরি হবে
        ]);

        $this->newFolderName   = '';
        $this->showFolderModal = false;

        // ফোল্ডার লিস্ট রিফ্রেশের জন্য শুধু পেজ রি-রেন্ডার
        $this->resetPage();
    }

    /* ========== insert (VERY IMPORTANT) ========== */
    public function insert()
    {
        if (! $this->selectedId) return;

        $file = MediaFile::withTrashed()->find($this->selectedId);
        if (! $file) return;

        // JS ধরে input + preview আপডেট করবে
        $this->dispatch('media-selected', [
            'id'   => $file->id,
            'url'  => $file->url,
            'name' => $file->name,
            'mime' => $file->mime_type,
        ]);

        // চাইলে এখানে internal state reset/close করতে পারো
        // $this->selectedId = null;
    }

    /**
     * Permanent delete (disk + DB) – শুধু Trash থেকে
     */
    public function deleteMedia($id)
    {
        $media = MediaFile::withTrashed()->find($id);

        if (! $media) {
            return;
        }

        $disk = $media->disk;
        $path = $media->path;

        $media->forceDelete();

        if ($disk && $path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }

        if ($this->selectedId === $id) {
            $this->selectedId = null;
        }
    }

    protected function runEmptyTrashLogic()
    {
        // শুধু Trash scope এ কাজ করবে
        if ($this->scope !== 'trash') {
            return;
        }

        // প্রয়োজন হলে current filters apply করতে পারো
        $filters = [
            'q'          => $this->q,
            'mime'       => $this->mime,
            'visibility' => $this->visibility,
            'from'       => $this->from,
            'to'         => $this->to,
            'folder_id'  => $this->folder_id,
            'tag'        => $this->tag,
        ];

        $query = MediaFile::withTrashed()
            ->onlyTrashed()
            ->filter($filters);

        // সেফ ভাবে chunk করে delete করি
        $query->chunkById(100, function ($items) {
            foreach ($items as $item) {
                $this->deleteMedia($item->id); // আগের মতই
            }
        });

        $this->selectedId = null;
        $this->resetPage();
        $this->resetPerPage();
        $this->toast('Trash has been cleared.');
    }

    // বাটন থেকে মডাল ওপেন
    public function openEmptyTrashModal()
    {
        if ($this->scope !== 'trash') {
            return;
        }

        $this->showEmptyTrashModal = true;
    }

    // মডাল বন্ধ
    public function closeEmptyTrashModal()
    {
        $this->showEmptyTrashModal = false;
    }

    // Confirm বাটন
    public function confirmEmptyTrash()
    {
        $this->runEmptyTrashLogic();

        $this->showEmptyTrashModal = false;

        $this->toast('Trash emptied successfully.');
    }

    public function openDeletePermanentModal(?int $id = null)
    {
        // id আসলে context menu থেকে আসবে, না এলে selectedId ব্যবহার
        $this->pendingDeleteId = $id ?: $this->selectedId;

        if (! $this->pendingDeleteId) {
            return;
        }

        $this->showDeletePermanentModal = true;

        // context menu থাকলে বন্ধ করি
        $this->closeContextMenu();
    }

    public function closeDeletePermanentModal()
    {
        $this->showDeletePermanentModal = false;
        $this->pendingDeleteId = null;
    }

    public function confirmDeletePermanent()
    {
        if (! $this->pendingDeleteId) {
            return;
        }

        $this->deleteMedia($this->pendingDeleteId);

        if ($this->selectedId === $this->pendingDeleteId) {
            $this->selectedId = null;
        }

        $this->pendingDeleteId = null;
        $this->showDeletePermanentModal = false;

        $this->resetPage();
        $this->resetPerPage();

        $this->toast('File permanently deleted.');
    }

    /* ========= Right-click context menu ========= */

    public function openContextMenu($fileId, $x, $y)
    {
        $this->selectedId = $fileId;

        $this->contextMenu = [
            'show'   => false, // ইনিশিয়ালি বন্ধ থাকুক
            'x'      => $x,
            'y'      => $y,
            'fileId' => $fileId,
        ];
        // context menu দেখানোর জন্য manual call
        $this->contextMenu['show'] = true;
    }

    public function closeContextMenu()
    {
        $this->contextMenu['show'] = false;
    }

    public function restoreFromTrash()
    {
        if(! $this->selectedId) return;

        $file = MediaFile::onlyTrashed()->find($this->selectedId);
        if (! $file) return;

        $file->restore();
        $this->selectedId = null;
        $this->resetPage();
        $this->closeContextMenu();
        $this->toast('File restored successfully.');
    }

    public $showRenameModal = false;
    public $renameInput = '';

    public function openRenameModal()
    {
        if (! $this->selectedId) return;

        $file = MediaFile::withTrashed()->find($this->selectedId);
        if (! $file) return;

        $this->renameInput   = $file->name;
        $this->showRenameModal = true;
        $this->resetErrorBag('renameInput');

        $this->closeContextMenu();
    }

    public function closeRenameModal()
    {
        $this->showRenameModal = false;
    }

    public function saveRename()
    {
        $this->validate([
            'renameInput' => 'required|string|max:191',
        ]);

        if (! $this->selectedId) return;

        $file = MediaFile::withTrashed()->find($this->selectedId);
        if (! $file) return;

        $file->name = $this->renameInput;
        $file->save();

        $this->showRenameModal = false;
        $this->toast('File successfully renamed.');
        $this->refreshState(); // ✅
    }

    public function resetPerPage()
    {
        $this->perPage = config('mediamanager.media.perPage', 24);
    }

    public function scopeFilter($query, array $filters)
    {
        // 🔍 q সার্চ: name বা mime_type এর উপর
        $query->when($filters['q'] ?? null, function ($q, $search) {
            $q->where(function ($p) use ($search) {
                $p->where('name', 'like', "%{$search}%")
                    ->orWhere('mime_type', 'like', "%{$search}%")
                    ->orWhere('alt', 'like', "%{$search}%");
            });
        });

        // 📁 current folder
        $query->when(array_key_exists('folder_id', $filters), function ($q) use ($filters) {
            if ($filters['folder_id']) {
                $q->where('folder_id', $filters['folder_id']);
            } else {
                $q->whereNull('folder_id');
            }
        });

        $query->when($filters['mime'] ?? null, function ($q, $mime) {
            $q->where('mime_type', 'like', "{$mime}%");
        });

        $query->when($filters['visibility'] ?? null, function ($q, $visibility) {
            $q->where('visibility', $visibility);
        });

        $query->when($filters['from'] ?? null, function ($q, $from) {
            $q->whereDate('created_at', '>=', $from);
        });

        $query->when($filters['to'] ?? null, function ($q, $to) {
            $q->whereDate('created_at', '<=', $to);
        });

        $query->when($filters['tag'] ?? null, function ($q, $tag) {
            $q->whereHas('tags', function ($t) use ($tag) {
                $t->where('name', $tag);
            });
        });
    }
    /* ========= Filters change (No change needed here) ========= */

    public function updatingQ()          { $this->resetPage(); $this->resetPerPage(); }
    public function updatingMime()       { $this->resetPage(); $this->resetPerPage(); }
    public function updatingVisibility() { $this->resetPage(); $this->resetPerPage(); }
    public function updatingFolderId()   { $this->resetPage(); $this->resetPerPage(); }
    public function updatingTag()        { $this->resetPage(); $this->resetPerPage(); }

    /* ========= RENDER ========= */

    public function render()
    {
        // ✅ ফিক্স: রেন্ডার হওয়ার আগে সিলেক্টেড ফাইলটি (সাইড প্যানেলের জন্য) ডেটাবেস থেকে রিফ্রেশ করা
        $this->selected = $this->selectedId
            ? MediaFile::withTrashed()->find($this->selectedId)
            : null;

        $filters = [
            'q'          => $this->q,
            'mime'       => $this->mime,
            'visibility' => $this->visibility,
            'from'       => $this->from,
            'to'         => $this->to,
            'folder_id'  => $this->folder_id,
            'tag'        => $this->tag,
        ];

        $query = MediaFile::with('tags')->filter($filters);

        // scope অনুযায়ী ডাটা ফিল্টার
        if ($this->scope === 'trash') {
            $query->onlyTrashed();
        } elseif ($this->scope === 'recent') {
            $query->where('created_at', '>=', now()->subDays(7));
        } elseif ($this->scope === 'favorites') {
            $query->where('is_favorite', true);
        } else {
            // normal: শুধু non-deleted
            $query->whereNull('deleted_at');
        }

        // sort
        switch ($this->sort) {
            case 'name-desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name-asc':
            default:
                $query->orderBy('name', 'asc');
                break;
        }

        $files   = $query->paginate($this->perPage ?? config('mediamanager.media.perPage', 24));
        $folders = MediaFolder::with('children')->whereNull('parent_id')->get();
        $tags    = MediaTag::orderBy('name')->get();

        return view('mediamanager::livewire.manager', [
            'files'   => $files,
            'folders' => $folders,
            'tags'    => $tags,
        ]);
    }
}
