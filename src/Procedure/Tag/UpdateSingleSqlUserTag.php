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
use UserTagBundle\Enum\TagType;
use UserTagBundle\Repository\CategoryRepository;
use UserTagBundle\Repository\SqlRuleRepository;
use UserTagBundle\Repository\TagRepository;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodTag('用户标签')]
#[MethodDoc('编辑单个SQL标签')]
#[MethodExpose('UpdateSingleSqlUserTag')]
#[Log]
class UpdateSingleSqlUserTag extends LockableProcedure
{
    #[MethodParam('id')]
    public string $id;

    #[MethodParam('SQL语句')]
    public string $sqlStatement;

    #[MethodParam('频率/定时表达式')]
    public string $cronStatement;

    #[MethodParam('用户标签名')]
    public string $name;

    #[MethodParam('标签类型')]
    public string $type;

    #[MethodParam('是否有效')]
    public bool $valid;

    #[MethodParam('描述')]
    public ?string $description = null;

    #[MethodParam('目录ID')]
    public ?string $categoryId = null;

    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryRepository $categoryRepository,
        private readonly SqlRuleRepository $ruleRepository,
    ) {
    }

    public function execute(): array
    {
        $tag = $this->tagRepository->findOneBy(['id' => $this->id]);
        if (!$tag) {
            throw new ApiException('找不到标签');
        }

        $this->entityManager->wrapInTransaction(function () use ($tag) {
            $tag->setName($this->name);
            $tag->setType(TagType::tryFrom($this->type));
            $tag->setDescription($this->description);
            if ($this->categoryId) {
                $category = $this->categoryRepository->find($this->categoryId);
                if (!$category) {
                    throw new ApiException('找不到指定分类');
                }
                $tag->setCategory($category);
            }
            $tag->setValid($this->valid);
            $this->entityManager->persist($tag);
            $this->entityManager->flush();

            $rule = $this->ruleRepository->findOneBy(['tag' => $tag]);
            $rule->setSqlStatement($this->sqlStatement);
            $rule->setCronStatement($this->cronStatement);
            $this->entityManager->persist($rule);
            $this->entityManager->flush();
        });

        return [
            '__message' => '编辑成功',
        ];
    }
}
