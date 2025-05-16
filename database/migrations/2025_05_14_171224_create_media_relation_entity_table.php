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
        Schema::create('media_relation_entity', function (Blueprint $table) {
            $table->id();
            // Связь с таблицей media. При удалении медиа, связанные записи в этой таблице также удалятся.
            $table->foreignId('media_id')->constrained()->onDelete('cascade');
            // Полиморфные поля: entity_id (ID сущности) и entity_type (тип сущности, например App\Models\Post)
            $table->morphs('entity');
            // Опциональное поле для хранения порядка медиа в рамках конкретной сущности
            $table->integer('order_column')->nullable();
            $table->timestamps();

            // Добавьте уникальный индекс для предотвращения дублирования связей
            // Одно медиа не может быть привязано к одной и той же сущности более одного раза.
            $table->unique(['media_id', 'entity_id', 'entity_type'], 'media_entity_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_relation_entity');
    }
};
