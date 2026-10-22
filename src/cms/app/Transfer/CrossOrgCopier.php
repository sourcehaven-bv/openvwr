<?php

declare(strict_types=1);

namespace App\Transfer;

use App\Enums\Authorization\Permission;
use App\Facades\AuditLog;
use App\Models\Organisation;
use App\Models\User;
use App\Services\AuditLog\Transfer\TransferFailureReason;
use App\Services\AuditLog\Transfer\TransferImportEvent;
use App\Services\CrossOrgAuthorization;
use App\Transfer\Export\BundleBuilder;
use App\Transfer\Import\BundleImporter;
use App\Transfer\Import\LibraryMediaResolver;
use App\Transfer\Import\TransferImportResult;
use Throwable;

/**
 * Copies register content directly from one organisation into another on the same instance,
 * without an intermediate zip. Reuses the export graph collection and the import pipeline;
 * media is read straight from the source media library.
 *
 * Authorization is enforced here (not only in the UI): the acting user must hold the import
 * permission *in the destination organisation*, evaluated with their roles there.
 */
readonly class CrossOrgCopier
{
    public function __construct(
        private BundleBuilder $bundleBuilder,
        private BundleImporter $bundleImporter,
        private CrossOrgAuthorization $crossOrgAuthorization,
    ) {
    }

    /**
     * @param list<string> $recordIds
     * @param array<string, list<string>> $selectedRelated selected related ids, keyed by relation name
     * @param array<string, array{selected: bool, strategy: ?string}> $plan per-entity import decisions
     *
     * @throws TransferException when the user may not write into the destination organisation
     */
    public function copy(
        TransferEntityType $recordType,
        array $recordIds,
        array $selectedRelated,
        array $plan,
        Organisation $source,
        Organisation $destination,
        User $user,
    ): TransferImportResult {
        try {
            $this->assertAuthorized($user, $source, $destination);
        } catch (TransferException $exception) {
            // assertAuthorized() only throws the fixed messages defined below.
            $this->audit($user, $source, $destination, null, $exception->getMessage());

            throw $exception;
        }

        try {
            $bundle = $this->bundleBuilder->build($recordType, $recordIds, $selectedRelated, $source);
            $result = $this->bundleImporter->import($bundle, new LibraryMediaResolver(), $plan, $destination, $user);
        } catch (Throwable $exception) {
            $this->audit($user, $source, $destination, null, TransferFailureReason::from($exception));

            throw $exception;
        }

        $this->audit($user, $source, $destination, $result);

        return $result;
    }

    private function audit(
        User $user,
        Organisation $source,
        Organisation $destination,
        ?TransferImportResult $result,
        ?string $failedReason = null,
    ): void {
        AuditLog::register(new TransferImportEvent(
            $user,
            TransferImportEvent::METHOD_COPY,
            $destination->id->toString(),
            $source->id->toString(),
            $result,
            $failedReason,
        ));
    }

    /**
     * @throws TransferException
     */
    private function assertAuthorized(User $user, Organisation $source, Organisation $destination): void
    {
        if ($source->id->equals($destination->id)) {
            throw new TransferException('cannot copy an organisation into itself');
        }

        if (!$this->crossOrgAuthorization->userHasPermissionInOrganisation($user, $source, Permission::EXPORT)) {
            throw new TransferException('user may not export from the source organisation');
        }

        if (!$this->crossOrgAuthorization->userHasPermissionInOrganisation($user, $destination, Permission::CORE_ENTITY_IMPORT)) {
            throw new TransferException('user may not import into the destination organisation');
        }
    }
}
