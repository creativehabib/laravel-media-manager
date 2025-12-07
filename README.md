# Laravel Media Manager

A Livewire v3-powered media manager for Laravel applications. It provides a Botble-like media library with uploads, tagging, folders, favorites, trash management, and basic image optimization features.

## Requirements
- PHP 8.2+
- Laravel with Livewire 3 installed
- intervention/image-laravel
- intervention/image
- Tailwindcss
- Fontawesome

## Features
- 📁 Folder-based file manager
- 🏷️ Tags & search filters
- 🖼️ Image preview, crop & optimize
- ☁️ Multiple disks (e.g. `public`, `s3`)
- 🗑️ Trash, restore, and permanent delete
- ⭐ Favorites
- 🔗 Direct & indirect shareable URLs
- 📤 Upload from local or external URL
- ⚡ Livewire-powered UI (no page reloads)
- 🧩 Ready-made Blade partials:
- `media-input` for input + preview
- `media-modal` for the media gallery modal
- `media-toast` for toasts/notifications
-Easy integration with:
- Any simple `<input>` field
- CKEditor (or any rich text editor)

---

## Installation
1. Install the package via Composer:
   ```bash
    composer require creativehabib/media-manager intervention/image intervention/image-laravel
   ```
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
