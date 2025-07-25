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
        if (!Schema::hasTable('UsersMetadata')) {
            Schema::create('UsersMetadata', function (Blueprint $table) {
                $table->string('uri_user', 40);
                $table->string('scope', 128);
                $table->string('key', 45);
                $table->string('value', 45);
                $table->primary(['uri_user', 'scope', 'key']);
                $table->engine = 'InnoDB';
            });
        } else {
            Schema::table('UsersMetadata', function (Blueprint $table) {
                // Ensure columns exist and have correct types/lengths
                if (!Schema::hasColumn('UsersMetadata', 'uri_user')) {
                    $table->string('uri_user', 40);
                } else {
                    $table->string('uri_user', 40)->change();
                }
                if (!Schema::hasColumn('UsersMetadata', 'scope')) {
                    $table->string('scope', 128);
                } else {
                    $table->string('scope', 128)->change();
                }
                if (!Schema::hasColumn('UsersMetadata', 'key')) {
                    $table->string('key', 45);
                } else {
                    $table->string('key', 45)->change();
                }
                if (!Schema::hasColumn('UsersMetadata', 'value')) {
                    $table->string('value', 45);
                } else {
                    $table->string('value', 45)->change();
                }
                // Drop and re-add primary key if needed
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('UsersMetadata');
                if (isset($indexes['primary']) && $indexes['primary']->getColumns() !== ['uri_user', 'scope', 'key']) {
                    $table->dropPrimary();
                    $table->primary(['uri_user', 'scope', 'key']);
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
        Schema::dropIfExists('UsersMetadata');
    }
};
