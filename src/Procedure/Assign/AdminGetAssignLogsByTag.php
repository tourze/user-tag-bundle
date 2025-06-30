<?php

namespace UserTagBundle\Procedure\Assign;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPCPaginatorBundle\Procedure\PaginatorTrait;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Repository\AssignLogRepository;
use UserTagBundle\Repository\TagRepository;

#[IsGranted(attribute: 'ROLE_OPERATOR')]
#[MethodTag(name: '用户标签')]
#[MethodDoc(summary: '根据标签获取打标记录')]
#[MethodExpose(method: 'AdminGetAssignLogsByTag')]
class AdminGetAssignLogsByTag extends BaseProcedure
{
    use PaginatorTrait;

    #[MethodParam(description: '标签ID')]
    public string $tagId;

    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly AssignLogRepository $assignLogRepository,
    ) {
    }

    public function execute(): array
    {
        $tag = $this->tagRepository->findOneBy(['id' => $this->tagId]);
        assert((bool) $tag, '找不到指定标签');

        $qb = $this->assignLogRepository->createQueryBuilder('a')
            ->where('a.tag = :tag')
            ->setParameter('tag', $tag)
            ->orderBy('a.createTime', 'DESC');

        return $this->fetchList($qb, $this->formatItem(...));
    }

    private function formatItem(AssignLog $assignLog): array
    {
        return [
            'id' => $assignLog->getId(),
            'createTime' => $assignLog->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $assignLog->getUpdateTime()?->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $assignLog->getUser()->getUserIdentifier(),
            ],
        ];
    }
}
