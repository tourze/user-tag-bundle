<?php

namespace UserTagBundle\ExpressionLanguage\Function;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use UserTagBundle\Repository\AssignLogRepository;
use UserTagBundle\Service\LocalUserTagLoader;

/**
 * CRM标签相关函数
 */
#[AutoconfigureTag('ecol.function.provider')]
class TagFunctionProvider implements ExpressionFunctionProviderInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly LocalUserTagLoader  $userTagService,
        private readonly AssignLogRepository $tagUserRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('checkCrmCustomerHasTag', fn (...$args) => sprintf('\%s(%s)', 'checkCrmCustomerHasTag', implode(', ', $args)), function ($values, ...$args) {
                $this->logger->debug('checkCrmCustomerHasTag', [
                    'values' => $values,
                    'args' => $args,
                ]);

                return $this->checkCrmCustomerHasTag($values, ...$args);
            }),
        ];
    }

    /**
     * 判断指定用户是否有特定标签，使用例子： checkCrmCustomerHasTag(user, '黑名单')
     */
    public function checkCrmCustomerHasTag(array $values, ?UserInterface $user, string $tagName, string $tagCategory = ''): bool
    {
        if (!$user) {
            return false;
        }
        $tag = $this->userTagService->getTagByName($tagName, $tagCategory);
        $item = $this->tagUserRepository->findOneBy([
            'user' => $user,
            'tag' => $tag,
            'valid' => true,
        ]);

        return null !== $item;
    }
}
