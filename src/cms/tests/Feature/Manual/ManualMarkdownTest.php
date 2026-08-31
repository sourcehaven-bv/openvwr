<?php

declare(strict_types=1);

use App\Manual\ManualMarkdown;

it('turns a hint blockquote into a callout without repeating the label', function (): void {
    $html = ManualMarkdown::render('> **Hint**: Sla tussentijds op.');

    expect($html)
        ->toContain('manual-callout--hint')
        ->toContain('Sla tussentijds op.')
        ->not->toContain('<strong>Hint</strong>');
});

it('turns a let op blockquote into a warning callout', function (): void {
    $html = ManualMarkdown::render('> **Let op**: Dit verwijdert alles.');

    expect($html)
        ->toContain('manual-callout--warning')
        ->toContain('Dit verwijdert alles.')
        ->not->toContain('<strong>Let op</strong>');
});

it('leaves a plain blockquote alone', function (): void {
    $html = ManualMarkdown::render('> Een gewoon citaat.');

    expect($html)
        ->toContain('<blockquote>')
        ->not->toContain('manual-callout');
});

it('turns an image into a figure with the alt text as caption', function (): void {
    $html = ManualMarkdown::render('![Het loginscherm](/handleiding/01_welkom/01_login.png)');

    expect($html)
        ->toContain('<figure class="manual-figure">')
        ->toContain('/handleiding/01_welkom/01_login.png')
        ->toContain('<figcaption>Het loginscherm</figcaption>');
});

it('turns a status marker into a coloured status', function (): void {
    $html = ManualMarkdown::render('`status:review:In Review` is de eerste status.');

    expect($html)
        ->toContain('<span class="manual-status manual-status--review">In Review</span>')
        ->not->toContain('status:review:');
});

it('leaves an ordinary code span alone', function (): void {
    $html = ManualMarkdown::render('Exporteer naar `.csv` of `.xlsx`.');

    expect($html)
        ->toContain('<code>.csv</code>')
        ->not->toContain('manual-status');
});

it('strips raw html so the manual text cannot inject markup', function (): void {
    $html = ManualMarkdown::render("<script>alert(1)</script>\n\nGewone tekst.");

    expect($html)
        ->not->toContain('<script>')
        ->not->toContain('alert(1)')
        ->toContain('Gewone tekst.');
});

it('renders links and lists as ordinary markdown', function (): void {
    $html = ManualMarkdown::render("Zie [de naslag](#export).\n\n- een\n- twee\n");

    expect($html)
        ->toContain('<a href="#export">de naslag</a>')
        ->toContain('<li>een</li>');
});
