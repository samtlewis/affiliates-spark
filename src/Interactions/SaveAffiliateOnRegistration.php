<?php

namespace KeithBrink\AffiliatesSpark\Interactions;

use Laravel\Spark\Spark;
use Carbon\Carbon;
use KeithBrink\AffiliatesSpark\Models\Affiliate;
use Laravel\Spark\Contracts\Interactions\Settings\PaymentMethod\RedeemCoupon;
use Laravel\Spark\Repositories\UserRepository;
use Laravel\Spark\Events\Teams\TeamCreated;
use Laravel\Spark\Repositories\TeamRepository;
use Laravel\Spark\Contracts\Interactions\Settings\Teams\AddTeamMember as AddTeamMemberContract;

class SaveAffiliateOnRegistration
{
    public function createUser($request)
    {
        $data = array_merge($request->all(), $extra_data);
        if($affiliate_id = $this->getAffiliateId($request)) {
            $data['affiliate_id'] = $affiliate_id;
        }
        
        $user = Spark::user();

        $user->forceFill($data)->save();

        Spark::interact(RedeemCoupon::class, [
            $user, $request->cookie('affiliate')
        ]);

        return $user;
    }

    public function createTeam($user, $data)
    {
        $attributes = [
            'owner_id' => $user->id,
            'name' => $data['name'],
            'trial_ends_at' => Carbon::now()->addDays(Spark::teamTrialDays()),
        ];

        if($affiliate_id = $user->affiliate_id) {
            $attributes['affiliate_id'] = $affiliate_id;
        }

        if (Spark::teamsIdentifiedByPath()) {
            $attributes['slug'] = $data['slug'];
        }

        $team = Spark::team()->forceCreate($attributes);

        event(new TeamCreated($team));

        Spark::interact(AddTeamMemberContract::class, [
            $team, $user, 'owner'
        ]);

        Spark::interact(RedeemCoupon::class, [
            $team, $request->cookie('affiliate')
        ]);

        return $team;
    }

    public function getAffiliateId($request)
    {
        if ($request->cookie('affiliate')) {
            if (Affiliate::where('token', $request->cookie('affiliate'))->count()) {
                return Affiliate::where('token', $request->cookie('affiliate'))->first()->id;
            }
        }
    }
}