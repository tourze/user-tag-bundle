<?php

namespace UserTagBundle\Procedure\Tag;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceMethodsSubscriberTrait;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use UserTagBundle\Entity\SmartRule;
use UserTagBundle\Enum\TagType;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodTag('用户标签')]
#[MethodDoc('创建单个智能标签')]
#[MethodExpose('CreateSingleSmartUserTag')]
#[Log]
class CreateSingleSmartUserTag extends CreateSingleUserTag implements ServiceSubscriberInterface
{
    use ServiceMethodsSubscriberTrait;

    #[MethodParam('JSON表达式')]
    public array $jsonStatement;

    #[MethodParam('频率/定时表达式')]
    public string $cronStatement;

    public function execute(): array
    {
        $this->getEntityManager()->wrapInTransaction(function () {
            $tag = $this->createTag(
                $this->name,
                TagType::SmartTag,
                $this->valid,
                $this->description,
                $this->categoryId,
            );

            $rule = new SmartRule();
            $rule->setTag($tag);
            $rule->setJsonStatement($this->jsonStatement);
            $rule->setCronStatement($this->cronStatement);
            $this->getEntityManager()->persist($rule);
            $this->getEntityManager()->flush();
        });

        return [
            '__message' => '创建成功',
        ];
    }

    #[SubscribedService]
    private function getEntityManager(): EntityManagerInterface
    {
        return $this->container->get(__METHOD__);
    }
}
