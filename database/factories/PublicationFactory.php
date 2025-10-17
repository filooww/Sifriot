<?php

namespace Database\Factories;

use App\Models\AuthorGroup;
use App\Models\IssueType;
use App\Models\Magazine;
use App\Models\Part;
use App\Models\Publication;
use App\Models\Publishing;
use App\Models\ThemeSet;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublicationFactory extends Factory
{
    protected $model = Publication::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'title_low' => fn(array $attributes) => mb_strtolower($attributes['title']),
            'id_publishing' => null, // Can be overridden
            'id_part' => null,
            'issue_year' => $this->faker->year(),
            'id_issue_type' => null,
            'id_magazine' => null,
            'upload_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'actuality' => $this->faker->randomElement([0, 1]),
            'id_theme_set' => null,
            'id_author_set' => null,
            '_del_mark' => 0,
            'add_int' => $this->faker->numberBetween(1, 100),
            'add_char' => $this->faker->word(),
            'word_count' => $this->faker->optional()->numberBetween(100, 50000),
        ];
    }

    /**
     * Indicate that the publication is deleted.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            '_del_mark' => 1,
        ]);
    }

    /**
     * Indicate that the publication is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            '_del_mark' => 0,
        ]);
    }

    /**
     * Create publication with relationships.
     */
    public function withRelationships(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'id_publishing' => Publishing::factory(),
                'id_part' => Part::factory(),
                'id_issue_type' => IssueType::factory(),
                'id_magazine' => Magazine::factory(),
                'id_theme_set' => ThemeSet::factory(),
                'id_author_set' => AuthorGroup::factory(),
            ];
        });
    }
}
