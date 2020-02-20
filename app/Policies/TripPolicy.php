<?php

namespace App\Policies;

use App\User;
use App\Trip;
use Illuminate\Auth\Access\HandlesAuthorization;

class TripPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any trips.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the trip.
     *
     * @param  \App\User  $user
     * @param  \App\Trip  $trip
     * @return mixed
     */
    public function view(User $user, Trip $trip)
    {
        return $user->id == $trip->user_id;
    }

    /**
     * Determine whether the user can create trips.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the trip.
     *
     * @param  \App\User  $user
     * @param  \App\Trip  $trip
     * @return mixed
     */
    public function update(User $user, Trip $trip)
    {
        return $user->id == $trip->user_id;
    }

    /**
     * Determine whether the user can delete the trip.
     *
     * @param  \App\User  $user
     * @param  \App\Trip  $trip
     * @return mixed
     */
    public function delete(User $user, Trip $trip)
    {
        return $user->id == $trip->user_id;
    }

    /**
     * Determine whether the user can restore the trip.
     *
     * @param  \App\User  $user
     * @param  \App\Trip  $trip
     * @return mixed
     */
    public function restore(User $user, Trip $trip)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the trip.
     *
     * @param  \App\User  $user
     * @param  \App\Trip  $trip
     * @return mixed
     */
    public function forceDelete(User $user, Trip $trip)
    {
        //
    }

    public function cooperate(User $user, Trip $trip){

        return $user->id == $trip->user_id;

    }
}
