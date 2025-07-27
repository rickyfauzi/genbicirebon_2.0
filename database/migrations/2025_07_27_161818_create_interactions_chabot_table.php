<?php

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
        // Tabel untuk menyimpan interaksi chatbot
        Schema::create('chatbot_interactions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->text('user_message');
            $table->string('detected_intent')->nullable();
            $table->text('bot_response');
            $table->json('suggestions_shown')->nullable();
            $table->string('selected_suggestion')->nullable();
            $table->float('confidence_score')->nullable();
            $table->json('context_data')->nullable();
            $table->timestamp('created_at');

            $table->index(['session_id', 'created_at']);
            $table->index('detected_intent');
        });

        // Tabel untuk analytics usage intent
        Schema::create('chatbot_intent_usage', function (Blueprint $table) {
            $table->id();
            $table->string('intent_name');
            $table->integer('usage_count')->default(1);
            $table->float('avg_confidence')->default(0);
            $table->date('date');
            $table->timestamps();

            $table->unique(['intent_name', 'date']);
            $table->index('date');
        });

        // Tabel untuk suggestion analytics
        Schema::create('chatbot_suggestion_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('suggestion_text');
            $table->string('context_intent');
            $table->integer('shown_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->float('click_rate')->default(0);
            $table->date('date');
            $table->timestamps();

            $table->unique(['suggestion_text', 'context_intent', 'date'], 'sugg_context_date_unique');

            $table->index(['date', 'click_rate']);
        });

        // Tabel untuk session analytics
        Schema::create('chatbot_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->integer('message_count')->default(0);
            $table->integer('suggestion_clicks')->default(0);
            $table->json('intent_flow')->nullable();
            $table->integer('session_duration')->nullable(); // in seconds
            $table->boolean('goal_achieved')->default(false);
            $table->string('final_intent')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['started_at', 'ended_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_sessions');
        Schema::dropIfExists('chatbot_suggestion_analytics');
        Schema::dropIfExists('chatbot_intent_usage');
        Schema::dropIfExists('chatbot_interactions');
    }
};
