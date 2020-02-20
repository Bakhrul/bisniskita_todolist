<?php

namespace App\Http\Controllers;

use App\Project;
use DB;
use Auth;
use Hash;
use File;
use Crypt;
use App\Todo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Project::orderBy('p_id','ASC')->limit(3)->get();
        $datas = array();

        foreach($data as $value){
            $arr = [
                'id'    => $value->p_id,
                'title' => $value->p_name,
                'start' => $value->p_time_start,
                'end'   => $value->p_time_end,
                'status' => $value->p_status
            ];
            array_push($datas,$arr);
        }
        return response()->json($datas);
    }

    public function create_project(Request $request){
        DB::beginTransaction();
        try {
        DB::table('d_project')->insert([
            'p_name' => $request->nama_project,
            'p_creator' => Auth::user()->us_id,
            'p_timestart' => Carbon::parse($request->time_start),
            'p_timeend' => Carbon::parse($request->time_end),
            'p_status' => 'Open',
            'p_created' => Carbon::now('Asia/Jakarta'),
            'p_updated' => Carbon::now('Asia/Jakarta'),
        ]);

        DB::commit();
        return response()->json([
            'status' => 'success',
        ]);

        } catch (Exception $e) {
            return $e;
        }
        
    }
    public function detail_project(Request $request){

        $getMember = DB::table('d_member_project')
                    ->join('m_users','mp_user','us_id')
                    ->join('m_roles','mp_role','r_id')
                    ->where('mp_project',$request->project)
                    ->get();

        $getTodo = DB::table('d_todolist')
                    ->where('tl_project',$request->project)
                    ->get();

        return response()->json([
            'member' => $getMember,
            'todo' => $getTodo,
        ]);

    }

    public function add_member_project(Request $request){
        DB::beginTransaction();
        try {
        
        $cekUser = DB::table('m_users')->where('us_email',$request->member)->first();

        if($cekUser == null){
            return response()->json([
                'status' => 'user tidak ada',
            ]);
        }else{
            $cekMember = DB::table('d_member_project')->where('mp_user',$cekUser->us_id)->where('mp_project',$request->project)->first();
            $cekTodo = DB::table('d_todolist')->where('tl_project',$request->project)->get();
            if($cekMember == null){
                DB::table('d_member_project')->insert([
                    'mp_project' => $request->project,
                    'mp_user' => $cekUser->us_id,
                    'mp_role' => $request->status,
                    'mp_created' => Carbon::now(),
                    'mp_updated' => Carbon::now(),
                ]);

                foreach ($cekTodo as $key => $value) {
                    DB::table('d_todolist_roles')->where('tlr_users',$cekUser->us_id)->where('tlr_todolist',$value->tl_id)->delete();
                    DB::table('d_todolist_roles')->insert([
                        'tlr_users' => $cekUser->us_id,
                        'tlr_todolist' => $value->tl_id,
                        'tlr_role' => $request->status,
                    ]);
                }
            }else{
                $cekRole = DB::table('m_roles')->where('r_id',$cekMember->mp_role)->first();
                return response()->json([
                    'status' => 'member sudah ada',
                    'role' => $cekRole->r_name,
                ]);
            }
        }

        DB::commit();
        return response()->json([
            'status' => 'success',
        ]);

        } catch (Exception $e) {
            return $e;
        }
    }

    public function add_todo_project(Request $request){
        DB::beginTransaction();
        try {
        
        $maxIdTodo = DB::table('d_todolist')->max('tl_id') + 1;
        DB::table('d_todolist')->insert([
            'tl_id' => $maxIdTodo,
            'tl_category' => '1',
            'tl_project' => $request->project,
            'tl_title' => $request->nama_todo,
            'tl_desc' => $request->deskripsi,
            'tl_status' => 'Open',
            'tl_progress' => 0,
            'tl_planstart' => Carbon::parse($request->tanggal_awal)->format('Y-m-d H:i:s'),
            'tl_planend' => Carbon::parse($request->tanggal_akhir)->format('Y-m-d H:i:s'),
            'tl_created' => Carbon::now('Asia/Jakarta'),
            'tl_updated' => Carbon::now('Asia/Jakarta'),
        ]);

        $getMember = DB::table('d_member_project')->where('mp_project',$request->project)->get();
        foreach ($getMember as $key => $value) {
            DB::table('d_todolist_roles')->where('tlr_users',$value->mp_user)->where('tlr_todolist',$maxIdTodo)->delete();
            DB::table('d_todolist_roles')->insert([
                'tlr_users' => $value->mp_user,
                'tlr_todolist' => $maxIdTodo,
                'tlr_role' => $value->mp_role,
            ]);
        }

        DB::commit();
        return response()->json([
            'status' => 'success',
        ]);

        } catch (Exception $e) {
            return $e;
        }   
    }
    public function delete_member_project(Request $request){
     DB::beginTransaction();
        try {
        DB::table('d_member_project')->where('mp_project',$request->project)->where('mp_user',$request->member)->delete();

        $getTodo = DB::table('d_todolist')->where('tl_project',$request->project)->get();

        foreach ($getTodo as $key => $value) {
            DB::table('d_todolist_roles')->where('tlr_users',$request->member)->where('tlr_todolist',$value->tl_id)->delete();
        }

        DB::commit();
        return response()->json([
            'status' => 'success',
        ]);

        } catch (Exception $e) {
            return $e;
        }      
    }

    public function delete_todo_project(Request $request){
     DB::beginTransaction();
        try {
        DB::table('d_todolist')->where('tl_project',$request->project)->where('tl_id',$request->todolist)->delete();

        $getMember = DB::table('d_member_project')->where('mp_project',$request->project)->get();

        foreach ($getMember as $key => $value) {
            DB::table('d_todolist_roles')->where('tlr_users',$value->mp_user)->where('tlr_todolist',$request->todolist)->delete();
        }

        DB::commit();
        return response()->json([
            'status' => 'success',
        ]);

        } catch (Exception $e) {
            return $e;
        }      
    }
    public function update_status_member_project(Request $request){
     DB::beginTransaction();
        try {
        DB::table('d_member_project')->where('mp_user',$request->member)->where('mp_project',$request->project)->update([
            'mp_role' => $request->role,
        ]);

        $getTodoProject = DB::table('d_todolist')->where('tl_project',$request->project)->get();

        foreach ($getTodoProject as $key => $value) {
            DB::table('d_todolist_roles')->where('tlr_users',$request->member)->where('tlr_todolist',$value->tl_id)->update([
                'tlr_role' => $request->role,
            ]);
        }

        DB::commit();
        return response()->json([
            'status' => 'success',
        ]);

        } catch (Exception $e) {
            return $e;
        }         
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        //
    }
}
