<?php

namespace App\Http\Controllers;

use App\Project;
use DB;
use Auth;
use Hash;
use File;
use Crypt;
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
        $data = Project::orderBy('p_id','ASC')->get();
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
