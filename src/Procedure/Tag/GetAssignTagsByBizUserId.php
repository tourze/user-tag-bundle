<?php

namespace UserTagBundle\Procedure\Tag;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
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

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodTag('用户标签')]
#[MethodDoc('根据用户id获取所有分配的标签记录')]
#[MethodExpose('GetAssignTagsByBizUserId')]
#[Log]
class GetAssignTagsByBizUserId extends LockableProcedure
{
    #[MethodParam('用户ID')]
    public string $userId;

    #[MethodParam('类型: static:静态标签、smart：智能标签、sql：SQL标签')]
    public array $type = [];

    public function __construct(
        private readonly AssignLogRepository $assignLogRepository,
        private readonly UserLoaderInterface $userLoader,
    ) {
    }

    public function execute(): array
    {
        $user = $this->userLoader->loadUserByIdentifier($this->userId);
        if ($user === null) {
            throw new ApiException('用户不存在');
        }
        $type = [];
        if (!empty($this->type)) {
            foreach ($this->type as $item) {
                $tmp = TagType::tryFrom($item);
                if ($tmp !== null) {
                    $type[] = $tmp;
                }
            }
        }

        //        $logs = $this->assignLogRepository->findBy(['user' => $user]);
        $query = $this->assignLogRepository->createQueryBuilder('a')
            ->where('a.user = :user')
            ->setParameter('user', $user);

        if (!empty($type)) {
            $query->innerJoin('a.tag', 't')
                ->andWhere('t.type in (:type)')
                ->setParameter('type', $type);
        }
        $logs = $query->orderBy('a.createTime', 'DESC')
            ->getQuery()
            ->getResult();
        $list = [];
        /** @var AssignLog $log */
        foreach ($logs as $log) {
            $tag = $log->getTag();
            if ($tag === null) {
                continue;
            }
            $list[] = [
                'tagInfo' => $log->getTag()->retrievePlainArray(),
                'userInfo' => [
                    'id' => $user->getUserIdentifier(),
                ],
                'assignTime' => $log->getAssignTime()?->format('Y-m-d H:i:s'),
                'unassignTime' => $log->getUnassignTime()?->format('Y-m-d H:i:s'),
                'createTime' => $log->getCreateTime()?->format('Y-m-d H:i:s'),
            ];
        }

        return [
            'list' => $list,
        ];
    }
}
