<?php

namespace UserTagBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use UserTagBundle\Entity\SmartRule;
use UserTagBundle\Entity\Tag;

class SmartRuleFixtures extends Fixture implements DependentFixtureInterface
{
    public const SMART_RULE_SAMPLE_1 = 'smart_rule_sample_1';
    public const SMART_RULE_SAMPLE_2 = 'smart_rule_sample_2';

    public function load(ObjectManager $manager): void
    {
        $smartRules = [
            [
                'tagReference' => TagFixtures::TAG_SAMPLE_1,
                'cronStatement' => '0 0 * * *',
                'jsonStatement' => [
                    'conditions' => [
                        'field' => 'register_source',
                        'operator' => 'eq',
                        'value' => 'mobile',
                    ],
                ],
                'reference' => self::SMART_RULE_SAMPLE_1,
            ],
            [
                'tagReference' => TagFixtures::TAG_SAMPLE_2,
                'cronStatement' => '0 2 * * *',
                'jsonStatement' => [
                    'conditions' => [
                        'field' => 'register_source',
                        'operator' => 'eq',
                        'value' => 'email',
                    ],
                ],
                'reference' => self::SMART_RULE_SAMPLE_2,
            ],
        ];

        foreach ($smartRules as $data) {
            $tag = $this->getReference($data['tagReference'], Tag::class);

            $smartRule = new SmartRule();
            $smartRule->setTag($tag);
            $smartRule->setCronStatement($data['cronStatement']);
            $smartRule->setJsonStatement($data['jsonStatement']);

            $manager->persist($smartRule);
            $this->addReference($data['reference'], $smartRule);
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
