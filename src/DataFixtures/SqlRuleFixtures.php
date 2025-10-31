<?php

namespace UserTagBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use UserTagBundle\Entity\SqlRule;
use UserTagBundle\Entity\Tag;

class SqlRuleFixtures extends Fixture implements DependentFixtureInterface
{
    public const SQL_RULE_SAMPLE_1 = 'sql_rule_sample_1';
    public const SQL_RULE_SAMPLE_2 = 'sql_rule_sample_2';

    public function load(ObjectManager $manager): void
    {
        $sqlRules = [
            [
                'tagReference' => TagFixtures::TAG_SAMPLE_3,
                'cronStatement' => '0 4 * * *',
                'sqlStatement' => 'SELECT user_id FROM user WHERE register_source = "miniprogram"',
                'reference' => self::SQL_RULE_SAMPLE_1,
            ],
            [
                'tagReference' => TagFixtures::TAG_SAMPLE_4,
                'cronStatement' => '0 6 * * *',
                'sqlStatement' => 'SELECT user_id FROM user WHERE register_source = "wechat"',
                'reference' => self::SQL_RULE_SAMPLE_2,
            ],
        ];

        foreach ($sqlRules as $data) {
            $tag = $this->getReference($data['tagReference'], Tag::class);

            $sqlRule = new SqlRule();
            $sqlRule->setTag($tag);
            $sqlRule->setCronStatement($data['cronStatement']);
            $sqlRule->setSqlStatement($data['sqlStatement']);

            $manager->persist($sqlRule);
            $this->addReference($data['reference'], $sqlRule);
        }

        $manager->flush();
    }

    /**
     * @return array<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            TagFixtures::class,
        ];
    }
}
