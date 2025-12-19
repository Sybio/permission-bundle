# Roadmap

This document outlines planned features and improvements for the Permission Bundle.

## Planned Features

### v1.1.0 (Planned)
- **PHP Attribute HasPermission** : A PHP 8 attribute to simplify permission checks in controllers
- **Twig Extension has_permission** : A Twig function to check permissions directly in templates

## Ideas for Future Versions

### Permission caching mechanism

**Concept**: Cache validation results to avoid redundant database queries and improve performance.

**Current limitation**:
```php
// Each call re-executes the entire validation (DB queries, etc.)
$this->authorizationChecker->isGranted('PERMISSION', $permission); // DB query
$this->authorizationChecker->isGranted('PERMISSION', $permission); // DB query again
```

**With caching**:
```yaml
# config/packages/sybio_permission.yaml
sybio_permission:
    cache:
        enabled: true
        ttl: 3600  # 1 hour
        pool: 'cache.permission'  # Symfony Cache pool
```

**Usage** (transparent):
```php
// First call: executes validation and caches result
$this->authorizationChecker->isGranted('PERMISSION', $permission); // DB query + cache

// Subsequent calls: retrieves from cache
$this->authorizationChecker->isGranted('PERMISSION', $permission); // From cache
```

**Benefits**:
- **Performance**: Avoids redundant database queries
- **Reduced load**: Less stress on database servers
- **Configurable**: Can be enabled/disabled per permission type
- **Cache invalidation**: Automatic invalidation when related data changes

**Use cases**:
- Permission checks in loops (e.g., listing multiple articles)
- Permissions checked multiple times in a single request
- Permissions based on data that changes infrequently

---

### Permission inheritance system

**Concept**: Permissions that inherit from other permissions, creating a hierarchical permission system.

**Current limitation**:
```php
// Each permission must be checked independently
$this->authorizationChecker->isGranted('PERMISSION', new ViewArticlePermission(...));
$this->authorizationChecker->isGranted('PERMISSION', new EditArticlePermission(...));
// No automatic relationship between permissions
```

**With inheritance**:
```php
// Base permission
final class ViewArticlePermission implements PermissionInterface
{
    public function __construct(
        private int $articleId,
        private int $userId,
    ) {}
}

// Permission that inherits (if you can view, you can comment)
final class CommentArticlePermission implements PermissionInterface
{
    public function __construct(
        private int $articleId,
        private int $userId,
    ) {}
    
    // New: inheritance support
    public function getParentPermissions(): array
    {
        return [
            new ViewArticlePermission($this->articleId, $this->userId)
        ];
    }
}

// Permission with multiple parents
final class EditArticlePermission implements PermissionInterface
{
    public function getParentPermissions(): array
    {
        return [
            new ViewArticlePermission($this->articleId, $this->userId),
            new CommentArticlePermission($this->articleId, $this->userId),
        ];
    }
}
```

**Usage**:
```php
// Automatically checks ViewArticlePermission first
// If ViewArticlePermission fails, EditArticlePermission fails too
$this->authorizationChecker->isGranted('PERMISSION', 
    new EditArticlePermission($articleId, $userId)
);
// The bundle automatically validates parent permissions first
```

**Benefits**:
- **DRY principle**: Avoids duplicating logic across related permissions
- **Clear hierarchy**: Makes permission relationships explicit
- **Composition**: Build complex permissions from simpler ones
- **Automatic validation**: Parent permissions are checked automatically

**Use cases**:
- Nested permissions (editing requires viewing)
- Permissions with prerequisites
- Hierarchical role systems
- Complex permission trees (e.g., admin > editor > author > viewer)

---

**Note**: This roadmap is subject to change. Features are planned but not guaranteed. If you have suggestions or want to contribute, please open an issue on GitHub.

