<?php

namespace UserTagBundle\Procedure;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use UserTagBundle\Repository\TagRepository;
use UserTagBundle\Service\LocalUserTagLoader;

#[MethodTag(name: '用户标签')]
#[MethodDoc(summary: '为指定用户分配标签')]
#[MethodExpose(method: 'ServerAssignCrmTag')]
#[Log]
class ServerAssignCrmTag extends LockableProcedure
{
    #[MethodParam(description: '用户唯一标志')]
    public string $identity;

    #[MethodParam(description: '标签ID')]
    public string $tagId;

    public function __construct(
        private readonly LocalUserTagLoader $userTagService,
        private readonly TagRepository $tagRepository,
        private readonly UserLoaderInterface $userLoader,
    ) {
    }

    public function execute(): array
    {
        $user = $this->userLoader->loadUserByIdentifier($this->identity);
        if (null === $user) {
            throw new ApiException('找不到用户信息');
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
