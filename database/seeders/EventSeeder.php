<?php

namespace Database\Seeders;

use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Enums\EventType;
use App\Domain\Events\Enums\QuestionKind;
use App\Domain\Events\Enums\SessionKind;
use App\Domain\Events\Enums\SpeakerRole;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Speaker;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('role', 'admin')->first();

        $speaker = Speaker::query()->updateOrCreate(
            ['slug' => 'sumon-selim'],
            [
                'name' => 'Sumon Selim',
                'title' => 'Organizer',
                'company' => 'Laravel Bangladesh',
                'bio_en' => 'Community organizer and Laravel developer from Bangladesh.',
            ],
        );

        $startsAt = now('Asia/Dhaka')->addWeeks(3)->setTime(18, 0)->utc();

        $event = Event::query()->updateOrCreate(
            ['slug' => 'april-laravel-meetup'],
            [
                'type' => EventType::Meetup,
                'status' => EventStatus::Published,
                'title_en' => 'Laravel Bangladesh Meetup',
                'title_bn' => 'Laravel Bangladesh মিটআপ',
                'excerpt_en' => 'Talks, snacks, and hallway conversations for Laravel developers in Dhaka.',
                'description_en' => 'Join the Laravel Bangladesh user group for an evening of talks and community.',
                'venue_name' => 'Dhaka',
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->addHours(3),
                'capacity' => 80,
                'cfp_enabled' => true,
                'cfp_opens_at' => now()->subWeek(),
                'cfp_closes_at' => $startsAt->copy()->subWeek(),
                'published_at' => now(),
                'created_by' => $admin?->id,
            ],
        );

        $event->speakers()->syncWithoutDetaching([
            $speaker->id => ['role' => SpeakerRole::Host->value],
        ]);

        $questions = [
            [QuestionKind::ShortText, 'Company / role', 'কোম্পানি / পদ', 'So we can print a name badge.', null, true],
            [QuestionKind::SingleChoice, 'T-shirt size', 'টি-শার্টের মাপ', null, ['S', 'M', 'L', 'XL'], false],
            [QuestionKind::MultipleChoice, 'Topics of interest', 'আগ্রহের বিষয়', 'Pick as many as you like.', ['Testing', 'Queues', 'APIs', 'Deployment'], false],
        ];

        foreach ($questions as $position => [$kind, $labelEn, $labelBn, $helpEn, $options, $required]) {
            $event->questions()->updateOrCreate(
                ['label_en' => $labelEn],
                [
                    'kind' => $kind,
                    'label_bn' => $labelBn,
                    'help_en' => $helpEn,
                    'options' => $options,
                    'required' => $required,
                    'position' => $position,
                ],
            );
        }

        if ($event->sessions()->doesntExist()) {
            $session = $event->sessions()->create([
                'title_en' => 'Building community products with Laravel',
                'kind' => SessionKind::Talk,
                'starts_at' => $startsAt->addMinutes(30),
                'ends_at' => $startsAt->addMinutes(90),
                'room' => 'Main hall',
                'sort_order' => 1,
            ]);

            $session->speakers()->syncWithoutDetaching([
                $speaker->id => ['role' => SpeakerRole::Speaker->value],
            ]);
        }
    }
}
