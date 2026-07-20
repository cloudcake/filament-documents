<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable(\Cloudcake\Documents\Document::getSettingsTableName())) {
            Schema::create(\Cloudcake\Documents\Document::getSettingsTableName(), function (Blueprint $table) {
                $table->string('id')->primary();
                $table->json('custom')->nullable();
                $table->json('settings')->nullable();
                $table->dateTimes();
            });
        }

        if (!Schema::hasTable(\Cloudcake\Documents\Document::getContentTableName())) {
            Schema::create(\Cloudcake\Documents\Document::getContentTableName(), function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('panel_id')->index();
                $table->uuid('parent_id')->nullable()->index();
                $table->uuid('category_id')->nullable()->index();
                $table->string('creator_id')->nullable()->index();
                $table->string('creator_type')->nullable();
                $table->string('title');
                $table->string('icon')->nullable();
                $table->string('slug')->nullable()->index();
                $table->integer('sort')->default(0);
                $table->integer('ratings_score')->default(0);
                $table->integer('ratings_total')->default(0);
                $table->boolean('published')->default(false);
                $table->boolean('featured')->default(false);
                $table->boolean('pinned')->default(false);
                $table->boolean('private')->default(false);
                $table->json('custom')->nullable();
                $table->json('gate')->nullable();
                $table->json('tags')->nullable();
                $table->json('schema')->nullable();
                $table->softDeletes();
                $table->dateTimes();
            });
        }

        if (!Schema::hasTable(\Cloudcake\Documents\Document::getCategoriesTableName())) {
            Schema::create(\Cloudcake\Documents\Document::getCategoriesTableName(), function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('panel_id')->index();
                $table->string('name');
                $table->string('icon')->nullable();
                $table->boolean('collapsible')->default(true);
                $table->boolean('published')->default(false);
                $table->boolean('featured')->default(false);
                $table->boolean('pinned')->default(false);
                $table->boolean('private')->default(false);
                $table->json('custom')->nullable();
                $table->json('gate')->nullable();
                $table->integer('sort')->default(0);
                $table->dateTime('created_at');
            });
        }

        if (!Schema::hasTable(\Cloudcake\Documents\Document::getRatingsTableName())) {
            Schema::create(\Cloudcake\Documents\Document::getRatingsTableName(), function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('panel_id')->index();
                $table->uuid('content_id')->index();
                $table->string('rater_id')->nullable()->index();
                $table->string('rater_type')->nullable();
                $table->ipAddress()->index()->nullable();
                $table->integer('rating')->default(0);
                $table->dateTime('created_at');
            });
        }

        if (!Schema::hasTable(\Cloudcake\Documents\Document::getRevisionsTableName())) {
            Schema::create(\Cloudcake\Documents\Document::getRevisionsTableName(), function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('panel_id')->index();
                $table->uuid('content_id')->index();
                $table->string('editor_id')->nullable()->index();
                $table->string('editor_type')->nullable();
                $table->json('diff')->nullable();
                $table->dateTime('created_at');
            });
        }

        if (!Schema::hasTable(\Cloudcake\Documents\Document::getComponentsTableName())) {
            Schema::create(\Cloudcake\Documents\Document::getComponentsTableName(), function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('panel_id')->index();
                $table->string('name');
                $table->string('type');
                $table->string('creator_id')->nullable()->index();
                $table->string('creator_type')->nullable();
                $table->json('schema')->nullable();
                $table->dateTimes();
            });
        }

        if (!Schema::hasTable(\Cloudcake\Documents\Document::getStatsTableName())) {
            Schema::create(\Cloudcake\Documents\Document::getStatsTableName(), function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('panel_id')->index();
                $table->string('key', 38)->index();
                $table->text('data')->nullable();
                $table->integer('count')->nullable();
                $table->dateTimes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(\Cloudcake\Documents\Document::getCategoriesTableName());
        Schema::dropIfExists(\Cloudcake\Documents\Document::getContentTableName());
        Schema::dropIfExists(\Cloudcake\Documents\Document::getComponentsTableName());
        Schema::dropIfExists(\Cloudcake\Documents\Document::getRatingsTableName());
        Schema::dropIfExists(\Cloudcake\Documents\Document::getRevisionsTableName());
        Schema::dropIfExists(\Cloudcake\Documents\Document::getSettingsTableName());
        Schema::dropIfExists(\Cloudcake\Documents\Document::getStatsTableName());
    }
};
