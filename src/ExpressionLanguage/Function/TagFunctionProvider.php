<?php

namespace UserTagBundle\ExpressionLanguage\Function;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use UserTagBundle\Repository\AssignLogRepository;
use UserTagBundle\Service\LocalUserTagLoader;

/**
 * CRM标签相关函数
 */
#[Autoconfigure(public: true)]
#[AutoconfigureTag(name: 'ecol.function.provider')]
#[WithMonologChannel(channel: 'user_tag')]
readonly class TagFunctionProvider implements ExpressionFunctionProviderInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private LocalUserTagLoader $userTagService,
        private AssignLogRepository $tagUserRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('checkCrmCustomerHasTag', fn (...$args) => sprintf('\%s(%s)', 'checkCrmCustomerHasTag', implode(', ', (array) $args)), function ($values, ...$args) {
                $this->logger->debug('checkCrmCustomerHasTag', [
                    'values' => $values,
                    'args' => $args,
                ]);

                /** @var array<string, mixed> $values */
                /** @var UserInterface|null $user */
                $user = $args[0] ?? null;
                /** @var string $tagName */
                $tagName = $args[1] ?? '';
                /** @var string $tagCategory */
                $tagCategory = $args[2] ?? '';

                return $this->checkCrmCustomerHasTag($values, $user, $tagName, $tagCategory);
            }),
        ];
    }

    /**
     * 判断指定用户是否有特定标签，使用例子： checkCrmCustomerHasTag(user, '黑名单')
     *
     * @param array<string, mixed> $values
     */
    public function checkCrmCustomerHasTag(array $values, ?UserInterface $user, string $tagName, string $tagCategory = ''): bool
    {
        if (null === $user) {
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
