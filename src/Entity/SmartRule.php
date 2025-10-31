<?php

namespace UserTagBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\Arrayable;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use UserTagBundle\Repository\SmartRuleRepository;

/**
 * @implements Arrayable<string, mixed>
 */
#[ORM\Entity(repositoryClass: SmartRuleRepository::class)]
#[ORM\Table(name: 'ims_user_tag_smart_rule', options: ['comment' => '智能规则'])]
class SmartRule implements \Stringable, Arrayable
{
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    public function __toString(): string
    {
        if (0 === $this->getId()) {
            return '';
        }

        return sprintf('SmartRule #%d', $this->getId());
    }

    public function getId(): int
    {
        return $this->id;
    }

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(unique: true, nullable: false, onDelete: 'CASCADE')]
    private Tag $tag;

    #[Assert\NotBlank]
    #[Assert\Length(max: 60)]
    #[ORM\Column(length: 60, options: ['comment' => '定时表达式'])]
    private string $cronStatement;

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => 'JSON规则'])]
    private array $jsonStatement = [];

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function setTag(Tag $tag): void
    {
        $this->tag = $tag;
    }

    /**
     * @return array<string, mixed>
     */
    public function getJsonStatement(): array
    {
        return $this->jsonStatement;
    }

    /**
     * @param array<string, mixed> $jsonStatement
     */
    public function setJsonStatement(array $jsonStatement): void
    {
        $this->jsonStatement = $jsonStatement;
    }

    public function getCronStatement(): string
    {
        return $this->cronStatement;
    }

    public function setCronStatement(string $cronStatement): void
    {
        $this->cronStatement = $cronStatement;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'cronStatement' => $this->getCronStatement(),
            'jsonStatement' => $this->getJsonStatement(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
        ];
    }
}
