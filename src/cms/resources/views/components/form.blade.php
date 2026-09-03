{{--
    A form element for the pages that submit a schema themselves.

    v5 dropped <x-filament-panels::form> without offering a replacement: its own
    pages build the whole page from a schema, so the wrapper had nothing left to
    do there. The pages here still hand-render their form and need the element
    itself, so it lives on as a component of our own rather than as markup
    repeated on each page.

    The guard against a double submit is the one behaviour worth keeping from
    the original: without it an impatient second click on a slow save (the
    two-factor check, an import) fires the request twice.
--}}
@props([
    'method' => 'post',
])

<form
    method="{{ $method }}"
    x-data="{ isProcessing: false }"
    x-on:submit="if (isProcessing) $event.preventDefault()"
    x-on:form-processing-started="isProcessing = true"
    x-on:form-processing-finished="isProcessing = false"
    {{ $attributes->class(['fi-form grid gap-y-6']) }}
>
    {{ $slot }}
</form>
