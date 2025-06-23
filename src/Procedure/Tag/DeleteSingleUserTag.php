<?php

namespace UserTagBundle\Procedure\Tag;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use UserTagBundle\Repository\TagRepository;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodTag('用户标签')]
#[MethodDoc('删除单个标签')]
#[MethodExpose('DeleteSingleUserTag')]
#[Log]
class DeleteSingleUserTag extends LockableProcedure
{
    #[MethodParam('标签ID')]
    public string $id;

    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $tag = $this->tagRepository->find($this->id);
        if ($tag === null) {
            throw new ApiException('找不到标签');
        }

        $this->entityManager->remove($tag);
        $this->entityManager->flush();

        return [
            '__message' => '删除成功',
        ];
    }
}
