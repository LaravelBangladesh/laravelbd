<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * A person's profile moves onto their user and companies get their own table,
 * replacing directory_listings. Users and companies share the directory URL
 * space, so their slugs stay unique across both tables.
 */
return new class extends Migration
{
    /**
     * @var list<string>
     */
    private const PROFILE_COLUMNS = [
        'title', 'company', 'city', 'bio_en', 'bio_bn', 'photo_path', 'website', 'github', 'linkedin', 'x',
    ];

    public function up(): void
    {
        $orphans = DB::table('directory_listings')
            ->where('kind', 'person')
            ->whereNull('user_id')
            ->orderBy('slug')
            ->pluck('slug');

        if ($orphans->isNotEmpty()) {
            throw new RuntimeException(
                'Person directory listings without a user cannot move onto users: '
                .$orphans->implode(', ')
                .'. Link each one to a user or delete it, then run the migration again.'
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique();
            $table->string('mobile_number')->nullable()->unique();
            $table->string('title')->nullable();
            $table->string('company')->nullable();
            $table->string('city')->nullable();
            $table->text('bio_en')->nullable();
            $table->text('bio_bn')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('website')->nullable();
            $table->string('github')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('x')->nullable();
            $table->string('directory_status')->default('hidden');
            $table->timestamp('directory_published_at')->nullable();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('status')->default('draft');
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('city')->nullable();
            $table->text('bio_en')->nullable();
            $table->text('bio_bn')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('website')->nullable();
            $table->string('github')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('x')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('directory_listings')->where('kind', 'person')->lazyById(500)->each(function (object $listing) {
            $user = DB::table('users')->where('id', $listing->user_id);

            $user->update([
                ...$this->profile($listing),
                'slug' => $listing->slug,
                'directory_status' => $listing->status === 'published' ? 'listed' : 'pending',
                'directory_published_at' => $listing->published_at,
            ]);

            // The account name wins; the listing name only fills a blank one.
            if (blank($user->value('name'))) {
                $user->update(['name' => $listing->name]);
            }
        });

        DB::table('directory_listings')->where('kind', 'company')->lazyById(500)->each(function (object $listing) {
            DB::table('companies')->insert([
                ...Arr::except($this->profile($listing), 'company'),
                'id' => $listing->id,
                'slug' => $listing->slug,
                'status' => $listing->status,
                'name' => $listing->name,
                'published_at' => $listing->published_at,
                'created_by' => $listing->created_by,
                'created_at' => $listing->created_at,
                'updated_at' => $listing->updated_at,
            ]);
        });

        Schema::drop('directory_listings');
    }

    public function down(): void
    {
        Schema::create('directory_listings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('kind');
            $table->string('status')->default('draft');
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('company')->nullable();
            $table->string('city')->nullable();
            $table->text('bio_en')->nullable();
            $table->text('bio_bn')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('website')->nullable();
            $table->string('github')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('x')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('users')
            ->whereNotNull('slug')
            ->where(function ($query) {
                $query->where('directory_status', '!=', 'hidden');

                foreach (self::PROFILE_COLUMNS as $column) {
                    $query->orWhereNotNull($column);
                }
            })
            ->lazyById(500)
            ->each(function (object $user) {
                DB::table('directory_listings')->insert([
                    ...$this->profile($user),
                    'id' => (string) Str::uuid7(),
                    'slug' => $user->slug,
                    'kind' => 'person',
                    'status' => $user->directory_status === 'listed' ? 'published' : 'draft',
                    'name' => $user->name,
                    'published_at' => $user->directory_published_at,
                    'created_by' => $user->id,
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        DB::table('companies')->lazyById(500)->each(function (object $company) {
            DB::table('directory_listings')->insert([
                ...$this->profile($company),
                'id' => $company->id,
                'slug' => $company->slug,
                'kind' => 'company',
                'status' => $company->status,
                'name' => $company->name,
                'published_at' => $company->published_at,
                'created_by' => $company->created_by,
                'created_at' => $company->created_at,
                'updated_at' => $company->updated_at,
            ]);
        });

        Schema::drop('companies');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropUnique(['mobile_number']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'mobile_number',
                ...self::PROFILE_COLUMNS,
                'directory_status',
                'directory_published_at',
            ]);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function profile(object $row): array
    {
        $values = [];

        foreach (self::PROFILE_COLUMNS as $column) {
            if (property_exists($row, $column)) {
                $values[$column] = $row->{$column};
            }
        }

        return $values;
    }
};
