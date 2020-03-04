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
use App\projectMember;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard(){
        $data = Project::leftJoin('d_project_member','mp_project','p_id')
        ->where('mp_user',Auth::user()->us_id)
        ->with(['role' => function($q){
            $q->with('user');
        }])->get();

        $datas = array(
            'project' => [
                
            ]
        );
        $dtx = array();
        // return response()->json()
        foreach ($data as $key => $value) {

            $arrProj = [
                'id' => $value->p_id,
                'title' => $value->p_name,
                'created_date' => $value->p_timestart,
                'members'=> [],
                'member_total'=> '',
                'percent'=> ''
            ];

            $sum = DB::table('d_todolist')->where('tl_project', $value->p_id)->sum('tl_progress');
            $count = DB::table('d_todolist')->where('tl_project', $value->p_id)->count('tl_progress');

            $percent = $count > 0 ? round(((($sum/$count) / 100) * 100),2) : 0;

            $arrProj['percent'] = $percent;        
            foreach ($value['role'] as $key2 => $valueRole) {
                $arrUser = [
                    'id' => $valueRole['user']->us_id,
                    'name' => $valueRole['user']->us_name,
                    'path' => $valueRole['user']->us_image,
                ];

                array_push($arrProj['members'],$arrUser);
            }
            $MemberTotal = DB::table('d_project_member')->where('mp_project',$value->p_id)->count();
            $arrProj['member_total'] =  $MemberTotal;
            array_push($datas['project'], $arrProj);

        }
        return response()->json($datas);
    }

    public function index()
    {
        $data = DB::table('d_project_member')->join('d_project','mp_project','p_id')->where('mp_user',Auth::user()->us_id)->orderBy('p_id','ASC')->get();
        $datas = array();

        foreach($data as $value){
            $arr = [
                'id'    => $value->p_id,
                'title' => $value->p_name,
                'start' => $value->p_timestart,
                'end'   => $value->p_timeend,
                'status' => $value->p_status
            ];
            array_push($datas,$arr);
        }
        return response()->json($datas);
    }

    public function create_project(Request $request){
        DB::beginTransaction();
        try {
        $idProject = DB::table('d_project')->max('p_id') + 1;
        DB::table('d_project')->insert([
            'p_id' => $idProject,
            'p_name' => $request->nama_project,
            'p_creator' => Auth::user()->us_id,
            'p_timestart' => Carbon::parse($request->time_start),
            'p_timeend' => Carbon::parse($request->time_end),
            'p_status' => 'Open',
            'p_created' => Carbon::now('Asia/Jakarta'),
            'p_updated' => Carbon::now('Asia/Jakarta'),
        ]);

        DB::table('d_project_member')->insert([
            'mp_project' => $idProject,
            'mp_user' => Auth::user()->us_id,
            'mp_role' => '1',
            'mp_created' => Carbon::now('Asia/Jakarta'),
            'mp_updated' => Carbon::now('Asia/Jakarta'),
        ]);

        DB::commit();
        return response()->json([
            'status' => 'success',
            'idproject' => $idProject,
        ]);

        } catch (Exception $e) {
            return $e;
        }
        
    }
    public function detail_project(Request $request){

        $getMember = DB::table('d_project_member')
                    ->join('m_users','mp_user','us_id')
                    ->join('m_roles','mp_role','r_id')
                    ->where('mp_project',$request->project)
                    ->orderBy('mp_role','ASC')
                    ->get();

        $getTodo = DB::table('d_todolist')
                    ->where('tl_project',$request->project)
                    ->get();
        $project = DB::table('d_project')->where('p_id',$request->project)->first();

        $statusKita = DB::table('d_project_member')->where('mp_user',Auth::user()->us_id)->where('mp_project',$request->project)->first();

        return response()->json([
            'member' => $getMember,
            'todo' => $getTodo,
            'project' => $project,
            'statuskita' => $statusKita,
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
            $cekMember = DB::table('d_project_member')->where('mp_user',$cekUser->us_id)->where('mp_project',$request->project)->first();
            $cekTodo = DB::table('d_todolist')->where('tl_project',$request->project)->get();
            if($cekMember == null){
                DB::table('d_project_member')->insert([
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
                        'tlr_own' => 'P',
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
        if($request->allday == '0'){
            $dateStart = Carbon::parse($request->tanggal_awal)->format('Y-m-d H:i:s');
            $dateEnd = Carbon::parse($request->tanggal_akhir)->format('Y-m-d H:i:s');
        }else{
            $dateStart = Carbon::parse($request->tanggal_awal)->setTime(00, 00, 00);
            $dateEnd = Carbon::parse($request->tanggal_akhir)->setTime(23, 59, 59);
        }
        DB::table('d_todolist')->insert([
            'tl_id' => $maxIdTodo,
            'tl_category' => '1',
            'tl_project' => $request->project,
            'tl_title' => $request->nama_todo,
            'tl_desc' => $request->deskripsi,
            'tl_status' => 'Open',
            'tl_progress' => 0,
            'tl_allday' => $request->allday,
            'tl_planstart' => $dateStart,
            'tl_planend' => $dateEnd,
            'tl_created' => Carbon::now('Asia/Jakarta'),
            'tl_updated' => Carbon::now('Asia/Jakarta'),
        ]);

        $getMember = DB::table('d_project_member')->where('mp_project',$request->project)->get();
        foreach ($getMember as $key => $value) {
            DB::table('d_todolist_roles')->where('tlr_users',$value->mp_user)->where('tlr_todolist',$maxIdTodo)->delete();
            DB::table('d_todolist_roles')->insert([
                'tlr_users' => $value->mp_user,
                'tlr_todolist' => $maxIdTodo,
                'tlr_role' => $value->mp_role,
                'tlr_own' => 'P',
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
        DB::table('d_project_member')->where('mp_project',$request->project)->where('mp_user',$request->member)->delete();

        $getTodo = DB::table('d_todolist')->where('tl_project',$request->project)->get();

        foreach ($getTodo as $key => $value) {
            DB::table('d_todolist_roles')->where('tlr_users',$request->member)->where('tlr_todolist',$value->tl_id)->where('tlr_own','P')->delete();
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

        $getMember = DB::table('d_project_member')->where('mp_project',$request->project)->get();

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
        DB::table('d_project_member')->where('mp_user',$request->member)->where('mp_project',$request->project)->update([
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
      public function update_status_todo_project(Request $request){
     DB::beginTransaction();
        try {
        DB::table('d_todolist')->where('tl_id',$request->todo)->update([
            'tl_status' => $request->status,
        ]);

        DB::commit();
        return response()->json([
            'status' => 'success',
        ]);

        } catch (Exception $e) {
            return $e;
        }         
    }
    public function getdata_project(Request $request){
        $primary =
         DB::table('d_project_member')->join('d_project','mp_project','p_id')->where('mp_user',Auth::user()->us_id)->groupBy('p_id');
        if($request->filter == null){
            $project = $primary->where('p_name','LIKE', $request->filter .'%')->get();
        }else{
            $project = $primary->get();
        }
        return response()->json([
            'project' => $project,
        ]);

    }
    public function filter_detail_project(Request $request){
         $getMember = DB::table('d_project_member')
                    ->join('m_users','mp_user','us_id')
                    ->join('m_roles','mp_role','r_id')
                    ->where('mp_project',$request->project)
                    ->where('us_name','LIKE', $request->filter .'%')
                    ->get();

        $getTodo = DB::table('d_todolist')
                    ->where('tl_project',$request->project)
                    ->where('tl_title','LIKE', $request->filter .'%')
                    ->get();

        return response()->json([
            'member' => $getMember,
            'todo' => $getTodo,
        ]);
    }
    public function detail_project_all(Request $request){
        $Project = DB::table('d_project')->where('p_id',$request->project)->first();
        $Todo = DB::table('d_todolist')
                ->leftJoin('d_todolist_important',function($join){
                    $join->on('d_todolist.tl_id','=','d_todolist_important.tli_todolist')
                        ->where('d_todolist_important.tli_users',Auth::user()->us_id);
                })
                ->where('tl_project',$request->project)
                ->get();
        $Member = DB::table('d_project_member')
                ->where('mp_project',$request->project)
                ->join('m_users','mp_user','us_id')
                ->get();
        $statusKita = DB::table('d_project_member')->where('mp_project',$request->project)->where('mp_user',Auth::user()->us_id)->first();

        $ProgressTodo = 0;
        $ProgressProject = 0.00;
        if(count($Todo) != null){
            foreach ($Todo as $key => $value) {

                $ProgressTodo += (int)$value->tl_progress;

            }

            $ProgressProject = round($ProgressTodo / count($Todo), 2);
        }
        

        return response()->json([
            'project' => $Project,
            'todo' => $Todo,
            'member' => $Member,
            'progressproject' => $ProgressProject,
            'statusKita' => $statusKita,
        ]);
    }
    public function detail_member_project(Request $request){
        $Member = DB::table('d_project_member')
                 ->join('m_users','mp_user','us_id')
                 ->where('mp_user',$request->member)
                 ->where('mp_project',$request->project)
                 ->first();
        return response()->json($Member);
    }
    public function update_data_project(Request $request){
        DB::beginTransaction();
        try {
                
            DB::table('d_project')->where('p_id',$request->project)->update([
                'p_name' => $request->nama_project,
                'p_timestart' => Carbon::parse($request->tanggal_awal),
                'p_timeend' => Carbon::parse($request->tanggal_akhir),
                'p_desc' => $request->deskripsi_project,
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
    }
    public function started_project(Request $request){
        DB::beginTransaction();
        try {
            
            switch ($request->type) {
                case 'baru mengerjakan':
                    DB::table('d_project')->where('p_id',$request->project)->update([
                        'p_status' => 'Working',
                    ]);
                    break;
                case 'pending mengerjakan':
                    DB::table('d_project')->where('p_id',$request->project)->update([
                        'p_status' => 'Pending',
                    ]);
                    break;
                case 'selesai mengerjakan':
                    DB::table('d_project')->where('p_id',$request->project)->update([
                        'p_status' => 'Finish',
                    ]);
                    break;
                case 'mulai mengerjakan lagi':
                    DB::table('d_project')->where('p_id',$request->project)->update([
                        'p_status' => 'Working',
                    ]);
                    break;
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
    }
    public function delete_project(Request $request){
        DB::beginTransaction();
        try {
            
            DB::table('d_project')->where('p_id',$request->project)->delete();

            $dataMemberProject = DB::table('d_project_member')->where('mp_project',$request->project)->delete();

            $todoProject = DB::table('d_todolist')->where('tl_project',$request->project)->get();

            foreach ($todoProject as $key => $value) {

                DB::table('d_todolist_action')->where('tla_todolist',$value->tl_id)->delete();

                DB::table('d_todolist_ready')->where('tlr_todolist',$value->tl_id)->delete();

                DB::table('d_todolist_done')->where('tld_todolist',$value->tl_id)->delete();

                DB::table('d_todolist_normal')->where('tln_todolist',$value->tl_id)->delete();

                DB::table('d_todolist_important')->where('tli_todolist',$value->tl_id)->delete();

                DB::table('d_todolist_roles')->where('tlr_todolist',$value->tl_id)->delete();

                DB::table('d_todolist_timeline')->where('tlt_todolist',$value->tl_id)->delete();

                DB::table('d_todolist_attachment')->where('tla_todolist',$value->tl_id)->delete();

            }

            DB::table('d_todolist')->where('tl_project',$request->project)->delete();

            DB::commit();
            return response()->json([
                'status' => 'success',
            ]);

        } catch (Exception $e) {
            DB::rollback();
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
