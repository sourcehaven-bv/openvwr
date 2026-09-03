<?php

declare(strict_types=1);

namespace App\Documentation;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Component;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

use function __;
use function array_values;
use function basename;
use function class_basename;
use function class_exists;
use function dirname;
use function glob;
use function is_array;
use function is_dir;
use function is_string;

/**
 * Reads the #[DocNote] explanations belonging to a register's sections.
 *
 * The notes sit on the schema methods (getStakeholder() and the like). Those
 * classes are found through the resource name: next to FooResource lives
 * FooResource\FooResourceFormSchemas.
 *
 * A note is matched to a section through the first field the method produces,
 * not through the method name: the method name is English while the section
 * heading is translated, so those would never line up.
 */
class SectionNotes
{
    /** @var array<string, string> note keyed by the name of its first field */
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

                    // The attribute holds a translation key; an untranslated
                    // key would put a raw identifier in the document, so it is
                    // skipped rather than shown.
                    $translation = __($note->key);
                    if (!is_string($translation) || $translation === $note->key) {
                        continue;
                    }

                    $this->notes[$fingerprint] = $this->describer->tidy($translation);
                }
            }
        }
    }

    /**
     * The note belonging to a section, found through the first field inside it.
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
     * The name of the first real field in a series of components.
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
     * A component's children, or an empty list when those can only be resolved
     * while filling in the form.
     *
     * @return array<int, Component>
     */
    public function childrenOf(Component $component): array
    {
        try {
            return array_values($component->getDefaultChildComponents());
        } catch (Throwable) {
            return [];
        }
    }

    private function nameOf(Field $field): ?string
    {
        $name = $field->getName();

        return $name === '' ? null : $name;
    }

    /**
     * The name of the first field a schema method produces.
     *
     * Calling the method is harmless: it only assembles a form definition.
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
     * The classes holding the schema methods that belong to a resource.
     *
     * @param class-string<Resource> $resourceClass
     *
     * @return array<int, class-string>
     */
    private function schemaClassesFor(string $resourceClass): array
    {
        $reflection = new ReflectionClass($resourceClass);

        // FooResource lives in App\Filament\Resources; its schemas live in the
        // subdirectory of the same name.
        $directory = dirname((string) $reflection->getFileName())
            . '/' . class_basename($resourceClass);
        if (!is_dir($directory)) {
            return [];
        }

        $found = glob($directory . '/*Schemas.php');
        $files = $found === false ? [] : $found;

        $namespace = $reflection->getNamespaceName() . '\\' . class_basename($resourceClass);

        $classes = [];
        foreach ($files as $file) {
            /** @var class-string $class */
            $class = $namespace . '\\' . basename($file, '.php');

            if (class_exists($class)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }
}
