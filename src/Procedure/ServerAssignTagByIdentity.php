<?php

namespace UserTagBundle\Procedure;

use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\UserIDBundle\Service\UserIdentityService;
use UserTagBundle\Repository\TagRepository;
use UserTagBundle\Service\LocalUserTagLoader;

#[MethodTag(name: '用户标签')]
#[MethodDoc(summary: '为指定身份打标签')]
#[MethodExpose(method: 'ServerAssignTagByIdentity')]
#[Log]
class ServerAssignTagByIdentity extends LockableProcedure
{
    #[MethodParam(description: '用户标识类型')]
    public string $identityType;

    #[MethodParam(description: '用户标识值')]
    public string $identityValue;

    #[MethodParam(description: '标签ID')]
    public string $tagId;

    public function __construct(
        private readonly LocalUserTagLoader $userTagService,
        private readonly TagRepository $tagRepository,
        private readonly UserIdentityService $userIdentityService,
    ) {
    }

    public function execute(): array
    {
        $identity = $this->userIdentityService->findByType($this->identityType, $this->identityValue);
        if (null === $identity) {
            throw new ApiException('找不到用户信息');
        }

        $user = $identity->getUser();
        if (null === $user) {
            throw new ApiException('身份信息未关联用户');
        }

        $tag = $this->tagRepository->findOneBy([
            'id' => $this->tagId,
            'valid' => true,
        ]);
        if (null === $tag) {
            throw new ApiException('找不到标签信息');
        }

        $this->userTagService->assignTag($user, $tag);

        return [
            '__message' => '分配成功',
        ];
    }
}
