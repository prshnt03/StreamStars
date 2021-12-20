<?php

namespace App\Http\Controllers;

use App\Models\StreamStars;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\Input;
use App\Http\Controllers\LengthAwarePaginator;

class StreamStarsController extends Controller
{
    /**
     * Display a listing of the resource.
     *StreamStarsController
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orderBy = 'id';
        $direction = 'ASC';
        $q = '';
        $type = '';
        
        $pageLimit = 100;
        $qb = StreamStars::query();
       
        
        if($request->has('q')){
            $q = $request->get('q');
        }

        if($request->has('type')){
            $type = $request->get('type');
        }

        if($request->has('pageLimit')){
            $pageLimit = $request->get('pageLimit');
        }

        if($request->has('direction')){
            $direction = $request->get('direction');
        }

        if($request->has('orderBy')){
            $orderBy = $request->get('orderBy');
            $qb->orderBy($orderBy, $direction); 
        }

        if($type == 'median'){
            $median = $this->getMedianViewers();
            return response()->json($median);
        }
        
        if($type == 'game_streamers'){
            $gameStreamers = $this->getGameStreamers();

            //$gameStreamers = arrayPaginator($gameStreamers, $request)
            return response()->json($gameStreamers);
        }
        

        if(!$request->has('orderBy')){
            $streamStars = StreamStars::all(['id','user_id','channel_name','stream_title','game_name','viewers_count','started_at']);
            return response()->json($streamStars);    
        } else {
            return response()->json($qb->paginate($pageLimit)); // 100 record pagination
        }
       
        //$streamStars = StreamStars::all(['id','user_id','channel_name','stream_title','game_name','viewers_count','started_at']);
        //return response()->json($streamStars);   
    }
 

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $streamStars = StreamStars::create($request->post());
        return response()->json([
            'message'=>'Stream Created Successfully!!',
            'stream_start'=>$streamStars
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StreamStars  $streamStars
     * @return \Illuminate\Http\Response
     */
    public function show(StreamStars $streamStars)
    {
        return response()->json($streamStars);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StreamStars  $streamStars
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StreamStars $streamStars)
    {
        $streamStars->fill($request->post())->save();
        return response()->json([
            'message'=>'Stream Updated Successfully!!',
            'stream_start'=>$streamStars
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StreamStars  $streamStars
     * @return \Illuminate\Http\Response
     */
    public function destroy(StreamStars $streamStars)
    {
        $streamStars->delete();
        return response()->json([
            'message'=>'Stream Deleted Successfully!!'
        ]);
    }

    public function getMedianViewers(){
        $median = DB::select("SELECT AVG(middle_values) AS 'median' FROM (
            SELECT t1.`viewers_count` AS 'middle_values' FROM
              (
                SELECT @row:=@row+1 as `row`, x.`viewers_count`
                FROM stream_stars AS x, (SELECT @row:=0) AS r
                WHERE 1
                -- put some where clause here
                ORDER BY x.`viewers_count`
              ) AS t1,
              (
                SELECT COUNT(*) as 'count'
                FROM stream_stars x
                WHERE 1
                -- put same where clause here
              ) AS t2
              -- the following condition will return 1 record for odd number sets, or 2 records for even number sets.
              WHERE t1.row >= t2.count/2 and t1.row <= ((t2.count/2) +1)) AS t3");
              
        return $median;
    }

    public function getGameStreamers(){
        $gameStreamers = DB::select("SELECT COUNT(*) streamers,game_name FROM `stream_stars` GROUP BY game_name HAVING streamers > 0");
        return $gameStreamers;
    }

    public function arrayPaginator($array, $request)
    {
        $page = Input::get('page', 1);
        $perPage = 10;
        $offset = ($page * $perPage) - $perPage;

        return new LengthAwarePaginator(array_slice($array, $offset, $perPage, true), count($array), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]);
    }

    //Game name and streamer
    //SELECT COUNT(*) streamers,game_name FROM `stream_stars` GROUP BY game_name HAVING streamers > 0;
    
    //Game name and total viewer count
    // SELECT game_name, SUM(viewers_count) FROM stream_stars Group By game_name;

    /*
    median
    SELECT AVG(middle_values) AS 'median' FROM (
        SELECT t1.`viewers_count` AS 'middle_values' FROM
          (
            SELECT @row:=@row+1 as `row`, x.`viewers_count`
            FROM stream_stars AS x, (SELECT @row:=0) AS r
            WHERE 1
            -- put some where clause here
            ORDER BY x.`viewers_count`
          ) AS t1,
          (
            SELECT COUNT(*) as 'count'
            FROM stream_stars x
            WHERE 1
            -- put same where clause here
          ) AS t2
          -- the following condition will return 1 record for odd number sets, or 2 records for even number sets.
          WHERE t1.row >= t2.count/2 and t1.row <= ((t2.count/2) +1)) AS t3;

    */

}