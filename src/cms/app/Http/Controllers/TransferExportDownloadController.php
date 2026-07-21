<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AuthenticationService;
use App\Transfer\Export\BundleExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\InvalidArgumentException;

use function abort_if;
use function abort_unless;
use function basename;
use function redirect;
use function response;
use function sprintf;

class TransferExportDownloadController extends Controller
{
    public function __construct(
        private readonly AuthenticationService $authenticationService,
    ) {
    }

    public function __invoke(Request $request, string $filename): RedirectResponse|BinaryFileResponse
    {
        try {
            $user = $this->authenticationService->user();
        } catch (InvalidArgumentException) {
            return redirect('login');
        }

        abort_if($request->query('user') !== $user->id->toString(), Response::HTTP_FORBIDDEN);

        $disk = Storage::disk(BundleExporter::DISK);
        $path = sprintf('%s/%s', BundleExporter::EXPORT_DIRECTORY, basename($filename));

        abort_unless($disk->exists($path), Response::HTTP_NOT_FOUND);

        return response()->download($disk->path($path));
    }
}
