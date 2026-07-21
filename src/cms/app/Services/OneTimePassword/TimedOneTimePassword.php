<?php

declare(strict_types=1);

namespace App\Services\OneTimePassword;

use App\ValueObjects\OneTimePassword\Code;
use App\ValueObjects\OneTimePassword\Secret;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use OTPHP\TOTP;
use Webmozart\Assert\Assert;

use function strpos;
use function substr;
use function trim;

class TimedOneTimePassword implements OneTimePassword
{
    public function isCodeValid(Code $code, Secret $secret): bool
    {
        $totp = TOTP::createFromSecret($secret->toString());

        return $totp->verify($code->toString());
    }

    public function generateSecretKey(): string
    {
        return TOTP::generate()->getSecret();
    }

    public function generateQRCodeInline(string $label, Secret $secret): string
    {
        Assert::stringNotEmpty($label);

        $otp = TOTP::createFromSecret($secret->toString())
            ->withLabel($label);
        $url = $otp->getProvisioningUri();

        $fill = Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(0, 0, 0));
        $rendererStyle = new RendererStyle(150, 1, null, null, $fill);
        $renderer = new ImageRenderer($rendererStyle, new SvgImageBackEnd());

        $svg = (new Writer($renderer))
            ->writeString($url);

        $strpos = strpos($svg, "\n");
        Assert::integer($strpos);

        return trim(substr($svg, $strpos + 1));
    }
}
