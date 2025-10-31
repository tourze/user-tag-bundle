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
use UserTagBundle\Entity\SqlRule;
use UserTagBundle\Enum\TagType;

#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodTag(name: '用户标签')]
#[MethodDoc(summary: '创建单个SQL标签')]
#[MethodExpose(method: 'CreateSingleSqlUserTag')]
#[Log]
class CreateSingleSqlUserTag extends CreateSingleUserTag implements ServiceSubscriberInterface
{
    use ServiceMethodsSubscriberTrait;

    #[MethodParam(description: 'SQL语句')]
    public string $sqlStatement;

    #[MethodParam(description: '频率/定时表达式')]
    public string $cronStatement;

    public function execute(): array
    {
        $tag = null;
        $this->getEntityManager()->wrapInTransaction(function () use (&$tag): void {
            $tag = $this->createTag(
                $this->name,
                TagType::SqlTag,
                $this->valid,
                $this->description,
                $this->catalogId,
            );

            $rule = new SqlRule();
            $rule->setTag($tag);
            $rule->setSqlStatement($this->sqlStatement);
            $rule->setCronStatement($this->cronStatement);
            $this->getEntityManager()->persist($rule);
            $this->getEntityManager()->flush();
        });

        if (null === $tag) {
            throw new \RuntimeException('Tag creation failed');
        }

        return [
            'id' => $tag->getId(),
            'name' => $tag->getName(),
            'type' => $tag->getType()->value,
            'valid' => $tag->isValid(),
            'description' => $tag->getDescription(),
            'sqlStatement' => $this->sqlStatement,
            'cronStatement' => $this->cronStatement,
            '__message' => '创建成功',
        ];
    }

    #[SubscribedService]
    private function getEntityManager(): EntityManagerInterface
    {
        /** @var EntityManagerInterface */
        return $this->container->get(__METHOD__);
    }
}
