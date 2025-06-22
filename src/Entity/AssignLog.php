<?php

namespace UserTagBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\Arrayable\PlainArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use UserTagBundle\Repository\AssignLogRepository;

#[ORM\Entity(repositoryClass: AssignLogRepository::class)]
#[ORM\Table(name: 'crm_tag_user', options: ['comment' => '打标记录'])]
#[ORM\UniqueConstraint(name: 'crm_tag_user_idx_uniq', columns: ['tag_id', 'user_id'])]
class AssignLog implements ApiArrayInterface, PlainArrayInterface
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tag $tag = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private UserInterface $user;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '绑定时间'])]
    private ?\DateTimeInterface $assignTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '解绑时间'])]
    private ?\DateTimeInterface $unassignTime = null;

    #[TrackColumn]
    private ?bool $valid = false;

    #[CreatedByColumn]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
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
