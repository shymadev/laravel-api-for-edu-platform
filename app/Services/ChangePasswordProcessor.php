<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\ChangePasswordDTO;
use App\Events\UserPasswordChanged;
use App\Services\Strategy\ChangePassword\ChangePasswordStrategy;
use RuntimeException;

/**
 * Service responsible for processing user password changes.
 */
final readonly class ChangePasswordProcessor
{
    /**
     * Constructs a new ChangePasswordProcessor instance.
     *
     * @param array<ChangePasswordStrategy> $strategies
     */
    public function __construct(protected array $strategies)
    {
    }

    /**
     * Processes the password change request using the appropriate strategy.
     *
     * @param ChangePasswordDTO $dto
     *
     * @return boolean
     *
     * @throws RuntimeException
     */
    public function process(ChangePasswordDTO $dto): bool
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($dto)) {
                $result = $strategy->handle($dto);

                if ($result) {
                    event(new UserPasswordChanged($dto->user));
                }

                return $result;
            }
        }

        throw new RuntimeException('No suitable strategy found for processing password change.');
    }
}
