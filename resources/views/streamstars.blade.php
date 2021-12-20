<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" value="{{ csrf_token() }}" />
    <title>Stream Stars</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
    <link href="{{ mix('css/app.css') }}" type="text/css" rel="stylesheet" />
</head>

<body>
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <?php

                use Illuminate\Support\Facades\Http;
                use App\Models\StreamStars;

                $client_id = env('SS_client_id'); //'3lvzzzzzn2ul';
                $client_code = env('SS_client_sec'); //'kmrxxxxzdv4u5b';
                $accept_v5 = 'application/vnd.twitchtv.v5+json';
                $redirect_uri = 'http://localhost:8000/streamstars';

                $code = isset($_REQUEST['code']) ? $_REQUEST['code'] : "";
                $logout = isset($_REQUEST['logout']) ? $_REQUEST['logout'] : "";

                if (empty($code)) {
                    $value = Session::get('code');
                    $code = $value;
                }

                if (empty($logout)) {
                } else {
                    Session::put('code', '');
                    Session::put('authorization', '');
                }


                //echo $code;
                $urlAuth = 'https://id.twitch.tv/oauth2/authorize?client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=viewing_activity_read+openid';

                if (empty($code)) {
                    echo "Please Login into twitch";
                    //$response2 = Http::get($urlAuth);
                    //error_log('Some message here.'.$response);
                    //echo $response2;

                ?>
                    <a href="<?php echo $urlAuth; ?>"> Please login into twitch</a>
                    <?php
                } else {

                    Session::put('code', $code);
                    //https://id.twitch.tv/oauth2/token?client_id={{client_id}}&client_secret={{client_secret}}&code={{dynamic_code}}&grant_type=authorization_code&redirect_uri={{redirect_uri}}

                    $authorization = Session::get('authorization');
                    if (empty($authorization)) {
                        $responseAuto = Http::withHeaders([
                            'Accept' => $accept_v5,
                            'Client-ID' => $client_id,
                            'Authorization' => 'Bearer 0123456789abcdefghijABCDEFGHIJ'
                        ])->post('https://id.twitch.tv/oauth2/token?client_id=' . $client_id . '&client_secret=' . $client_code . '&code=' . $code . '&grant_type=authorization_code&redirect_uri=http://localhost:8000/streamstars');

                        if (!empty($responseAuto)) {
                            $json = json_decode($responseAuto, true);
                            $statusCode = 200;
                            if (array_key_exists("status", $json)) {
                                $statusCode = $json['status'];
                            }

                            if ($statusCode === 400 || $statusCode === 404) {
                    ?>
                                <h5> Opps, Session timeout.</h4><br>
                                    <a href="<?php echo $urlAuth; ?>"> Please login into twitch</a>
                                <?php
                                Session::put('code', '');
                                Session::put('authorization', '');
                            } else {
                                if (array_key_exists("access_token", $json)) {
                                    $authorization =  $json['access_token'];
                                    Session::put('code', $code);
                                    Session::put('authorization', $authorization);
                                }
                                //print_r($json);
                            }
                        }
                    }

                    error_log('authorization - Bearer ' . $authorization);
                    error_log('code ' . $code);

                    if (empty($authorization)) {
                        exit();
                    }

                    $cursor_next_page = '';
                    $urlStreamList = 'https://api.twitch.tv/helix/streams?first=100';
                    $maxPages = 5; //10
                    for ($i = 0; $i < $maxPages; $i++) {
                        //echo $i;
                        if (empty($cursor_next_page)) {
                        } else {
                            // echo "i \n\n cursor_next_page--".$cursor_next_page;
                            error_log('Position ## ' . $i . '## after = ' . $cursor_next_page);
                            $urlStreamList = 'https://api.twitch.tv/helix/streams?first=100&after=' . $cursor_next_page;
                        }

                        $response = Http::withHeaders([
                            'Accept' => $accept_v5,
                            'Client-ID' => $client_id,
                            'Authorization' => 'Bearer ' . $authorization
                        ])->get($urlStreamList);

                        //'Authorization' => 'Bearer 7ag2zxwuq59jsgnqrsuzu9jg09ic48'
                        // &after = for pagination  "pagination": { "cursor": "eyJiIjp7IkN1cn
                        error_log('response_manager');
                        error_log('response' . $response);
                        $cursor_next_page = response_manager($response, $urlAuth);
                    }
                }

                function response_manager($response, $urlAuth)
                {
                    $cursor = "";
                    if (!empty($response)) {
                        $json = json_decode($response, true);

                        $statusCode = 200;
                        if (array_key_exists("status", $json)) {
                            $statusCode = $json['status'];
                        }

                        if ($statusCode === 400 || $statusCode === 401 || $statusCode === 404) {
                                ?>
                                <h5> Opps, Session timeout.</h4><br>
                                    <a href="<?php echo $urlAuth; ?>"> Please login into twitch</a>
                        <?php
                            Session::put('code', '');
                            Session::put('authorization', '');
                            exit();
                        } else {
                            //print_r($json);
                            if (array_key_exists("data", $json)) {
                                $data = $json['data'];

                                $finalArray = array();
                                //['id','user_id','channel_name','stream_title',
                                //'game_name','viewers_count','started_at'];
                                foreach ($data as $key => $value) {
                                    array_push(
                                        $finalArray,
                                        array(
                                            'id' => $value['id'],
                                            'user_id' => $value['user_id'],
                                            'channel_name' => $value['user_login'],
                                            'stream_title' => $value['title'],
                                            'game_name' => $value['game_name'],
                                            'viewers_count' => $value['viewer_count'],
                                            'started_at' => $value['started_at']
                                        )
                                    );
                                }

                                // print_r($finalArray);
                                // StreamStars::insert($finalArray);
                                DB::table('stream_stars')->upsert($finalArray, 'id');
                                error_log('inserted response');
                            }

                            if (array_key_exists("pagination", $json)) {
                                error_log('pagination response');
                                $pagination = $json['pagination'];
                                if (array_key_exists("cursor", $pagination)) {
                                    $cursor = $pagination['cursor'];
                                    //Next Page Url-->print_r($cursor);
                                    error_log('pagination response - cursor' . $cursor);
                                }
                            }
                        }
                        // Model::insert($data); // Eloquent approach
                        //DB::table('table')->insert($data);
                    }

                    return $cursor;
                }
                        ?>

                        <div class="container mt-12">
                            <div class="col-12 text-center">
                                <a href="http://localhost:8000/streamstars?logout=1">
                                    <h5>Logout</h5>
                                </a>
                            </div>
                        </div>


            </div>
        </div>

        <div id="php">
        </div>

        <div id="app">
        </div>
        <script src="{{ mix('js/app.js') }}" type="text/javascript"></script>

</body>

</html>