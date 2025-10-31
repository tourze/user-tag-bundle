<?php

namespace UserTagBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\Arrayable\PlainArrayInterface;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use UserTagBundle\Repository\AssignLogRepository;

/**
 * @implements ApiArrayInterface<string, mixed>
 * @implements PlainArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: AssignLogRepository::class)]
#[ORM\Table(name: 'crm_tag_user', options: ['comment' => '打标记录'])]
#[ORM\UniqueConstraint(name: 'crm_tag_user_idx_uniq', columns: ['tag_id', 'user_id'])]
class AssignLog implements \Stringable, ApiArrayInterface, PlainArrayInterface
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;
    use IpTraceableAware;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tag $tag = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_ref_id', nullable: true)]
    private ?UserInterface $user = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false, options: ['comment' => '用户标识符'])]
    private string $userId = '';

    #[Assert\Type(type: \DateTimeInterface::class)]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '绑定时间'])]
    private ?\DateTimeInterface $assignTime = null;

    #[Assert\Type(type: \DateTimeInterface::class)]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '解绑时间'])]
    private ?\DateTimeInterface $unassignTime = null;

    #[Assert\Type(type: 'bool')]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    public function __toString(): string
    {
        if (null === $this->getId() || '' === $this->getId()) {
            return '';
        }

        return sprintf(
            '%s - %s',
            $this->getTag()?->getName() ?? 'No Tag',
            $this->getUserId()
        );
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): void
    {
        $this->tag = $tag;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;

        if (null !== $user) {
            $this->userId = $user->getUserIdentifier();
        }
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getAssignTime(): ?\DateTimeInterface
    {
        return $this->assignTime;
    }

    public function setAssignTime(?\DateTimeInterface $assignTime): void
    {
        $this->assignTime = $assignTime;
    }

    public function getUnassignTime(): ?\DateTimeInterface
    {
        return $this->unassignTime;
    }

    public function setUnassignTime(?\DateTimeInterface $unassignTime): void
    {
        $this->unassignTime = $unassignTime;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveApiArray(): array
    {
        $tag = [];
        if (null !== $this->getTag()) {
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

    /**
     * @return array<string, mixed>
     */
    public function retrievePlainArray(): array
    {
        $tag = null;
        if (null !== $this->getTag()) {
            $tag = $this->getTag()->retrievePlainArray();
        }

        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'valid' => $this->isValid(),
            'user' => $this->getUserId(),
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
