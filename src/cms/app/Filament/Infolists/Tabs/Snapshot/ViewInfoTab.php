<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Tabs\Snapshot;

use App\Config\Feature;
use App\Enums\Authorization\Permission;
use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Enums\Snapshot\SnapshotDataSection;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Facades\DateFormat;
use App\Filament\Infolists\Components\DateTimeEntry;
use App\Filament\Infolists\Components\SnapshotEstablishAction;
use App\Filament\Infolists\Components\SnapshotStateEntry;
use App\Filament\Infolists\Components\SnapshotStatusChangeAction;
use App\Filament\Infolists\Components\SnapshotStatusFlow;
use App\Filament\Infolists\Components\SnapshotUrlEntry;
use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Services\Snapshot\SnapshotApprovalService;
use App\Services\Snapshot\SnapshotDataMarkdownRenderer;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Component;
use Webmozart\Assert\Assert;

use function __;
use function class_basename;
use function redirect;
use function sprintf;
use function view;

class ViewInfoTab extends Tab
{
    public const string SECTION_KEY_STATUS_FLOW = 'status_flow_section';

    public static function make(Htmlable|Closure|string|null $label = null): static
    {
        return parent::make($label)
            ->icon('heroicon-o-information-circle')
            ->schema([
                self::getStatusFlowSection(),
                self::getPropertiesSection(),
                self::getPublicDataSection(),
                self::getPrivateDataSection(),
                self::getRelatedSnapshotSourcesSection(),
                self::getApprovalSection(),
            ]);
    }

    private static function getStatusFlowSection(): Section
    {
        return Section::make(__('snapshot.status_flow'))
            ->key(self::SECTION_KEY_STATUS_FLOW)
            // Beside the flow rather than in the page header: the button changes exactly
            // what the flow shows, so it belongs next to it.
            ->headerActions([
                SnapshotEstablishAction::make(),
                SnapshotStatusChangeAction::make(),
            ])
            ->schema([
                SnapshotStatusFlow::make(),
            ]);
    }

    private static function getPropertiesSection(): Section
    {
        return Section::make(__('snapshot.properties'))
            ->schema([
                TextEntry::make('snapshot_source_type')
                    ->label(__('snapshot.snapshot_source_type'))
                    ->formatStateUsing(static function (string $state): string {
                        return __(sprintf('%s.model_singular', Str::snake(class_basename($state))));
                    }),
                TextEntry::make('snapshotSource')
                    ->label(__('snapshot.snapshot_source_display_name'))
                    ->formatStateUsing(static function (Snapshot $snapshot): ?string {
                        return $snapshot->snapshotSource?->getDisplayName();
                    }),
                TextEntry::make('version')
                    ->label(__('snapshot.version')),
                TextEntry::make('name')
                    ->label(__('snapshot.name')),
                SnapshotStateEntry::make(),
                SnapshotUrlEntry::make(),
                DateTimeEntry::make('established_at')
                    ->label(__('snapshot.established_at')),
                DateTimeEntry::make('replaced_at')
                    ->label(__('snapshot.replaced_at')),
            ])
            ->columns(2);
    }

    private static function getPublicDataSection(): Section
    {
        return Section::make(__('snapshot.public_data'))
            ->description(new HtmlString(view('filament.infolists.components.entries.snapshot_data_description')->render()))
            ->visible(Feature::publishingEnabled())
            ->schema([
                TextEntry::make('snapshotData.public_markdown')
                    ->label('')
                    ->formatStateUsing(
                        static function (Snapshot $snapshot, SnapshotDataMarkdownRenderer $snapshotDataMarkdownRenderer): string {
                            Assert::notNull($snapshot->snapshotData);

                            return $snapshotDataMarkdownRenderer->fromSnapshotMarkdown(
                                $snapshot,
                                $snapshot->snapshotData->public_markdown,
                                SnapshotDataSection::PUBLIC,
                            );
                        },
                    )
                    ->columnSpan(2)
                    ->markdown(),
            ]);
    }

    private static function getPrivateDataSection(): Section
    {
        // Without publishing there is no public counterpart, so the section is
        // simply "the data" instead of "the private data".
        $heading = Feature::publishingEnabled()
            ? __('snapshot.private_data')
            : __('snapshot.data');

        return Section::make($heading)
            ->description(new HtmlString(view('filament.infolists.components.entries.snapshot_data_description')->render()))
            ->schema([
                TextEntry::make('snapshotData.private_markdown')
                    ->label('')
                    ->formatStateUsing(
                        static function (Snapshot $snapshot, SnapshotDataMarkdownRenderer $snapshotDataMarkdownRenderer): string {
                            Assert::notNull($snapshot->snapshotData);

                            return $snapshotDataMarkdownRenderer->fromSnapshotMarkdown(
                                $snapshot,
                                $snapshot->snapshotData->private_markdown,
                                SnapshotDataSection::PRIVATE,
                            );
                        },
                    )
                    ->columnSpan(2)
                    ->markdown(),
            ]);
    }

    private static function getRelatedSnapshotSourcesSection(): Section
    {
        return Section::make(__('snapshot.related_snapshot_sources'))
            ->view('filament.infolists.components.entries.snapshot_related_snapshot_sources')
            ->visible(static function (Snapshot $snapshot): bool {
                return $snapshot->relatedSnapshotSources()->exists();
            });
    }

    private static function getApprovalSection(): Section
    {
        return Section::make(__('snapshot_approval.personal'))
            ->schema([
                self::getApprovalInfo(),
                self::getApprovalButtons(),
            ])
            ->visible(Authorization::hasPermission(Permission::SNAPSHOT_APPROVAL_UPDATE_PERSONAL));
    }

    private static function getApprovalInfo(): Group
    {
        return Group::make([
            TextEntry::make('snapshotApprovals')
                ->label(__('snapshot_approval.status'))
                ->formatStateUsing(static function (Snapshot $snapshot): string {
                    /** @var SnapshotApproval $snapshotApproval */
                    $snapshotApproval = $snapshot->snapshotApprovals()
                        ->where('assigned_to', Authentication::user()->id)
                        ->firstOrFail();

                    return __(sprintf('snapshot_approval_status.%s', $snapshotApproval->status->value));
                }),
            TextEntry::make('snapshotApprovals.updated_at')
                ->label(__('snapshot_approval.reviewed_at'))
                ->formatStateUsing(static function (Snapshot $snapshot) {
                    /** @var SnapshotApproval $snapstotApproval */
                    $snapstotApproval = $snapshot->snapshotApprovals()
                        ->where('assigned_to', Authentication::user()->id)
                        ->firstOrFail();

                    return DateFormat::toDateTime($snapstotApproval->updated_at);
                }),
        ])
            ->visible(static function (Snapshot $snapshot): bool {
                /** @var SnapshotApproval|null $snapshotApproval */
                $snapshotApproval = $snapshot->snapshotApprovals()
                    ->where('assigned_to', Authentication::user()->id)
                    ->first();

                if ($snapshotApproval === null) {
                    return false;
                }

                return $snapshotApproval->status !== SnapshotApprovalStatus::UNKNOWN;
            })
            ->columns();
    }

    private static function getApprovalButtons(): ViewEntry
    {
        return ViewEntry::make('snapshot_approval_actions')
            ->view('filament.infolists.components.entries.snapshot_approval_actions')
            ->registerActions([
                self::getApproveAction(),
                self::getDeclineAction(),
            ]);
    }

    private static function getApproveAction(): Action
    {
        return Action::make('snapshot_approval_approve_action')
            ->label(__('snapshot_approval.approve'))
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->action(
                static function (
                    array $arguments,
                    Component $livewire,
                    Snapshot $snapshot,
                    SnapshotApprovalService $snapshotApprovalService,
                ): void {
                    /** @var SnapshotApproval $snapshotApproval */
                    $snapshotApproval = $snapshot->snapshotApprovals()
                        ->firstOrCreate([
                            'assigned_to' => Authentication::user()->id,
                        ]);
                    $snapshotApprovalService->setStatus(
                        Authentication::user(),
                        $snapshotApproval,
                        SnapshotApprovalStatus::APPROVED,
                    );

                    Assert::keyExists($arguments, 'next');
                    $next = $arguments['next'];
                    Assert::boolean($next);

                    if ($next) {
                        redirect(ViewSnapshot::getNextUrl($snapshot));
                    }
                },
            )
            ->after(static function (Component $livewire): void {
                $livewire->dispatch(ViewSnapshot::REFRESH_LIVEWIRE_COMPONENT);
            })
            ->requiresConfirmation()
            ->modalWidth(Width::TwoExtraLarge)
            ->modalSubmitAction(false)
            ->extraModalFooterActions(static function (Action $action, Snapshot $record): array {
                return [
                    $action->makeModalSubmitAction(__('snapshot_approval.confirm_next'), ['next' => true])
                        ->icon(null)
                        ->visible(static function () use ($record): bool {
                            return ViewSnapshot::
                                getNext($record) !== null;
                        })
                        ->color('success'),
                    $action->makeModalSubmitAction(__('snapshot_approval.confirm'), ['next' => false])
                        ->icon(null)
                        ->color('success'),
                ];
            })
            ->disabled(static function (Snapshot $snapshot): bool {
                /** @var SnapshotApproval|null $approval */
                $approval = $snapshot->snapshotApprovals()
                    ->where('assigned_to', Authentication::user()->id)
                    ->first();

                return $approval?->status === SnapshotApprovalStatus::APPROVED;
            });
    }

    private static function getDeclineAction(): Action
    {
        return Action::make('snapshot_approval_decline_action')
            ->label(__('snapshot_approval.decline'))
            ->color('danger')
            ->icon('heroicon-o-x-mark')
            ->action(
                static function (
                    array $arguments,
                    array $data,
                    Snapshot $snapshot,
                    SnapshotApprovalService $snapshotApprovalService,
                ): void {
                    /** @var SnapshotApproval $snapshotApproval */
                    $snapshotApproval = $snapshot->snapshotApprovals()
                        ->firstOrCreate([
                            'assigned_to' => Authentication::user()->id,
                        ]);

                    $notes = $data['notes'];
                    Assert::nullOrString($notes);

                    $snapshotApprovalService->setStatus(
                        Authentication::user(),
                        $snapshotApproval,
                        SnapshotApprovalStatus::DECLINED,
                        $notes,
                    );

                    Assert::keyExists($arguments, 'next');
                    $next = $arguments['next'];
                    Assert::boolean($next);

                    if ($next) {
                        redirect(ViewSnapshot::getNextUrl($snapshot));
                    }
                },
            )
            ->after(static function (Component $livewire): void {
                $livewire->dispatch(ViewSnapshot::REFRESH_LIVEWIRE_COMPONENT);
            })
            ->schema([
                Textarea::make('notes'),
            ])
            ->requiresConfirmation()
            ->modalWidth(Width::TwoExtraLarge)
            ->modalSubmitAction(false)
            ->extraModalFooterActions(static function (Action $action, Snapshot $record): array {
                return [
                    $action->makeModalSubmitAction(__('snapshot_approval.confirm_next'), ['next' => true])
                        ->icon(null)
                        ->visible(static function () use ($record): bool {
                            return ViewSnapshot::getNext($record) !== null;
                        })
                        ->color('danger'),
                    $action->makeModalSubmitAction(__('snapshot_approval.confirm'), ['next' => false])
                        ->icon(null)
                        ->color('danger'),
                ];
            })
            ->disabled(static function (Snapshot $snapshot): bool {
                /** @var SnapshotApproval|null $approval */
                $approval = $snapshot->snapshotApprovals()
                    ->where('assigned_to', Authentication::user()->id)
                    ->first();

                return $approval?->status === SnapshotApprovalStatus::DECLINED;
            });
    }
}
