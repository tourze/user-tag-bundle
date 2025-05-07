<?php

namespace UserTagBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\KernelInterface;
use UserTagBundle\Entity\Tag;
use Yiisoft\Json\Json;

/**
 * 标签信息
 *
 * @see https://www.yonghongtech.com/gy/xwhd/6459288.html
 */
class TagFixture extends Fixture
{
    public function __construct(private readonly KernelInterface $kernel)
    {
    }

    public function load(ObjectManager $manager): void
    {
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

        $chinaRegions = file_get_contents("{$this->kernel->getProjectDir()}/vendor/medz/gb-t-2260/resources/2020.json");
        $chinaRegions = Json::decode($chinaRegions);
        foreach ($chinaRegions as $chinaRegion) {
            $list[] = [
                '基本属性',
                "籍贯-{$chinaRegion}",
            ];
        }

        foreach ($list as [$category, $name]) {
            $tag = new Tag();
            $tag->setCategory($category);
            $tag->setName($name);
            $this->addReference(Tag::class . '籍贯-广东省', $tag);
            $manager->persist($tag);
        }

        $manager->flush();
    }
}
