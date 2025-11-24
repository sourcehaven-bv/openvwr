<?php

declare(strict_types=1);

namespace App\Http;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;
use Spatie\Csp\Scheme;

class CspPolicy implements Preset
{
    public function configure(Policy $policy): void
    {
        $policy
            ->add(Directive::BASE, Keyword::SELF)
            ->add(Directive::CONNECT, Keyword::SELF)
            ->add(Directive::DEFAULT, Keyword::UNSAFE_INLINE)
            ->add(Directive::FONT, Keyword::SELF)
            ->add(Directive::FONT, Keyword::UNSAFE_INLINE)
            ->add(Directive::FONT, Scheme::DATA)
            ->add(Directive::FORM_ACTION, Keyword::SELF)
            ->add(Directive::IMG, Keyword::UNSAFE_INLINE)
            ->add(Directive::IMG, Keyword::SELF)
            ->add(Directive::IMG, Scheme::BLOB)
            ->add(Directive::IMG, Scheme::DATA)
            ->add(Directive::MEDIA, Keyword::SELF)
            ->add(Directive::MEDIA, Keyword::UNSAFE_INLINE)
            ->add(Directive::SCRIPT, Keyword::SELF)
            ->add(Directive::SCRIPT, Keyword::UNSAFE_EVAL)
            ->add(Directive::SCRIPT, Keyword::UNSAFE_INLINE)
            ->add(Directive::STYLE, Keyword::SELF)
            ->add(Directive::STYLE, Keyword::UNSAFE_INLINE)
            ->add(Directive::WORKER, Keyword::SELF)
            ->add(Directive::WORKER, Scheme::BLOB);
    }
}
