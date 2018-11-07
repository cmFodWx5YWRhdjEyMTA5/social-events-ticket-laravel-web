<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_customer_id', 'event_id', 'bought_tickets_count',
    ];
}