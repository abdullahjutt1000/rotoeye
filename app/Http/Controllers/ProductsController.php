<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Job;
use App\Models\Machine_sleeve;
use App\Models\Product_sleeve;
use App\Models\MaterialCombination;
use App\Models\Process;
use App\Models\Machine;
use App\Models\Process_Structure;
use App\Models\Record;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use App\Models\Users;
use App\Models\Product;
use App\Models\Sleeve;

use Illuminate\Support\Facades\Validator;
use PhpSpec\Exception\Exception;

class ProductsController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($id)
	{
		$data['path'] = Route::getFacadeRoot()->current()->uri();
		if(Session::get('rights') == 0){
			$data['layout'] = 'web-layout';
		}
		elseif(Session::get('rights') == 1){
			$data['layout'] = 'admin-layout';
		}
		elseif(Session::get('rights') == 2){
			$data['layout'] = 'power-user-layout';
		}
		$data['user'] = Users::find(Session::get('user_name'));
		$data['products'] = Product::all();
        $data['machine'] = Machine::find(Crypt::decrypt($id));
		return view('roto.products', $data);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$data['path'] = Route::getFacadeRoot()->current()->uri();
		if(Session::get('rights') == 0){
			$data['layout'] = 'web-layout';
		}
		elseif(Session::get('rights') == 1){
			$data['layout'] = 'admin-layout';
		}
		elseif(Session::get('rights') == 2){
			$data['layout'] = 'power-user-layout';
		}
		$data['user'] = Users::find(Session::get('user_name'));
		$data['materials'] = MaterialCombination::all();
		return view('roto.add-product', $data);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$product=array(
			"id"=>"id",
			"name"=>"Product Name",
			"material"=>"Product Material",
			"uom"=>"Unit of Measurement",
			"ups"=>"UPS",
			"col"=>"COL",
			"color_adh"=>"Color/Adhesive",
		);
		$validator=Validator::make($request->all(),
			[
				"id"=>"required",
				"name"=>"required",
				"material"=>"required",
				"uom"=>"required",
				"ups"=>"required",
				"col"=>"required",
				"color_adh"=>"required",
			]);
		$validator->setAttributeNames($product);
		if($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}
		else {
			try {
				$product = new Product();
				$product->id = $request->input('id');
				$product->name = $request->input('name');
				$product->material_combination_id = $request->input('material');
				$product->uom = $request->input('uom');
				$product->ups = $request->input('ups');
				$product->col = $request->input('col');
				$product->color_adh = $request->input('color_adh');
				$product->save();

				Session::flash('success', 'A new product has been added.');
				return Redirect('products');
			} catch (QueryException $e) {
				Session::flash('error', 'Please contact System Administrator with code ' . '<strong>' . $e->getCode() . '</strong>');
				return Redirect::back()->withInput();
			}
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($product_id,$id)
	{
		$data['path'] = Route::getFacadeRoot()->current()->uri();
		if(Session::get('rights') == 0){
			$data['layout'] = 'web-layout';
		}
		elseif(Session::get('rights') == 1){
			$data['layout'] = 'admin-layout';
		}
		elseif(Session::get('rights') == 2){
			$data['layout'] = 'power-user-layout';
		}
		$data['user'] = Users::find(Session::get('user_name'));
		$data['product'] = Product::find($product_id);
        $data['machine'] = Machine::find(Crypt::decrypt($id));

		$machine_sleeves = Machine_sleeve::where('machine_id','=',$data['machine']->id)->get();
        $data['product_sleeves']  = Product_sleeve::where('product_id','=',$product_id)->get();

		if(count($machine_sleeves) > 0){
		    $data['machine_sleeve'] = $machine_sleeves;
		}
		else{
            $data['machine_sleeve']=Null;
        }

		$data['materials'] = MaterialCombination::all();

		return view('roto.update-product', $data);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $id)
	{
		$product=array(
			"id"=>"id",
			"name"=>"Product Name",
			"material"=>"Product Material",
			"uom"=>"Unit of Measurement",
			"ups"=>"UPS",
			"col"=>"COL",
			"srw"=>"Slitted Reel Width",
			"trimWidth"=>"Trim Width",
			"gsm"=>"GSM",
			"thickness"=>"Thickness",
			"density"=>"Density",
			"color_adh"=>"Color/Adhesive",
		);
		$validator=Validator::make($request->all(),
			[
				"id"=>"required",
				"name"=>"required",
				"material"=>"required",
                "thickness" => "required_with_all:density",
			]);
		$validator->setAttributeNames($product);
		if($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}
		else {
			try {
				$product = Product::find($id);
				$product->uom = $request->input('uom');
				$product->ups = $request->input('ups');
				$product->col = $request->input('col')==''? NULL: $request->input('col')/1000;
				$product->slitted_reel_width = $request->input('srw')==''? NULL:$request->input('srw')/1000;
				$product->trim_width = $request->input('trimWidth')==''? NULL:$request->input('trimWidth')/1000;
				$product->gsm = $request->input('gsm')==''? NULL:$request->input('gsm')/1000;
				$product->thickness = $request->input('thickness')==''? NULL:$request->input('thickness')/1000000;
				$product->density = $request->input('density');
				$product->color_adh = $request->input('color_adh') ;
				$product->save();

				$sleeve_id = $request->input('sleeve_id');
                if(isset($sleeve_id)){
                    Product_sleeve::updateOrCreate([
                        'product_id' => $product->id,
                    ],[
                        'product_id' => $product->id,
                        'sleeve_id' => $request->input('sleeve_id'),
                    ]);
                }

				Session::flash('success', 'The product has been updated.');
                return Redirect::back()->withInput();
			} catch (QueryException $e) {
				Session::flash('error', 'Please contact System Administrator with code ' . '<strong>' . $e->getMessage() . '</strong>');
				return Redirect::back()->withInput();
			}
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		try{
			MaterialCombination::destroy($id);
			Session::flash('success','Product deleted Successfully');
			return redirect(URL::to('products'));
		}
		catch(QueryException $e){
			Session::flash('error','Please contact System Administrator with code '.'<strong>'.$e->getCode().'</strong>');
			return Redirect::back();
		}
	}

	function csvToArray($filename = '', $delimiter = ',')
	{
		$header = null;
		$data = array();
		if (($handle = fopen($filename, 'r')) !== false)
		{
			while (($row = fgetcsv($handle, 5000, $delimiter)) !== false)
			{
				$header = ['new_number', 'old_number', 'name', 'material', 'process'];
				$data[] = array_combine($header, $row);
			}
			fclose($handle);
		}
		return $data;
	}

	public function uploadProducts(){
		try {
			$notFoundProducts = 0;
			$notFoundMaterials = 0;
			$notFoundProcess = 0;

			DB::beginTransaction();

			Schema::table('products',function (Blueprint $table) {
				$table->string('temporary_id', 10)->after('id')->nullable();
			});

			$file = asset('assets/products-correction.csv');
			$products = $this->csvToArray($file);
			for ($i = 1; $i < count($products); $i ++) {
				$product = Product::find($products[$i]['old_number']);
				if(isset($product)){
					$material = MaterialCombination::where('name', '=', $products[$i]['material'])->first();
					if(!isset($material)){
						$notFoundMaterials+=1;
						$material = new MaterialCombination();
						$material->name = $products[$i]['material'];
						$material->nominal_speed = 250;
						$material->save();
					}
					$process = Process::where('name', '=', $products[$i]['process'])->first();
					if(!isset($process)){
						$notFoundProcess+=1;
						$process = new Process();
						$process->name = $products[$i]['process'];
						$process->section = NULL;
						$process->save();
					}

					$jobs = Job::where('product_id', '=', $product->id)->get();
					if(count($jobs) > 0){
						foreach($jobs as $job){
							$job->product_id = $products[$i]['new_number'];
							$job->save();
						}
					}
					$productAlreadyExist = Product::find($products[$i]['new_number']);
					if(!isset($productAlreadyExist)){
						if($product->id == $products[$i]['new_number']){
							$product->name = $products[$i]['name'];
							$product->material_combination_id = $material->id;
							$product->save();
						}
						else{
							$product->temporary_id = $products[$i]['new_number'];
							$product->name = $products[$i]['name'];
							$product->material_combination_id = $material->id;
							$product->save();
						}
					}
					else{
						$productAlreadyExist->name = $products[$i]['name'];
						$productAlreadyExist->material_combination_id = $material->id;
						$productAlreadyExist->save();
						$product = $productAlreadyExist;
					}
					$alreadyExistStructure = Process_Structure::where('product_id', '=', $product->id)
						->where('material_combination_id', '=', $material->id)
						->where('process_id', '=', $process->id)
						->get();
					if(count($alreadyExistStructure) == 0){
						$processStructure = new Process_Structure();
						$processStructure->process_id = $process->id;
						$processStructure->material_combination_id =  $material->id;
						if(isset($product->temporary_id)){
							$processStructure->product_id = $product->temporary_id;
						}
						else{
							$processStructure->product_id = $product->id;
						}
						$processStructure->save();
					}
				}
				else{
					$notFoundProducts+=1;
					echo 'Not Found '.$products[$i]['old_number'];
					Log::info('Not Found '.$products[$i]['old_number']);
					echo '<br>';
				}
			}
			$products = Product::whereNotNull('temporary_id')->get();
			if(count($products) > 0){
				foreach($products as $product){
					$product->id = $product->temporary_id;
					$product->save();
				}
			}
			Schema::table('products',function (Blueprint $table) {
				$table->dropColumn('temporary_id');
			});
			echo 'Not Found Products: '.$notFoundProducts;
			echo '<br>';
			echo 'Not Found Process: '.$notFoundProcess;
			echo '<br>';
			echo 'Not Found Materials: '.$notFoundMaterials;

//			DB::commit();
		}
		catch(Exception $e){
			Session::flash("error", "Opps!! Something went Wrong");
			return Redirect::back()->withInput();
		}
	}

	public function getJobs($id){
		$jobs = Job::where('product_id', '=', $id)->get();
		if(count($jobs) > 0){
			return json_encode($jobs, 200);
		}
		else{
			return json_encode('Not Found', 500);
		}
	}

	public function getProcesses($id){
		$product = Product::find($id);
		if(count($product) > 0){
			return json_encode($product->process, 200);
		}
		else{
			return json_encode('Not Found', 500);
		}
	}

	public function sapUpdate(){
		$entityBody = file_get_contents('php://input');
		Log::info('---------------SAP Pushed-------------------');
		Log::info($entityBody);
		Log::info('---------------SAP Pushed-------------------');

	}

	public function allProducts(){
        $data['path'] = Route::getFacadeRoot()->current()->uri();
        if(Session::get('rights') == 0){
            $data['layout'] = 'web-layout';
        }
        elseif(Session::get('rights') == 1){
            $data['layout'] = 'admin-layout';
        }
        elseif(Session::get('rights') == 2){
            $data['layout'] = 'power-user-layout';
        }
        $data['user'] = Users::find(Session::get('user_name'));

        //return Session::get('machine');
        $data['machine'] = Session::get('machine');
        $data['products'] = Product::all();
        //return $data;
	    return view('roto.all-products', $data);
    }
}
