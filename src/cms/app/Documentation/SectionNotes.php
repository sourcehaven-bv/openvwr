<?php

declare(strict_types=1);

namespace App\Documentation;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

use function array_values;
use function basename;
use function class_basename;
use function class_exists;
use function dirname;
use function glob;
use function is_array;
use function is_dir;

/**
 * Leest de #[DocNote]-toelichtingen die bij de secties van een register horen.
 *
 * De notities staan bij de schema-methodes (getStakeholder() en dergelijke).
 * Die klassen worden gevonden via de naam van de resource: naast FooResource
 * hoort FooResource\FooResourceFormSchemas.
 *
 * De koppeling met een sectie loopt niet via de methodenaam - die is Engels en
 * de sectiekop Nederlands - maar via het eerste veld dat de methode oplevert.
 * Dat veld komt terug in de sectie waar de methode bij hoort.
 */
class SectionNotes
{
    /** @var array<string, string> notitie per naam van het eerste veld */
    private array $notes = [];

    public function __construct(
        private readonly FormEnvironment $environment,
        private readonly FieldDescriber $describer,
    ) {
    }

    /**
     * @param class-string<Resource> $resourceClass
     */
    public function load(string $resourceClass): void
    {
        $this->notes = [];

        foreach ($this->schemaClassesFor($resourceClass) as $schemaClass) {
            $reflection = new ReflectionClass($schemaClass);

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_STATIC) as $method) {
                foreach ($method->getAttributes(DocNote::class) as $attribute) {
                    /** @var DocNote $note */
                    $note = $attribute->newInstance();

                    $fingerprint = $this->fingerprintOf($method);
                    if ($fingerprint === null) {
                        continue;
                    }

                    $this->notes[$fingerprint] = $this->describer->tidy($note->text);
                }
            }
        }
    }

    /**
     * De notitie bij een sectie, gevonden via het eerste veld dat erin staat.
     *
     * @param array<int, Component> $sectionComponents
     */
    public function forSection(array $sectionComponents): ?string
    {
        $fingerprint = $this->firstFieldName($sectionComponents);

        if ($fingerprint === null) {
            return null;
        }

        return $this->notes[$fingerprint] ?? null;
    }

    /**
     * De naam van het eerste echte veld in een reeks componenten.
     *
     * @param array<mixed> $components
     */
    public function firstFieldName(array $components): ?string
    {
        foreach ($components as $component) {
            if (!$component instanceof Component) {
                continue;
            }

            if ($component instanceof Hidden || $component instanceof Placeholder) {
                continue;
            }

            if ($component instanceof Field) {
                $name = $this->nameOf($component);
                if ($name !== null) {
                    return $name;
                }
            }

            $nested = $this->firstFieldName($this->childrenOf($component));
            if ($nested !== null) {
                return $nested;
            }
        }

        return null;
    }

    /**
     * De kinderen van een component, of een lege lijst als die pas tijdens het
     * invullen te bepalen zijn.
     *
     * @return array<int, Component>
     */
    public function childrenOf(Component $component): array
    {
        try {
            return array_values($component->getChildComponents());
        } catch (Throwable) {
            return [];
        }
    }

    private function nameOf(Field $field): ?string
    {
        try {
            $name = $field->getName();
        } catch (Throwable) {
            return null;
        }

        return $name === '' ? null : $name;
    }

    /**
     * De naam van het eerste veld dat een schema-methode oplevert.
     *
     * Het aanroepen van de methode is ongevaarlijk: er wordt alleen een
     * formulierdefinitie opgebouwd.
     */
    private function fingerprintOf(ReflectionMethod $method): ?string
    {
        if ($method->getNumberOfRequiredParameters() > 0) {
            return null;
        }

        try {
            $components = $this->environment->run(static fn (): mixed => $method->invoke(null));
        } catch (Throwable) {
            return null;
        }

        if (!is_array($components)) {
            return null;
        }

        return $this->firstFieldName($components);
    }

    /**
     * De klassen met schema-methodes die bij een resource horen.
     *
     * @param class-string<Resource> $resourceClass
     *
     * @return array<int, class-string>
     */
    private function schemaClassesFor(string $resourceClass): array
    {
        $reflection = new ReflectionClass($resourceClass);

        $fileName = $reflection->getFileName();
        if ($fileName === false) {
            return [];
        }

        // FooResource staat in App\Filament\Resources; de bijbehorende schema's
        // in de gelijknamige submap daaronder.
        $directory = dirname($fileName) . '/' . class_basename($resourceClass);
        if (!is_dir($directory)) {
            return [];
        }

        $files = glob($directory . '/*Schemas.php');
        if ($files === false) {
            return [];
        }

        $namespace = $reflection->getNamespaceName() . '\\' . class_basename($resourceClass);

        $classes = [];
        foreach ($files as $file) {
            /** @var class-string $class */
            $class = $namespace . '\\' . basename($file, '.php');

            if (!class_exists($class)) {
                continue;
            }

            $classes[] = $class;
        }

        return $classes;
    }
}
