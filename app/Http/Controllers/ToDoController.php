<?php

namespace App\Http\Controllers;

use App\Todo;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use Auth;

class ToDoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function category()
    {
        $data = DB::table('m_category')->orderBy('c_name','ASC')->get();
        $datas = array();
        foreach ($data as $key => $value) {
            $arr = [
                'id' => $value->c_id,
                'name'=> $value->c_name
            ];
            array_push($datas, $arr);
        }
        return response()->json($datas);
    }

    public function index($index)
    {
          setlocale(LC_TIME, 'IND');

        $type = $index;
        $todos = array();
        $data = Todo::orderBy('tl_planstart','ASC')
                ->leftJoin('d_todolist_roles','tlr_todolist','tl_id')
                ->leftJoin('d_todolist_important',function($join){
                    $join->on('d_todolist.tl_id','=','d_todolist_important.tli_todolist')
                        ->where('d_todolist_important.tli_users',Auth::user()->us_id);
                })
                ->where('tlr_users',Auth::user()->us_id);
        if ($type == "1") {
            $data = $data->where("tl_planstart" ,'<=', Carbon::now('Asia/Jakarta')->setTime(23,59,59))
            ->where("tl_planend" ,'>',Carbon::now('Asia/Jakarta'))
            ->limit(5)->get();
        }else if ($type == "2") {
            $data = $data->where(function($q){
            $q->whereBetween("tl_planstart" ,[Carbon::tomorrow(),Carbon::now('Asia/Jakarta')->addDays(4)])
            ->orWhere("tl_planend" ,'>',Carbon::tomorrow())
            ->Where('tl_planend','<=', Carbon::now('Asia/Jakarta')->addDays(4)->setTime(23,59,59));
            })->limit(5)->get();
        }else if ($type == "3") {
            $data = $data->where(function($q){
              $q->whereBetween("tl_planstart" ,[Carbon::now('Asia/Jakarta')->addDays(5),Carbon::now('Asia/Jakarta')->addDays(13)])
            ->orWhere("tl_planend" ,'>',Carbon::now('Asia/Jakarta')->addDays(5))
            ->Where('tl_planend','<=', Carbon::now('Asia/Jakarta')->addDays(13)->setTime(23,59,59));
            })->limit(5)->get();
        }else if ($type == "4") {
            $data = $data->where(function($q){
              $q->whereBetween("tl_planstart" ,[Carbon::now('Asia/Jakarta')->addDays(13),Carbon::now('Asia/Jakarta')->addDays(32)])
            ->orWhere("tl_planend" ,'>',Carbon::now('Asia/Jakarta')->addDays(13))
            ->Where('tl_planend','<=', Carbon::now('Asia/Jakarta')->addDays(32)->setTime(23,59,59));
            })->limit(5)->get();
        }
        else if ($type == "5") {
            $data = $data->where(function($q){
              $q->whereBetween("tl_planstart" ,[Carbon::now('Asia/Jakarta')->addDays(33),Carbon::now('Asia/Jakarta')->addDays(62)])
            ->orWhere("tl_planend" ,'>',Carbon::now('Asia/Jakarta')->addDays(33))
            ->Where('tl_planend','<=', Carbon::now('Asia/Jakarta')->addDays(62)->setTime(23,59,59));
            })->limit(5)->get();
        }

        foreach ($data as $key => $value) {
           $arr = [
                'id'    => $value->tl_id,
                'title' => $value->tl_title,
                'start' => $value->tl_planstart,
                'end'   => $value->tl_planend,
                'status' => $value->tl_status,
                'category' => $value->tl_category,
                'statuspinned' => $value->tli_todolist,
            ];
            array_push($todos,$arr);
        }
        return response()->json($todos);
    }
    public function actionpinned_todo(Request $request){
        DB::BeginTransaction();
        try {
            $cekTodo = DB::table('d_todolist_important')->where('tli_todolist',$request->todolist)->where('tli_users',Auth::user()->us_id)->first();
            $status = '';
            if($cekTodo == null){
                DB::table('d_todolist_important')->insert([
                    'tli_users' => Auth::user()->us_id,
                    'tli_todolist' => $request->todolist,
                    'tli_created' => Carbon::now(),
                ]);
                $status = 'tambah';
            }else{
              DB::table('d_todolist_important')->where('tli_todolist',$request->todolist)->where('tli_users',Auth::user()->us_id)->delete();
              $status = 'hapus';
            }

            DB::commit();
            return response()->json([
                'status' => $status,
            ]);
        } catch (Exception $e) {
            return $e;
        }
    }
    public function todolist_berbintang(Request $request){
        $type = $request->filter;

        $Todo = DB::table('d_todolist_important')
                ->join('d_todolist_roles',function($join){
                    $join->on('d_todolist_important.tli_todolist','=','d_todolist_roles.tlr_todolist')
                        ->where('d_todolist_roles.tlr_users',Auth::user()->us_id);
                })
                ->join('d_todolist','tli_todolist','tl_id')
                ->where('tl_title','LIKE', $request->search .'%');
        if ($type == "1") {
            $Todo = $Todo->where("tl_planstart" ,'<=', Carbon::now('Asia/Jakarta')->setTime(23,59,59))
            ->where("tl_planend" ,'>',Carbon::now('Asia/Jakarta'))
            ->get();

        }else if ($type == "2") {
            $Todo = $Todo->where(function($q){
            $q->whereBetween("tl_planstart" ,[Carbon::tomorrow(),Carbon::now('Asia/Jakarta')->addDays(4)])
            ->orWhere("tl_planend" ,'>',Carbon::tomorrow())
            ->Where('tl_planend','<=', Carbon::now('Asia/Jakarta')->addDays(4)->setTime(23,59,59));
            })->get();
        }else if ($type == "3") {
            $Todo = $Todo->where(function($q){
              $q->whereBetween("tl_planstart" ,[Carbon::now('Asia/Jakarta')->addDays(5),Carbon::now('Asia/Jakarta')->addDays(13)])
            ->orWhere("tl_planend" ,'>',Carbon::now('Asia/Jakarta')->addDays(5))
            ->Where('tl_planend','<=', Carbon::now('Asia/Jakarta')->addDays(13)->setTime(23,59,59));
            })->get();
        }else if ($type == "4") {
            $Todo = $Todo->where(function($q){
              $q->whereBetween("tl_planstart" ,[Carbon::now('Asia/Jakarta')->addDays(13),Carbon::now('Asia/Jakarta')->addDays(32)])
            ->orWhere("tl_planend" ,'>',Carbon::now('Asia/Jakarta')->addDays(13))
            ->Where('tl_planend','<=', Carbon::now('Asia/Jakarta')->addDays(32)->setTime(23,59,59));
            })->get();
        }
        else if ($type == "5") {
            $Todo = $Todo->where(function($q){
              $q->whereBetween("tl_planstart" ,[Carbon::now('Asia/Jakarta')->addDays(33),Carbon::now('Asia/Jakarta')->addDays(62)])
            ->orWhere("tl_planend" ,'>',Carbon::now('Asia/Jakarta')->addDays(33))
            ->Where('tl_planend','<=', Carbon::now('Asia/Jakarta')->addDays(62)->setTime(23,59,59));
            })->get();
        }
        return response()->json([
            'todo' => $Todo,
            'counttodo' => count($Todo),
        ]);
    }
    public function search_todo_project(Request $request){
         $type = $request->filter;

        $Todo = DB::table('d_todolist')
                ->join('d_todolist_roles',function($join){
                    $join->on('d_todolist.tl_id','=','d_todolist_roles.tlr_todolist')
                        ->where('d_todolist_roles.tlr_users',Auth::user()->us_id);
                })
                ->leftJoin('d_todolist_important',function($join){
                    $join->on('d_todolist.tl_id','=','d_todolist_important.tli_todolist')
                        ->where('d_todolist_important.tli_users',Auth::user()->us_id);
                })
                ->where('tl_title','LIKE', $request->search .'%');
        if ($type == "1") {
            $Todo = $Todo->where("tl_planstart" ,'<=', Carbon::now('Asia/Jakarta')->setTime(23,59,59))
            ->where("tl_planend" ,'>',Carbon::now('Asia/Jakarta'))
            ->get();

        }else if ($type == "2") {
            $Todo = $Todo->where(function($q){
            $q->whereBetween("tl_planstart" ,[Carbon::tomorrow(),Carbon::now('Asia/Jakarta')->addDays(4)])
            ->orWhere("tl_planend" ,'>',Carbon::tomorrow())
            ->Where('tl_planend','<=', Carbon::now('Asia/Jakarta')->addDays(4)->setTime(23,59,59));
            })->get();
        }else if ($type == "3") {
            $Todo = $Todo->where(function($q){
              $q->whereBetween("tl_planstart" ,[Carbon::now('Asia/Jakarta')->addDays(5),Carbon::now('Asia/Jakarta')->addDays(13)])
            ->orWhere("tl_planend" ,'>',Carbon::now('Asia/Jakarta')->addDays(5))
            ->Where('tl_planend','<=', Carbon::now('Asia/Jakarta')->addDays(13)->setTime(23,59,59));
            })->get();
        }else if ($type == "4") {
            $Todo = $Todo->where(function($q){
              $q->whereBetween("tl_planstart" ,[Carbon::now('Asia/Jakarta')->addDays(13),Carbon::now('Asia/Jakarta')->addDays(32)])
            ->orWhere("tl_planend" ,'>',Carbon::now('Asia/Jakarta')->addDays(13))
            ->Where('tl_planend','<=', Carbon::now('Asia/Jakarta')->addDays(32)->setTime(23,59,59));
            })->get();
        }
        else if ($type == "5") {
            $Todo = $Todo->where(function($q){
              $q->whereBetween("tl_planstart" ,[Carbon::now('Asia/Jakarta')->addDays(33),Carbon::now('Asia/Jakarta')->addDays(62)])
            ->orWhere("tl_planend" ,'>',Carbon::now('Asia/Jakarta')->addDays(33))
            ->Where('tl_planend','<=', Carbon::now('Asia/Jakarta')->addDays(62)->setTime(23,59,59));
            })->get();
        }
        
        $Project = DB::table('d_member_project')
                    ->join('d_project','mp_project','p_id')
                    ->where('p_name','LIKE', $request->search .'%')
                    ->where('mp_user',Auth::user()->us_id)
                    ->get();

        return response()->json([
            'todo' => $Todo,
            'counttodo' => count($Todo),
            'project' => $Project,
            'countproject' => count($Project),
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
        DB::BeginTransaction();
        try {
        
            $todo = new Todo;
            $project = null;
            if($request->category == '1'){
                $project = $request->project;
            }

            $todo->tl_title         = $request->title;
            $todo->tl_category      = $request->category;
            $todo->tl_project       = $project;
            $todo->tl_desc          = $request->desc;
            $todo->tl_status        = 'Open';
            $todo->tl_progress      = 0;
            $todo->tl_planstart     = Carbon::parse($request->planstart)->format('Y-m-d H:i:s');
            $todo->tl_planend       = Carbon::parse($request->planend)->format('Y-m-d H:i:s');
            $todo->tl_exestart      = null;
            $todo->tl_exeend        = null;
            $todo->tl_created       = Carbon::now();
            $todo->tl_updated       = Carbon::now();
            $todo->save();

            DB::table('d_todolist_roles')
                ->insert([
                    'tlr_users'     => Auth::user()->us_id,
                    'tlr_todolist'  => $todo->tl_id,
                    'tlr_role'      => 1
                ]);
            DB::commit();
            return response()->json([
                'status' => 'success'
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
        
    }
    public function detail_todo(Request $request){
        $Todo = DB::table('d_todolist')
                ->where('tl_id',$request->todolist)
                ->first();

        $member = DB::table('d_todolist_roles')
                ->join('m_users','tlr_users','us_id')
                ->where('tlr_todolist',$request->todolist)
                ->get();

        $TodoActivity = DB::table('d_todolist_timeline')
                        ->join('m_users','tlt_user','us_id')
                        ->where('tlt_todolist',$request->todolist)
                        ->get();


        return response()->json([
            'todo' => $Todo,
            'member' => $member,
            'todo_activity' => $TodoActivity,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function show(Todo $todo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function edit(Todo $todo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Todo $todo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Todo $todo)
    {
        //
    }
}
