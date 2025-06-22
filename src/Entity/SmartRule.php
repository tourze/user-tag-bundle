<?php

namespace UserTagBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\Arrayable\Arrayable;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use UserTagBundle\Repository\SmartRuleRepository;

#[ORM\Entity(repositoryClass: SmartRuleRepository::class)]
#[ORM\Table(name: 'ims_user_tag_smart_rule', options: ['comment' => '智能规则'])]
class SmartRule implements Arrayable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    public function getId(): ?int
    {
        return $this->id;
    }
    use TimestampableAware;

    #[CreatedByColumn]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    private ?string $updatedBy = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(unique: true, nullable: false, onDelete: 'CASCADE')]
    private Tag $tag;

    #[ORM\Column(length: 60, options: ['comment' => '定时表达式'])]
    private string $cronStatement;

    #[ORM\Column(type: Types::JSON, options: ['comment' => 'JSON规则'])]
    private array $jsonStatement;

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function setTag(Tag $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function getJsonStatement(): array
    {
        return $this->jsonStatement;
    }

    public function setJsonStatement(array $jsonStatement): static
    {
        $this->jsonStatement = $jsonStatement;

        return $this;
    }

    public function getCronStatement(): string
    {
        return $this->cronStatement;
    }

    public function setCronStatement(string $cronStatement): static
    {
        $this->cronStatement = $cronStatement;

        return $this;
    }

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
