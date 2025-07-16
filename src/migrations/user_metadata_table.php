<?php

/**
 * @author Erick Escobar
 * @license MIT
 * @version 1.0.0
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
        Schema::create('UsersMetadata', function (Blueprint $table) {
            $table->string('uri_user', 40);
            $table->string('scope', 128);
            $table->string('key', 45);
            $table->string('value', 45);
            $table->primary(['uri_user', 'scope', 'key']);
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('UsersMetadata');
    }
};
