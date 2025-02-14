<?php

namespace App\Http\Controllers\API\v2\member;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\User;
use Illuminate\Http\Request;

class KtaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'province_id' => 'required',
            'city_id' => 'required',
            'district_id' => 'required',
        ]);

        $user = User::findOrFail($request->user()->id);

        if ($user->kta_id != null) {
            return response()->json([
                'message' => 'KTA sudah terdaftar',
            ], 400);
        }

        $district = District::with('profiles')->findOrFail($request->district_id);
        $member = User::whereHas('profile.district', function ($query) use ($district) {
            $query->where('id', $district->id);
        })->where('kta_id', '!=', null);

       

        if ($member->doesntExist()) {
            $user->kta_id = $district->id . '001';
        } else {
            $kta_id = $member->latest()->first()->kta_id ?? $district->id . '001';
            while (User::where('kta_id', $kta_id)->exists()) {
                $kta_id++;
            }
            $user->kta_id = $kta_id;
        }

        $user->profile()->update([
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'district_id' => $request->district_id,
        ]);

        $user->update();

        return response()->json([
            'message' => 'KTA berhasil dibuat',
            'kta_id' => $user->kta_id,
           
        ], 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
