<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

use DB;

class CalendarController extends Controller {
	public function addCalendar(Request $request)
	{
		//получаем googleApi от клиента
        $googleApi = $request->input('googleApi');
        $userId    = $request->input('user_id'); 
        if (!$googleApi) $googleApi = 1;

        $calendar = DB::table('calendars')->where('googleApi', $googleApi)->where('user_id', $userId)->first();

        //если календарь есть, отправляем о нем инфу
        //если нету, создаем новый
        if (!$calendar) {
            $calendarId = DB::table('calendars')->insertGetId(
                ['user_id' => $userId, 'date_created' => date_create()->format('Y-m-d H:i:s'),'googleApi' => $googleApi]
            );
            $calendar = DB::table('calendars')->where('calendar_id', $calendarId)->first();
            
        }

        return response()->json($calendar);
	}

    public function createEvent(Request $request, $calendarId)
    {
        $data = $request->json()->all();

        $eventId = DB::table('calendars_events')->insertGetId(
                ['calendar_id' => $calendarId,
                 'date_created' => date_create()->format('Y-m-d H:i:s'),
                 'google_api_id' => $data['google_api_id'],
                 'sport_type_id' => $data['sport_type_id'],
                 'event_type' => $data['event_type']]
            );
        
        $event = DB::table('calendars_events')->where('event_id', $eventId)->first();
        return response()->json($event);
    }

    public function getEvent($calendarId, $eventId)
    {
        $event = DB::table('calendars_events')
                    ->where('event_id', $eventId)
                    ->join('sport_types', 'calendars_events.sport_type_id', '=', 'sport_types.sport_type_id')
                    ->select('calendars_events.*', 'sport_types.sport_type_name')
                    ->first(); 

        return response()->json($event);
    }

    public function getCalendarEvents($calendarId)
    {
       $events = DB::table('calendars_events')
                    ->where('calendar_id', $calendarId)
                    ->join('sport_types', 'calendars_events.sport_type_id', '=', 'sport_types.sport_type_id')
                    ->select('calendars_events.*', 'sport_types.sport_type_name')
                    ->get(); 

        return response()->json($events);
    }

    public function updateEvent(Request $request, $calendarId, $eventId)
    {
        $data = $request->json()->all();
        //обновляем calendar_id
        if ( isset($data['calendar_id']) ) {
            DB::table('calendars_events')->where('event_id', $eventId)
                              ->update(['calendar_id' => $data['calendar_id'],
                                         'last_update' => date_create()->format('Y-m-d H:i:s') 
                                ]);
        }

        //обновляем google_api_id
        if ( isset($data['google_api_id']) ) {
            DB::table('calendars_events')->where('event_id', $eventId)
                              ->update(['google_api_id' => $data['google_api_id'],
                                         'last_update' => date_create()->format('Y-m-d H:i:s') 
                                ]);
        }

        //обновляем sport_type_id
        if ( isset($data['sport_type_id']) ) {
            DB::table('calendars_events')->where('event_id', $eventId)
                              ->update(['sport_type_id' => $data['sport_type_id'],
                                         'last_update' => date_create()->format('Y-m-d H:i:s') 
                                ]);
        }

        //обновляем event_type
        if ( isset($data['event_type']) ) {
            DB::table('calendars_events')->where('event_id', $eventId)
                              ->update(['event_type' => $data['event_type'],
                                         'last_update' => date_create()->format('Y-m-d H:i:s') 
                                ]);
        }

        //получаем пользователя и отправляем объект
        return $this->getEvent($calendarId, $eventId);
    }
}