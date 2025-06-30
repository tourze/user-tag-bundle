<?php

namespace UserTagBundle\Procedure\Assign;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use UserTagBundle\Repository\TagRepository;
use UserTagBundle\Service\LocalUserTagLoader;

#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodTag(name: '用户标签')]
#[MethodDoc(summary: '为指定用户打标签')]
#[MethodExpose(method: 'AssignTagToBizUser')]
#[Log]
class AssignTagToBizUser extends LockableProcedure
{
    #[MethodParam(description: '用户ID')]
    public string $userId;

    #[MethodParam(description: '标签ID')]
    public string $tagId;

    public function __construct(
        private readonly UserLoaderInterface $userLoader,
        private readonly TagRepository $tagRepository,
        private readonly LocalUserTagLoader  $userTagService,
    ) {
    }

    public function execute(): array
    {
        $user = $this->userLoader->loadUserByIdentifier($this->userId);
        if ($user === null) {
            throw new ApiException('找不到指定用户');
        }

        $tag = $this->tagRepository->find($this->tagId);
        if ($tag === null) {
            throw new ApiException('找不到指定标签');
        }

        $log = $this->userTagService->assignTag($user, $tag);

        return [
            '__message' => '打标成功',
            'id' => $log->getId(),
        ];
    }
}
