<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptionalMenuOptionsTable extends Migration
{
    public function up()
    {
        if(!Schema::hasTable('foodninjas_optional_menu_options')) {
            Schema::create('foodninjas_optional_menu_options', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->integer('menu_item_option_value_id')->unsigned();
                $table->integer('menu_item_option_id')->unsigned();
                $table->primary(['menu_item_option_value_id', 'menu_item_option_id'], 'optional_menu_options_primary');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('foodninjas_optional_menu_options');
    }
}
