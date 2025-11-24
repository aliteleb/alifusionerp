<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if ticket_replies table exists
        if (! Schema::hasTable('ticket_replies')) {
            return;
        }

        // Check if replies table exists
        if (! Schema::hasTable('replies')) {
            return;
        }

        // Migrate data from ticket_replies to replies
        DB::statement("
            INSERT INTO replies (
                repliable_type, 
                repliable_id, 
                user_id, 
                message, 
                type, 
                is_internal, 
                is_from_client, 
                is_system_generated, 
                status_changes, 
                time_spent, 
                email_message_id, 
                sent_via_email, 
                email_sent_at, 
                client_ip, 
                user_agent, 
                source, 
                created_at, 
                updated_at, 
                deleted_at
            )
            SELECT 
                'App\\\\Models\\\\Ticket' as repliable_type,
                ticket_id as repliable_id,
                user_id,
                message,
                type,
                is_internal,
                is_from_client,
                is_system_generated,
                status_changes,
                time_spent,
                email_message_id,
                sent_via_email,
                email_sent_at,
                client_ip,
                user_agent,
                source,
                created_at,
                updated_at,
                deleted_at
            FROM ticket_replies
        ");

        // Log the migration
        $migratedCount = DB::table('ticket_replies')->count();
        if ($migratedCount > 0) {
            \Illuminate\Support\Facades\Log::info("Migrated {$migratedCount} ticket replies to polymorphic replies table");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove migrated ticket replies from replies table
        DB::table('replies')
            ->where('repliable_type', 'App\\Models\\Ticket')
            ->delete();
    }
};
