<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('seo_title')->nullable()->after('description');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->string('focus_keyword')->nullable()->after('seo_description');
            $table->string('canonical_url')->nullable()->after('focus_keyword');
            $table->string('og_title')->nullable()->after('canonical_url');
            $table->text('og_description')->nullable()->after('og_title');
            $table->string('og_image')->nullable()->after('og_description');
            $table->enum('robots', ['index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'])
                ->default('index,follow')
                ->after('og_image');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'seo_title', 'seo_description', 'focus_keyword',
                'canonical_url', 'og_title', 'og_description', 'og_image', 'robots',
            ]);
        });
    }
};
