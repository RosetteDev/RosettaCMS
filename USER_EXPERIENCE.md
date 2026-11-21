# Rosetta CMS - User Experience Features

This document describes the user experience features implemented in Rosetta CMS.

## Features Implemented

### 1. Drag-and-Drop Page Builder

The page builder allows administrators to create and arrange content blocks on pages using drag-and-drop interactions.

**Features:**
- Add blocks of various types (text, heading, image, gallery, callout, grid, quote, divider, HTML)
- Drag-and-drop reordering using native HTML5 drag API
- Inline editing with Alpine.js for dynamic form handling
- Preview mode to see how blocks render
- Block-specific editing controls

**Usage:**
1. Navigate to a page in the admin panel
2. Scroll to the "Page Builder" section
3. Click "Add Block" to add a new block
4. Drag blocks to reorder them
5. Click on a block to edit its content
6. Click "Save Blocks" to persist changes

### 2. Visual Menu Editor

The menu editor provides a drag-and-drop interface for organizing navigation menu items.

**Features:**
- Drag-and-drop reordering of menu items
- Support for hierarchical menu structures (parent/child)
- Visual feedback during drag operations
- Real-time preview of menu structure
- Easy addition of new menu items

**Usage:**
1. Navigate to Menus > Edit in the admin panel
2. Drag menu items to reorder them
3. Use the form on the right to add new items
4. Click "Save Menu Order" to persist changes

### 3. Inline Content Editing

Admins can edit page content directly on the frontend without navigating to the admin panel.

**Features:**
- Click any content area to open inline editing
- Block-level editing with visual controls
- Drag-and-drop reordering of blocks
- Real-time preview of changes
- Quick save and cancel actions

**Usage:**
1. Navigate to a public page while logged in as an admin
2. Click the "Edit" button in the inline editing toolbar
3. Edit content directly on the page
4. Use the toolbar to save or cancel changes

### 4. Real-Time Theme Preview

Real-time preview of theme changes without page reload.

**Features:**
- Theme settings update instantly
- Visual feedback for theme customization
- Support for CDN asset loading
- Responsive preview mode

**Usage:**
1. Navigate to Theme settings in the admin panel
2. Make changes to theme options
3. Changes are applied in real-time to the preview
4. Click "Save Theme" to persist changes

## Technical Implementation

### Backend Components

#### Models
- **Block** (`app/Models/Block.php`): Represents content blocks with type-specific settings
- **MenuItem** (`app/Models/MenuItem.php`): Menu items with parent/child relationships
- **Post** (`app/Models/Post.php`): Extended with blocks relationship

#### Controllers
- **BlockController** (`app/Http/Controllers/Admin/BlockController.php`): CRUD operations for blocks
- **MenuController** (`app/Http/Controllers/Admin/MenuController.php`): Menu management with reorder support

#### Middleware
- **InlineEdit** (`app/Http/Middleware/InlineEdit.php`): Enables inline editing for authenticated admins

#### Helpers
- **ThemeHelper** (`app/Http/Controllers/ThemeHelper.php`): Theme rendering with CDN support

### Frontend Components

#### Alpine.js Data Components
- **blockEditor**: Handles drag-and-drop, block management, and preview rendering
- **menuEditor**: Manages menu item reordering and hierarchy

#### Blade Partials
- **block-editor**: Page builder UI with drag-and-drop
- **menu-item**: Individual menu item with drag handle
- **inline-edit-modal**: Frontend inline editing interface
- **inline-block-editor**: Block editing in inline mode

### Routes

```php
// Blocks management
Route::prefix('admin/pages/{post}/blocks')->name('admin.pages.blocks.')->group(function () {
    Route::get('/', [BlockController::class, 'index'])->name('index');
    Route::post('/', [BlockController::class, 'store'])->name('store');
    Route::put('/{block}', [BlockController::class, 'update'])->name('update');
    Route::delete('/{block}', [BlockController::class, 'destroy'])->name('destroy');
    Route::post('/reorder', [BlockController::class, 'reorder'])->name('reorder');
    Route::get('/media-picker', [BlockController::class, 'mediaPicker'])->name('media-picker');
});

// Menu management
Route::prefix('admin/menus')->name('admin.menus.')->group(function () {
    Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('edit');
    Route::post('/{menu}/reorder', [MenuController::class, 'reorder'])->name('reorder');
    // ... other routes
});
```

## Configuration

### Environment Variables

```
# Enable inline editing
ROSETTA_INLINE_EDIT_ENABLED=false

# Enable CDN for theme assets
ROSETTA_CDN_ENABLED=true

# Cache settings
ROSETTA_CACHE_ENABLED=true
ROSETTA_CACHE_TTL=3600
```

### Configuration File

Create `config/rosetta.php` with the provided configuration.

## Testing

### Login Credentials
- Email: `admin@rosettacms.com`
- Password: `password`

### Test Scenarios

1. **Page Builder**
   - Create a new page
   - Add multiple blocks of different types
   - Drag blocks to reorder
   - Save and view on frontend

2. **Menu Editor**
   - Create a new menu
   - Add several menu items
   - Create parent/child relationships
   - Drag to reorder
   - Save and verify navigation

3. **Inline Editing**
   - Navigate to a public page as an admin
   - Click inline edit toolbar
   - Edit block content
   - Save and verify changes

## Future Enhancements

1. **More Block Types**
   - Video embed blocks
   - FORM blocks
   - Social media feed blocks
   - Product showcase blocks

2. **Advanced Features**
   - Versioning for content
   - Draft preview mode
   - Multi-language support
   - Theme customization options

3. **Performance**
   - CDN caching for static assets
   - Block-level caching
   - Optimized asset loading

## Troubleshooting

### Drag and Drop Not Working
- Ensure JavaScript is enabled in your browser
- Check browser console for errors
- Verify Alpine.js is loaded

### Inline Editing Not Showing
- Ensure `ROSETTA_INLINE_EDIT_ENABLED=true` in `.env`
- Verify admin user is logged in
- Check user has admin role

### Menu Reordering Not Saving
- Verify CORS settings for API requests
- Check database for menu_items table
- Ensure CSRF token is present in requests

## Contributing

When adding new features:
1. Follow existing code patterns
2. Use Alpine.js for frontend interactions
3. Create Blade partials for reusable components
4. Test with both desktop and mobile views
5. Update this documentation
