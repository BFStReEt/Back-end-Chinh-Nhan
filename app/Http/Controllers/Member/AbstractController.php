<?php

namespace App\Http\Controllers\Member;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


abstract class AbstractController extends Controller
{

    //method
    protected  $model;
    public function __construct()
    {
        $this->model = $this->getModel();
    }


    protected  function getModel() {
        return $this->model;
    }

    // properties
    public function index()
    {
         $record = $this->model->all();
         return $record;
    }

    public function create()
    {
        return view('create');
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $record = $this->model->create($data);
        return $record;
    }

    public function show(Request $request, $id)
    {
        $data = $request->all();
        $record = $this->model->findOrFail($id);
        return $record;
    }


    public function edit($id)
    {
        $record = $this->model->findOrFail($id);
        return $record;
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $record = $this->model->findOrFail($id);
        return $record;
    }

    public function destroy($id)
    {
        $record = $this->model->findOrFail($id);
        $record->delete();
        return $record;
    }
}
