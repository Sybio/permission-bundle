# Permission Bundle

A Symfony bundle to manage permissions (authorizations) as Value Objects, with an integrated validation system and native integration with Symfony's security system.

## Introduction

This bundle provides a modern and type-safe approach to managing permissions in your Symfony applications. Instead of using strings or constants, permissions are represented as immutable **Value Objects**, each having its own **checker** (equivalent to a handler) that contains the business logic for validation.

This approach offers better maintainability, better testability, and better IDE integration thanks to strong typing.

## Advantages

✅ **Type-safe** : Permissions are typed objects, not strings  
✅ **Separation of concerns** : Each permission has its own dedicated checker  
✅ **Symfony integration** : Compatible with the security (Voter) and validation systems  
✅ **Auto-configuration** : Checkers are automatically detected and registered  
✅ **Testability** : Easy to test thanks to dependency injection  
✅ **Maintainability** : Easy to extend with new permissions and maintain over time

## Installation

```bash
composer require sybio/permission-bundle
```

If you're using Symfony Flex, the bundle will be automatically registered. Otherwise, register it manually in `config/bundles.php`:

```php
return [
    // ...
    Sybio\PermissionBundle\SybioPermissionBundle::class => ['all' => true],
];
```

## Configuration

Configuration is optional. By default, the bundle uses the `PERMISSION` security attribute. You can customize it in `config/packages/sybio_permission.yaml`:

```yaml
sybio_permission:
    security_attribute: 'PERMISSION'  # Attribute used by the PermissionVoter
```

## Creating a permission and its checker

### 1. Create the permission (Value Object)

Create a class that implements `PermissionInterface`:

```php
<?php

declare(strict_types=1);

namespace App\Security\Permission;

use Sybio\PermissionBundle\PermissionInterface;

final readonly class EditArticlePermission implements PermissionInterface
{
    public function __construct(
        private int $articleId,
        private int $userId,
    ) {
    }
}
```

### 2. Create the checker

Create a checker that implements `PermissionCheckerInterface`:

```php
<?php

declare(strict_types=1);

namespace App\Security\Permission;

use App\Repository\ArticleRepository;
use Sybio\PermissionBundle\PermissionCheckerInterface;
use Sybio\PermissionBundle\PermissionInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @implements PermissionCheckerInterface<EditArticlePermission>
 */
final class EditArticlePermissionChecker implements PermissionCheckerInterface
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
    ) {
    }

    public function check(
        PermissionInterface $permission,
        ExecutionContextInterface $context,
    ): void {
        $article = $this->articleRepository->find($permission->articleId);
        
        if ($article === null) {
            $context->buildViolation('The article does not exist.')
                ->addViolation();
            return;
        }

        if ($article->getAuthorId() !== $permission->userId) {
            $context->buildViolation('You are not authorized to edit this article.')
                ->addViolation();
        }
    }

    public static function permissionClass(): string
    {
        return EditArticlePermission::class;
    }
}
```

The checker is automatically detected and registered thanks to the bundle's auto-configuration.

## Usage

### Method 1: With the security system

Use the security system to check permissions:

```php
<?php
//...
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

$this->authorizationChecker->isGranted(
    'PERMISSION', 
    new EditArticlePermission($articleId, $userId),
);
```
Note that the attribute `'PERMISSION'` is defined in the configuration and you can customize it.
You can also use the Permission class as attribute, it will work : `$this->isGranted(EditArticlePermission::class, new EditArticlePermission(...`.

Then get violations list if needed:
```php
<?php
//...
use Sybio\PermissionBundle\Validation\PermissionValidatorInterface;

dd(
    $this->permissionValidator->getLastViolations(),
);
```
The power of this bundle is that you can also pass to the view why access was denied by reading violations.

#### Example with a controller

```php
//...
use App\Security\Permission\EditArticlePermission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleController extends AbstractController
{
    #[Route('/article/{id}/edit', name: 'article_edit')]
    public function edit(int $id): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION', new EditArticlePermission($id, $this->getUser()->getId()));
        
        // Do whatever you want here
        
        return $this->render('article/edit.html.twig');
    }
}
```

### Method 2: With PermissionValidator

Thanks to the validator component, you can validate permissions directly with validation:
```php
//...
use Sybio\PermissionBundle\Validation\PermissionValidatorInterface;

$violations = $this->permissionValidator->validate($permission);

if ($violations->count() > 0) {
    // Do something with the violations
}
```

## Complete example

Here's a complete example with a (little) more complex permission:

```php
<?php

declare(strict_types=1);

namespace App\Security\Permission;

use Sybio\PermissionBundle\PermissionInterface;

final readonly class DeleteCommentPermission implements PermissionInterface
{
    public function __construct(
        public Comment $comment,
        public ?User $user,
        public bool $isAdmin,
    ) {
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Security\Permission;

use App\Repository\CommentRepository;
use Sybio\PermissionBundle\PermissionCheckerInterface;
use Sybio\PermissionBundle\PermissionInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @implements PermissionCheckerInterface<DeleteCommentPermission>
 */
final class DeleteCommentPermissionChecker implements PermissionCheckerInterface
{
    public function check(
        PermissionInterface $permission,
        ExecutionContextInterface $context,
    ): void {
        $comment = $permission->comment;
        
        // Check that the comment is not soft deleted
        if ($comment->isDeleted()) {
            $context->buildViolation('The comment is already deleted.')
                ->addViolation();
            return;
        }
        
        // Admins can always delete
        if ($permission->isAdmin) {
            return;
        }

        // Check that the user is the author of the comment
        if ($comment->getAuthorId() !== $permission->user->getId()) {
            $context->buildViolation('You are not authorized to delete this comment.')
                ->addViolation();
        }
    }

    public static function permissionClass(): string
    {
        return DeleteCommentPermission::class;
    }
}
```

## License

MIT

## Author

Sybio (Clément Guillemain)
