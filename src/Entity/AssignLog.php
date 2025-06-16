<?php

namespace UserTagBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\Arrayable\PlainArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\Copyable;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Action\Exportable;
use Tourze\EasyAdmin\Attribute\Column\BoolColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use UserTagBundle\Repository\AssignLogRepository;

#[AsPermission(title: '打标记录')]
#[Deletable]
#[Editable]
#[Creatable]
#[Copyable]
#[Exportable]
#[ORM\Entity(repositoryClass: AssignLogRepository::class)]
#[ORM\Table(name: 'crm_tag_user', options: ['comment' => '打标记录'])]
#[ORM\UniqueConstraint(name: 'crm_tag_user_idx_uniq', columns: ['tag_id', 'user_id'])]
class AssignLog implements ApiArrayInterface, PlainArrayInterface
{
    #[Filterable]
    #[IndexColumn]
    #[ListColumn(order: 98, sorter: true)]
    #[ExportColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ListColumn(order: 99, sorter: true)]
    #[Filterable]
    #[ExportColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

    public function setCreateTime(?\DateTimeInterface $createdAt): void
    {
        $this->createTime = $createdAt;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }

    #[ExportColumn]
    #[ListColumn(order: -1, sorter: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ListColumn(title: '标签')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tag $tag = null;

    #[ListColumn(title: '客户')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private UserInterface $user;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '绑定时间'])]
    private ?\DateTimeInterface $assignTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '解绑时间'])]
    private ?\DateTimeInterface $unassignTime = null;

    #[BoolColumn]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[ListColumn(order: 97)]
    #[FormField(order: 97)]
    private ?bool $valid = false;

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

    #[ORM\Column(nullable: true, options: ['comment' => '创建IP'])]
    private ?string $createdFromIp = null;

    #[ORM\Column(nullable: true, options: ['comment' => '更新IP'])]
    private ?string $updatedFromIp = null;

    public function getId(): ?string
    {
        return $this->id;
    }

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

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getAssignTime(): ?\DateTimeInterface
    {
        return $this->assignTime;
    }

    public function setAssignTime(?\DateTimeInterface $assignTime): static
    {
        $this->assignTime = $assignTime;

        return $this;
    }

    public function getUnassignTime(): ?\DateTimeInterface
    {
        return $this->unassignTime;
    }

    public function setUnassignTime(?\DateTimeInterface $unassignTime): static
    {
        $this->unassignTime = $unassignTime;

        return $this;
    }

    public function retrieveApiArray(): array
    {
        $tag = [];
        if ($this->getTag()) {
            $tag = [
                'id' => $this->getTag()->getId(),
                'name' => $this->getTag()->getName(),
            ];
        }

        return [
            'id' => $this->getId(),
            'tag' => $tag,
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
        ];
    }

    public function retrievePlainArray(): array
    {
        $tag = null;
        if ($this->getTag()) {
            $tag = $this->getTag()->retrievePlainArray();
        }

        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'valid' => $this->isValid(),
            'user' => $this->getUser()->getUserIdentifier(),
            'tag' => $tag,
            'assignTime' => $this->getAssignTime()?->format('Y-m-d H:i:s'),
            'unassignTime' => $this->getUnassignTime()?->format('Y-m-d H:i:s'),
            'createdBy' => $this->getCreatedBy(),
            'updatedBy' => $this->getUpdatedBy(),
            'createdFromIp' => $this->getCreatedFromIp(),
            'updatedFromIp' => $this->getUpdatedFromIp(),
        ];
    }
}
