<?php

namespace UserTagBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Entity\Tag;

#[Autoconfigure(public: true)]
class AssignLogFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 通过引用获取tags，避免在DataFixtures中使用repository调用
        $tag1 = $this->getReference(TagFixtures::TAG_SAMPLE_1, Tag::class);
        $tag2 = $this->getReference(TagFixtures::TAG_SAMPLE_2, Tag::class);
        $tag3 = $this->getReference(TagFixtures::TAG_SAMPLE_3, Tag::class);

        $tags = [$tag1, $tag2, $tag3];

        // 创建一些测试用的AssignLog记录
        $testUserIds = [
            'user1@images.unsplash.com',
            'user2@source.unsplash.com',
            'user3@pexels.com',
        ];

        foreach ($tags as $tag) {
            foreach ($testUserIds as $userId) {
                $assignLog = new AssignLog();
                $assignLog->setTag($tag);
                $assignLog->setUserId($userId);
                $assignLog->setValid(true);
                $assignLog->setAssignTime(new \DateTimeImmutable());

                $manager->persist($assignLog);
            }
        }

        $manager->flush();
    }

    /**
     * @return array<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [TagFixtures::class];
    }
}
