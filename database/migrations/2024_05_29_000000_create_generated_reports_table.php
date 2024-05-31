<?php
// database/migrations/2024_05_29_000000_create_generated_reports_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneratedReportsTable extends Migration
{
    public function up()
    {
        Schema::create('generated_reports', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('data_set');
            $table->string('name');
            $table->text('query');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('generated_reports');
    }
}
