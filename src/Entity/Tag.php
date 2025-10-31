<?php

namespace UserTagBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\PlainArrayInterface;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\UserTagContracts\TagInterface;
use UserTagBundle\Enum\TagType;
use UserTagBundle\Repository\TagRepository;

/**
 * @implements PlainArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'crm_tag', options: ['comment' => '客户标签'])]
#[ORM\UniqueConstraint(name: 'crm_tag_idx_uniq', columns: ['catalog_id', 'name'])]
class Tag implements \Stringable, PlainArrayInterface, TagInterface
{
    use TimestampableAware;
    use BlameableAware;
    use IpTraceableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    #[Groups(groups: ['restful_read', 'restful_write'])]
    #[ORM\ManyToOne(targetEntity: Catalog::class)]
    private ?Catalog $catalog = null;

    #[Assert\Choice(callback: [TagType::class, 'cases'])]
    #[Groups(groups: ['restful_read', 'restful_write'])]
    #[ORM\Column(length: 40, nullable: false, enumType: TagType::class, options: ['comment' => '类型', 'default' => 'static'])]
    private TagType $type = TagType::StaticTag;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(groups: ['restful_read', 'restful_write'])]
    #[ORM\Column(length: 255, nullable: false, options: ['comment' => '标签名称'])]
    private string $name;

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    #[Groups(groups: ['restful_read', 'restful_write'])]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '标签描述'])]
    private ?string $description = null;

    #[Assert\Type(type: 'bool')]
    #[ORM\Column(nullable: true, options: ['comment' => '是否有效'])]
    #[TrackColumn]
    private ?bool $valid = false;

    public function __toString(): string
    {
        if (0 === $this->getId()) {
            return '';
        }

        return "{$this->getCatalog()}:{$this->getName()}";
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): TagType
    {
        return $this->type;
    }

    public function setType(TagType $type): void
    {
        $this->type = $type;
    }

    public function getCatalog(): ?Catalog
    {
        return $this->catalog;
    }

    public function setCatalog(?Catalog $catalog): void
    {
        $this->catalog = $catalog;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrievePlainArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'type' => $this->getType()->toArray(),
            'catalog' => $this->getCatalog()?->getName(),
            'valid' => $this->isValid(),
        ];
    }
}
