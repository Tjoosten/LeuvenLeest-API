<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use Illuminate\Http\Response;
use App\Place;
use \DB;
use App\Favourite;


class FavouriteController extends Controller
{

    public function __construct()
    {

    }

    public function getFavouritePlaces()
    {
        $favourites = DB::table('favourites')
            ->where('userId', '=', Auth::user()->id)
            ->get();

        if ($favourites)
        {
            return new Response($favourites, 200);
        }
        return new Response('You haven\'t favourite any places.', 400);
    }

    public function addToFavourites($id){
        try{
            $user = Auth::user();
            $place = Place::find($id);
            if (!$place)
            {
                return Response('We weren\'t able to find the place', 404);
            }

            $favourite = $this->create($user, $place);

            return new Response($favourite, 201);

        } catch(Exception $ex){
            Log::error($ex);
            return new Response('We were not able to add this place to your favourites.', 500);
        }
    }

    public function removeFromFavourites($id){
        try {
            $user = Auth::user();
            $place = Place::find($id);

            if (!$place)
            {
                return new Response('We weren\'t able to find the place', 404);
            }

            $favouriteId = $this->createUniqueId($user, $place);

            $favourite = Favourite::find($favouriteId);

            if ($favourite)
            {
                $favourite->delete();
            }

            return new Response('Successfully removed the place from your favourites.',200);
        } catch (Exception $ex){
            Log::error($ex);
            return new Response('We were not able to remove this place from you favourites.',500);
        }
    }

    private function create($user, $place)
    {
        $favouriteId = $this->createUniqueId($user, $place);

        $favourite = Favourite::find($favouriteId);

        if ($favourite)
        {
            return new Response($favourite, 200);
        }

        $favourite = new Favourite();
        $favourite->id = $favouriteId;
        $favourite->userId = $user->id;
        $favourite->placeId = $place->id;
        $favourite->save();

        return $favourite;
    }

    private function createUniqueId($user, $place)
    {
        return $user->id . '_' . $place->id;
    }
}
