<?php

namespace App\Http\Controllers;

use App\Todo;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

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

    public function index()
    {
        
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
            if ($request->category != null) {
            $todo->tl_category = $request->category;
            }
            if ($request->project != null) {
            $todo->tl_project = $request->project;
            }

            $todo->tl_title         = $request->title;
            $todo->tl_desc          = $request->desc;
            $todo->tl_status        = 'O';
            $todo->tl_progress      = 0;
            $todo->tl_planstart     = $request->planstart;
            $todo->tl_planend       = $request->planend;
            $todo->tl_exestart      = Carbon::now();
            $todo->tl_exeend        = Carbon::now();
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
