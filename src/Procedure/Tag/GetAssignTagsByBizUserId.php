<?php

namespace UserTagBundle\Procedure\Tag;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Enum\TagType;
use UserTagBundle\Repository\AssignLogRepository;

#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodTag(name: '用户标签')]
#[MethodDoc(summary: '根据用户id获取所有分配的标签记录')]
#[MethodExpose(method: 'GetAssignTagsByBizUserId')]
#[Log]
class GetAssignTagsByBizUserId extends LockableProcedure
{
    #[MethodParam(description: '用户ID')]
    public string $userId;

    /** @var array<int, string> */
    #[MethodParam(description: '类型: static:静态标签、smart：智能标签、sql：SQL标签')]
    public array $type = [];

    public function __construct(
        private readonly AssignLogRepository $assignLogRepository,
        private readonly UserLoaderInterface $userLoader,
    ) {
    }

    public function execute(): array
    {
        $user = $this->loadUser();
        $validTypes = $this->parseTagTypes();
        $logs = $this->queryAssignLogs($user, $validTypes);

        return [
            'list' => $this->formatLogList($logs, $user),
        ];
    }

    private function loadUser(): UserInterface
    {
        $user = $this->userLoader->loadUserByIdentifier($this->userId);
        if (null === $user) {
            throw new ApiException('用户不存在');
        }

        return $user;
    }

    /**
     * @return array<int, TagType>
     */
    private function parseTagTypes(): array
    {
        if ([] === $this->type) {
            return [];
        }

        $validTypes = [];
        foreach ($this->type as $item) {
            $tmp = TagType::tryFrom($item);
            if (null !== $tmp) {
                $validTypes[] = $tmp;
            }
        }

        return $validTypes;
    }

    /**
     * @param array<int, TagType> $validTypes
     * @return array<int, AssignLog>
     */
    private function queryAssignLogs(UserInterface $user, array $validTypes): array
    {
        $query = $this->assignLogRepository->createQueryBuilder('a')
            ->where('a.user = :user')
            ->setParameter('user', $user)
        ;

        if ([] !== $validTypes) {
            $query->innerJoin('a.tag', 't')
                ->andWhere('t.type in (:type)')
                ->setParameter('type', $validTypes)
            ;
        }

        /** @var array<int, AssignLog> */
        return $query->orderBy('a.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param array<int, AssignLog> $logs
     * @return array<int, array<string, mixed>>
     */
    private function formatLogList(array $logs, UserInterface $user): array
    {
        $list = [];
        /** @var AssignLog $log */
        foreach ($logs as $log) {
            $tag = $log->getTag();
            if (null === $tag) {
                continue;
            }
            $list[] = [
                'tagInfo' => $tag->retrievePlainArray(),
                'userInfo' => [
                    'id' => $user->getUserIdentifier(),
                ],
                'assignTime' => $log->getAssignTime()?->format('Y-m-d H:i:s'),
                'unassignTime' => $log->getUnassignTime()?->format('Y-m-d H:i:s'),
                'createTime' => $log->getCreateTime()?->format('Y-m-d H:i:s'),
            ];
        }

        return $list;
    }
}
