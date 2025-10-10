# Livewire 3 Guide - How It Works in Your Project

## What is Livewire?

Livewire is a full-stack framework for Laravel that makes building dynamic interfaces simple, without leaving the comfort of Laravel. Think of it as "Laravel for the frontend" - you write PHP components that feel reactive like Vue or React, but without writing JavaScript.

### Key Concept: No Page Reloads

Livewire allows you to build reactive components that update in real-time without full page reloads. It uses AJAX under the hood to communicate between frontend and backend.

---

## How Livewire Works in Your Project

### The Flow

```
User Action (click, type, etc.)
    ↓
Livewire detects the action
    ↓
Sends AJAX request to server with component state
    ↓
Server runs the PHP method
    ↓
Component re-renders
    ↓
Livewire sends back only the HTML that changed
    ↓
DOM updates (no full page reload!)
```

### Example from Your Project

When you search in the publications list:

1. **You type** in the search box
2. **Livewire detects** the `wire:model.live` directive
3. **Sends request** to server with new search value
4. **Server runs** `updatingSearch()` method and re-queries database
5. **Re-renders** the table with filtered results
6. **Updates** only the table HTML (not the whole page)

---

## Your Livewire Folder Structure

```
app/Livewire/
└── Publications/
    ├── PublicationList.php      ← Component class (logic)
    └── PublicationForm.php      ← Component class (form logic)

resources/views/livewire/
└── publications/
    ├── publication-list.blade.php    ← Component view (HTML)
    └── publication-form.blade.php    ← Component view (HTML)
```

### The Pattern

Each Livewire component has **2 files**:

1. **PHP Class** (in `app/Livewire/`) - Contains the logic
2. **Blade View** (in `resources/views/livewire/`) - Contains the HTML

They are automatically connected by naming convention.

---

## Anatomy of a Livewire Component

### 1. The PHP Class (`PublicationList.php`)

```php
<?php

namespace App\Livewire\Publications;

use App\Models\Publication;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class PublicationList extends Component
{
    use WithPagination;  // ← Trait for pagination

    // PUBLIC PROPERTIES = REACTIVE DATA
    // Any public property is automatically available in the view
    // and syncs between frontend and backend
    #[Url]  // ← This makes it show in URL: ?search=something
    public $search = '';

    #[Url]
    public $showDeleted = false;

    public $perPage = 15;

    // LIFECYCLE HOOKS
    // These run automatically at specific times
    public function updatingSearch()
    {
        // Runs BEFORE $search is updated
        $this->resetPage();  // Reset pagination when searching
    }

    // ACTIONS = METHODS THE FRONTEND CAN CALL
    // Any public method can be called from the view
    public function toggleDeleted()
    {
        $this->showDeleted = !$this->showDeleted;
        $this->resetPage();
    }

    public function deletePublication($id)
    {
        $publication = Publication::find($id);
        if ($publication) {
            $publication->_del_mark = 1;
            $publication->save();
        }
        // Component automatically re-renders after this!
    }

    // RENDER METHOD = WHAT TO DISPLAY
    // This runs every time the component updates
    public function render()
    {
        $publications = Publication::query()
            ->when($this->search, function ($query) {
                // Use $this->search to filter
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->when($this->showDeleted, function ($query) {
                $query->where('_del_mark', 1);
            }, function ($query) {
                $query->where('_del_mark', 0);
            })
            ->paginate($this->perPage);

        return view('livewire.publications.publication-list', [
            'publications' => $publications,
        ])->layout('components.layouts.app');  // ← Use this layout
    }
}
```

### 2. The Blade View (`publication-list.blade.php`)

```blade
<div>
    <!-- WIRE DIRECTIVES = LIVEWIRE MAGIC -->

    <!-- wire:model.live = Two-way data binding with live updates -->
    <input
        type="text"
        wire:model.live.debounce.300ms="search"
        placeholder="Search..."
    >
    <!-- As you type, $search updates on the server (debounced 300ms) -->

    <!-- wire:click = Call a method when clicked -->
    <button wire:click="toggleDeleted">
        Toggle Deleted
    </button>
    <!-- Calls the toggleDeleted() method in the PHP class -->

    <!-- Display public properties directly -->
    @if($search)
        <p>Searching for: {{ $search }}</p>
    @endif

    <!-- Loop through data from render() method -->
    @foreach($publications as $publication)
        <div>
            <h3>{{ $publication->title }}</h3>

            <!-- Pass parameters to methods -->
            <button wire:click="deletePublication({{ $publication->id_publication }})">
                Delete
            </button>
        </div>
    @endforeach

    <!-- Pagination links (Livewire-aware) -->
    {{ $publications->links() }}
</div>
```

---

## Livewire Directives You'll Use Most

### Data Binding

```blade
<!-- Live update (sends request on every keystroke, debounced) -->
<input wire:model.live.debounce.300ms="search">

<!-- Update on blur (when user leaves the field) -->
<input wire:model.blur="email">

<!-- Update on change (for selects/checkboxes) -->
<select wire:model="status">
```

### Actions

```blade
<!-- Call a method -->
<button wire:click="save">Save</button>

<!-- Call with parameters -->
<button wire:click="delete({{ $id }})">Delete</button>

<!-- Prevent default behavior -->
<form wire:submit.prevent="save">

<!-- Call on key press -->
<input wire:keydown.enter="search">
```

### Loading States

```blade
<!-- Show while request is in progress -->
<div wire:loading>
    Processing...
</div>

<!-- Hide while loading -->
<div wire:loading.remove>
    Content
</div>

<!-- Target specific action -->
<button wire:click="save">
    <span wire:loading.remove wire:target="save">Save</span>
    <span wire:loading wire:target="save">Saving...</span>
</button>
```

### Polling (Auto-refresh)

```blade
<!-- Refresh every 5 seconds -->
<div wire:poll.5s>
    Current time: {{ now() }}
</div>
```

---

## Creating New Livewire Components

### Command

```bash
docker compose exec web php artisan make:livewire Publications/PublicationForm
```

This creates:
- `app/Livewire/Publications/PublicationForm.php`
- `resources/views/livewire/publications/publication-form.blade.php`

### Basic Template

```php
<?php

namespace App\Livewire\Publications;

use Livewire\Component;

class PublicationForm extends Component
{
    // Properties (form fields)
    public $title = '';
    public $issueYear = '';

    // Validation rules
    protected $rules = [
        'title' => 'required|min:3',
        'issueYear' => 'required|digits:4',
    ];

    // Real-time validation
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    // Save action
    public function save()
    {
        $this->validate();

        Publication::create([
            'title' => $this->title,
            'issue_year' => $this->issueYear,
        ]);

        session()->flash('message', 'Publication created successfully!');
        $this->reset(); // Clear form
    }

    public function render()
    {
        return view('livewire.publications.publication-form');
    }
}
```

```blade
<div>
    <form wire:submit.prevent="save">
        <div>
            <label>Title</label>
            <input type="text" wire:model="title">
            @error('title') <span>{{ $message }}</span> @enderror
        </div>

        <div>
            <label>Year</label>
            <input type="text" wire:model="issueYear">
            @error('issueYear') <span>{{ $message }}</span> @enderror
        </div>

        <button type="submit">Save</button>
    </form>
</div>
```

---

## Tailwind CSS Integration

### ✅ YES! Tailwind is Already Integrated

Your project is **already fully configured** with Tailwind CSS v4. Here's how:

### How Tailwind is Set Up

**1. Configuration File** (`resources/css/app.css`)
```css
@import 'tailwindcss';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../**/*.blade.php';
@source '../**/*.js';

@theme {
    --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
}
```

**2. Vite Config** (`vite.config.js`)
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
```

**3. Layout Includes** (`resources/views/components/layouts/app.blade.php`)
```blade
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
```

### Using Tailwind in Your Views

You can use **any Tailwind class** directly in your Blade templates:

```blade
<!-- Container -->
<div class="container mx-auto px-4 py-8">

    <!-- Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- Card -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Title</h2>
            <p class="text-gray-600">Content</p>
        </div>

    </div>

    <!-- Button -->
    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
        Click Me
    </button>

    <!-- Form -->
    <input
        type="text"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
    >

    <!-- Responsive -->
    <div class="hidden md:block lg:flex">
        <!-- Hidden on mobile, block on tablet, flex on desktop -->
    </div>

</div>
```

### Common Tailwind Patterns in Your Project

**1. Form Fields**
```blade
<input
    type="text"
    wire:model.live="search"
    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
    placeholder="Search..."
>
```

**2. Buttons**
```blade
<!-- Primary Button -->
<button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
    Save
</button>

<!-- Secondary Button -->
<button class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
    Cancel
</button>

<!-- Danger Button -->
<button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
    Delete
</button>
```

**3. Tables**
```blade
<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Title
            </th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                Content
            </td>
        </tr>
    </tbody>
</table>
```

**4. Cards**
```blade
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="px-6 py-4">
        <h3 class="text-xl font-bold text-gray-800">Card Title</h3>
        <p class="text-gray-600 mt-2">Card content</p>
    </div>
</div>
```

---

## Livewire + Tailwind = Perfect Match

### Example: Modal Component

```php
// app/Livewire/Publications/PublicationModal.php
class PublicationModal extends Component
{
    public $isOpen = false;
    public $publicationId;

    public function open($id)
    {
        $this->publicationId = $id;
        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function render()
    {
        $publication = Publication::find($this->publicationId);
        return view('livewire.publications.publication-modal', [
            'publication' => $publication
        ]);
    }
}
```

```blade
<!-- publication-modal.blade.php -->
<div>
    <!-- Backdrop (Tailwind) -->
    @if($isOpen)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-40" wire:click="close"></div>
    @endif

    <!-- Modal (Tailwind) -->
    <div
        class="fixed inset-0 flex items-center justify-center z-50 {{ $isOpen ? '' : 'hidden' }}"
    >
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h3 class="text-xl font-bold">{{ $publication->title }}</h3>
                <button wire:click="close" class="text-gray-400 hover:text-gray-600">
                    ✕
                </button>
            </div>

            <!-- Content -->
            <div class="px-6 py-4">
                <p class="text-gray-700">{{ $publication->description }}</p>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-2">
                <button
                    wire:click="close"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300"
                >
                    Close
                </button>
                <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Save
                </button>
            </div>
        </div>
    </div>
</div>
```

---

## Building Your CSS

### Development Mode (with hot reload)

```bash
npm run dev
```
- Watches for file changes
- Auto-reloads browser
- Fast development

### Production Mode (optimized)

```bash
npm run build
```
- Minifies CSS
- Removes unused classes
- Optimizes for production

---

## Quick Reference

### Livewire Lifecycle Hooks

```php
public function mount() { }           // Runs when component is created
public function hydrate() { }         // Runs before every update
public function updating($name) { }   // Before a property updates
public function updated($name) { }    // After a property updates
public function render() { }          // Renders the view
```

### Common Tailwind Classes

```
Spacing:     p-4, m-4, px-6, py-2, mx-auto
Width:       w-full, w-1/2, w-64
Colors:      bg-blue-600, text-white, border-gray-300
Typography:  text-lg, font-bold, text-center
Flexbox:     flex, items-center, justify-between, gap-4
Grid:        grid, grid-cols-3, gap-6
Rounded:     rounded, rounded-lg, rounded-full
Shadow:      shadow, shadow-lg, shadow-xl
Hover:       hover:bg-blue-700, hover:text-gray-900
```

---

## Summary

**Livewire:**
✅ Already installed and working
✅ Component structure: PHP class + Blade view
✅ Real-time reactivity without JavaScript
✅ Use `wire:model`, `wire:click` for interactivity

**Tailwind CSS:**
✅ Already installed and configured (v4)
✅ Use utility classes directly in Blade templates
✅ No custom CSS needed for most styling
✅ Built with Vite for fast compilation

Both work together seamlessly in your project! 🎉
