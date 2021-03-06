<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_customer_id')->unsigned();
            $table->foreign('ticket_customer_id')->references('id')->on('ticket_customers');
            $table->integer('ticket_category_detail_id')->unsigned();
            $table->foreign('ticket_category_detail_id')->references('id')->on('ticket_category_details');
            $table->text('validation_token');
            $table->integer('unique_ID', false, true);
            $table->text('qr_code_image_url');
            $table->string('pdf_format_url');
            $table->integer('event_id')->unsigned();
            $table->foreign('event_id')->references('id')->on('events');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
