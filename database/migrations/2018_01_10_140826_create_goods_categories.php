<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name_jp')->nullable()->default('')->comment('分类名称_日文');
            $table->string('name_cn')->nullable()->default('')->comment('分类名称_中文');
            $table->integer('yh_category_id')->nullable()->default(0)->comment('雅虎拍卖分类id');
            $table->integer('yh_parent_category_id')->nullable()->default(0)->comment('所属上级雅虎拍卖分类id');
            $table->integer('child_category_num')->nullable()->default(0)->comment('下属子分类数');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
