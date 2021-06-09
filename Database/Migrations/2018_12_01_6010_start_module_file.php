<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StartModuleFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'files',
            function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->unsigned()->comment('');
                $table->integer('extension_id')->unsigned()->comment('ID расширения файла');
                $table->integer('size')->unsigned()->comment('Размер файла');
                $table->tinyInteger('order')->default(1)->unsigned()->comment('');
                $table->string('name', 300)->nullable()->comment('Название файла');
                $table->string('alias', 300)->nullable()->comment('Алиас файла, копия с модели');
                $table->string('token', 32)->comment('уникальное имя файла');
                $table->string('link', 300)->nullable()->comment('Ссылка');
                $table->timestamp('created_at')->nullable()->comment('Дата создания');
                $table->timestamp('deleted_at')->nullable()->comment('Дата удаления');

                $table->index('extension_id');
            }
        );

        Schema::create(
            'file_versions',
            function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->unsigned()->comment('Размер файла');
                $table->integer('file_id')->unsigned()->comment('ID оригинала файла');
                $table->integer('extension_id')->unsigned()->comment('ID расширения файла');
                $table->integer('size')->unsigned()->comment('Размер файла');
                $table->string('prefix', 50)->comment('Префикс к имени файла');
                $table->smallInteger('height')->unsigned()->default(0)->comment('Высота изобразения');
                $table->smallInteger('width')->unsigned()->default(0)->comment('Ширина изобразения');
                $table->softDeletes();

                $table->foreign('file_id')->references('id')->on('files')->onDelete('CASCADE');
            }
        );

        Schema::create(
            'fileables',
            function (Blueprint $table) {
                $table->integer('file_id')->primary()->unsigned()->comment('ID файла');
                $table->integer('fileable_id')->unsigned()->comment('ID сущности');
                $table->string('fileable_type', 50)->comment('');

                $table->foreign('file_id')->references('id')->on('files')->onDelete('CASCADE');
            }
        );

        Schema::create('extensions',
            function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->unsigned()->comment('ID');
                $table->string('name', 15)->unique()->comment('Расширение');
                $table->string('note', 255)->nullable()->comment('Описание');
                $table->string('mime', 50)->nullable()->comment('MIME тип контента');
            }
        );

        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE files comment 'Хранилище файлов'");
            DB::statement("ALTER TABLE file_versions comment 'Версии одного файла (картинки с разным разрешением)'");
            DB::statement("ALTER TABLE fileables comment 'Связь файла с сущностью'");
            DB::statement("ALTER TABLE extensions comment 'Расширения файлов'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
