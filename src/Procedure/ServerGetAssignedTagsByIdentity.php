<?php

namespace UserTagBundle\Procedure;

use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\JsonRPCPaginatorBundle\Procedure\PaginatorTrait;
use Tourze\UserIDBundle\Service\UserIdentityService;
use UserTagBundle\Entity\AssignLog;
use UserTagBundle\Repository\AssignLogRepository;

#[MethodTag('用户标签')]
#[MethodDoc('为指定身份打标签')]
#[MethodExpose('ServerGetAssignedTagsByIdentity')]
#[Log]
class ServerGetAssignedTagsByIdentity extends LockableProcedure
{
    use PaginatorTrait;

    #[MethodParam('用户标识类型')]
    public string $identityType;

    #[MethodParam('用户标识值')]
    public string $identityValue;

    public function __construct(
        private readonly UserIdentityService $userIdentityService,
        private readonly AssignLogRepository $assignLogRepository,
    ) {
    }

    public function execute(): array
    {
        $user = $this->userIdentityService->findByType($this->identityType, $this->identityValue);
        if ($user === null) {
            throw new ApiException('找不到用户信息');
        }

        $qb = $this->assignLogRepository->createQueryBuilder('a')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.createTime', 'DESC');

        return $this->fetchList($qb, $this->formatItem(...));
    }

    private function formatItem(AssignLog $assignLog): array
    {
        return $assignLog->retrieveApiArray();
    }
}
