<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Components\Uuid\Uuid;
use App\Models\Contracts\TenantAware;
use App\Models\Organisation;
use App\Services\AuthenticationService;
use App\Vendor\MediaLibrary\Media;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

use function abort;
use function abort_if;
use function redirect;
use function str_contains;
use function str_starts_with;

class PrivateMediaController extends Controller
{
    public function __construct(
        private readonly AuthenticationService $authenticationService,
    ) {
    }

    public function __invoke(string $id): RedirectResponse|StreamedResponse
    {
        try {
            $user = $this->authenticationService->user();
        } catch (InvalidArgumentException) {
            return redirect('login');
        }

        $media = Media::where('uuid', $id)->firstOrFail();
        $model = $media->model;
        if ($model === null) {
            abort(Response::HTTP_NOT_FOUND);
        }

        if ($model instanceof TenantAware) {
            // Organisation cannot be retrieved through $model->getOrganisation() because the organisation could be deleted, but this
            // is currently not supported troughout the application. This will be fixed in issue#1465
            // Therefore we have to retrieve it using the organisation_id property.
            $organisationId = $model->getAttribute('organisation_id');
            Assert::isInstanceOf($organisationId, Uuid::class);

            $organisation = Organisation::query()->findOrFail($organisationId);
            Filament::setTenant($organisation, true);
        }

        abort_if($user->cannot('view', $model), Response::HTTP_FORBIDDEN);
        abort_if($media->validated_at === null, Response::HTTP_NOT_FOUND);

        $mimeType = $media->mime_type ?? 'application/octet-stream';
        $contentType = str_starts_with($mimeType, 'text/') && !str_contains($mimeType, 'charset')
            ? $mimeType . '; charset=UTF-8'
            : $mimeType;

        return Storage::disk($media->disk)->response(
            $media->getPathRelativeToRoot(),
            headers: ['Content-Type' => $contentType],
        );
    }
}
