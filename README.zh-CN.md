# 用户标签管理包

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)](https://github.com/tourze/tourze)
[![Code Coverage](https://img.shields.io/badge/coverage-95%25-brightgreen.svg)](https://github.com/tourze/tourze)

[English](README.md) | [中文](README.zh-CN.md)

一个全面的用户标签管理系统，适用于 Symfony 应用程序，支持静态标签、
智能标签和基于 SQL 的标签，具有分类管理功能。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [配置](#配置)
- [快速开始](#快速开始)
- [高级用法](#高级用法)
- [API 参考](#api-参考)
- [系统要求](#系统要求)
- [许可证](#许可证)

## 功能特性

- **静态标签**: 手动为用户分配标签
- **智能标签**: 基于 JSON 规则和定时表达式自动分配标签
- **SQL 标签**: 使用自定义 SQL 查询动态分配标签
- **分类管理**: 标签的层次化组织
- **分配追踪**: 完整的标签分配审计跟踪
- **EasyAdmin 集成**: 现成的管理界面
- **JSON-RPC API**: 用于标签管理的 RESTful API

## 安装

```bash
composer require tourze/user-tag-bundle
```

## 配置

该 Bundle 开箱即用，只需最少的配置。服务通过 `services.yaml` 自动配置。

## 快速开始

### 1. 注册Bundle

```php
// config/bundles.php
return [
    // ...
    UserTagBundle\UserTagBundle::class => ['all' => true],
];
```

### 2. 配置数据库

运行迁移以创建所需的表：

```bash
bin/console doctrine:migrations:migrate
```

### 3. 基本使用

```php
use UserTagBundle\Entity\Tag;
use UserTagBundle\Entity\Category;
use UserTagBundle\Enum\TagType;

// 创建分类
$category = new Category();
$category->setName('客户状态');
$entityManager->persist($category);

// 创建静态标签
$tag = new Tag();
$tag->setName('VIP客户')
    ->setType(TagType::StaticTag)
    ->setCategory($category)
    ->setDescription('高价值客户');
$entityManager->persist($tag);

// 创建带有JSON规则的智能标签
$smartTag = new Tag();
$smartTag->setName('活跃用户')
         ->setType(TagType::SmartTag)
         ->setCategory($category);
         
$smartRule = new SmartRule();
$smartRule->setTag($smartTag)
          ->setCronStatement('0 0 * * *') // 每天午夜
          ->setJsonStatement([
              'conditions' => [
                  ['field' => 'last_login', 'operator' => '>=', 'value' => '7 days ago']
              ]
          ]);
          
$entityManager->persist($smartTag);
$entityManager->persist($smartRule);
$entityManager->flush();

// 创建基于SQL的标签
$sqlTag = new Tag();
$sqlTag->setName('近期购买者')
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

### 4. 使用 JSON-RPC API

```php
// 获取用户标签
$procedure = new GetUserTagList();
$result = $procedure->execute();

// 为用户分配标签
$assignProcedure = new AssignTagToBizUser();
$assignProcedure->setParameters([
    'tagId' => $tag->getId(),
    'bizUserId' => $userId
]);
$assignProcedure->execute();
```

## 高级用法

### 自定义标签类型

您可以通过实现 `TagInterface` 来使用自定义标签类型扩展系统：

```php
use UserTagBundle\Enum\TagType;

// 创建自定义标签类型
class CustomTagType extends TagType
{
    public const CustomType = 'custom';
}
```

### 事件监听器

Bundle 会分发您可以监听的事件：

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
        // 标签分配前的自定义逻辑
        $user = $event->getUser();
        $tag = $event->getTag();
        
        // 示例：验证业务规则
        if (!$this->validateTagAssignment($user, $tag)) {
            throw new \InvalidArgumentException('不允许标签分配');
        }
    }
}
```

### 智能标签规则

为智能标签配置复杂的 JSON 规则：

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
    'logic' => 'AND' // 或 'OR'
]);
```

### SQL 标签示例

使用 SQL 查询的动态标签：

```php
$sqlRule->setSqlStatement("
    SELECT user_id 
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY user_id 
    HAVING COUNT(*) >= 3
");
```

## API 参考

### JSON-RPC 过程

#### 标签管理
- `CreateSingleUserTag` - 创建新标签
- `UpdateSingleUserTag` - 更新现有标签
- `DeleteSingleUserTag` - 删除标签
- `GetUserTagList` - 列出所有标签

#### 分类管理
- `CreateSingleUserTagCategory` - 创建新分类
- `UpdateSingleUserTagCategory` - 更新现有分类
- `DeleteSingleUserTagCategory` - 删除分类
- `GetUserTagCategories` - 列出所有分类

#### 标签分配
- `AssignTagToBizUser` - 为用户分配标签
- `UnassignTagToBizUser` - 移除用户标签
- `GetAssignTagsByBizUserId` - 获取用户的已分配标签
- `AdminGetAssignLogsByTag` - 获取分配历史

### 实体

- `Tag` - 主标签实体，支持不同类型
- `Category` - 层次化分类系统
- `SmartRule` - 智能标签的基于 JSON 的规则
- `SqlRule` - 动态标签的基于 SQL 的规则
- `AssignLog` - 分配追踪和审计跟踪

## 系统要求

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 3.0+
- Doctrine DBAL 3.0+

### 必需的包

- `symfony/framework-bundle`
- `doctrine/orm`
- `doctrine/doctrine-bundle`
- `tourze/doctrine-timestamp-bundle`
- `tourze/doctrine-snowflake-bundle`
- `tourze/arrayable`

## 许可证

此 Bundle 在 MIT 许可证下发布。详情请参阅 [LICENSE](LICENSE) 文件。
