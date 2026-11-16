# Category/Taxonomy System Implementation

## Decision
- Categories + Tags system
- Categories: Single-level (flat list)
- Tags: Flat list
- Both can be applied to Posts (and Pages optionally)

## Implementation Plan

### 1. Database Migrations
- `categories` table: id, name, slug, description, color, order
- `tags` table: id, name, slug, description, color, order
- `category_post` pivot table: category_id, post_id
- `tag_post` pivot table: tag_id, post_id

### 2. Models
- `Category` model with posts relationship
- `Tag` model with posts relationship
- Update `Post` model with categories() and tags() relationships

### 3. Controllers
- `CategoryController` (CRUD)
- `TagController` (CRUD)

### 4. Admin Views
- Categories listing and form
- Tags listing and form

### 5. Post Form Integration
- Category selection (multi-select)
- Tag selection (tag input)

### 6. Public Views
- Category listing pages
- Tag listing pages
- Filter posts by category/tag
