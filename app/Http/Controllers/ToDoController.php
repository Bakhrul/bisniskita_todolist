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
        $data = Todo::orderBy('tl_planstart','ASC')->leftJoin('d_todolist_roles','tlr_todolist','tl_id')->where('tlr_users',Auth::user()->us_id);

        if ($type == "1") {

            $data = $data->where("tl_planstart" ,'<=', Carbon::today()->setTime(23,59,59))
            ->where("tl_planend" ,'>',Carbon::today())
            ->limit(5)->get();

        }else if ($type == "2") {
            $data = $data->where(function($q){
            $q->whereBetween("tl_planstart" ,[Carbon::tomorrow(),Carbon::today()->addDays(4)])
            ->orWhere("tl_planend" ,'>',Carbon::tomorrow())
            ->Where('tl_planend','<=', Carbon::today()->addDays(4)->setTime(23,59,59));
            })->limit(5)->get();
        }else if ($type == "3") {
            $data = $data->where(function($q){
              $q->whereBetween("tl_planstart" ,[Carbon::today()->addDays(5),Carbon::today()->addDays(13)])
            ->orWhere("tl_planend" ,'>',Carbon::today()->addDays(5))
            ->Where('tl_planend','<=', Carbon::today()->addDays(13)->setTime(23,59,59));
            })->limit(5)->get();
        }else if ($type == "4") {
            $data = $data->where(function($q){
              $q->whereBetween("tl_planstart" ,[Carbon::today()->addDays(13),Carbon::today()->addDays(32)])
            ->orWhere("tl_planend" ,'>',Carbon::today()->addDays(13))
            ->Where('tl_planend','<=', Carbon::today()->addDays(32)->setTime(23,59,59));
            })->limit(5)->get();
        }
        else if ($type == "5") {
            $data = $data->where(function($q){
              $q->whereBetween("tl_planstart" ,[Carbon::today()->addDays(33),Carbon::today()->addDays(62)])
            ->orWhere("tl_planend" ,'>',Carbon::today()->addDays(33))
            ->Where('tl_planend','<=', Carbon::today()->addDays(62)->setTime(23,59,59));
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
            ];
            array_push($todos,$arr);
        }
        return response()->json($todos);
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
            $todo->tl_planstart     = $request->planstart;
            $todo->tl_planend       = $request->planend;
            $todo->tl_exestart      = null;
            $todo->tl_exeend        = null;
            $todo->tl_created       = Carbon::now();
            $todo->tl_updated       = Carbon::now();
            $todo->save();
            DB::commit();
            return response()->json([
                'status' => 'success'
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
        

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
