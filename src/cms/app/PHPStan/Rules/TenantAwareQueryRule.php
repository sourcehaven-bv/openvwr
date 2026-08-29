<?php

declare(strict_types=1);

namespace App\PHPStan\Rules;

use App\Models\Contracts\TenantAware;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

use function in_array;
use function sprintf;
use function str_starts_with;

/**
 * Prevents request-facing Filament code from starting an unscoped query for a
 * tenant-aware model. Cross-tenant jobs and maintenance services are outside
 * this boundary because they have different, explicit scoping requirements.
 *
 * @implements Rule<StaticCall>
 */
class TenantAwareQueryRule implements Rule
{
    private const QUERY_ENTRY_METHODS = [
        'all',
        'avg',
        'count',
        'cursor',
        'doesntExist',
        'exists',
        'find',
        'findMany',
        'findOrFail',
        'first',
        'firstWhere',
        'firstOrCreate',
        'firstOrFail',
        'firstOrNew',
        'get',
        'latest',
        'lazy',
        'max',
        'min',
        'newQuery',
        'oldest',
        'onlyTrashed',
        'orderBy',
        'paginate',
        'pluck',
        'query',
        'select',
        'simplePaginate',
        'sole',
        'sum',
        'updateOrCreate',
        'value',
        'where',
        'whereIn',
        'whereKey',
        'whereNot',
        'whereNotIn',
        'with',
        'withTrashed',
        'withoutGlobalScope',
        'withoutGlobalScopes',
    ];

    public function __construct(private readonly ReflectionProvider $reflectionProvider)
    {
    }

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $namespace = $scope->getNamespace();
        if ($namespace !== 'App\\Filament' && !str_starts_with($namespace ?? '', 'App\\Filament\\')) {
            return [];
        }

        if (!$node->class instanceof Name || !$node->name instanceof Identifier) {
            return [];
        }

        $method = $node->name->toString();
        if (!$this->isQueryEntryMethod($method)) {
            return [];
        }

        $model = $scope->resolveName($node->class);
        if (!$this->reflectionProvider->hasClass($model)) {
            return [];
        }

        $reflection = $this->reflectionProvider->getClass($model);
        if (!$reflection->implementsInterface(TenantAware::class)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Start queries for tenant-aware model %s with %s::tenantQuery().',
                $reflection->getDisplayName(),
                $reflection->getDisplayName(),
            ))
                ->identifier('openvwr.tenantAwareQuery')
                ->build(),
        ];
    }

    private function isQueryEntryMethod(string $method): bool
    {
        if (in_array($method, self::QUERY_ENTRY_METHODS, true)) {
            return true;
        }

        foreach (['find', 'orWhere', 'orderBy', 'where', 'with'] as $prefix) {
            if (str_starts_with($method, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
