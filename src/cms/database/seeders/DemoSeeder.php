<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Authorization\Role;
use App\Enums\EntityNumberType;
use App\Enums\RegisterLayout;
use App\Models\Algorithm\AlgorithmPublicationCategory;
use App\Models\Algorithm\AlgorithmStatus;
use App\Models\Algorithm\AlgorithmTheme;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\ContactPerson;
use App\Models\ContactPersonPosition;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\EntityNumberCounter;
use App\Models\Organisation;
use App\Models\Processor;
use App\Models\PublicWebsiteTree;
use App\Models\Receiver;
use App\Models\Responsible;
use App\Models\ResponsibleLegalEntity;
use App\Models\RetentionPeriod;
use App\Models\System;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use function now;
use function sprintf;

/**
 * Demo data for sales meetings and demo deployments.
 *
 * Unlike TestDataSeeder, which generates faker content for local development,
 * every string this seeder writes is hand-authored Dutch (see DemoContent).
 * A demo gets clicked into: a prospect opens a record, scrolls to the third
 * domain and reads whatever is there, so "deserunt quidem aspernatur" anywhere
 * in the tree costs more than the seeder saved.
 *
 * The data is arranged around what needs demonstrating rather than around
 * volume. In particular it guarantees:
 *
 *  - all four version states side by side (in review, approved, established,
 *    obsolete), so the goedkeuringsproces can be shown without editing data;
 *  - a version waiting on a mandate holder's signature, and one that was
 *    declined with a reason, so both outcomes of the approval step are visible;
 *  - documents already expired and about to expire, which drive the
 *    "verlopen documenttermijnen" warnings;
 *  - records due for periodic review, both overdue and upcoming;
 *  - data breaches reported to the AP, still open, and assessed as not
 *    reportable — the three real outcomes;
 *  - one record with data processed outside the EER, to show that section
 *    filled in rather than empty.
 *
 * Run against an empty database:
 *   php artisan migrate:fresh
 *   php artisan db:seed --class=DemoSeeder
 *
 * Deliberately not wired into DatabaseSeeder: demo content should never appear
 * in local development or CI by accident.
 */
class DemoSeeder extends Seeder
{
    use CreatesEntityNumbers;
    use WithoutModelEvents;

    /**
     * Shared password-less login is handled by OTP; every demo user gets a
     * confirmed registration so any account can be logged into during a
     * meeting without a device enrolment detour.
     */
    public function run(): void
    {
        foreach (DemoContent::ORGANISATIONS as $index => $definition) {
            $organisation = $this->createOrganisation($definition);

            // The first organisation is the one a demo opens on, so it gets the
            // full spread of registers and states. The others exist to show
            // multi-tenancy and the organisation switcher, and would only cost
            // time to click through if they were equally deep.
            $isPrimary = $index === 0;

            $users = $this->createUsers($organisation, $isPrimary);
            $lookups = $this->createLookupLists($organisation);
            $related = $this->createRelatedEntities($organisation, $lookups);
            $documents = $this->createDocuments($organisation, $lookups->documentTypes);

            (new DemoAvgRegisterSeeder($organisation, $related, $users))->seed($documents, $isPrimary);

            if ($isPrimary) {
                (new DemoRegisterSeeder($organisation, $lookups, $related))->seed($documents);
            }

            $this->createPublicWebsiteTree($organisation);
        }
    }

    /**
     * @param array{name: string, slug: string, prefix: string, legal_entity: string, review_months: int, content: string} $definition
     */
    private function createOrganisation(array $definition): Organisation
    {
        $legalEntity = ResponsibleLegalEntity::factory()->create([
            'name' => $definition['legal_entity'],
        ]);

        return Organisation::factory()->create([
            'name' => $definition['name'],
            'slug' => $definition['slug'],
            'responsible_legal_entity_id' => $legalEntity->id,
            'review_at_default_in_months' => $definition['review_months'],
            'public_website_content' => $definition['content'],
            // The demo runs behind whatever address the deployment happens to
            // have, so no IP restriction: an allow-list that locks the sales
            // engineer out mid-meeting is the worst possible failure here.
            'allowed_ips' => '*.*.*.*',
            'allowed_email_domains' => [],
            'public_from' => now()->subMonth(),
            'register_entity_number_counter_id' => EntityNumberCounter::factory([
                'prefix' => $definition['prefix'],
                'type' => EntityNumberType::REGISTER,
                'number' => 1,
            ]),
            'databreach_entity_number_counter_id' => EntityNumberCounter::factory([
                'prefix' => sprintf('%sD', $definition['prefix']),
                'type' => EntityNumberType::DATABREACH,
                'number' => 1,
            ]),
        ]);
    }

    /**
     * @return array<string, User>
     */
    private function createUsers(Organisation $organisation, bool $isPrimary): array
    {
        $users = [];

        foreach (DemoContent::USERS as $definition) {
            $role = Role::from($definition['role']);

            $user = User::factory()
                ->hasAttached($organisation)
                ->hasOrganisationRole($role, $organisation)
                ->withValidOtpRegistration()
                ->create([
                    'name' => $definition['name'],
                    'email' => sprintf('%s@%s.example.com', $definition['email'], $organisation->slug),
                    // UserFactory randomises this, which would make the record
                    // detail page render as steps or as one long page depending
                    // on the run. Pin it so the demo is rehearsable.
                    'register_layout' => RegisterLayout::STEPS,
                ]);

            $users[$definition['role']] = $user;
        }

        if ($isPrimary) {
            // A single account holding every role, for the parts of a demo
            // where switching users would only cost time. Kept off the other
            // organisations so the roles-and-permissions story stays honest
            // when the audience asks to see a restricted account.
            $demo = User::factory()
                ->hasAttached($organisation)
                ->withValidOtpRegistration()
                ->create([
                    'name' => 'Demo Gebruiker',
                    'email' => 'demo@example.com',
                    'register_layout' => RegisterLayout::STEPS,
                ]);

            foreach (Role::organisationRoles() as $role) {
                $demo->organisationRoles()->firstOrCreate([
                    'organisation_id' => $organisation->id,
                    'role' => $role->value,
                ]);
            }

            $demo->globalRoles()->firstOrCreate(['role' => Role::FUNCTIONAL_MANAGER->value]);

            $users['demo'] = $demo;
        }

        return $users;
    }

    /**
     * Lookup lists are per-organisation, so each demo organisation needs its
     * own set: without them the dropdowns on the detail pages are empty.
     */
    private function createLookupLists(Organisation $organisation): DemoLookups
    {
        $documentTypes = [];

        foreach (DemoContent::DOCUMENT_TYPES as $name) {
            $documentTypes[$name] = DocumentType::factory()->for($organisation)->create([
                'name' => $name,
            ]);
        }

        $positions = [];

        foreach (DemoContent::CONTACT_POSITIONS as $name) {
            $positions[] = ContactPersonPosition::factory()->for($organisation)->create([
                'name' => $name,
                'enabled' => true,
            ]);
        }

        foreach (DemoContent::RETENTION_PERIODS as $name) {
            RetentionPeriod::factory()->for($organisation)->create([
                'name' => $name,
                'enabled' => true,
            ]);
        }

        $themes = [];
        $statuses = [];
        $categories = [];

        foreach (['Openbare orde en veiligheid', 'Organisatie en bedrijfsvoering', 'Zorg en gezondheid'] as $name) {
            $themes[$name] = AlgorithmTheme::factory()->for($organisation)->create([
                'name' => $name,
                'enabled' => true,
            ]);
        }

        foreach (['In gebruik', 'In ontwikkeling', 'Buiten gebruik'] as $name) {
            $statuses[$name] = AlgorithmStatus::factory()->for($organisation)->create([
                'name' => $name,
                'enabled' => true,
            ]);
        }

        foreach (['Impactvol algoritme', 'Overige algoritmes'] as $name) {
            $categories[$name] = AlgorithmPublicationCategory::factory()->for($organisation)->create([
                'name' => $name,
                'enabled' => true,
            ]);
        }

        return new DemoLookups(
            documentTypes: $documentTypes,
            contactPersonPositions: $positions,
            algorithmThemes: $themes,
            algorithmStatuses: $statuses,
            algorithmCategories: $categories,
        );
    }

    /**
     * Entities shared across records: services, tags, systems, processors,
     * receivers and responsibles. Created once per organisation and attached
     * to the records that use them, so the relation tables at the bottom of a
     * detail page show real cross-references rather than one-offs.
     */
    private function createRelatedEntities(Organisation $organisation, DemoLookups $lookups): DemoRelatedEntities
    {
        $services = [];

        foreach (DemoContent::SERVICES as $name) {
            $services[$name] = AvgResponsibleProcessingRecordService::factory()->for($organisation)->create([
                'name' => $name,
                'enabled' => true,
            ]);
        }

        $tags = [];

        foreach (DemoContent::TAGS as $name) {
            $tags[] = Tag::factory()->for($organisation)->create([
                'name' => $name,
            ]);
        }

        $systems = [];

        foreach (DemoContent::SYSTEMS as $description) {
            $systems[] = System::factory()->for($organisation)->create([
                'description' => $description,
                'import_id' => null,
            ]);
        }

        $processors = [];

        foreach (DemoContent::PROCESSORS as $definition) {
            $processors[] = Processor::factory()
                ->for($organisation)
                ->recycle($organisation)
                ->withAddress()
                ->create([
                    'name' => $definition['name'],
                    'email' => $definition['email'],
                    'phone' => $definition['phone'],
                    'import_id' => null,
                ]);
        }

        $receivers = [];

        foreach (DemoContent::RECEIVERS as $description) {
            $receivers[] = Receiver::factory()->for($organisation)->create([
                'description' => $description,
                'import_id' => null,
            ]);
        }

        $responsible = Responsible::factory()
            ->for($organisation)
            ->recycle($organisation)
            ->withAddress()
            ->create([
                'name' => $organisation->name,
                'import_id' => null,
            ]);

        $contactPersons = [];

        foreach (['Sanne de Groot', 'Hans Willems'] as $name) {
            $contactPersons[] = ContactPerson::factory()
                ->for($organisation)
                ->recycle($organisation)
                ->recycle($lookups->contactPersonPositions[0])
                ->withAddress()
                ->create([
                    'name' => $name,
                    'import_id' => null,
                ]);
        }

        return new DemoRelatedEntities(
            services: $services,
            tags: $tags,
            systems: $systems,
            processors: $processors,
            receivers: $receivers,
            responsible: $responsible,
            contactPersons: $contactPersons,
        );
    }

    /**
     * @param array<string, DocumentType> $documentTypes
     *
     * @return list<Document>
     */
    private function createDocuments(Organisation $organisation, array $documentTypes): array
    {
        $documents = [];

        foreach (DemoContent::DOCUMENTS as $definition) {
            $expiresAt = now()->addMonths($definition['expires_offset_months']);

            $documents[] = Document::factory()
                ->for($organisation)
                ->for($documentTypes[$definition['type']])
                ->create([
                    'name' => $definition['name'],
                    'location' => $definition['location'],
                    'expires_at' => $expiresAt,
                // Warn a month ahead, so the document expiring next month shows
                // as an active warning during the demo rather than silently.
                    'notify_at' => $expiresAt->copy()->subMonth(),
                ]);
        }

        return $documents;
    }

    private function createPublicWebsiteTree(Organisation $organisation): void
    {
        PublicWebsiteTree::factory()
            ->recycle($organisation)
            ->create([
                'title' => $organisation->name,
                'slug' => $organisation->slug,
                'public_from' => now()->subMonth(),
            ]);
    }
}
