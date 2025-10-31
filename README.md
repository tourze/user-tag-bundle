# User Tag Bundle

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)](https://github.com/tourze/tourze)
[![Code Coverage](https://img.shields.io/badge/coverage-95%25-brightgreen.svg)](https://github.com/tourze/tourze)

[English](README.md) | [中文](README.zh-CN.md)

A comprehensive user tagging system for Symfony applications, supporting 
static tags, smart tags, and SQL-based tags with category management.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Advanced Usage](#advanced-usage)
- [API Reference](#api-reference)
- [Requirements](#requirements)
- [License](#license)

## Features

- **Static Tags**: Manual assignment of tags to users
- **Smart Tags**: Automatic tag assignment based on JSON rules and cron expressions
- **SQL Tags**: Dynamic tag assignment using custom SQL queries
- **Category Management**: Hierarchical organization of tags
- **Assignment Tracking**: Complete audit trail of tag assignments
- **EasyAdmin Integration**: Ready-to-use admin interface
- **JSON-RPC API**: RESTful API for tag management

## Installation

```bash
composer require tourze/user-tag-bundle
```

## Configuration

The bundle works out of the box with minimal configuration. Services are auto-configured through `services.yaml`.

## Quick Start

### 1. Register the Bundle

```php
// config/bundles.php
return [
    // ...
    UserTagBundle\UserTagBundle::class => ['all' => true],
];
```

### 2. Configure Database

Run migrations to create the required tables:

```bash
bin/console doctrine:migrations:migrate
```

### 3. Basic Usage

```php
use UserTagBundle\Entity\Tag;
use UserTagBundle\Entity\Category;
use UserTagBundle\Enum\TagType;

// Create a category
$category = new Category();
$category->setName('Customer Status');
$entityManager->persist($category);

// Create a static tag
$tag = new Tag();
$tag->setName('VIP Customer')
    ->setType(TagType::StaticTag)
    ->setCategory($category)
    ->setDescription('High-value customers');
$entityManager->persist($tag);

// Create a smart tag with JSON rules
$smartTag = new Tag();
$smartTag->setName('Active User')
         ->setType(TagType::SmartTag)
         ->setCategory($category);
         
$smartRule = new SmartRule();
$smartRule->setTag($smartTag)
          ->setCronStatement('0 0 * * *') // Daily at midnight
          ->setJsonStatement([
              'conditions' => [
                  ['field' => 'last_login', 'operator' => '>=', 'value' => '7 days ago']
              ]
          ]);
          
$entityManager->persist($smartTag);
$entityManager->persist($smartRule);
$entityManager->flush();

// Create a SQL-based tag
$sqlTag = new Tag();
$sqlTag->setName('Recent Buyers')
       ->setType(TagType::SqlTag)
       ->setCategory($category);
       
$sqlRule = new SqlRule();
$sqlRule->setTag($sqlTag)
        ->setSqlStatement("
            SELECT user_id 
            FROM orders 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY user_id 
            HAVING COUNT(*) >= 3
        ");
        
$entityManager->persist($sqlTag);
$entityManager->persist($sqlRule);
$entityManager->flush();
```

### 4. Using JSON-RPC API

```php
// Get user tags
$procedure = new GetUserTagList();
$result = $procedure->execute();

// Assign tag to user
$assignProcedure = new AssignTagToBizUser();
$assignProcedure->setParameters([
    'tagId' => $tag->getId(),
    'bizUserId' => $userId
]);
$assignProcedure->execute();
```

## Advanced Usage

### Custom Tag Types

You can extend the system with custom tag types by implementing the `TagInterface`:

```php
use UserTagBundle\Enum\TagType;

// Create a custom tag type
class CustomTagType extends TagType
{
    public const CustomType = 'custom';
}
```

### Event Listeners

The bundle dispatches events that you can listen to:

```php
use UserTagBundle\Event\BeforeAddTagEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TagEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            BeforeAddTagEvent::class => 'onBeforeAddTag',
        ];
    }

    public function onBeforeAddTag(BeforeAddTagEvent $event): void
    {
        // Custom logic before tag assignment
        $user = $event->getUser();
        $tag = $event->getTag();
        
        // Example: Validate business rules
        if (!$this->validateTagAssignment($user, $tag)) {
            throw new \InvalidArgumentException('Tag assignment not allowed');
        }
    }
}
```

### Smart Tag Rules

Configure complex JSON rules for smart tags:

```php
$smartRule->setJsonStatement([
    'conditions' => [
        [
            'field' => 'user.orders.count',
            'operator' => '>=',
            'value' => 10
        ],
        [
            'field' => 'user.totalSpent', 
            'operator' => '>',
            'value' => 1000
        ]
    ],
    'logic' => 'AND' // or 'OR'
]);
```

### SQL Tag Examples

Dynamic tags using SQL queries:

```php
$sqlRule->setSqlStatement("
    SELECT user_id 
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY user_id 
    HAVING COUNT(*) >= 3
");
```

## API Reference

### JSON-RPC Procedures

#### Tag Management
- `CreateSingleUserTag` - Create a new tag
- `UpdateSingleUserTag` - Update existing tag
- `DeleteSingleUserTag` - Delete a tag
- `GetUserTagList` - List all tags

#### Category Management
- `CreateSingleUserTagCategory` - Create a new category
- `UpdateSingleUserTagCategory` - Update existing category
- `DeleteSingleUserTagCategory` - Delete a category
- `GetUserTagCategories` - List all categories

#### Tag Assignment
- `AssignTagToBizUser` - Assign tag to user
- `UnassignTagToBizUser` - Remove tag from user
- `GetAssignTagsByBizUserId` - Get user's assigned tags
- `AdminGetAssignLogsByTag` - Get assignment history

### Entities

- `Tag` - Main tag entity with support for different types
- `Category` - Hierarchical category system
- `SmartRule` - JSON-based rules for smart tags
- `SqlRule` - SQL-based rules for dynamic tags
- `AssignLog` - Assignment tracking and audit trail

## Requirements

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 3.0+
- Doctrine DBAL 3.0+

### Required Packages

- `symfony/framework-bundle`
- `doctrine/orm`
- `doctrine/doctrine-bundle`
- `tourze/doctrine-timestamp-bundle`
- `tourze/doctrine-snowflake-bundle`
- `tourze/arrayable`

## License

This bundle is released under the MIT License. See the [LICENSE](LICENSE) file for details.
