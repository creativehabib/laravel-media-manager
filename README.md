# Laravel Media Manager

A powerful Livewire-powered media manager for Laravel applications. It provides a Botble-like media library with seamless uploads, tagging, folders, favorites, trash management, and built-in image optimization features.

## Requirements
- **PHP:** 8.2+
- **Laravel:** 10.x / 11.x (with Livewire 3 or 4 installed)
- **Image Processing:** `intervention/image` 4.x & `intervention/image-laravel` 4.x (installed automatically)
- **Styling & Icons:** Tailwind CSS & FontAwesome

## ✨ Features
- 📁 **Folder-based File Manager:** Neatly organize your files.
- 🏷️ **Tags & Filters:** Search and filter by name, tag, MIME type, visibility, and date range.
- 🖼️ **Image Preview & Crop:** Built-in image preview, cropping, and optimization.
- 🛡️ **Graceful Error Handling:** Automatically catches and rejects unsupported (e.g., SVG) or corrupted image formats without crashing the server.
- ☁️ **Multiple Disks:** Support for `public`, `s3`, `do_spaces`, etc.
- 🗑️ **Trash Management:** Move to trash, restore, and permanent delete.
- ⭐ **Favorites:** Bookmark your most-used media.
- 🔗 **Shareable URLs:** Generate direct & indirect shareable links.
- 📤 **Upload Options:** Upload files from local devices or external URLs.
- ⚡ **Livewire-powered UI:** Smooth, SPA-like experience with no page reloads.
- 🧩 **Ready-made Blade Partials:**
    - `media-input`: For input + preview.
    - `media-modal`: For the media gallery modal.
    - `media-toast`: For toast notifications.
- 🛠️ **Easy Integration:** Works out of the box with any simple `<input>` field or rich text editors like CKEditor.

---

## 🚀 Installation

1. Install the package via Composer:
   ```bash
   composer require creativehabib/media-manager
   ```
   The package requires Intervention Image 3 and its Laravel integration, so Composer will install compatible versions automatically. Do not install Intervention Image 2 alongside this package: its image manager does not provide the `usingDriver()` API required by the Laravel integration.
2. Publish the configuration (and adjust disks, route prefix, middleware, or permissions as needed):
   ```bash
   php artisan vendor:publish --provider="Habib\\MediaManager\\MediaManagerServiceProvider" --tag=config
   ```
3. Run the migrations to create the media tables:
   ```bash
   php artisan migrate
   ```
## Configuration
The published `config/mediamanager.php` file lets you control several behaviors:
- **disks**: which filesystem disks can be managed (default: `public`, `s3`, `do_spaces`).
- **default_disk**: disk used when none is specified.
- **route_prefix**: base URL for the bundled routes (default: `admin/media`).
- **middleware**: middleware stack protecting the routes (default: `['web', 'auth']`).
- **permission**: optional Gate ability checked before allowing access (default: `manage_media`).
- **per_page**: pagination size for the media listing.
- **toast**: toast notification defaults (position, timeout, queue length).

## Usage
### Routes
The service provider automatically registers routes under the configured prefix, guarded by the configured middleware and optional permission check. Visit `/admin/media` (or your configured prefix) to access the media manager page.

### Livewire component
Render the media manager anywhere in your app:
```blade
<livewire:media-manager />
{{-- or --}}
@livewire('media-manager')
```

### Blade input helper
Drop a media picker + preview into any form field:
```blade
@include('mediamanager::includes.media-input', [
    'name'  => 'site_logo',
    'id'    => 'site_logo',
    'label' => 'Site Logo',
    'value' => $site_logo,
])
```

### Modal include
To open the manager inside a modal and let users pick media for form fields, include the packaged modal partial and trigger it via the provided JavaScript helpers:
```blade
@include('mediamanager::includes.media-modal')

<button type="button" onclick="openMediaManager('thumbnail')">Select media</button>
<input type="hidden" id="thumbnail" name="thumbnail"> <!-- populated when a file is chosen -->
```
The modal listens for `open-media-manager` and `close-media-manager` events and dispatches `media-insert` when users confirm selection.

### View publishing
If you need to customize the views, publish them:
```bash
php artisan vendor:publish --provider="Habib\\MediaManager\\MediaManagerServiceProvider" --tag=mediamanager-views
# or publish everything under the mediamanager namespace
php artisan vendor:publish --provider="Habib\\MediaManager\\MediaManagerServiceProvider" --tag=mediamanager-all
```

## Features
- Upload files from local devices or by URL, storing them on the configured disks.
- Organize files into folders, tag them, and search/filter by name, tag, MIME type, visibility, date range, or folder.
- Mark items as favorites, manage trash, and view recent uploads.
- Image uploads automatically record dimensions; cropping and optimization options are built in for image assets.
- Optional Gate permission check to restrict access to authorized users.
