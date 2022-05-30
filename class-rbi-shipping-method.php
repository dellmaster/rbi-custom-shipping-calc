<?php
class RBI_Shipping_Method extends WC_Shipping_Method {
    /**
     * Constructor for your shipping class
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->id                 = 'rbi_shipping';
        $this->method_title       = __( 'Combined Shipping', 'rbi_shipping' );
        $this->method_description = __( 'Custom Shipping Method for RBI', 'rbi_shipping' );

        $this->availability = 'including';
        $this->countries = array(
          'PL', // Poland
          'Poland',
        );

        $this->init();

        $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
        $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Combined Shipping', 'rbi_shipping' );

        //START Price Settings
        $this->courier_price = isset( $this->settings['rbi_courier_price'] ) ? $this->settings['rbi_courier_price'] : 0;
        $this->small_pallet_price = isset( $this->settings['rbi_small_pallet_price'] ) ? $this->settings['rbi_small_pallet_price'] : 0;
        $this->big_pallet_price = isset( $this->settings['rbi_big_pallet_price'] ) ? $this->settings['rbi_big_pallet_price'] : 0;
        //END Price Settings

        //START Weight Settings
        $this->courier_max_weight = isset( $this->settings['rbi_courier_packet_max_weight'] ) ? $this->settings['rbi_courier_packet_max_weight'] : 0;
        $this->small_pallet_max_weight = isset( $this->settings['rbi_small_pallet_max_weight'] ) ? $this->settings['rbi_small_pallet_max_weight'] : 0;
        $this->big_pallet_max_weight = isset( $this->settings['rbi_big_pallet_max_weight'] ) ? $this->settings['rbi_big_pallet_max_weight'] : 0;
        //END Weight Settings

        //START Size Settings
        $this->courier_max_width = isset( $this->settings['rbi_courier_packet_max_width'] ) ? $this->settings['rbi_courier_packet_max_width'] : 0;
        $this->courier_max_height = isset( $this->settings['rbi_courier_packet_max_height'] ) ? $this->settings['rbi_courier_packet_max_height'] : 0;
        $this->courier_max_length = isset( $this->settings['rbi_courier_packet_max_length'] ) ? $this->settings['rbi_courier_packet_max_length'] : 0;

        $this->small_pallet_max_width = isset( $this->settings['rbi_small_pallet_max_width'] ) ? $this->settings['rbi_small_pallet_max_width'] : 0;
        $this->small_pallet_max_height = isset( $this->settings['rbi_small_pallet_max_height'] ) ? $this->settings['rbi_small_pallet_max_height'] : 0;
        $this->small_pallet_max_length = isset( $this->settings['rbi_small_pallet_max_length'] ) ? $this->settings['rbi_small_pallet_max_length'] : 0;

        $this->big_pallet_max_width = isset( $this->settings['rbi_big_pallet_max_width'] ) ? $this->settings['rbi_big_pallet_max_width'] : 0;
        $this->big_pallet_max_height = isset( $this->settings['rbi_big_pallet_max_height'] ) ? $this->settings['rbi_big_pallet_max_height'] : 0;
        $this->big_pallet_max_length = isset( $this->settings['rbi_big_pallet_max_length'] ) ? $this->settings['rbi_big_pallet_max_length'] : 0;
        //END Size Settings

        //Create Shipping Variants array
        $this->shipping_variant = array(
          'courier' => array(
            'price' =>   $this->courier_price,
            'max_weight' =>   $this->courier_max_weight,
            'max_width' =>   $this->courier_max_width,
            'max_height' =>   $this->courier_max_height,
            'max_length' =>   $this->courier_max_length,
          ),
          'small_pallet' => array(
            'price' =>   $this->small_pallet_price,
            'max_weight' =>   $this->small_pallet_max_weight,
            'max_width' =>   $this->small_pallet_max_width,
            'max_height' =>   $this->small_pallet_max_height,
            'max_length' =>   $this->small_pallet_max_length,
          ),
          'big_pallet' => array(
            'price' =>   $this->big_pallet_price,
            'max_weight' =>   $this->big_pallet_max_weight,
            'max_width' =>   $this->big_pallet_max_width,
            'max_height' =>   $this->big_pallet_max_height,
            'max_length' =>   $this->big_pallet_max_length,
          )
        );
    }

    /**
     * Init your settings
     *
     * @access public
     * @return void
     */
    function init() {
        // Load the settings API
        $this->init_form_fields();
        $this->init_settings();

        // Save settings in admin if you have any defined
        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    /**
     * Define settings field for this shipping
     * @return void
     */
    function init_form_fields() {
      $this->form_fields = array(
        'enabled' => array(
          'title' => __( 'Enable', 'rbi_shipping' ),
          'type' => 'checkbox',
          'description' => __( 'Enable this shipping type.', 'rbi_shipping' ),
          'default' => 'yes'
        ),

        'title' => array(
          'title' => __( 'Title', 'rbi_shipping' ),
          'type' => 'text',
          'description' => __( 'Title to be display on site', 'rbi_shipping' ),
          'default' => __( 'RBI Calc Shipping', 'rbi_shipping' )
        ),

        'free_delivery_min_sum' => array(
          'title' => __( 'Free delivery minimal Sum', 'rbi_shipping' ),
          'type' => 'number',
          'description' => __( 'Minimal sum of 1 product from Free Shipping category', 'rbi_shipping' ),
          'default' => 2000
        ),

        'free_delivery_category' => array(
          'title' => __( 'Free delivery category ID', 'rbi_shipping' ),
          'type' => 'number',
          'description' => __( 'Free delivery category ID', 'rbi_shipping' ),
          'default' => 0
        )
      );

    }

    /**
     * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
     *
     * @access public
     * @param mixed $package
     * @return void
     */
    public function calculate_shipping( $package ) {

      $order_shipping_content = array(
        'big_pallet' => 0,
        'small_pallet' => 0,
        'courier' => 0
      );
      //How we can get category IDs by shipping type??????
      /*$category_id_can_be_shipped = array(
        'courier' => array(1,2,3),
        'small_pallet' => array(4,5,6),
        'big_pallet' => array(7,8,9)
      );*/

      //Prepare arrays for pruducts by category
      $courier_packet_products = array();
      $small_pallet_products = array();
      $big_pallet_products = array();

      //separate products by category of shipping
      foreach ( $package['contents'] as  $values ) {
        $one_product = $values['data'];
        //$one_product_categories =  $one_product->get_category_ids();
        $product_max_size = product_max_size($one_product);
        if ($product_max_size > $this->small_pallet_max_length) {
          //need big pallet
          $big_pallet_products[] = $values;
        }
        elseif($product_max_size > $this->courier_max_length) {
          //need small pallet
          $small_pallet_products[] = $values;
        }
        else {
          // all other products we put in courier box
          $courier_packet_products[] = $values;
        }
      }

      //create item array
      $big_pallet_items = $this->create_items_array($big_pallet_products);
      $small_pallet_items = $this->create_items_array($small_pallet_products);
      $courier_packet_items = $this->create_items_array($courier_packet_products);

      $total_items_left = count($big_pallet_items) + count($small_pallet_items) + count($courier_packet_items);

      $need_big_pallet = 0;
      $need_small_pallet = 0;
      $need_courier_pack = 0;

      $left_weight_in_big_pallet_start = $this->shipping_variant['big_pallet']['max_weight'];
      $left_volume_in_big_pallet_start = ($this->shipping_variant['big_pallet']['max_width']/1000) * ($this->shipping_variant['big_pallet']['max_height']/1000) * ($this->shipping_variant['big_pallet']['max_length']/1000);

      $left_weight_in_small_pallet_start = $this->shipping_variant['small_pallet']['max_weight'];
      $left_volume_in_small_pallet_start = ($this->shipping_variant['small_pallet']['max_width']/1000) * ($this->shipping_variant['small_pallet']['max_height']/1000) * ($this->shipping_variant['small_pallet']['max_length']/1000);

      $left_weight_in_courier_pack = $this->shipping_variant['courier']['max_weight'];
      $left_volume_in_courier_pack = ($this->shipping_variant['courier']['max_width']/1000) * ($this->shipping_variant['courier']['max_height']/1000) * ($this->shipping_variant['courier']['max_length']/1000);

      while ($total_items_left > 0) {

        $left_weight_in_big_pallet = $left_weight_in_big_pallet_start;
        $left_volume_in_big_pallet = $left_volume_in_big_pallet_start;

        $left_weight_in_small_pallet = $left_weight_in_small_pallet_start;
        $left_volume_in_small_pallet = $left_volume_in_small_pallet_start;

        if (count($big_pallet_items)>0){
          //if we have big pallet items - put it at big pallet
          $big_pallet_items_sort_more_volume = $this->sort_products_put_more_volume($big_pallet_items);
          $put_in_big_pallet_response = $this->put_products_in_volume_and_weight($big_pallet_items_sort_more_volume, $left_weight_in_big_pallet, $left_volume_in_big_pallet);

          $big_pallet_items = $put_in_big_pallet_response['not_in_pack_items_array'];
          $left_weight_in_big_pallet = $put_in_big_pallet_response['weight_left'];
          $left_volume_in_big_pallet = $put_in_big_pallet_response['volume_left'];

          if (count($put_in_big_pallet_response['in_pack_items_array']) > 0) $need_big_pallet++;
          //if we put some products at the big pallet increase it

        }
        else {
          //if we dont use big pallet - we dont have space on it
          $left_weight_in_big_pallet = 0;
          $left_volume_in_big_pallet = 0;
        }

        if (count($small_pallet_items) > 0) {
          //if we have small pallet items then 1st step put it on big pallet free space
          $small_pallet_items_sort_more_volume = $this->sort_products_put_more_volume($small_pallet_items);
          $put_in_big_pallet_response = $this->put_products_in_volume_and_weight($small_pallet_items_sort_more_volume, $left_weight_in_big_pallet, $left_volume_in_big_pallet);

          $small_pallet_items = $put_in_big_pallet_response['not_in_pack_items_array'];
          $left_weight_in_big_pallet = $put_in_big_pallet_response['weight_left'];
          $left_volume_in_big_pallet = $put_in_big_pallet_response['volume_left'];

          if (count($small_pallet_items) > 0) {
            //if small pallet products left -  then 2nd step put it on small pallet free space
            $small_pallet_items_sort_more_weight = $this->sort_products_put_more_weight($small_pallet_items);
            $put_in_small_pallet_response = $this->put_products_in_volume_and_weight($small_pallet_items_sort_more_weight, $left_weight_in_small_pallet, $left_volume_in_small_pallet);

            $small_pallet_items = $put_in_small_pallet_response['not_in_pack_items_array'];
            $left_weight_in_small_pallet = $put_in_small_pallet_response['weight_left'];
            $left_volume_in_small_pallet = $put_in_small_pallet_response['volume_left'];

            if (count($put_in_small_pallet_response['in_pack_items_array']) > 0) $need_small_pallet++;
            //if we put some products to small pallet - increase it
          }

        }
        else {
          //if we dont use small pallet - we dont have space on it
          $left_weight_in_small_pallet = 0;
          $left_volume_in_small_pallet = 0;
        }

        if (count($courier_packet_items) > 0) {
          //if we have courier items then 1st step put it on big pallet free space
          $courier_packet_items_sort_more_volume = $this->sort_products_put_more_volume($courier_packet_items);
          $put_in_big_pallet_response = $this->put_products_in_volume_and_weight($courier_packet_items_sort_more_volume, $left_weight_in_big_pallet, $left_volume_in_big_pallet);

          $courier_packet_items = $put_in_big_pallet_response['not_in_pack_items_array'];
          //$left_weight_in_big_pallet = $put_in_small_pallet_response['weight_left'];
          //$left_volume_in_big_pallet = $put_in_small_pallet_response['volume_left'];

          if (count($courier_packet_items) > 0) {
            // if courier items left then 2nd step put courier items to small pallet free space
            $courier_packet_items_sort_more_weight = $this->sort_products_put_more_weight($courier_packet_items);
            $put_in_small_pallet_response = $this->put_products_in_volume_and_weight($courier_packet_items_sort_more_weight, $left_weight_in_small_pallet, $left_volume_in_small_pallet);

            $courier_packet_items = $put_in_big_pallet_response['not_in_pack_items_array'];

            if (count($courier_packet_items) > 0) {
              // if courier items left then 3rd step put courier items to courier packet
              //$courier_packet_items_sort_more_weight = $this->sort_products_put_more_weight($courier_packet_items);
              $put_in_courier_packet_response = $this->put_products_in_volume_and_weight($courier_packet_items, $left_weight_in_courier_pack, $left_volume_in_courier_pack);

              $courier_packet_items = $put_in_courier_packet_response['not_in_pack_items_array'];

              if (count($put_in_courier_packet_response['in_pack_items_array']) > 0) $need_courier_pack++;
            }
          }

        }
        else {
          // code...
        }

        $total_items_left = count($big_pallet_items) + count($small_pallet_items) + count($courier_packet_items);
      }

      //total we need
      $order_shipping_content = array(
        'big_pallet' => $need_big_pallet,
        'small_pallet' => $need_small_pallet,
        'courier' => $need_courier_pack
      );
      $total_shipping_price = $need_big_pallet * $this->shipping_variant['big_pallet']['price'] + $need_small_pallet * $this->shipping_variant['small_pallet']['price'] + $need_courier_pack * $this->shipping_variant['courier']['price'];

      $rate = array(
          'id' => $this->id,
          'label' => $this->title,
          'cost' => $total_shipping_price
      );

      $this->add_rate( $rate );

    }
///////////////////////////////////////////////////////
/////////////// Functions  ////////////////////////////
///////////////////////////////////////////////////////

    // count big pallets by weight of pruducts
    public function calc_pack_by_weight($items, $pack_max_weight) {
      $need_big_pallets = array();
      $total_weight = $this->calc_products_weight($big_pallet_products);

      $need_big_pallets['num'] = ceil($total_weight/$this->shipping_variant['big_pallet']['max_weight']);
      $need_big_pallets['total_weight'] = $total_weight;
      $need_big_pallets['weight_left'] = $need_big_pallets['num'] * $this->shipping_variant['big_pallet']['max_weight'] - $total_weight;

      return $need_big_pallets;
    }


    // count big pallets by volume of pruducts
    public function calc_pack_by_volume($big_pallet_products) {
      $need_big_pallets = array();
      $total_volume = $this->calc_products_volume($big_pallet_products);

      $big_pallet_max_volume = (($this->shipping_variant['big_pallet']['max_width']/1000) * ($this->shipping_variant['big_pallet']['max_height']/1000) * ($this->shipping_variant['big_pallet']['max_length']/1000));

      $need_big_pallets['num'] = ceil($total_weight/$big_pallet_max_volume);
      $need_big_pallets['total_volume'] = $total_volume;
      $need_big_pallets['volume_left'] = $need_big_pallets['num'] * $big_pallet_max_volume - $total_volume;

      return $need_big_pallets;
    }

    public function add_volume_to_product_array($products_list) {
      $result_array = array();
      foreach ($products_list as $one_value) {
        $one_product = $one_value['data'];

        $one_value['rbi_item_volume'] = ($one_product->get_width()/100) * ($one_product->get_height()/100) * ($one_product->get_length()/100);
        $result_array[] = $one_value;
      }

      return $result_array;
    }



    //создание массива с единицами товара
    public function create_items_array($products_list) {
      $items = array();
      $k = 0;
      foreach ($products_list as $one_value) {
        for ($i=0; $i < $one_value['quantity']; $i++) {
          $items[$k+$i] = $one_value;
        }
        $k = $k + $one_value['quantity'];
      }
      return $items;
    }

    // Try put items in weight and volume
    public function put_products_in_volume_and_weight($items, $free_weight, $free_volume)
    {
      $response_array = array();
      $in_pack_items_array = array();
      $not_in_pack_items_array = array();
      $space_left = 1;
      $weight_left = $free_weight;
      $volume_left = $free_volume;

      foreach ($items as $item) {
        $item_details = $item['data'];
        $item_volume = ($item_details->get_width()/100) * ($item_details->get_height()/100) * ($item_details->get_length()/100);
        if ((($free_weight - $item_details->get_weight()) >= 0) && ($free_volume - $item_volume) >= 0) {
          $in_pack_items_array[] = $item;
          $weight_left = $weight_left - $item_details->get_weight();
          $volume_left = $volume_left - $item_volume;
        }
        else {
          $not_in_pack_items_array[] = $item;
        }

      $response_array['in_pack_items_array'] = $in_pack_items_array;
      $response_array['not_in_pack_items_array'] = $not_in_pack_items_array;
      $response_array['weight_left'] = $weight_left;
      $response_array['volume_left'] = $volume_left;

      }

      return $response_array;
    }

    // calc products weight from given products list
    public function calc_products_weight($products_list) {
      $total_weight = 0;
      foreach ($products_list as $one_value) {
        $one_product = $one_value['data'];
        $total_weight = $total_weight  + $one_product->get_weight() * $one_value['quantity'];
      }

      return $total_weight;
    }



    // calc products volume from given products list
    public function calc_products_volume($products_list) {
      $total_volume = 0;
      foreach ($products_list as $one_value) {
        $one_product = $one_value['data'];
          $total_volume = $total_volume + $one_value['quantity'] * (($one_product->get_width()/100) * ($one_product->get_height()/100) * ($one_product->get_length()/100));
      }

      return $total_volume;
    }

    //calc kg|m3 rate for each product
    public function products_weight_and_volume_rate($products_list) {
      $new_products = array();
      foreach ($products_list as $one_value) {
        $one_product = $one_value['data'];
        $product_volume =  ($one_product->get_width()/100) * ($one_product->get_height()/100) * ($one_product->get_length()/100);
        $product_weight = $one_product->get_weight();
        $one_value['weight_volume_rate'] = $product_weight/$product_volume;
        $new_products[] = $one_value;
      }

      //$new_products_sort = usort($new_products, );
      return $new_products;
    }

    public function sort_products_put_more_volume($products_list) {
      usort($products_list, [RBI_Shipping_Method::class, "sort_products_put_more_volume_callback"]);
      return $products_list;
    }

    public function sort_products_put_more_weight($products_list) {
      usort($products_list, [RBI_Shipping_Method::class, "sort_products_put_more_weight_callback"]);
      return $products_list;
    }



    public function sort_products_put_more_volume_callback($product_a, $product_b) {

      if ($product_a['weight_volume_rate'] == $product_b['weight_volume_rate']) {
          return 0;
      }
      return ($product_a['weight_volume_rate'] < $product_b['weight_volume_rate']) ? -1 : 1;
    }

    public function sort_products_put_more_weight_callback($product_a, $product_b) {

      if ($product_a['weight_volume_rate'] == $product_b['weight_volume_rate']) {
          return 0;
      }
      return ($product_a['weight_volume_rate'] > $product_b['weight_volume_rate']) ? -1 : 1;
    }


    //calculation of the number of pallets by weight and pallet type
    public function calc_pallets_by_weight($products_list, $pallet_type) {
      $need_pallets = array();
      $total_weight = $this->calc_products_weight($products_list);

      $need_pallets['float'] = $total_weight/$this->shipping_variant[$pallet_type]['max_weight'];
      $need_pallets['num'] = ceil($need_pallets['float']);
      $need_pallets['total_weight'] = $total_weight;
      $need_pallets['left'] = $need_pallets['num'] * $this->shipping_variant[$pallet_type]['max_weight'] - $total_weight;

      return $need_pallets;
    }

    //calculation of the number of pallets by volume and pallet type
    public function calc_pallets_by_volume($products_list, $pallet_type) {
      $need_big_pallets = array();
      $total_volume = $this->calc_products_volume($products_list);

      $pallet_max_volume = (($this->shipping_variant[$pallet_type]['max_width']/1000) * ($this->shipping_variant[$pallet_type]['max_height']/1000) * ($this->shipping_variant[$pallet_type]['max_length']/1000));

      $need_big_pallets['float'] = $total_volume/$pallet_max_volume;
      $need_big_pallets['num'] = ceil($need_big_pallets['float']);
      $need_big_pallets['total_volume'] = $total_volume;
      $need_big_pallets['left'] = $need_big_pallets['num'] * $pallet_max_volume - $total_volume;

      return $need_big_pallets;
    }

    // count big pallets by weight of pruducts
    public function calc_big_pallets_by_weight($big_pallet_products) {
      $need_big_pallets = array();
      $total_weight = $this->calc_products_weight($big_pallet_products);

      $need_big_pallets['num'] = ceil($total_weight/$this->shipping_variant['big_pallet']['max_weight']);
      $need_big_pallets['total_weight'] = $total_weight;
      $need_big_pallets['weight_left'] = $need_big_pallets['num'] * $this->shipping_variant['big_pallet']['max_weight'] - $total_weight;

      return $need_big_pallets;
    }


    // count big pallets by volume of pruducts
    public function calc_big_pallets_by_volume($big_pallet_products) {
      $need_big_pallets = array();
      $total_volume = $this->calc_products_volume($big_pallet_products);

      $big_pallet_max_volume = (($this->shipping_variant['big_pallet']['max_width']/1000) * ($this->shipping_variant['big_pallet']['max_height']/1000) * ($this->shipping_variant['big_pallet']['max_length']/1000));

      $need_big_pallets['num'] = ceil($total_weight/$big_pallet_max_volume);
      $need_big_pallets['total_volume'] = $total_volume;
      $need_big_pallets['volume_left'] = $need_big_pallets['num'] * $big_pallet_max_volume - $total_volume;

      return $need_big_pallets;
    }

    // check product max size with courier package max length, if the package fits - return TRUE
    public function courier_check_size($product_in_cart) {

      $courier_max_length = $this->courier_max_length;

      if (product_max_size($product_in_cart) <= $courier_max_length) return true;

      return false;

      //if ($courier_max_weight > $_product){}

    }

    // check product max size with small pallet max length, if the package fits - return TRUE
    public function small_pallet_check_size($product_in_cart) {

      $small_pallet_max_length = $this->small_pallet_max_length;

      if (product_max_size($product_in_cart) <= $small_pallet_max_length) return true;

      return false;

    }

    // check product max size with big pallet max length, if the package fits - return TRUE
    public function big_pallet_check_size($product_in_cart) {

      $big_pallet_max_length = $this->big_pallet_max_length;

      if ($this->product_max_size($product_in_cart) <= $big_pallet_max_length) return true;

      return false;

    }

    // returns the type of minimal packing required for this order
    public function check_size($all_products_in_cart) {
      $k = 1;
      $shipping_methods_priority = array( // min is better
        1 => 'courier',
        2 => 'small_pallet',
        3 => 'big_pallet'
      );

      $check_size_array = array();

      foreach ($all_products_in_cart as $one_product) {
        if (courier_check_size($one_product)) $check_size_array[$k] = 1;
        if (small_pallet_check_size($one_product)) $check_size_array[$k] = 2;
        if (big_pallet_check_size($one_product)) $check_size_array[$k] = 3;

        $k++;
      }
      return $shipping_methods_priority[max($check_size_array)];

    }

    // "rotate" the product and find it max size.
    public function product_max_size($one_product) {
      max($one_product->get_length(), $one_product->get_width(), $one_product->get_height());
    }

    public function pack_max_volume($pack_type) {
      return ($this->shipping_variant[$pack_type]['max_legth']/1000) * ($this->shipping_variant[$pack_type]['max_width']/1000) * ($this->shipping_variant[$pack_type]['max_height']/1000);
    }

}