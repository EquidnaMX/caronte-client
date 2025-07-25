<?php

/**
 * @author Erick Escobar
 * @license MIT
 * @version 1.3.2
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('Users')) {
            Schema::create('Users', function (Blueprint $table) {
                $table->string('uri_user', 40)->primary();
                $table->string('name', 150);
                $table->string('email', 150);
                $table->engine = 'InnoDB';
            });
        } else {
            Schema::table('Users', function (Blueprint $table) {
                if (!Schema::hasColumn('Users', 'uri_user')) {
                    $table->string('uri_user', 40);
                }
                if (!Schema::hasColumn('Users', 'name')) {
                    $table->string('name', 150);
                }
                if (!Schema::hasColumn('Users', 'email')) {
                    $table->string('email', 150);
                }
                $table->engine = 'InnoDB';
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Users');
    }
};
