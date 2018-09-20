@extends('tickets.layouts')

@section('content')
<div class="row page-row">
    @foreach ($events as $event)
    <div class="col-md-3 col-sm-12">
        <div class="card">
            <div class="image-container">
                <a href="{{ route('ticket_details', $event->slug) }}">
                    <img class="card-image" src="{{ asset('storage/images/events') }}{{'/'.$event->media_url}}" alt="Event image">
                </a>        
            </div>
            <div class="card-content">      
                <a href="{{ route('ticket_details', $event->slug) }}" class="title">{{$event->name}}</a>
                <div class="row">
                    <div class="col-md-6">
                        <p class="dates"><i class="fa fa-calendar-check-o"></i> {{date("jS M Y", strtotime($event->start_date))}}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="price"><i class="fa fa-money"></i> 
                        @if ($event->price==null)
                            {{'Free'}}
                        @else
                            Ksh {{$event->price}}
                        @endif 
                        </p>

                    </div>
                </div>
                <p class="location"><i class="fa fa-map-marker"></i> {{$event->location}}</p>
                <p class="description">{{str_limit($event->description, $limit = 75, $end = '...')}}</p>
            </div>
            <button>Buy Now</button>
        </div>
    </div>        
    @endforeach
</div>
@endsection
