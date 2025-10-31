<?php

namespace UserTagBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 标签类型
 */
enum TagType: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    case Empty = '';
    case StaticTag = 'static';
    case SmartTag = 'smart';
    case SqlTag = 'sql';

    public function getLabel(): string
    {
        return match ($this) {
            self::Empty => '未知',
            self::StaticTag => '静态标签',
            self::SmartTag => '智能标签',
            self::SqlTag => 'SQL标签',
        };
    }
}
