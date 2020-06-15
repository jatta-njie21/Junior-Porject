<?php
session_start();
if (!array_key_exists('cart_id',$_SESSION)) {
    if (\App\Models\Cart::where(['status' => 'not-checked-out','user_id' => 0])->exists()) {

        session_unset();
        session_destroy();

        $carts = \App\Models\Cart::where(['status' => 'not-checked-out','user_id' => 0])->get();

        foreach ($carts as $cart) {
            if (\App\Models\LineItem::where('cart_id',$cart->id)->exists()) {
                \App\Models\LineItem::where('cart_id',$cart->id)->delete();
            }
            $cart->delete();
        }

    }
}
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/account', function() {
  if (\Illuminate\Support\Facades\Auth::guest()) {
      return redirect('/login');
  }else {
    $departments = \App\Models\CategoryDepartment::all();
    $carts = \App\Models\Cart::where(['user_id' => \Illuminate\Support\Facades\Auth::user()->id,'status' => 'checked-out'])->orderBy('id','desc')->get();

    return view('pages.account', compact('departments','carts'));
  }
});

Route::get('/order/line-items/{id}', function($id) {
  if (\Illuminate\Support\Facades\Auth::guest()) {
      return redirect('/login');
  }else {
      $departments = \App\Models\CategoryDepartment::all();
      $cart = \App\Models\Cart::where(['user_id' => \Illuminate\Support\Facades\Auth::user()->id,'status' => 'checked-out','id' => $id])->orderBy('id','desc')->first();
      // dd($cart);
      if ($cart) {
        $line_items = \App\Models\LineItem::where('cart_id',$cart->id)->get();
        $grand_total = \App\Models\LineItem::where('cart_id',$cart->id)->sum('total_price');
        $total_discount = \App\Models\LineItem::where('cart_id',$cart->id)->sum('discount');
        $cart->total_price = $grand_total;
        $cart->total_discount = $total_discount;
        $cart->save();

        return view('pages.order_items', compact('departments', 'cart','line_items'));
      }
      else {
        $status = 'No items in order.';
        return view('pages.order_items', compact('departments','status'));
      }

  }
});

Route::get('/department/{id}', function ($id) {
    $department = \App\Models\CategoryDepartment::find($id);
    $tab_cat1 = \App\Models\Category::where('department_id',$department->id)->first();
    $tab_cats = \App\Models\Category::where('department_id',$department->id)->skip(1)->take(4)->get();
    $tab1_cat_products = \App\Models\Product::where('category','like',"%\"{$tab_cat1->id}\"%")->get();

    $departments = \App\Models\CategoryDepartment::all();

    return view('pages.department_categories', compact('departments','department','tab_cat1','tab_cats','tab1_cat_products'));
});

Route::get('/category/{id}',function ($id) {
    $departments = \App\Models\CategoryDepartment::all();
    $categories = \App\Models\Category::all();
    $found_category = \App\Models\Category::find($id);
    $products = \App\Models\Product::where('category','like',"%\"{$found_category->id}\"%")->orderBy('id', 'desc')->get();
    $brands = \App\Models\Product::groupby('brand')->distinct()->get();
    return view('pages.products',compact('departments','categories','found_category','products','brands'));
});

Route::get('/product/{id}', function ($id) {
    $departments = \App\Models\CategoryDepartment::all();
    $categories = \App\Models\Category::all();
    $found_product = \App\Models\Product::find($id);

    $tab_cat1 = \App\Models\Category::all()->first();
    $tab_cats = \App\Models\Category::skip(1)->take(3)->get();
    $tab1_cat_products = \App\Models\Product::where('category','like',"%\"{$tab_cat1->id}\"%")->get();
    $recommended_first_slide = \App\Models\Product::orderBy('id', 'desc')->take(3)->get();
    $recommended_second_slide = \App\Models\Product::orderBy('id', 'desc')->skip(3)->take(3)->get();
    $recommended_third_slide = \App\Models\Product::orderBy('id', 'desc')->skip(6)->take(3)->get();
    $brands = \App\Models\Product::groupby('brand')->distinct()->get();
    return view('pages.product',compact('departments','categories','found_product',
        'tab_cat1','tab_cats','tab1_cat_products','recommended_third_slide','recommended_second_slide',
        'recommended_first_slide','brands'));
});

Route::get('/cart', function () {
    if (\Illuminate\Support\Facades\Auth::guest()) {
        $departments = \App\Models\CategoryDepartment::all();
        if (array_key_exists('cart_id',$_SESSION)) {
            $cart = \App\Models\Cart::where(['user_id' => 0,'status' => 'not-checked-out'])->orderBy('id','desc')->first();
            if ($cart) {
                $line_items = \App\Models\LineItem::where('cart_id',$cart->id)->get();
                $grand_total = \App\Models\LineItem::where('cart_id',$cart->id)->sum('total_price');
                $total_discount = \App\Models\LineItem::where('cart_id',$cart->id)->sum('discount');
                $cart->total_price = $grand_total;
                $cart->total_discount = $total_discount;
                $cart->save();


                return view('pages.cart', compact('departments', 'cart','line_items'));
            }
            else {
                $status = 'No items in cart.';
                return view('pages.cart', compact('departments','status'));
            }
        }
        else {
            $status = 'No items in cart.';
            return view('pages.cart', compact('departments','status'));
        }
    }else {
        $departments = \App\Models\CategoryDepartment::all();

        $cart = \App\Models\Cart::where(['user_id' => \Illuminate\Support\Facades\Auth::user()->id,'status' => 'not-checked-out'])->orderBy('id','desc')->first();
        if ($cart) {
          $line_items = \App\Models\LineItem::where('cart_id',$cart->id)->get();
          $grand_total = \App\Models\LineItem::where('cart_id',$cart->id)->sum('total_price');
          $total_discount = \App\Models\LineItem::where('cart_id',$cart->id)->sum('discount');
          $cart->total_price = $grand_total;
          $cart->total_discount = $total_discount;
          $cart->save();


          return view('pages.cart', compact('departments', 'cart','line_items'));
        }
        else {
          $status = 'No items in cart.';
          return view('pages.cart', compact('departments','status'));
        }

    }
});

Route::post('/cart/update-cart', function (\Illuminate\Http\Request $request) {
    $line_item_id = $request->input('id');
    $kind = $request->input('kind');
    if ($kind == 'inc') {
        $line_item = \App\Models\LineItem::find($line_item_id);
        $line_item->quantity = $line_item->quantity + 1;
        $line_item->total_price = $line_item->unit_price * $line_item->quantity;
        $line_item->save();
        return response()->json([
            'res' => $line_item->quantity
        ]);
    }else {
        $line_item = \App\Models\LineItem::find($line_item_id);
        $line_item->quantity = $line_item->quantity-1;
        $line_item->total_price = $line_item->unit_price * $line_item->quantity;
        $line_item->save();
        return response()->json([
            'res' => $line_item->quantity
        ]);
    }
});

Route::get('/check_out_cart', function () {
    return redirect('/cart');
});

Route::post('/check-out', function (\Illuminate\Http\Request $request) {
    if (\Illuminate\Support\Facades\Auth::guest()) {
        return view('auth.register');
    }else {
        if ($request->input('country') == 'no_select' || $request->input('region') == 'no_select') {
          $status = 'Please Select Your Country and Region';
          return redirect('/cart')->with(['status' => $status]);
        }
        $departments = \App\Models\CategoryDepartment::all();

        $cart = \App\Models\Cart::where(['user_id' => \Illuminate\Support\Facades\Auth::user()->id,'status' => 'not-checked-out'])->first();

        $line_items = \App\Models\LineItem::where('cart_id',$cart->id)->get();
        $grand_total = \App\Models\LineItem::where('cart_id',$cart->id)->sum('total_price');
        $total_discount = \App\Models\LineItem::where('cart_id',$cart->id)->sum('discount');
        $cart->total_price = $grand_total;
        $cart->total_discount = $total_discount;

        $country = $request->input('country');
        $region = $request->input('region');
        $country_name = '';
        if ($country == 'gmb')
          $country_name = 'Gambia';
        else
          $country_name = 'Senegal';

        $tax_rate = 0;
        $region_name = '';
        if ($region == 'g_b') {
          $tax_rate = 10;
          $region_name = "Greater Banjul";
        }
        elseif ($region == 'w_c_r'){
          $tax_rate = 15;
          $region_name = "West Coast Region";
        }
        elseif ($region == 'u_r_r'){
          $tax_rate = 20;
          $region_name = "Upper River Region";
        }
        elseif ($region == 'l_r_r'){
          $tax_rate = 20;
          $region_name = "Lower River Region";
        }
        elseif ($region == 'dk'){
          $tax_rate = 30;
          $region_name = "Dakar";
        }
        $tax = ($tax_rate / 100) * $grand_total;
        $cart->tax = $tax;
        $cart->save();

        $user_id = \Illuminate\Support\Facades\Auth::user()->id;
        $billing_info = new \App\Models\BillingInfo();
        $billing_info->user_id = $user_id;
        $billing_info->state = $region_name;
        $billing_info->country = $country_name;
        $billing_info->save();

        return view('pages.checkout', compact('departments', 'cart','line_items','tax_rate'));
    }
});

Route::post('/process_order', function (\Illuminate\Http\Request $request) {

    if (\Illuminate\Support\Facades\Auth::guest()) {
        return redirect('/login');
    }
    else {
        $user_id = \Illuminate\Support\Facades\Auth::user()->id;
        $cart_id = $request->input('cart_id');
        $total_price = $request->input('total_price');
        $company = $request->input('company');
        $email = $request->input('email');
        $name = $request->input('name');
        $address = $request->input('address');
        $zip_code = $request->input('zip_code');
        $confirm_pwd = $request->input('confirm_pwd');
        $phone = $request->input('phone');
        $mobile_phone = $request->input('mobile_phone');
        $fax = $request->input('fax');
        $message = $request->input('message');
        $payment_method = $request->input('payment_method');
        $shipping_to_billing = $request->input('shipping_to_billing');

        $cart = \App\Models\Cart::find($cart_id);
        $order = new \App\Models\Order();
        $order->cart_id = $cart->id;
        $order->order_id = 110011;
        $order->date = new \DateTime();
        $order->address = $address;
        $order->status = 'pending';

        if ($order->save()) {
            $billing_info = \App\Models\BillingInfo::where('user_id',$user_id)->first();
            $billing_info->order_id = $order->id;
            $billing_info->company = $company;
            $billing_info->email = $email;
            $billing_info->name = $name;
            $billing_info->address = $address;
            $billing_info->zip_code = $zip_code;
            $billing_info->phone = $phone;
            $billing_info->mobile_phone = $mobile_phone;
            $billing_info->fax = $fax;
            $billing_info->shipping_order = $message;
            $billing_info->payment_method = $payment_method;
            $billing_info->shipping_to_billing = $shipping_to_billing;

            if ($billing_info->save()) {
                $cart->status = 'checked-out';
                $cart->save();
		
		$amount_in_usd = (($cart->total_price + $cart->tax) / 47);		
		
                $gateway = \Omnipay\Omnipay::create('Migs_ThreeParty');
                $gateway->setMerchantId('458537030767');
                $gateway->setMerchantAccessCode('D932F5D0');
                $gateway->setSecureHash('BD138C3F8EE630E56A0C254B0F04F0B3');
                try {
                    $response = $gateway->purchase(array(
                        'amount' => $amount_in_usd, // amount should be greater than zero
                        'currency' => 'GMD',
                        'transactionId' => '1011010', // replace this for your reference # such as invoice reference #
                        'returnURL' => 'https://suncreekonline.com'))->send();

                    if ($response->isRedirect()) {
                        $url = $response->getRedirectUrl(); // do whatever with the return url
                        return response()->json([
                            'res' => $url
                        ]);
                    } else {
                        // payment failed: display message to customer
                        return response()->json([
                            'res' => $response->getMessage()
                        ]);
                    }
                } catch (\Exception $e) {
                    // internal error, log exception and display a generic message to the customer
//                    echo $e;
//                    exit('Sorry, there was an error processing your payment. Please try again later.');
                    return response()->json([
                        'res' => 'Sorry, there was an error processing your payment. Please try again later.'
                    ]);
                }
//                return response()->json([
//                    'res' => 'successful'
//                ]);
            }
        }

        return response()->json([
            'res' => 'error'
        ]);
    }
});

Route::post('/check-out/process-order/',function (\Illuminate\Http\Request $request) {
   if (\Illuminate\Support\Facades\Auth::guest()) {
       return redirect('/login');
   }
   else {
       $user_id = \Illuminate\Support\Facades\Auth::user()->id;
       $cart_id = $request->getContent('cart_id');
       $total_price = $request->getContent('total_price');
       $company = $request->getContent('company');
       $email = $request->getContent('email');
       $name = $request->getContent('name');
       $address = $request->getContent('address');
       $zip_code = $request->getContent('zip_code');
       $confirm_pwd = $request->getContent('confirm_pwd');
       $phone = $request->getContent('phone');
       $mobile_phone = $request->getContent('mobile_phone');
       $fax = $request->getContent('fax');
       $message = $request->getContent('message');
       $payment_method = $request->getContent('payment_method');
       $shipping_to_billing = $request->getContent('shipping_to_billing');

       $cart = \App\Models\Cart::find($cart_id);
       $order = new \App\Models\Order();
       $order->cart_id = $cart_id;
       $order->order_id = 110011;
       $order->date = new \DateTime();
       $order->address = $address;
       $order->status = 'pending';

       if ($order->save()) {
           $billing_info = \App\Models\BillingInfo::where('user_id',$user_id)->first();
           $billing_info->order_id = $order->id;
           $billing_info->company = $company;
           $billing_info->email = $email;
           $billing_info->name = $name;
           $billing_info->address = $address;
           $billing_info->zip_code = $zip_code;
           $billing_info->phone = $phone;
           $billing_info->mobile_phone = $mobile_phone;
           $billing_info->fax = $fax;
           $billing_info->shipping_order = $message;
           $billing_info->payment_method = $payment_method;
           $billing_info->shipping_to_billing = $shipping_to_billing;

           if ($billing_info->save()) {
             $cart->status = 'checked-out';
             $cart->save();
             return response()->json([
                 'res' => 'successful'
             ]);
           }
       }

       return response()->json([
           'res' => 'error'
       ]);
       }
       $gateway = \Omnipay\Omnipay::create('Migs_ThreeParty');
       $gateway->setMerchantId('MerchantId');
       $gateway->setMerchantAccessCode('MerchantAccessCode');
       $gateway->setSecureHash('SecureHash');

       try {
           $response = $gateway->purchase(array(
               'amount' => '10.00', // amount should be greater than zero
               'currency' => 'AED',
               'transactionId' => 'refnodata', // replace this for your reference # such as invoice reference #
               'returnURL' => 'http://yourdomain.com/returnPage.php'))->send();

           if ($response->isRedirect()) {
               $url = $response->getRedirectUrl(); // do whatever with the return url
           } else {
               // payment failed: display message to customer
               echo $response->getMessage();
           }
       } catch (\Exception $e) {
           // internal error, log exception and display a generic message to the customer
           echo $e;
           exit('Sorry, there was an error processing your payment. Please try again later.');
       }

});

Route::get('/cart/delete-item/{id}', function ($id) {
    $line_item = \App\Models\LineItem::find($id);
    $line_item->delete();
    return redirect('/cart');
});

Route::post('/add-to-cart', function (\Illuminate\Http\Request $request) {
    $id = $request->input('id');
    $product = \App\Models\Product::find($id);

    if (\Illuminate\Support\Facades\Auth::guest()) {
        $inactive_time = 3600;
        ini_set('session.gc_maxlifetime',$inactive_time);
      $cart = \App\Models\Cart::where(['status' => 'not-checked-out','user_id' => 0])->exists();
      if($cart == false) {
          $new_cart = new \App\Models\Cart();
          $_SESSION['cart_id'] = $new_cart->id;
          $_SESSION['session_id'] = session_id();
          if ($new_cart->save()) {
              $line_item = new \App\Models\LineItem();
              $line_item->product_id = $product->id;
              $line_item->cart_id = $new_cart->id;
              $line_item->quantity = 1;
              $line_item->unit_price = $product->price;
              $line_item->discount = 0.0;
              $line_item->total_price = $product->price;

              if ($line_item->save()) {
                  $line_item_count = \App\Models\LineItem::where('cart_id',\App\Models\Cart::where(['user_id'=>0,'status'=>'not-checked-out'])->first()->id)->count();

                  return response()->json([
                      'res' => 'success',
                      'item_count' => $line_item_count,
                      'session_data' => $_SESSION['cart_id']
                  ]);
              }else {
                  return response()->json([
                      'res' => 'error'
                  ]);
              }
          }
      }else {
          $cart = \App\Models\Cart::where(['status' => 'not-checked-out','user_id' => 0])->first();
          $_SESSION['cart_id'] = $cart->id;
          $_SESSION['session_id'] = session_id();
          if (\App\Models\LineItem::where(['cart_id' => $cart->id,'product_id' => $product->id])->exists()) {
              return response()->json([
                  'res' => 'found_product'
              ]);
          }
          $line_item = new \App\Models\LineItem();
          $line_item->product_id = $product->id;
          $line_item->cart_id = $cart->id;
          $line_item->quantity = 1;
          $line_item->unit_price = $product->price;
          $line_item->discount = 0.0;
          $line_item->total_price = $product->price;

          if ($line_item->save()) {
              $line_item_count = \App\Models\LineItem::where('cart_id',\App\Models\Cart::where(['user_id' => 0,'status'=>'not-checked-out'])->first()->id)->count();

              return response()->json([
                  'res' => 'success',
                  'item_count' => $line_item_count,
                  'session_data' => $_SESSION['cart_id']
              ]);
          }else {
              return response()->json([
                  'res' => 'error'
              ]);
          }
      }
    }
    else {
      $user_id = \Illuminate\Support\Facades\Auth::user()->id;
      $cart = \App\Models\Cart::where(['user_id' => $user_id,'status' => 'not-checked-out'])->exists();
      if($cart == false) {
          $new_cart = new \App\Models\Cart();
          $new_cart->user_id = $user_id;

          if ($new_cart->save()) {
              $line_item = new \App\Models\LineItem();
              $line_item->product_id = $product->id;
              $line_item->cart_id = $new_cart->id;
              $line_item->quantity = 1;
              $line_item->unit_price = $product->price;
              $line_item->discount = 0.0;
              $line_item->total_price = $product->price;

              if ($line_item->save()) {
                  $line_item_count = \App\Models\LineItem::where('cart_id',\App\Models\Cart::where(['user_id'=>Auth::user()->id,'status'=>'not-checked-out'])->first()->id)->count();
                  return response()->json([
                      'res' => 'success',
                      'item_count' => $line_item_count
                  ]);
              }else {
                  return response()->json([
                      'res' => 'error'
                  ]);
              }
          }
      }else {
          $cart = \App\Models\Cart::where(['user_id' => $user_id,'status' => 'not-checked-out'])->first();
          if (\App\Models\LineItem::where(['cart_id' => $cart->id,'product_id' => $product->id])->exists()) {
              return response()->json([
                  'res' => 'found_product'
              ]);
          }
          $line_item = new \App\Models\LineItem();
          $line_item->product_id = $product->id;
          $line_item->cart_id = $cart->id;
          $line_item->quantity = 1;
          $line_item->unit_price = $product->price;
          $line_item->discount = 0.0;
          $line_item->total_price = $product->price;

          if ($line_item->save()) {
              $line_item_count = \App\Models\LineItem::where('cart_id',\App\Models\Cart::where(['user_id'=>Auth::user()->id,'status'=>'not-checked-out'])->first()->id)->count();
              return response()->json([
                  'res' => 'success',
                  'item_count' => $line_item_count
              ]);
          }else {
              return response()->json([
                  'res' => 'error'
              ]);
          }
      }
    }
});

Route::get('/tab_category/products', 'HomeController@get_tab_products');

Route::get('/create-account', function () {
    return view('auth.register');
});

Route::get('/car-rentals', function () {
    $departments = \App\Models\CategoryDepartment::all();
    $categories = \App\Models\Category::all();

    $car_rentals = \App\Models\CarRental::all();
    $brands = \App\Models\Product::groupby('brand')->distinct()->get();
    return view('pages.car_rentals', compact('departments','categories','car_rentals','brands'));
});

Route::get('/car-rentals/car-for-rent/{id}', function($id) {
    $departments = \App\Models\CategoryDepartment::all();
    $categories = \App\Models\Category::all();

    $car_rental = \App\Models\CarRental::find($id);
    $brands = \App\Models\Product::groupby('brand')->distinct()->get();
    $car_rentals = \App\Models\CarRental::whereNotIn('id',array($id))->get();
    return view('pages.show_car', compact('departments','categories','car_rental','brands','car_rentals'));
});

Route::get('/rent-car/{id}', function($id) {
  if (\Illuminate\Support\Facades\Auth::guest()) {
      return redirect('/login');
  }
  else {
    $departments = \App\Models\CategoryDepartment::all();
    $categories = \App\Models\Category::all();

    $car = \App\Models\CarRental::find($id);
    $brands = \App\Models\Product::groupby('brand')->distinct()->get();

    return view('pages.car_for_rent',compact('departments','categories','brands','car'));
  }
});

Route::post('/process-car-rental', function(\Illuminate\Http\Request $request) {
    $car_id = $request->input('car');
    $user_id = \Illuminate\Support\Facades\Auth::user()->id;
    $address = $request->input('address');
    $duration = $request->get('duration');
    $date_needed = $request->input('date_needed');

    $car = \App\Models\CarRental::find($car_id);

    $exploded_duration = explode(" ",$duration);
    $total_price = 0;
    if ($exploded_duration[1] == 'hours' || $exploded_duration[1] == 'hour') {
        $total_price = $car->price_per_hour * $exploded_duration[0];
    }
    else if ($exploded_duration[1] == 'days' || $exploded_duration[1] == 'day') {
        $total_price = $car->price_per_day * $exploded_duration[0];
    }
    else {
      $total_price = $car->price_per_day * ($exploded_duration[0] * 7);
    }

    $new_rental = new \App\Models\Rental();

    $new_rental->user_id = $user_id;
    $new_rental->car_id = $car->id;
    $new_rental->address = $address;
    $new_rental->duration = $duration;
    $new_rental->date = $date_needed;
    $new_rental->total_price = $total_price;
    $new_rental->discount = 0;

    if ($new_rental->save()) {
      $status = 'Car rental processed successfully, you will be emailed with confirmation';
      return redirect('/car-rentals')->with(['status' => $status]);
    }
});

Route::get('/rentals', function() {
  if (\Illuminate\Support\Facades\Auth::guest()) {
      return redirect('/login');
  }
  else {
    $departments = \App\Models\CategoryDepartment::all();

    $rentals = \App\Models\Rental::where(['user_id' => \Illuminate\Support\Facades\Auth::user()->id])->orderBy('id','desc')->get();
    // dd($rentals);

    return view('pages.rentals',compact('departments','categories','rentals'));
  }
});

Route::get('/product/brand/{brand}', function($brand) {
    $departments = \App\Models\CategoryDepartment::all();
    $categories = \App\Models\Category::all();
    $products = \App\Models\Product::where('brand',$brand)->get();
    $brands = \App\Models\Product::groupby('brand')->distinct()->get();
    return view('pages/product_brand', compact('products','departments','categories','brand','brands'));
});

Route::get('/contact', function () {
    $departments = \App\Models\CategoryDepartment::all();
    return view('pages.contact', compact('departments'));
});
/* ================== Homepage + Admin Routes ================== */

require __DIR__.'/admin_routes.php';
