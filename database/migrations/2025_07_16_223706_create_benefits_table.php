<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBenefitsTable extends Migration
{
    public function up()
    {
        Schema::create('benefits', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); 
            $table->integer('point_required');
            $table->string('category')->nullable();
            $table->string('discount_type')->nullable(); // percentage, fixed
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->json('applicable_categories')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('benefits');
    }
}