<?php

namespace UserTagBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\CatalogBundle\DataFixtures\CatalogTypeFixtures;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use UserTagBundle\Entity\Tag;

/**
 * 标签信息
 *
 * @see https://www.yonghongtech.com/gy/xwhd/6459288.html
 */
class TagFixtures extends Fixture implements DependentFixtureInterface
{
    public const TAG_SAMPLE_1 = 'tag_sample_1';
    public const TAG_SAMPLE_2 = 'tag_sample_2';
    public const TAG_SAMPLE_3 = 'tag_sample_3';
    public const TAG_SAMPLE_4 = 'tag_sample_4';
    public const TAG_SAMPLE_5 = 'tag_sample_5';

    public function getDependencies(): array
    {
        return [
            CatalogTypeFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $catalogType = $this->getReference(CatalogTypeFixtures::REFERENCE_PRODUCT_TYPE, CatalogType::class);

        $list = [
            [
                '注册信息',
                '注册渠道-手机号',
            ],
            [
                '注册信息',
                '注册渠道-邮箱',
            ],
            [
                '注册信息',
                '注册渠道-小程序',
            ],
            [
                '注册信息',
                '注册渠道-公众号',
            ],
            [
                '注册信息',
                '注册渠道-门店',
            ],
        ];

        // 创建分类
        $catalogs = [];
        foreach ($list as [$catalogName, $name]) {
            if (!isset($catalogs[$catalogName])) {
                $catalog = new Catalog();
                $catalog->setType($catalogType);
                $catalog->setName($catalogName);
                $catalog->setEnabled(true);
                $manager->persist($catalog);
                $catalogs[$catalogName] = $catalog;
            }
        }

        $tagReferences = [
            self::TAG_SAMPLE_1,
            self::TAG_SAMPLE_2,
            self::TAG_SAMPLE_3,
            self::TAG_SAMPLE_4,
            self::TAG_SAMPLE_5,
        ];

        $tagIndex = 0;
        foreach ($list as [$catalogName, $name]) {
            $tag = new Tag();
            $tag->setCatalog($catalogs[$catalogName]);
            $tag->setName($name);
            $tag->setValid(true);
            $manager->persist($tag);

            // 设置引用供其他Fixtures使用
            $this->addReference($tagReferences[$tagIndex], $tag);
            ++$tagIndex;
        }

        $manager->flush();
    }
}
