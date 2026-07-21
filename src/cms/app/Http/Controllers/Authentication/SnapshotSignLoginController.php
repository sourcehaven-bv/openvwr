<?php

declare(strict_types=1);

namespace App\Http\Controllers\Authentication;

use App\Enums\RouteName;
use App\Facades\Authentication;
use App\Filament\Resources\PersonalSnapshotApprovalResource;
use App\Filament\Resources\PersonalSnapshotApprovalResource\Pages\ListPersonalSnapshotApprovalItems;
use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\Snapshot;
use App\Models\User;
use App\Services\AuditLog\AuditLogger;
use App\Services\AuditLog\Authentication\AuthenticationSuccessEvent;
use App\Services\UserLoginToken\UserLoginService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\Factory;
use Throwable;

use function redirect;
use function sprintf;

class SnapshotSignLoginController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly UserLoginService $userLoginService,
        private readonly Factory $view,
    ) {
    }

    public function openBatch(Request $request): RedirectResponse|View
    {
        $organisation = $this->getOrganisationFromRequest($request);
        $user = $this->getUserFromRequest($request);

        try {
            $auhtenticatedUser = Authentication::user();
            $this->auditLogger->register(new AuthenticationSuccessEvent($auhtenticatedUser));
        } catch (Throwable) {
            return $this->view->make('auth.snapshot_sign', [
                'postUrl' => URL::signedRoute(RouteName::SNAPSHOT_SIGN_LOGIN_BATCH_LOGIN, [
                    'organisation_id' => $organisation->id->toString(),
                    'user_id' => $user->id->toString(),
                ]),
                'user' => $user,
            ]);
        }

        return redirect($this->getDestinationUrlForPersonalSnapshotApprovals($organisation));
    }

    public function loginBatch(Request $request): View|RedirectResponse
    {
        $organisation = $this->getOrganisationFromRequest($request);
        $user = $this->getUserFromRequest($request);
        $destination = $this->getDestinationUrlForPersonalSnapshotApprovals($organisation);

        $this->userLoginService->sendSnapshotSignLoginLink($user, $destination);
        Session::flash('snapshot_sign_login_link_success');

        return redirect(URL::signedRoute(RouteName::SNAPSHOT_SIGN_LOGIN_BATCH_OPEN, [
            'organisation_id' => $organisation->id->toString(),
            'user_id' => $user->id->toString(),
        ]));
    }

    public function openSingle(Request $request): RedirectResponse|View
    {
        $snapshot = $this->getSnapshotFromRequest($request);
        $user = $this->getUserFromRequest($request);

        try {
            $auhtenticatedUser = Authentication::user();
            $this->auditLogger->register(new AuthenticationSuccessEvent($auhtenticatedUser));
        } catch (Throwable) {
            return $this->view->make('auth.snapshot_sign', [
                'postUrl' => URL::signedRoute(RouteName::SNAPSHOT_SIGN_LOGIN_SINGLE_LOGIN, [
                    'snapshot_id' => $snapshot->id->toString(),
                    'user_id' => $user->id->toString(),
                ]),
                'user' => $user,
            ]);
        }

        return redirect($this->getDestinatioUrlForSnapshot($snapshot));
    }

    public function loginSingle(Request $request): View|RedirectResponse
    {
        $snapshot = $this->getSnapshotFromRequest($request);
        $user = $this->getUserFromRequest($request);

        $destination = $this->getDestinatioUrlForSnapshot($snapshot);
        $this->userLoginService->sendSnapshotSignLoginLink($user, $destination);
        Session::flash('snapshot_sign_login_link_success');

        return redirect(URL::signedRoute(RouteName::SNAPSHOT_SIGN_LOGIN_SINGLE_OPEN, [
            'snapshot_id' => $snapshot->id->toString(),
            'user_id' => $user->id->toString(),
        ]));
    }

    private function getOrganisationFromRequest(Request $request): Organisation
    {
        return Organisation::where('id', $request->query('organisation_id'))
            ->firstOrFail();
    }

    private function getSnapshotFromRequest(Request $request): Snapshot
    {
        return Snapshot::where('id', $request->query('snapshot_id'))
            ->firstOrFail();
    }

    private function getUserFromRequest(Request $request): User
    {
        return User::where('id', $request->query('user_id'))
            ->firstOrFail();
    }

    private function getDestinationUrlForPersonalSnapshotApprovals(Organisation $organisation): string
    {
        return PersonalSnapshotApprovalResource::getUrl(parameters: [
            'tenant' => $organisation,
            'activeTab' => ListPersonalSnapshotApprovalItems::TAB_ID_UNREVIEWED,
        ]);
    }

    private function getDestinatioUrlForSnapshot(Snapshot $snapshot): string
    {
        return ViewSnapshot::getUrl([
            'record' => $snapshot,
            'tenant' => $snapshot->organisation,
            'tab' => sprintf('-%s-tab', ViewSnapshot::TAB_ID_INFO),
        ]);
    }
}
