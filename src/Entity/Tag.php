<?php

namespace UserTagBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\Arrayable\PlainArrayInterface;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\UserTagContracts\TagInterface;
use UserTagBundle\Enum\TagType;
use UserTagBundle\Repository\TagRepository;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'crm_tag', options: ['comment' => '客户标签'])]
#[ORM\UniqueConstraint(name: 'crm_tag_idx_uniq', columns: ['category_id', 'name'])]
class Tag implements \Stringable, PlainArrayInterface, TagInterface
{
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[TrackColumn]
    private ?bool $valid = false;

    #[Groups(groups: ['restful_read', 'restful_write'])]
    #[ORM\ManyToOne(inversedBy: 'tags')]
    private ?Category $category = null;

    #[Groups(groups: ['restful_read', 'restful_write'])]
    #[ORM\Column(length: 40, nullable: false, enumType: TagType::class, options: ['comment' => '类型', 'default' => 'static'])]
    private TagType $type = TagType::StaticTag;

    #[Groups(groups: ['restful_read', 'restful_write'])]
    private string $name;

    #[Groups(groups: ['restful_read', 'restful_write'])]
    private ?string $description = null;


    #[CreateIpColumn]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    private ?string $updatedFromIp = null;

    public function __toString(): string
    {
        if ($this->getId() === null || $this->getId() === 0) {
            return '';
        }

        return "{$this->getCategory()}:{$this->getName()}";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): TagType
    {
        return $this->type;
    }

    public function setType(TagType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }


    public function setCreatedFromIp(?string $createdFromIp): self
    {
        $this->createdFromIp = $createdFromIp;

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setUpdatedFromIp(?string $updatedFromIp): self
    {
        $this->updatedFromIp = $updatedFromIp;

        return $this;
    }

    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
    }public function retrievePlainArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'type' => $this->getType()->toArray(),
            'category' => $this->getCategory()?->retrievePlainArray(),
            'valid' => $this->isValid(),
        ];
    }
}
