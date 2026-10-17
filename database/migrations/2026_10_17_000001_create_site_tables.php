<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 8)->nullable();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('solutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 8)->nullable();
            $table->string('tagline')->nullable();
            $table->string('audience')->nullable();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->json('features')->nullable();
            $table->json('included')->nullable();
            $table->string('website_url')->nullable();
            $table->string('accent', 16)->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('solution_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('compiniya_id')->nullable()->unique();
            $table->string('number', 32)->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('unit', 32)->default('Forfait');
            $table->unsignedBigInteger('price')->default(0);
            $table->string('summary')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('references', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('acronym', 32)->nullable();
            $table->string('sector')->nullable();
            $table->string('location')->nullable();
            $table->string('website_url')->nullable();
            $table->string('headline')->nullable();
            $table->text('description')->nullable();
            $table->json('services')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('cover')->nullable();
            $table->string('author')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->string('group')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16)->default('contact');
            $table->string('reference', 24)->unique();
            $table->string('name');
            $table->string('organization')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->json('items')->nullable();
            $table->unsignedBigInteger('total_ht')->nullable();
            $table->string('status', 16)->default('new');
            $table->text('internal_notes')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('references');
        Schema::dropIfExists('services');
        Schema::dropIfExists('solutions');
        Schema::dropIfExists('service_categories');
        Schema::dropIfExists('settings');
    }
};
