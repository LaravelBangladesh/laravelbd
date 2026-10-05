<?php

namespace Database\Seeders;

use App\Domain\Cfp\Enums\ProposalKind;
use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\Company;
use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Enums\EventType;
use App\Domain\Events\Enums\QuestionKind;
use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Enums\SessionKind;
use App\Domain\Events\Enums\SpeakerRole;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Local-only demo content so the UI has something realistic to render.
 * Everything keys off a slug or an email so a second run is a no-op.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('role', UserRole::Admin)->first();

        $speakers = $this->speakers();
        $this->pastEvents($admin?->id, $speakers);
        $this->draftEvent($admin?->id);
        $this->registrationClosedEvent($admin?->id);
        $this->directory($admin?->id);
        $this->proposals();
        $this->registrations();
        $this->incompleteMember();
    }

    /**
     * Demo speakers are members with a complete profile, keyed by a short
     * name the event rows below refer to.
     *
     * @return array<string, User>
     */
    private function speakers(): array
    {
        $rows = [
            ['tahmid-rahman', 'Tahmid Rahman', 'Senior Engineer', 'Brain Station 23', 'Builds payment systems with Laravel and has been part of the community since the first Dhaka meetup.'],
            ['nusrat-jahan', 'Nusrat Jahan', 'Backend Lead', 'Therap BD', 'Works on healthcare platforms at scale and writes about testing and queues.'],
            ['arif-hossain', 'Arif Hossain', 'Freelance Developer', null, 'Full-time freelancer from Chattogram, building SaaS products for clients abroad.'],
            ['sadia-akter', 'Sadia Akter', 'Software Engineer', 'Cefalo', 'Focuses on API design and developer tooling, and mentors newcomers to PHP.'],
            ['rifat-chowdhury', 'Rifat Chowdhury', 'CTO', 'Kaz Software', 'Has run engineering teams in Dhaka for a decade and speaks about architecture trade-offs.'],
        ];

        $speakers = [];

        foreach ($rows as [$key, $name, $title, $company, $bio]) {
            $speakers[$key] = $this->member(str_replace('-', '.', $key).'@example.com', $name, $title, $company);
            $speakers[$key]->forceFill(['bio_en' => $bio])->save();
        }

        return $speakers;
    }

    /**
     * @param  array<string, User>  $speakers
     */
    private function pastEvents(?string $adminId, array $speakers): void
    {
        $events = [
            [
                'slug' => 'laravel-day-dhaka',
                'type' => EventType::Conference,
                'title_en' => 'Laravel Day Dhaka',
                'title_bn' => 'লারাভেল ডে ঢাকা',
                'excerpt_en' => 'A full day of talks, workshops and hallway conversations for the Laravel community.',
                'venue_name' => 'Dhaka',
                'months_ago' => 4,
                'capacity' => 200,
                'recording' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'speakers' => ['tahmid-rahman', 'nusrat-jahan'],
                'sessions' => [
                    ['Scaling a Laravel monolith', SessionKind::Keynote, 'স্কেলিং লারাভেল মনোলিথ', 'tahmid-rahman'],
                    ['Queues in production', SessionKind::Talk, null, 'nusrat-jahan'],
                    ['Testing what matters', SessionKind::Talk, null, 'nusrat-jahan'],
                ],
            ],
            [
                'slug' => 'chattogram-laravel-workshop',
                'type' => EventType::Workshop,
                'title_en' => 'Chattogram Laravel Workshop',
                'title_bn' => 'চট্টগ্রাম লারাভেল ওয়ার্কশপ',
                'excerpt_en' => 'A hands-on afternoon building an API from scratch with Laravel.',
                'venue_name' => 'Chattogram',
                'months_ago' => 8,
                'capacity' => 40,
                'recording' => null,
                'speakers' => ['arif-hossain'],
                'sessions' => [
                    ['Setting up the project', SessionKind::Workshop, null, 'arif-hossain'],
                    ['Building the API', SessionKind::Workshop, null, 'arif-hossain'],
                ],
            ],
            [
                'slug' => 'sylhet-laravel-meetup',
                'type' => EventType::Meetup,
                'title_en' => 'Sylhet Laravel Meetup',
                'title_bn' => 'সিলেট লারাভেল মিটআপ',
                'excerpt_en' => 'The first Laravel meetup in Sylhet, with talks from local developers.',
                'venue_name' => 'Sylhet',
                'months_ago' => 12,
                'capacity' => 60,
                'recording' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'speakers' => ['sadia-akter', 'rifat-chowdhury'],
                'sessions' => [
                    ['Why we moved to Laravel', SessionKind::Talk, null, 'sadia-akter'],
                    ['Open floor', SessionKind::Panel, null, 'rifat-chowdhury'],
                    ['Closing notes', SessionKind::Other, null, 'sadia-akter'],
                ],
            ],
            [
                'slug' => 'dhaka-laravel-meetup',
                'type' => EventType::Meetup,
                'title_en' => 'Dhaka Laravel Meetup',
                'title_bn' => 'ঢাকা লারাভেল মিটআপ',
                'excerpt_en' => 'An evening of short talks and questions from the floor.',
                'venue_name' => 'Dhaka',
                'months_ago' => 17,
                'capacity' => 90,
                'recording' => null,
                'speakers' => ['tahmid-rahman', 'sadia-akter'],
                'sessions' => [
                    ['Eloquent beyond the basics', SessionKind::Talk, 'ইলোকোয়েন্ট, বেসিকের বাইরে', 'tahmid-rahman'],
                    ['Deploying without downtime', SessionKind::Talk, null, 'sadia-akter'],
                ],
            ],
        ];

        foreach ($events as $row) {
            $startsAt = Carbon::now('Asia/Dhaka')
                ->subMonths($row['months_ago'])
                ->setTime(15, 0)
                ->utc();

            $event = Event::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'type' => $row['type'],
                    'status' => EventStatus::Published,
                    'title_en' => $row['title_en'],
                    'title_bn' => $row['title_bn'],
                    'excerpt_en' => $row['excerpt_en'],
                    'description_en' => $row['excerpt_en'].' Organised by volunteers from the Laravel Bangladesh community.',
                    'venue_name' => $row['venue_name'],
                    'starts_at' => $startsAt,
                    'ends_at' => $startsAt->copy()->addHours(4),
                    'capacity' => $row['capacity'],
                    'published_at' => $startsAt->copy()->subMonth(),
                    'created_by' => $adminId,
                ],
            );

            $event->speakers()->syncWithoutDetaching(
                collect($row['speakers'])
                    ->mapWithKeys(fn (string $slug, int $index) => [
                        $speakers[$slug]->id => [
                            'role' => $index === 0
                                ? SpeakerRole::Host->value
                                : SpeakerRole::Speaker->value,
                        ],
                    ])
                    ->all(),
            );

            if ($event->sessions()->doesntExist()) {
                foreach ($row['sessions'] as $index => [$title, $kind, $titleBn, $speakerSlug]) {
                    $sessionStart = $startsAt->copy()->addMinutes(30 + ($index * 60));

                    $session = $event->sessions()->create([
                        'title_en' => $title,
                        'title_bn' => $titleBn,
                        'kind' => $kind,
                        'starts_at' => $sessionStart,
                        'ends_at' => $sessionStart->copy()->addMinutes(45),
                        'room' => 'Main hall',
                        'recording_url' => $index === 0 ? $row['recording'] : null,
                        'sort_order' => $index,
                    ]);

                    $session->speakers()->syncWithoutDetaching([
                        $speakers[$speakerSlug]->id => [
                            'role' => SpeakerRole::Speaker->value,
                        ],
                    ]);
                }
            }
        }
    }

    private function draftEvent(?string $adminId): void
    {
        $startsAt = Carbon::now('Asia/Dhaka')->addMonths(2)->setTime(18, 0)->utc();

        Event::query()->updateOrCreate(
            ['slug' => 'rajshahi-laravel-meetup-draft'],
            [
                'type' => EventType::Meetup,
                'status' => EventStatus::Draft,
                'title_en' => 'Rajshahi Laravel Meetup',
                'title_bn' => 'রাজশাহী লারাভেল মিটআপ',
                'excerpt_en' => 'Still being planned. Venue and speakers are not confirmed yet.',
                'venue_name' => 'Rajshahi',
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addHours(3),
                'capacity' => 50,
                'created_by' => $adminId,
            ],
        );
    }

    private function registrationClosedEvent(?string $adminId): void
    {
        $startsAt = Carbon::now('Asia/Dhaka')->addWeeks(6)->setTime(18, 0)->utc();

        Event::query()->updateOrCreate(
            ['slug' => 'khulna-laravel-meetup'],
            [
                'type' => EventType::Meetup,
                'status' => EventStatus::Published,
                'title_en' => 'Khulna Laravel Meetup',
                'title_bn' => 'খুলনা লারাভেল মিটআপ',
                'excerpt_en' => 'Registration has closed for this meetup, the room is full.',
                'description_en' => 'An evening of short talks in Khulna. Registration is closed.',
                'venue_name' => 'Khulna',
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addHours(3),
                'capacity' => 40,
                'registration_enabled' => false,
                'published_at' => now(),
                'created_by' => $adminId,
            ],
        );
    }

    /**
     * Demo artisans become members with example emails, so every person in
     * the directory is a user like in production.
     */
    private function directory(?string $adminId): void
    {
        /** @var list<array{string, string, string, string|null, string, bool, string}> $people */
        $people = [
            ['ahsan.habib@example.com', 'Ahsan Habib', 'Backend Developer', 'Brain Station 23', 'Dhaka', true, 'Builds Laravel APIs for fintech clients and helps run the Dhaka meetups.'],
            ['farzana.islam@example.com', 'Farzana Islam', 'Full-stack Developer', 'Cefalo', 'Dhaka', true, 'Works across Laravel and React, and writes about frontend tooling.'],
            ['mahmudul.hasan@example.com', 'Mahmudul Hasan', 'Freelance Developer', null, 'Chattogram', true, 'Freelances on Laravel projects for clients in Europe and Australia.'],
            ['tanvir.ahmed@example.com', 'Tanvir Ahmed', 'Software Engineer', 'Therap BD', 'Dhaka', true, 'Builds healthcare software and is interested in queues and background jobs.'],
            ['sharmin.sultana@example.com', 'Sharmin Sultana', 'Engineering Manager', 'Kaz Software', 'Sylhet', false, 'Leads a product team in Sylhet and mentors junior Laravel developers.'],
        ];

        foreach ($people as [$email, $name, $title, $company, $city, $isListed, $bio]) {
            $this->member($email, $name, $title, $company, $isListed)
                ->forceFill(['city' => $city, 'bio_en' => $bio])
                ->save();
        }

        /** @var list<array{string, string, string, string, string}> $companies */
        $companies = [
            ['brain-station-23', 'Brain Station 23', 'Software company', 'Dhaka', 'One of the largest software companies in Bangladesh, with a long-running Laravel practice.'],
            ['kaz-software', 'Kaz Software', 'Software studio', 'Dhaka', 'A Dhaka studio building custom software for clients in Bangladesh and abroad.'],
            ['cefalo-bangladesh', 'Cefalo Bangladesh', 'Product engineering', 'Dhaka', 'Product engineering teams working with Norwegian and Bangladeshi clients.'],
        ];

        foreach ($companies as [$slug, $name, $title, $city, $bio]) {
            $row = Company::query()->firstOrNew(['slug' => $slug]);
            $row->forceFill([
                'status' => DirectoryStatus::Published,
                'name' => $name,
                'title' => $title,
                'city' => $city,
                'bio_en' => $bio,
                'published_at' => $row->published_at ?? now(),
                'created_by' => $adminId,
            ])->save();
        }
    }

    private function proposals(): void
    {
        $members = [
            ['imran.kabir@example.com', 'Imran Kabir', 'Backend Developer', 'Brain Station 23'],
            ['rumana.haque@example.com', 'Rumana Haque', 'Software Engineer', 'Therap BD'],
        ];

        $users = [];

        foreach ($members as [$email, $name, $title, $company]) {
            $users[] = $this->member($email, $name, $title, $company);
        }

        $event = Event::query()->acceptingProposals()->reorder('starts_at')->first()
            ?? Event::query()->published()->reorder('starts_at')->first();

        if ($event === null) {
            return;
        }

        $rows = [
            [0, ProposalStatus::Submitted, ProposalKind::Talk, 'Refactoring a legacy PHP app into Laravel', 'What we kept, what we threw away, and the migrations that hurt.'],
            [0, ProposalStatus::Accepted, ProposalKind::Workshop, 'A practical introduction to Laravel queues', 'A hands-on session on jobs, failures and retries, with a worked example.'],
            [1, ProposalStatus::Rejected, ProposalKind::Talk, 'Why we left the framework', 'A retrospective that turned out to be a better fit for a blog post.'],
        ];

        foreach ($rows as [$userIndex, $status, $kind, $title, $abstract]) {
            TalkProposal::query()->updateOrCreate(
                ['title_en' => $title],
                [
                    'kind' => $kind,
                    'status' => $status,
                    'abstract_en' => $abstract,
                    'event_id' => $event->id,
                    'user_id' => $users[$userIndex]->id,
                ],
            );
        }
    }

    private function registrations(): void
    {
        $event = Event::query()
            ->published()
            ->upcoming()
            ->reorder('starts_at')
            ->first();

        if ($event === null) {
            return;
        }

        $attendees = [
            ['shakib.al.hasan@example.com', 'Shakib Al Hasan', 'DevOps Engineer', 'Cefalo'],
            ['nadia.rahman@example.com', 'Nadia Rahman', 'Frontend Developer', 'Kaz Software'],
            ['jubair.alam@example.com', 'Jubair Alam', 'Laravel Developer', 'Brain Station 23'],
            ['tasnim.ferdous@example.com', 'Tasnim Ferdous', 'QA Engineer', 'Therap BD'],
            ['rakibul.islam@example.com', 'Rakibul Islam', 'Freelance Developer', 'Self employed'],
            ['sumaiya.khatun@example.com', 'Sumaiya Khatun', 'Product Engineer', 'Cefalo'],
        ];

        foreach ($attendees as [$email, $name, $title, $company]) {
            $user = $this->member($email, $name, $title, $company);

            $registration = EventRegistration::query()->updateOrCreate(
                ['event_id' => $event->id, 'user_id' => $user->id],
                [
                    'status' => RegistrationStatus::Registered,
                    'registered_at' => now(),
                ],
            );

            $this->answers($event, $registration, $title, $company);
        }
    }

    /**
     * Demo answers for whichever registration questions the event carries, so
     * the admin attendee list has something to show.
     */
    private function answers(Event $event, EventRegistration $registration, string $title, string $company): void
    {
        $registration->answers()->delete();

        foreach ($event->questions as $question) {
            $options = $question->optionList();

            $value = match (true) {
                $question->kind === QuestionKind::MultipleChoice => array_slice($options, 0, 2),
                $options !== [] => $options[0],
                default => $title.' at '.$company,
            };

            $registration->answers()->create([
                'question' => $question->snapshot(),
                'value' => $value,
            ]);
        }
    }

    /**
     * A demo member whose own profile carries every field the RSVP and CFP
     * gate asks for, including a mobile number derived from the email.
     */
    private function member(string $email, string $name, string $title, ?string $company, bool $isListed = true): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'role' => UserRole::Member,
                'locale' => 'en',
            ],
        );

        $user->forceFill([
            'email_verified_at' => now(),
            'title' => $title,
            'company' => $company ?? 'Self employed',
            'city' => 'Dhaka',
            'photo_path' => $this->demoPhoto(),
            'mobile_number' => '+88017'.str_pad((string) (crc32($email) % 100000000), 8, '0', STR_PAD_LEFT),
            'directory_status' => $isListed ? DirectoryVisibility::Listed : DirectoryVisibility::Pending,
            'directory_published_at' => $isListed ? ($user->directory_published_at ?? now()) : null,
        ]);
        $user->refreshSlug();
        $user->save();

        return $user;
    }

    private function demoPhoto(): string
    {
        $path = 'directory/demo-member.svg';
        $disk = Storage::disk((string) config('images.disk', 'public'));

        if (! $disk->exists($path)) {
            $disk->put($path, (string) file_get_contents(public_path('images/profile-placeholder.svg')));
        }

        return $path;
    }

    /**
     * One member left deliberately without a title, company, photo or mobile
     * number so the profile completeness gate can be seen locally.
     */
    private function incompleteMember(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'incomplete.member@example.com'],
            [
                'name' => 'Habibur Rahman',
                'role' => UserRole::Member,
                'locale' => 'en',
            ],
        );

        $user->forceFill([
            'email_verified_at' => now(),
            'city' => 'Khulna',
            'directory_status' => DirectoryVisibility::Pending,
        ]);
        $user->refreshSlug();
        $user->save();
    }
}
