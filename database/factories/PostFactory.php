<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    protected $model = \App\Models\Post::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'=> $this->faker->sentence,
            'slug' => Str::slug($this->faker->sentence),
            'excerpt' => $this->faker->text(100),
            'content' => $this->faker->paragraphs(3, true),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
