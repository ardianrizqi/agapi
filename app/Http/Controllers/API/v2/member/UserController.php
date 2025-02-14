<?php

namespace App\Http\Controllers\API\v2\member;

use App\Http\Controllers\Controller;
use App\Models\user;
use App\Models\PnsStatus;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $users = User::where('user_activated_at', '!=', null)
            ->whereIn('role_id', [2, 7, 9, 10, 11])
            ->with('profile')
            ->paginate();

        return response()->json($users);
    }


    public function getUserByProvince($province_id)
    {
        //
        $users = User::where('user_activated_at', '!=', null)
            ->whereIn('role_id', [2, 7, 9, 10, 11])
            ->whereHas('profile', function ($query) use ($province_id) {
                $query->where('province_id', $province_id);
            })
            ->paginate();

        return response()->json($users);
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
        $user = User::with('profile', 'pns_status')->findOrFail($id);
        return response()->json($user);
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

    public function gettotalmember()
    {
        $total = User::where('user_activated_at', '!=', null)
            ->whereIn('role_id', [2, 7, 9, 10, 11])
            ->count();
        return response()->json([
            'total' => $total
        ]);
    }

    public function gettotalpnsmember()
    {
        $total = User::whereHas('pns_status', function ($query) {
            $query->where('is_pns', '1');
        })->count();
        return response()->json([
            'total' => $total
        ]);
    }

    public function gettotalnonpnsmember()
    {
        $total = User::whereHas('pns_status', function ($query) {
            $query->where('is_pns', '0');
        })->count();
        return response()->json([
            'total' => $total
        ]);
    }

    public function gettotalcertificatemember()
    {
        $total = User::whereHas('pns_status', function ($query) {
            $query->where('is_certification', '1');
        })->count();
        return response()->json([
            'total' => $total
        ]);
    }

    public function gettotalinpassingmember()
    {
        $total = User::whereHas('pns_status', function ($query) {
            $query->where('is_non_pns_inpassing', '1');
        })->count();
        return response()->json([
            'total' => $total
        ]);
    }

    public function gettotalbsimember()
    {
        $total = User::whereHas('pns_status', function ($query) {
            $query->where('bank_account_no','!=', '');
        })->count();
        return response()->json([
            'total' => $total
        ]);
    }

    public function gettotalexpiredmember()
    {
        $total = User::where('user_activated_at', '!=', null)
            ->whereDate('user_activated_at', '<', \Carbon\Carbon::now()->subMonths(6)->format('Y-m-d'))
            ->whereIn('role_id', [2, 7, 9, 10, 11])
            ->count();
        return response()->json([
            'total' => $total
        ]);
    }

    public function gettotalpensionmember()
    {
        // total member yang sudah pensiun yang umur nya sudah lebih dari 60 tahun
        $total = User::whereHas('profile', function ($query) {
            $query->where('birthdate', '<', now()->subYears(60))->where('user_activated_at', '!=', 'null');
        })->count();
        return response()->json([
            'total' => $total
        ]);
    }

    public function updateStatus(Request $request, $id){
        $pns_status = PnsStatus::firstOrNew(['user_id' => $id]);
        if($request->is_pns == 1){
            $pns_status->is_certification = $request->is_certification;
            $pns_status->is_non_pns_inpassing = 0;
        } else {
            $pns_status->is_non_pns_inpassing = $request->is_non_pns_inpassing;

            $pns_status->is_certification = $request->is_certification;
        }
        $pns_status->is_pns = $request->is_pns;
        $pns_status->bank_name = $request->bank_name;
        $pns_status->bank_account_no = $request->bank_account_no;
        $pns_status->employment_status = $request->employment_status;
        $pns_status->save();

        $res['status']='success';
        $res['data'] = $pns_status;

        return response()->json($res);

    }

    public function search($keyword)
    {
        $users = User::where('user_activated_at', '!=', null)
            ->whereIn('role_id', [2, 7, 9, 10, 11])
            ->where('name', 'like', '%' . $keyword . '%')
            ->orWhere('email', 'like', '%' . $keyword . '%')
            ->orWhere('kta_id', 'like', '%' . $keyword . '%')
            ->has('profile')
            ->with('profile')
            ->paginate();

        return response()->json($users);
    }

    public function searchInProvince($province_id,$keyword)
    {
        // $users = User::where('user_activated_at', '!=', null)
        //     ->whereIn('role_id', [2, 7, 9, 10, 11])
        //     // ->where('name', 'like', '%' . $keyword . '%')
        //     // ->orWhere('email', 'like', '%' . $keyword . '%')
        //     // ->orWhere('kta_id', 'like', '%' . $keyword . '%')
        //     ->whereHas('profile', function ($query) use ($province_id) {
        //         $query->where('province_id', $province_id);
        //     })
        //     ->paginate();


        $users = User::where('user_activated_at', '!=', null)
            ->whereIn('role_id', [2, 7, 9, 10, 11])
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%')
                    ->orWhere('kta_id', 'like', '%' . $keyword . '%');
            })
            ->whereHas('profile', function ($query) use ($province_id) {
                $query->where('province_id', $province_id);
            })
            ->paginate();
    
            
       

        return response()->json($users);
    }
}
