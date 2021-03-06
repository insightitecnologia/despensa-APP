<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Stock;
use App\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Collection;

class StockController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    function index()
    {
        $stock = Stock::where('stock.user_id', Auth::user()->user_id)
                ->join('products','stock.product_id','=','products.product_id')
                ->orderBy('products.name')
                ->get();
        return view('stock.list')->with('stock', $stock);
    }
    
    function create()
    {
        $products = Product::with('unit')->where('user_id', Auth::user()->user_id)->whereNotIn('product_id', function($query){
            $query->select('product_id');
            $query->from('stock');
            $query->where('user_id', Auth::user()->user_id);
        })->orderBy('name', 'ASC')->get();

        return view('stock.create')->with('products', $products);
    }
    
    function store(Request $request)
    {
        foreach ($request->products as $key => $value) {
                $stock = new Stock;
                $stock->product_id = $value;
                $stock->min = $request->mins[$key];
                $stock->current = 0;
                $stock->user_id = Auth::user()->user_id;
                $stock->save();
        }
        
        return Redirect::to('stock');
    }
    function update($id, Request $request) {
        $valid = validator($request->all(),[
            'min' => 'required|numeric|min:0',
        ]);
        
        if($valid->fails())
            return Redirect::to('/stock/'.$id.'/edit')->withErrors($valid);

        $stock = Stock::find($id);
        $stock->min = $request->min;
        $stock->save();

        return Redirect::to('stock');
    }
    function edit($id) {
        $stock = Stock::find($id);
        
        return view('stock.edit')->with('stock', $stock);
    }
    function show($id) {
        $stock = Stock::find($id);
        
        return view('stock.show')->with('stock', $stock);
    }
    function delete($id) {
        $stock = Stock::find($id);
        $stock->delete();

        return Redirect::to('stock');        
    }
}
